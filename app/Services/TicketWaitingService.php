<?php

namespace App\Services;

use App\Enums\TicketCommentVisibility;
use App\Enums\TicketStatus;
use App\Enums\TicketWaitEndReason;
use App\Enums\TicketWaitType;
use App\Models\Ticket;
use App\Models\TicketWait;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketWaitingService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TicketCommentService $comments,
        private readonly TicketSlaService $sla,
        private readonly WorkingCalendarService $calendar,
        private readonly OperationalPolicyService $policies,
        private readonly TicketNotificationService $notifications,
    ) {}

    /**
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function requestInformation(User $actor, Ticket $ticket, string $question, array $fileGroups = []): Ticket
    {
        $this->authorization->authorize($actor, 'requestInformation', $ticket, 'ticket.wait.requester');
        $now = Carbon::now(config('app.timezone'));
        $calendar = $this->policies->currentCalendar();
        $waitDays = (int) ($this->policies->settings()['requester_wait_working_days'] ?? 3);
        $dueAt = $this->calendar->deadlineAfterWorkingDays($now, $waitDays, $calendar);

        $result = $this->database->transaction(function () use ($actor, $ticket, $question, $fileGroups, $now, $dueAt, $waitDays, $calendar): Ticket {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.wait.requester');
            $this->authorization->authorize($actor, 'requestInformation', $lockedTicket, 'ticket.wait.requester');
            $this->assertNoActiveWait($lockedTicket, $actor, 'ticket.wait.requester');

            if (! in_array($lockedTicket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true)
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'ticket.wait.requester', 'Tiket tidak tersedia untuk meminta informasi.');
            }

            $fromStatus = $lockedTicket->status;
            $wait = $lockedTicket->waits()->create([
                'kind' => TicketWaitType::Requester,
                'started_by_id' => $actor->getKey(),
                'from_status' => $fromStatus,
                'from_assignee_id' => $lockedTicket->assigned_to_id,
                'from_assigned_tier' => $lockedTicket->assigned_tier,
                'started_at' => $now,
                'due_at' => $dueAt,
                'metadata' => [
                    'working_days' => $waitDays,
                    'calendar_id' => $calendar?->getKey(),
                    'calendar_version' => $calendar?->version,
                ],
            ]);
            $comment = $this->comments->create(
                $lockedTicket,
                $actor,
                TicketCommentVisibility::Public,
                $question,
                $fileGroups,
            );
            $lockedTicket->forceFill(['status' => TicketStatus::MenungguPemohon])->save();
            $this->recordStatus($lockedTicket, $fromStatus, TicketStatus::MenungguPemohon, 'ticket.wait.requester', $actor, $question, [
                'wait_id' => $wait->getKey(),
                'comment_id' => $comment->getKey(),
                'due_at' => $dueAt->toIso8601String(),
            ], $now);
            $this->sla->pause($lockedTicket, 'requester_wait', $now);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.wait.requester',
                $lockedTicket,
                'Tiket menunggu informasi dari Pemohon.',
                null,
                ['wait_id' => $wait->getKey(), 'due_at' => $dueAt->toIso8601String()],
            );

            return $lockedTicket->fresh(['requester', 'assignee', 'activeWait']);
        });

        if ($result->requester !== null
            && (int) $result->requester->getKey() !== (int) $actor->getKey()) {
            $waitId = $result->activeWait?->getKey();
            $this->notifications->send(
                $result,
                'requester_information',
                'Informasi tambahan diperlukan',
                "Agen meminta informasi tambahan untuk tiket {$result->ticket_number}.",
                [$result->requester_id],
                "ticket:{$result->getKey()}:requester-wait:".($waitId ?? 'unknown'),
            );
        }

        return $result;
    }

    /**
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function requesterReply(User $actor, Ticket $ticket, string $body, array $fileGroups = []): Ticket
    {
        $this->authorization->authorize($actor, 'replyRequester', $ticket, 'ticket.reply.requester');
        $now = Carbon::now(config('app.timezone'));

        [$result, $assignee, $commentId] = $this->database->transaction(function () use ($actor, $ticket, $body, $fileGroups, $now): array {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.reply.requester');
            $this->authorization->authorize($actor, 'replyRequester', $lockedTicket, 'ticket.reply.requester');
            $wait = $lockedTicket->waits()->active()->requester()->latest('started_at')->lockForUpdate()->first();

            if ($wait === null || $lockedTicket->status !== TicketStatus::MenungguPemohon) {
                $this->deny($actor, $lockedTicket, 'ticket.reply.requester', 'Tiket tidak lagi menunggu balasan Pemohon.');
            }

            $comment = $this->comments->create(
                $lockedTicket,
                $actor,
                TicketCommentVisibility::Public,
                $body,
                $fileGroups,
            );
            $assigneeId = $wait->from_assignee_id ?: $lockedTicket->assigned_to_id;
            $assignedTier = $wait->from_assigned_tier ?: $lockedTicket->assigned_tier;
            $wait->forceFill([
                'ended_at' => $now,
                'ended_by_id' => $actor->getKey(),
                'end_reason' => TicketWaitEndReason::RequesterReplied,
            ])->save();
            $lockedTicket->forceFill([
                'status' => TicketStatus::Dikerjakan,
                'assigned_to_id' => $assigneeId,
                'assigned_tier' => $assignedTier,
            ])->save();
            $this->recordStatus($lockedTicket, TicketStatus::MenungguPemohon, TicketStatus::Dikerjakan, 'ticket.reply.requester', $actor, null, [
                'wait_id' => $wait->getKey(),
                'comment_id' => $comment->getKey(),
                'returned_to_user_id' => $assigneeId,
            ], $now);
            $this->sla->resume($lockedTicket, $now);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.reply.requester',
                $lockedTicket,
                'Balasan Pemohon mengakhiri waktu tunggu.',
                null,
                ['wait_id' => $wait->getKey(), 'comment_id' => $comment->getKey()],
            );

            return [$lockedTicket->fresh(['requester', 'assignee']), $assigneeId, $comment->getKey()];
        });

        if ($assignee !== null && (int) $assignee !== (int) $actor->getKey()) {
            $this->notifications->send(
                $result,
                'requester_reply',
                'Pemohon membalas tiket',
                "Pemohon membalas tiket {$result->ticket_number} dan tiket kembali dikerjakan.",
                [$assignee],
                "ticket:{$result->getKey()}:comment:{$commentId}",
            );
        }

        return $result;
    }

    public function startThirdParty(
        User $actor,
        Ticket $ticket,
        string $thirdPartyName,
        ?string $followUpDate = null,
        ?string $note = null,
    ): Ticket {
        $this->authorization->authorize($actor, 'startThirdPartyWait', $ticket, 'ticket.wait.third_party');
        $now = Carbon::now(config('app.timezone'));

        $result = $this->database->transaction(function () use ($actor, $ticket, $thirdPartyName, $followUpDate, $note, $now): Ticket {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.wait.third_party');
            $this->authorization->authorize($actor, 'startThirdPartyWait', $lockedTicket, 'ticket.wait.third_party');
            $this->assertNoActiveWait($lockedTicket, $actor, 'ticket.wait.third_party');

            if (! in_array($lockedTicket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true)
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'ticket.wait.third_party', 'Tiket tidak tersedia untuk menunggu pihak ketiga.');
            }

            $fromStatus = $lockedTicket->status;
            $wait = $lockedTicket->waits()->create([
                'kind' => TicketWaitType::ThirdParty,
                'started_by_id' => $actor->getKey(),
                'from_status' => $fromStatus,
                'from_assignee_id' => $lockedTicket->assigned_to_id,
                'from_assigned_tier' => $lockedTicket->assigned_tier,
                'started_at' => $now,
                'third_party_name' => trim($thirdPartyName),
                'follow_up_date' => $followUpDate,
                'metadata' => ['note' => filled($note) ? trim($note) : null],
            ]);
            $lockedTicket->forceFill(['status' => TicketStatus::MenungguPihakKetiga])->save();
            $this->recordStatus($lockedTicket, $fromStatus, TicketStatus::MenungguPihakKetiga, 'ticket.wait.third_party', $actor, $note, [
                'wait_id' => $wait->getKey(),
                'third_party_name' => $wait->third_party_name,
                'follow_up_date' => $wait->follow_up_date?->toDateString(),
            ], $now);
            $this->sla->pause($lockedTicket, 'third_party_wait', $now);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.wait.third_party',
                $lockedTicket,
                'Tiket menunggu pihak ketiga.',
                null,
                ['wait_id' => $wait->getKey(), 'third_party_name' => $wait->third_party_name],
            );

            return $lockedTicket->fresh(['requester', 'assignee', 'activeWait']);
        });

        if ($result->requester !== null
            && (int) $result->requester->getKey() !== (int) $actor->getKey()) {
            $waitId = $result->activeWait?->getKey();
            $this->notifications->send(
                $result,
                'third_party_wait',
                'Tiket menunggu pihak ketiga',
                "Tiket {$result->ticket_number} sedang menunggu pihak ketiga: {$thirdPartyName}.",
                [$result->requester_id],
                "ticket:{$result->getKey()}:third-party-wait:".($waitId ?? 'unknown'),
            );
        }

        return $result;
    }

    public function resumeThirdParty(User $actor, Ticket $ticket, ?string $reason = null): Ticket
    {
        $this->authorization->authorize($actor, 'resumeThirdPartyWait', $ticket, 'ticket.resume.third_party');
        $now = Carbon::now(config('app.timezone'));

        $result = $this->database->transaction(function () use ($actor, $ticket, $reason, $now): Ticket {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.resume.third_party');
            $this->authorization->authorize($actor, 'resumeThirdPartyWait', $lockedTicket, 'ticket.resume.third_party');
            $wait = $lockedTicket->waits()->active()->where('kind', TicketWaitType::ThirdParty->value)->latest('started_at')->lockForUpdate()->first();

            if ($wait === null || $lockedTicket->status !== TicketStatus::MenungguPihakKetiga) {
                $this->deny($actor, $lockedTicket, 'ticket.resume.third_party', 'Tiket tidak sedang menunggu pihak ketiga.');
            }

            $wait->forceFill([
                'ended_at' => $now,
                'ended_by_id' => $actor->getKey(),
                'end_reason' => TicketWaitEndReason::ThirdPartyCompleted,
                'metadata' => array_merge($wait->metadata ?? [], ['resume_reason' => filled($reason) ? trim($reason) : null]),
            ])->save();
            $lockedTicket->forceFill([
                'status' => TicketStatus::Dikerjakan,
                'assigned_to_id' => $wait->from_assignee_id ?: $lockedTicket->assigned_to_id,
                'assigned_tier' => $wait->from_assigned_tier ?: $lockedTicket->assigned_tier,
            ])->save();
            $this->recordStatus($lockedTicket, TicketStatus::MenungguPihakKetiga, TicketStatus::Dikerjakan, 'ticket.resume.third_party', $actor, $reason, [
                'wait_id' => $wait->getKey(),
                'third_party_name' => $wait->third_party_name,
            ], $now);
            $this->sla->resume($lockedTicket, $now);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.resume.third_party',
                $lockedTicket,
                'Tiket kembali dikerjakan setelah ketergantungan selesai.',
                null,
                ['wait_id' => $wait->getKey()],
            );

            return $lockedTicket->fresh(['requester', 'assignee']);
        });

        if ($result->requester !== null
            && (int) $result->requester->getKey() !== (int) $actor->getKey()) {
            $this->notifications->send(
                $result,
                'third_party_resumed',
                'Tiket kembali dikerjakan',
                "Tiket {$result->ticket_number} kembali dikerjakan setelah menunggu pihak ketiga.",
                [$result->requester_id],
                "ticket:{$result->getKey()}:third-party-resumed:{$result->statusHistories()->where('action', 'ticket.resume.third_party')->latest('id')->value('id')}",
            );
        }

        return $result;
    }

    public function expireRequesterWaits(?Carbon $now = null): int
    {
        $now ??= Carbon::now(config('app.timezone'));
        $waitIds = TicketWait::query()
            ->requester()
            ->active()
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $now)
            ->orderBy('id')
            ->pluck('id');
        $expired = 0;

        foreach ($waitIds as $waitId) {
            $transition = $this->expireWait((int) $waitId, $now);

            if ($transition !== null) {
                $expired++;

                $this->notifications->send(
                    $transition['ticket'],
                    'requester_wait_timeout',
                    'Waktu tunggu Pemohon berakhir',
                    "Waktu tunggu informasi untuk tiket {$transition['ticket']->ticket_number} telah berakhir.",
                    [$transition['user']?->getKey()],
                    "ticket:{$transition['ticket']->getKey()}:requester-wait-timeout:{$transition['wait_id']}",
                );
            }
        }

        return $expired;
    }

    /** @return array{user:?User,ticket:Ticket,wait_id:int}|null */
    private function expireWait(int $waitId, Carbon $now): ?array
    {
        return $this->database->transaction(function () use ($waitId, $now): ?array {
            $wait = TicketWait::query()->whereKey($waitId)->lockForUpdate()->first();

            if ($wait === null || $wait->ended_at !== null || $wait->kind !== TicketWaitType::Requester || $wait->due_at === null || $wait->due_at->gt($now)) {
                return null;
            }

            $ticket = Ticket::query()->with(['requester', 'assignee'])->whereKey($wait->ticket_id)->lockForUpdate()->first();

            if ($ticket === null || $ticket->status !== TicketStatus::MenungguPemohon) {
                return null;
            }

            $assigneeId = $wait->from_assignee_id ?: $ticket->assigned_to_id;
            $assignedTier = $wait->from_assigned_tier ?: $ticket->assigned_tier;
            $wait->forceFill([
                'ended_at' => $now,
                'end_reason' => TicketWaitEndReason::Timeout,
                'timed_out' => true,
            ])->save();
            $ticket->forceFill([
                'status' => TicketStatus::Dikerjakan,
                'assigned_to_id' => $assigneeId,
                'assigned_tier' => $assignedTier,
            ])->save();
            $this->recordStatus($ticket, TicketStatus::MenungguPemohon, TicketStatus::Dikerjakan, 'ticket.wait.requester.timeout', null, null, [
                'wait_id' => $wait->getKey(),
                'timed_out' => true,
                'returned_to_user_id' => $assigneeId,
            ], $now);
            $this->sla->resume($ticket, $now);
            $this->auditLogger->succeeded(
                null,
                'ticket.wait.requester.timeout',
                $ticket,
                'Waktu tunggu Pemohon berakhir dan tiket kembali dikerjakan.',
                null,
                ['wait_id' => $wait->getKey(), 'timed_out' => true],
            );

            $user = $assigneeId === null ? null : User::query()->find($assigneeId);

            return [
                'user' => $user,
                'ticket' => $ticket->fresh(['requester', 'assignee']),
                'wait_id' => (int) $wait->getKey(),
            ];
        });
    }

    private function lockTicket(Ticket $ticket, User $actor, string $action): Ticket
    {
        $lockedTicket = Ticket::query()
            ->with('serviceType')
            ->whereKey($ticket->getKey())
            ->lockForUpdate()
            ->first();

        if ($lockedTicket === null) {
            $this->deny($actor, $ticket, $action, 'Tiket tidak ditemukan.');
        }

        return $lockedTicket;
    }

    private function assertNoActiveWait(Ticket $ticket, User $actor, string $action): void
    {
        if ($ticket->waits()->active()->exists()) {
            $this->deny($actor, $ticket, $action, 'Tiket sudah memiliki waktu tunggu aktif.');
        }
    }

    private function deny(User $actor, Ticket $ticket, string $action, string $reason): never
    {
        $this->auditLogger->denied($actor, $action, $ticket, $reason);

        throw ValidationException::withMessages(['ticket' => $reason]);
    }

    /** @param array<string, mixed>|null $metadata */
    private function recordStatus(
        Ticket $ticket,
        ?TicketStatus $from,
        TicketStatus $to,
        string $action,
        ?User $actor,
        ?string $reason,
        ?array $metadata,
        Carbon $occurredAt,
    ): void {
        $ticket->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'action' => $action,
            'actor_id' => $actor?->getKey(),
            'reason' => filled($reason) ? trim($reason) : null,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }
}
