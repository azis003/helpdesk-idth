<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TicketApprovalService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly ApproverAssignmentService $approvers,
        private readonly TicketSlaService $sla,
    ) {}

    public function request(User $actor, Ticket $ticket, ?string $reason = null): ApprovalRequest
    {
        $this->authorization->authorize($actor, 'requestApproval', $ticket, 'ticket.approval.request');
        $now = Carbon::now(config('app.timezone'));
        $reason = filled($reason) ? trim($reason) : null;

        $approval = $this->database->transaction(function () use ($actor, $ticket, $reason, $now): ApprovalRequest {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $this->deny($actor, $ticket, 'Tiket tidak ditemukan.');
            }

            $this->authorization->authorize($actor, 'requestApproval', $lockedTicket, 'ticket.approval.request');

            if (! in_array($lockedTicket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true)
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'Persetujuan hanya dapat diminta oleh penanggung jawab pada status Diproses atau Dikerjakan.');
            }

            $pending = ApprovalRequest::query()
                ->pending()
                ->where('ticket_id', $lockedTicket->getKey())
                ->lockForUpdate()
                ->first();

            if ($pending !== null) {
                $this->deny($actor, $lockedTicket, 'Tiket ini sudah memiliki permintaan persetujuan yang sedang menunggu keputusan.');
            }

            $assignment = $this->approvers->currentEligible(true);

            if ($assignment === null) {
                $this->deny($actor, $lockedTicket, 'Manajer TI/Approver aktif belum ditetapkan atau belum memenuhi syarat keamanan.');
            }

            $before = $this->ticketSnapshot($lockedTicket);
            $fromStatus = $lockedTicket->status;
            $approval = ApprovalRequest::query()->create([
                'ticket_id' => $lockedTicket->getKey(),
                'approver_id' => $assignment->user_id,
                'status' => ApprovalRequest::STATUS_PENDING,
                'previous_status' => $fromStatus->value,
                'previous_assignee_id' => $lockedTicket->assigned_to_id,
                'previous_tier' => $lockedTicket->assigned_tier,
                'requested_by' => $actor->getKey(),
                'requested_at' => $now,
                'decision_note' => null,
            ]);

            $lockedTicket->forceFill(['status' => TicketStatus::MenungguPersetujuan])->save();
            $this->recordStatusHistory(
                $lockedTicket,
                $fromStatus,
                TicketStatus::MenungguPersetujuan,
                'ticket.approval.requested',
                $actor,
                $reason,
                [
                    'approval_request_id' => $approval->getKey(),
                    'approver_id' => $assignment->user_id,
                    'previous_status' => $fromStatus->value,
                    'previous_assignee_id' => $lockedTicket->assigned_to_id,
                    'previous_tier' => $lockedTicket->assigned_tier,
                ],
                $now,
            );
            $this->sla->pause($lockedTicket, 'approval', $now);

            $this->auditLogger->succeeded(
                $actor,
                'ticket.approval.requested',
                $lockedTicket,
                $reason ?: 'Persetujuan diminta kepada Manajer TI/Approver aktif.',
                $before,
                array_merge($this->ticketSnapshot($lockedTicket), [
                    'approval_request_id' => $approval->getKey(),
                    'approver_id' => $assignment->user_id,
                    'previous_status' => $fromStatus->value,
                    'previous_assignee_id' => $lockedTicket->assigned_to_id,
                    'previous_tier' => $lockedTicket->assigned_tier,
                ]),
            );

            return $approval->fresh(['ticket', 'approver', 'requestedBy']);
        });

        $approval->approver?->notify(new TicketEventNotification(
            'approval_requested',
            'Persetujuan diperlukan',
            "Tiket {$approval->ticket?->ticket_number} menunggu keputusan Anda.",
            $approval->ticket,
        ));

        return $approval;
    }

    public function approve(User $actor, ApprovalRequest $approvalRequest, ?string $note = null): Ticket
    {
        return $this->decide($actor, $approvalRequest, ApprovalRequest::STATUS_APPROVED, $note);
    }

    public function reject(User $actor, ApprovalRequest $approvalRequest, string $note): Ticket
    {
        return $this->decide($actor, $approvalRequest, ApprovalRequest::STATUS_REJECTED, $note);
    }

    /** @return Collection<int, ApprovalRequest> */
    public function pendingFor(User $actor): Collection
    {
        if (! $this->approvers->isCurrentApprover($actor)) {
            return collect();
        }

        return ApprovalRequest::query()
            ->pending()
            ->where('approver_id', $actor->getKey())
            ->with(['ticket.requester', 'ticket.assignee', 'requestedBy'])
            ->orderBy('requested_at')
            ->orderBy('id')
            ->get();
    }

    private function decide(
        User $actor,
        ApprovalRequest $approvalRequest,
        string $decision,
        ?string $note,
    ): Ticket {
        $action = $decision === ApprovalRequest::STATUS_APPROVED
            ? 'ticket.approval.approve'
            : 'ticket.approval.reject';
        $this->authorization->authorize($actor, 'decide', $approvalRequest, $action);

        $note = filled($note) ? trim($note) : null;

        if ($decision === ApprovalRequest::STATUS_REJECTED && $note === null) {
            throw ValidationException::withMessages([
                'decision_note' => 'Catatan wajib diisi untuk keputusan Tidak Setuju.',
            ]);
        }

        $now = Carbon::now(config('app.timezone'));
        [$ticket, $resolvedApproval] = $this->database->transaction(function () use (
            $actor,
            $approvalRequest,
            $decision,
            $note,
            $action,
            $now,
        ): array {
            $lockedApproval = ApprovalRequest::query()
                ->whereKey($approvalRequest->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedApproval === null) {
                $this->deny($actor, null, 'Permintaan persetujuan tidak ditemukan.', $action);
            }

            $lockedTicket = Ticket::query()
                ->whereKey($lockedApproval->ticket_id)
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $this->deny($actor, null, 'Tiket untuk persetujuan tidak ditemukan.', $action);
            }

            $this->authorization->authorize($actor, 'decide', $lockedApproval, $action);

            if (! $lockedApproval->isPending()
                || (int) $lockedApproval->approver_id !== (int) $actor->getKey()
                || $lockedTicket->status !== TicketStatus::MenungguPersetujuan) {
                $this->deny($actor, $lockedTicket, 'Permintaan persetujuan sudah diputuskan atau tidak lagi tersedia.', $action);
            }

            $assignment = $this->approvers->currentEligible(true);

            if ($assignment === null || (int) $assignment->user_id !== (int) $actor->getKey()) {
                $this->deny($actor, $lockedTicket, 'Hanya Manajer TI/Approver aktif yang dapat memutuskan persetujuan.', $action);
            }

            $previousStatus = TicketStatus::tryFrom((string) $lockedApproval->previous_status);

            if ($previousStatus === null
                || ! in_array($previousStatus, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true)) {
                $this->deny($actor, $lockedTicket, 'State sebelum persetujuan tidak valid sehingga keputusan tidak dapat diterapkan.', $action);
            }

            $before = $this->ticketSnapshot($lockedTicket);
            $beforeApproval = $this->approvalSnapshot($lockedApproval);
            $nextStatus = $decision === ApprovalRequest::STATUS_APPROVED
                ? $previousStatus
                : TicketStatus::TidakDisetujui;

            $lockedApproval->forceFill([
                'status' => $decision,
                'decided_at' => $now,
                'decision_note' => $note,
            ])->save();
            $lockedTicket->forceFill([
                'status' => $nextStatus,
                'assigned_to_id' => $decision === ApprovalRequest::STATUS_APPROVED
                    ? $lockedApproval->previous_assignee_id
                    : $lockedTicket->assigned_to_id,
                'assigned_tier' => $decision === ApprovalRequest::STATUS_APPROVED
                    ? $lockedApproval->previous_tier
                    : $lockedTicket->assigned_tier,
            ])->save();

            $this->recordStatusHistory(
                $lockedTicket,
                TicketStatus::MenungguPersetujuan,
                $nextStatus,
                $decision === ApprovalRequest::STATUS_APPROVED
                    ? 'ticket.approval.approved'
                    : 'ticket.approval.rejected',
                $actor,
                $note,
                [
                    'approval_request_id' => $lockedApproval->getKey(),
                    'decision' => $decision,
                    'previous_status' => $lockedApproval->previous_status,
                    'previous_assignee_id' => $lockedApproval->previous_assignee_id,
                    'previous_tier' => $lockedApproval->previous_tier,
                ],
                $now,
            );

            if ($decision === ApprovalRequest::STATUS_APPROVED) {
                $this->sla->resume($lockedTicket, $now);
            } else {
                $this->sla->stop($lockedTicket, $now);
            }

            $this->auditLogger->succeeded(
                $actor,
                $decision === ApprovalRequest::STATUS_APPROVED
                    ? 'ticket.approval.approved'
                    : 'ticket.approval.rejected',
                $lockedTicket,
                $decision === ApprovalRequest::STATUS_APPROVED
                    ? 'Persetujuan diberikan dan state tiket sebelumnya dipulihkan.'
                    : 'Persetujuan ditolak dan tiket menjadi Tidak Disetujui.',
                array_merge($before, ['approval' => $beforeApproval]),
                array_merge($this->ticketSnapshot($lockedTicket), [
                    'approval' => $this->approvalSnapshot($lockedApproval),
                ]),
            );

            return [
                $lockedTicket->fresh(['requester', 'assignee']),
                $lockedApproval->fresh(['requestedBy', 'approver']),
            ];
        });

        $this->notifyDecision($actor, $ticket, $resolvedApproval, $decision, $note);

        return $ticket;
    }

    private function notifyDecision(
        User $actor,
        Ticket $ticket,
        ApprovalRequest $approvalRequest,
        string $decision,
        ?string $note,
    ): void {
        $recipientIds = collect([
            $ticket->requester?->getKey(),
            $approvalRequest->requested_by,
            $ticket->assigned_to_id,
        ])
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === (int) $actor->getKey())
            ->unique()
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $approved = $decision === ApprovalRequest::STATUS_APPROVED;
        $title = $approved ? 'Persetujuan disetujui' : 'Persetujuan tidak disetujui';
        $message = $approved
            ? "Persetujuan untuk tiket {$ticket->ticket_number} diberikan. Tiket kembali ke state sebelumnya."
            : "Persetujuan untuk tiket {$ticket->ticket_number} tidak disetujui.";

        if (filled($note)) {
            $message .= " Catatan: {$note}";
        }

        User::query()
            ->whereIn('id', $recipientIds->all())
            ->get()
            ->each(fn (User $recipient) => $recipient->notify(new TicketEventNotification(
                $approved ? 'approval_approved' : 'approval_rejected',
                $title,
                $message,
                $ticket,
            )));
    }

    private function deny(
        User $actor,
        ?Ticket $ticket,
        string $reason,
        string $action = 'ticket.approval.request',
        string $field = 'ticket',
    ): never {
        $this->auditLogger->denied($actor, $action, $ticket, $reason);

        throw ValidationException::withMessages([$field => $reason]);
    }

    private function recordStatusHistory(
        Ticket $ticket,
        TicketStatus $from,
        TicketStatus $to,
        string $action,
        User $actor,
        ?string $reason,
        ?array $metadata,
        Carbon $occurredAt,
    ): void {
        $ticket->statusHistories()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'action' => $action,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }

    /** @return array<string, mixed> */
    private function ticketSnapshot(Ticket $ticket): array
    {
        return [
            'ticket_id' => $ticket->getKey(),
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status?->value,
            'assigned_to_id' => $ticket->assigned_to_id,
            'assigned_tier' => $ticket->assigned_tier,
        ];
    }

    /** @return array<string, mixed> */
    private function approvalSnapshot(ApprovalRequest $approvalRequest): array
    {
        return [
            'approval_request_id' => $approvalRequest->getKey(),
            'ticket_id' => $approvalRequest->ticket_id,
            'approver_id' => $approvalRequest->approver_id,
            'status' => $approvalRequest->status,
            'previous_status' => $approvalRequest->previous_status,
            'previous_assignee_id' => $approvalRequest->previous_assignee_id,
            'previous_tier' => $approvalRequest->previous_tier,
            'requested_by' => $approvalRequest->requested_by,
            'requested_at' => $approvalRequest->requested_at?->toIso8601String(),
            'decided_at' => $approvalRequest->decided_at?->toIso8601String(),
            'decision_note' => $approvalRequest->decision_note,
        ];
    }
}
