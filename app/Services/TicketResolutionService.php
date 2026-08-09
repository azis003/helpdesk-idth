<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketResolutionService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TicketSlaService $sla,
        private readonly WorkingCalendarService $calendar,
        private readonly OperationalPolicyService $policies,
        private readonly DatabaseChangeControlService $specialControls,
        private readonly TicketAttachmentService $attachments,
        private readonly TicketNotificationService $notifications,
    ) {}

    public function complete(User $actor, Ticket $ticket, string $solution, ?UploadedFile $dataExportResult = null): Ticket
    {
        $this->authorization->authorize($actor, 'complete', $ticket, 'ticket.complete');
        $solution = trim($solution);

        if ($solution === '') {
            $this->auditLogger->denied($actor, 'ticket.complete', $ticket, 'Solusi wajib diisi sebelum tiket menunggu konfirmasi.');

            throw ValidationException::withMessages([
                'solution' => 'Solusi wajib diisi sebelum tiket menunggu konfirmasi.',
            ]);
        }

        $now = Carbon::now(config('app.timezone'));
        $waitDays = (int) ($this->policies->settings()['confirmation_wait_working_days'] ?? 3);
        $calendar = $this->policies->currentCalendar();
        $confirmationDueAt = $this->calendar->deadlineAfterWorkingDays($now, $waitDays, $calendar);
        $failure = null;

        $result = $this->database->transaction(function () use ($actor, $ticket, $solution, $dataExportResult, $now, $confirmationDueAt, $waitDays, $calendar, &$failure): ?Ticket {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.resolution');
            $this->authorization->authorize($actor, 'complete', $lockedTicket, 'ticket.complete');

            if ($lockedTicket->status !== TicketStatus::Dikerjakan
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'Tiket hanya dapat diselesaikan oleh penanggung jawab pada status Dikerjakan.');
            }

            if ($this->specialControls->serviceCode($lockedTicket) === DatabaseChangeControlService::SVC_DATA_EXPORT
                && $dataExportResult !== null) {
                $this->attachments->storeDataExportResult($lockedTicket, $actor, $dataExportResult);
            }

            $specialControlFailure = $this->specialControls->completionFailure($lockedTicket);

            if ($specialControlFailure !== null) {
                $failure = $specialControlFailure;

                return null;
            }

            $before = $this->snapshot($lockedTicket);
            $lockedTicket->forceFill([
                'status' => TicketStatus::MenungguKonfirmasi,
                'solution' => $solution,
                'confirmation_started_at' => $now,
                'confirmation_due_at' => $confirmationDueAt,
                'closed_at' => null,
                'closed_reason' => null,
            ])->save();
            $this->sla->pause($lockedTicket, 'confirmation', $now);
            $metrics = $this->sla->metrics($lockedTicket, $now);
            $lockedTicket->forceFill([
                'sla_compliant' => $metrics['compliant'] ?? $metrics['within_target'] ?? null,
                'sla_elapsed_working_minutes' => $metrics['elapsed_working_minutes'] ?? null,
            ])->save();
            $this->recordStatus(
                $lockedTicket,
                TicketStatus::Dikerjakan,
                TicketStatus::MenungguKonfirmasi,
                'ticket.completed',
                $actor,
                null,
                [
                    'confirmation_due_at' => $confirmationDueAt->toIso8601String(),
                    'confirmation_wait_working_days' => $waitDays,
                    'calendar_id' => $calendar?->getKey(),
                    'calendar_version' => $calendar?->version,
                    'sla_compliant' => $lockedTicket->sla_compliant,
                    'sla_elapsed_working_minutes' => $lockedTicket->sla_elapsed_working_minutes,
                ],
                $now,
            );
            $this->auditLogger->succeeded(
                $actor,
                'ticket.completed',
                $lockedTicket,
                'Solusi disimpan dan tiket menunggu konfirmasi Pemohon.',
                $before,
                $this->snapshot($lockedTicket),
            );

            return $lockedTicket->fresh(['requester', 'assignee']);
        });

        if ($result === null) {
            $reason = $failure ?: 'Tiket belum dapat dipindahkan ke Menunggu Konfirmasi.';
            $this->auditLogger->denied($actor, 'ticket.resolution', $ticket, $reason);

            throw ValidationException::withMessages(['ticket' => $reason]);
        }

        $this->notifications->send(
            $result,
            'ticket_completed',
            'Tiket menunggu konfirmasi',
            "Solusi untuk tiket {$result->ticket_number} sudah tersedia. Silakan konfirmasi hasilnya.",
            [$result->requester_id],
            "ticket:{$result->getKey()}:completed:{$result->statusHistories()->where('action', 'ticket.completed')->latest('id')->value('id')}",
        );

        return $result;
    }

    public function confirm(User $actor, Ticket $ticket): Ticket
    {
        $this->authorization->authorize($actor, 'confirm', $ticket, 'ticket.confirm');
        $now = Carbon::now(config('app.timezone'));

        [$result, $recipientIds] = $this->database->transaction(function () use ($actor, $ticket, $now): array {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.confirm');
            $this->authorization->authorize($actor, 'confirm', $lockedTicket, 'ticket.confirm');

            if ($lockedTicket->status !== TicketStatus::MenungguKonfirmasi
                || (int) $lockedTicket->requester_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'Konfirmasi hanya tersedia untuk Pemohon saat tiket Menunggu Konfirmasi.');
            }

            $before = $this->snapshot($lockedTicket);
            $lockedTicket->forceFill([
                'status' => TicketStatus::Ditutup,
                'closed_at' => $now,
                'closed_reason' => 'requester_confirmed',
                'confirmation_due_at' => null,
            ])->save();
            $this->sla->stop($lockedTicket, $now);
            $this->recordStatus(
                $lockedTicket,
                TicketStatus::MenungguKonfirmasi,
                TicketStatus::Ditutup,
                'ticket.closed',
                $actor,
                null,
                ['closure_reason' => 'requester_confirmed'],
                $now,
            );
            $this->auditLogger->succeeded(
                $actor,
                'ticket.closed',
                $lockedTicket,
                'Tiket ditutup berdasarkan konfirmasi Pemohon.',
                $before,
                $this->snapshot($lockedTicket),
            );

            $recipientIds = $this->recipientIds($lockedTicket, $actor);

            return [$lockedTicket->fresh(['requester', 'assignee']), $recipientIds];
        });

        $this->notifyUsers(
            $recipientIds,
            'ticket_closed',
            'Tiket ditutup',
            "Tiket {$result->ticket_number} telah ditutup berdasarkan konfirmasi Pemohon.",
            $result,
            "ticket:{$result->getKey()}:closed:{$result->statusHistories()->where('action', 'ticket.closed')->latest('id')->value('id')}",
        );

        return $result;
    }

    public function notSatisfied(User $actor, Ticket $ticket, string $reason): Ticket
    {
        $this->authorization->authorize($actor, 'notSatisfied', $ticket, 'ticket.confirm.not_satisfied');
        $reason = trim($reason);

        if ($reason === '') {
            $this->auditLogger->denied($actor, 'ticket.confirm.not_satisfied', $ticket, 'Alasan hasil belum sesuai wajib diisi.');

            throw ValidationException::withMessages([
                'reason' => 'Alasan hasil belum sesuai wajib diisi.',
            ]);
        }

        $now = Carbon::now(config('app.timezone'));
        [$result, $assigneeId] = $this->database->transaction(function () use ($actor, $ticket, $reason, $now): array {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.confirm.not_satisfied');
            $this->authorization->authorize($actor, 'notSatisfied', $lockedTicket, 'ticket.confirm.not_satisfied');

            if ($lockedTicket->status !== TicketStatus::MenungguKonfirmasi
                || (int) $lockedTicket->requester_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'Hasil belum sesuai hanya dapat dikirim saat tiket Menunggu Konfirmasi.');
            }

            if ($lockedTicket->assigned_to_id === null) {
                $this->deny($actor, $lockedTicket, 'Penanggung jawab terakhir tiket tidak ditemukan.');
            }

            $before = $this->snapshot($lockedTicket);
            $lockedTicket->forceFill([
                'status' => TicketStatus::Dikerjakan,
                'confirmation_due_at' => null,
            ])->save();
            $this->sla->resume($lockedTicket, $now);
            $this->recordStatus(
                $lockedTicket,
                TicketStatus::MenungguKonfirmasi,
                TicketStatus::Dikerjakan,
                'ticket.confirmation.not_satisfied',
                $actor,
                $reason,
                [
                    'returned_to_user_id' => $lockedTicket->assigned_to_id,
                    'reopen_count' => $lockedTicket->reopen_count,
                ],
                $now,
            );
            $this->auditLogger->succeeded(
                $actor,
                'ticket.confirmation.not_satisfied',
                $lockedTicket,
                $reason,
                $before,
                $this->snapshot($lockedTicket),
            );

            return [$lockedTicket->fresh(['requester', 'assignee']), (int) $lockedTicket->assigned_to_id];
        });

        if ($assigneeId > 0) {
            $this->notifications->send(
                $result,
                'ticket_not_satisfied',
                'Hasil tiket belum sesuai',
                "Pemohon menyatakan hasil tiket {$result->ticket_number} belum sesuai. Tiket kembali dikerjakan oleh Anda.",
                [$assigneeId],
                "ticket:{$result->getKey()}:not-satisfied:{$result->statusHistories()->where('action', 'ticket.confirmation.not_satisfied')->latest('id')->value('id')}",
            );
        }

        return $result;
    }

    public function reopen(User $actor, Ticket $ticket, ?string $reason = null): Ticket
    {
        $this->authorization->authorize($actor, 'reopen', $ticket, 'ticket.reopen');
        $reason = filled($reason) ? trim($reason) : null;
        $now = Carbon::now(config('app.timezone'));

        [$result, $assigneeId] = $this->database->transaction(function () use ($actor, $ticket, $reason, $now): array {
            $lockedTicket = $this->lockTicket($ticket, $actor, 'ticket.reopen');
            $this->authorization->authorize($actor, 'reopen', $lockedTicket, 'ticket.reopen');

            if ($lockedTicket->status !== TicketStatus::Ditutup || $lockedTicket->closed_at === null) {
                $this->deny($actor, $lockedTicket, 'Hanya tiket Ditutup yang dapat dibuka kembali.');
            }

            $settings = $this->policies->settings();
            $maxReopenCount = (int) ($settings['max_reopen_count'] ?? 3);
            $windowDays = (int) ($settings['reopen_window_working_days'] ?? 7);
            $calendar = $this->policies->currentCalendar();

            if ((int) $lockedTicket->reopen_count >= $maxReopenCount) {
                $this->deny($actor, $lockedTicket, "Tiket sudah dibuka kembali {$maxReopenCount} kali. Silakan membuat tiket baru.");
            }

            if (! $this->calendar->isWithinWorkingDays($lockedTicket->closed_at, $now, $windowDays, $calendar)) {
                $this->deny($actor, $lockedTicket, "Buka kembali hanya tersedia dalam {$windowDays} hari kerja setelah tiket ditutup.");
            }

            if ($lockedTicket->assigned_to_id === null) {
                $this->deny($actor, $lockedTicket, 'Penanggung jawab terakhir tiket tidak ditemukan.');
            }

            $before = $this->snapshot($lockedTicket);
            $reopenCount = (int) $lockedTicket->reopen_count + 1;
            $lockedTicket->forceFill([
                'status' => TicketStatus::Dikerjakan,
                'closed_at' => null,
                'closed_reason' => null,
                'confirmation_started_at' => null,
                'confirmation_due_at' => null,
                'reopen_count' => $reopenCount,
                'sla_compliant' => null,
                'sla_elapsed_working_minutes' => null,
            ])->save();
            $segment = $this->sla->startNewCycle($lockedTicket, $now);
            $this->recordStatus(
                $lockedTicket,
                TicketStatus::Ditutup,
                TicketStatus::Dikerjakan,
                'ticket.reopened',
                $actor,
                $reason,
                [
                    'reopen_count' => $reopenCount,
                    'sla_cycle' => $lockedTicket->sla_cycle,
                    'sla_segment_id' => $segment?->getKey(),
                ],
                $now,
            );
            $this->auditLogger->succeeded(
                $actor,
                'ticket.reopened',
                $lockedTicket,
                $reason ?: 'Tiket dibuka kembali oleh Pemohon.',
                $before,
                $this->snapshot($lockedTicket),
            );

            return [$lockedTicket->fresh(['requester', 'assignee']), (int) $lockedTicket->assigned_to_id];
        });

        if ($assigneeId > 0) {
            $this->notifications->send(
                $result,
                'ticket_reopened',
                'Tiket dibuka kembali',
                "Tiket {$result->ticket_number} dibuka kembali oleh Pemohon dan kembali berstatus Dikerjakan.",
                [$assigneeId],
                "ticket:{$result->getKey()}:reopened:{$result->statusHistories()->where('action', 'ticket.reopened')->latest('id')->value('id')}",
            );
        }

        return $result;
    }

    public function autoCloseDueTickets(?Carbon $now = null): int
    {
        $now ??= Carbon::now(config('app.timezone'));
        $ticketIds = Ticket::query()
            ->awaitingConfirmation()
            ->whereNotNull('confirmation_due_at')
            ->where('confirmation_due_at', '<=', $now)
            ->orderBy('id')
            ->pluck('id');
        $closed = 0;

        foreach ($ticketIds as $ticketId) {
            $result = $this->autoClose((int) $ticketId, $now);

            if ($result !== null) {
                $closed++;
                $this->notifyUsers(
                    $result['recipient_ids'],
                    'ticket_auto_closed',
                    'Tiket ditutup otomatis',
                    "Tiket {$result['ticket']->ticket_number} ditutup otomatis karena tidak ada konfirmasi Pemohon sampai batas waktu.",
                    $result['ticket'],
                    "ticket:{$result['ticket']->getKey()}:auto-closed:{$result['ticket']->statusHistories()->where('action', 'ticket.auto_closed')->latest('id')->value('id')}",
                );
            }
        }

        return $closed;
    }

    public function autoCloseTickets(?Carbon $now = null): int
    {
        return $this->autoCloseDueTickets($now);
    }

    /** @return array{ticket:Ticket,recipient_ids:list<int>}|null */
    private function autoClose(int $ticketId, Carbon $now): ?array
    {
        return $this->database->transaction(function () use ($ticketId, $now): ?array {
            $lockedTicket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->first();

            if ($lockedTicket === null
                || $lockedTicket->status !== TicketStatus::MenungguKonfirmasi
                || $lockedTicket->confirmation_due_at === null
                || $lockedTicket->confirmation_due_at->greaterThan($now)) {
                return null;
            }

            $before = $this->snapshot($lockedTicket);
            $lockedTicket->forceFill([
                'status' => TicketStatus::Ditutup,
                'closed_at' => $now,
                'closed_reason' => 'auto_closed',
                'confirmation_due_at' => null,
            ])->save();
            $this->sla->stop($lockedTicket, $now);
            $this->recordStatus(
                $lockedTicket,
                TicketStatus::MenungguKonfirmasi,
                TicketStatus::Ditutup,
                'ticket.auto_closed',
                null,
                null,
                ['closure_reason' => 'auto_closed'],
                $now,
            );
            $this->auditLogger->succeeded(
                null,
                'ticket.auto_closed',
                $lockedTicket,
                'Tiket ditutup otomatis setelah batas konfirmasi terlewati.',
                $before,
                $this->snapshot($lockedTicket),
            );

            $ticket = $lockedTicket->fresh(['requester', 'assignee']);

            return [
                'ticket' => $ticket,
                'recipient_ids' => $this->recipientIds($ticket),
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
            $this->auditLogger->denied($actor, $action, $ticket, 'Tiket tidak ditemukan.');

            throw ValidationException::withMessages(['ticket' => 'Tiket tidak ditemukan.']);
        }

        return $lockedTicket;
    }

    private function deny(User $actor, Ticket $ticket, string $reason): never
    {
        $this->auditLogger->denied($actor, 'ticket.resolution', $ticket, $reason);

        throw ValidationException::withMessages(['ticket' => $reason]);
    }

    /** @param array<string, mixed>|null $metadata */
    private function recordStatus(
        Ticket $ticket,
        TicketStatus $from,
        TicketStatus $to,
        string $action,
        ?User $actor,
        ?string $reason,
        ?array $metadata,
        Carbon $occurredAt,
    ): void {
        $ticket->statusHistories()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'action' => $action,
            'actor_id' => $actor?->getKey(),
            'reason' => filled($reason) ? trim($reason) : null,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }

    /** @return list<int> */
    private function recipientIds(Ticket $ticket, ?User $actor = null): array
    {
        $actorId = $actor?->getKey();

        return collect([$ticket->requester_id, $ticket->assigned_to_id, $ticket->created_by_id])
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $actorId !== null && $id === (int) $actorId)
            ->unique()
            ->values()
            ->all();
    }

    /** @param list<int> $recipientIds */
    private function notifyUsers(
        array $recipientIds,
        string $event,
        string $title,
        string $message,
        Ticket $ticket,
        ?string $eventKey = null,
    ): void {
        $this->notifications->send($ticket, $event, $title, $message, $recipientIds, $eventKey);
    }

    /** @return array<string, mixed> */
    private function snapshot(Ticket $ticket): array
    {
        return [
            'ticket_id' => $ticket->getKey(),
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status?->value,
            'assigned_to_id' => $ticket->assigned_to_id,
            'assigned_tier' => $ticket->assigned_tier,
            'solution' => $ticket->solution,
            'confirmation_due_at' => $ticket->confirmation_due_at?->toIso8601String(),
            'closed_at' => $ticket->closed_at?->toIso8601String(),
            'closed_reason' => $ticket->closed_reason,
            'reopen_count' => $ticket->reopen_count,
            'sla_cycle' => $ticket->sla_cycle,
            'sla_compliant' => $ticket->sla_compliant,
        ];
    }
}
