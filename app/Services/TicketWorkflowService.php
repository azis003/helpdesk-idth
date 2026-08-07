<?php

namespace App\Services;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketAssignmentAction;
use App\Enums\TicketStatus;
use App\Enums\TicketTriageOutcome;
use App\Models\ProblemCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class TicketWorkflowService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly SkillSuggestionService $skillSuggestions,
        private readonly TicketSlaService $sla,
        private readonly TicketNotificationService $notifications,
    ) {}

    public function claim(User $actor, Ticket $ticket): bool
    {
        $this->authorization->authorize($actor, 'claim', $ticket, 'ticket.claim');

        $failure = null;
        $claimed = $this->database->transaction(function () use ($actor, $ticket, &$failure): bool {
            $lockedTicket = Ticket::query()->whereKey($ticket->getKey())->lockForUpdate()->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $this->auditLogger->denied($actor, 'ticket.claim', $ticket, $failure);

                return false;
            }

            if ($lockedTicket->status !== TicketStatus::Baru || $lockedTicket->assigned_to_id !== null) {
                $failure = 'Tiket sudah diambil oleh agen lain atau tidak tersedia.';
                $this->auditLogger->denied($actor, 'ticket.claim', $lockedTicket, $failure);

                return false;
            }

            $before = $this->snapshot($lockedTicket);
            $occurredAt = now();
            $lockedTicket->forceFill([
                'status' => TicketStatus::Diproses,
                'assigned_to_id' => $actor->getKey(),
                'assigned_tier' => Role::AgenTier1->value,
            ])->save();

            $this->recordStatusHistory(
                $lockedTicket,
                TicketStatus::Baru,
                TicketStatus::Diproses,
                'ticket.claimed',
                $actor,
                null,
                null,
                $occurredAt,
            );
            $this->recordAssignmentHistory(
                $lockedTicket,
                TicketAssignmentAction::Claimed,
                null,
                $actor->getKey(),
                null,
                Role::AgenTier1->value,
                $actor,
                null,
                null,
                $occurredAt,
            );

            $this->auditLogger->succeeded(
                $actor,
                'ticket.claim',
                $lockedTicket,
                'Tiket diklaim dari antrean Tier 1.',
                $before,
                $this->snapshot($lockedTicket),
            );

            $this->notifications->send(
                $lockedTicket,
                'ticket_claimed',
                'Tiket diambil dari antrean',
                "Tiket {$lockedTicket->ticket_number} diambil oleh {$actor->name} dan mulai diproses.",
                [$lockedTicket->requester_id, $lockedTicket->created_by_id],
                "ticket:{$lockedTicket->getKey()}:claimed",
            );

            return true;
        });

        return $claimed;
    }

    public function startHandling(User $actor, Ticket $ticket): bool
    {
        $this->authorization->authorize($actor, 'handle', $ticket, 'ticket.handle');

        $failure = null;
        $handled = $this->database->transaction(function () use ($actor, $ticket, &$failure): bool {
            $lockedTicket = Ticket::query()->whereKey($ticket->getKey())->lockForUpdate()->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';

                return false;
            }

            if ($lockedTicket->status !== TicketStatus::Diproses
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $failure = 'Tiket tidak lagi tersedia untuk mulai dikerjakan.';
                $this->auditLogger->denied($actor, 'ticket.handle', $lockedTicket, $failure);

                return false;
            }

            $before = $this->snapshot($lockedTicket);
            $occurredAt = now();
            $lockedTicket->forceFill(['status' => TicketStatus::Dikerjakan])->save();
            $this->recordStatusHistory(
                $lockedTicket,
                TicketStatus::Diproses,
                TicketStatus::Dikerjakan,
                'ticket.handle',
                $actor,
                null,
                null,
                $occurredAt,
            );
            $this->auditLogger->succeeded(
                $actor,
                'ticket.handle',
                $lockedTicket,
                'Tiket ditandai sedang dikerjakan.',
                $before,
                $this->snapshot($lockedTicket),
            );

            return true;
        });

        return $handled;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function triage(User $actor, Ticket $ticket, array $data): Ticket
    {
        $this->authorization->authorize($actor, 'triage', $ticket, 'ticket.triage');

        $outcome = TicketTriageOutcome::tryFrom((string) ($data['outcome'] ?? ''));
        $priority = Priority::tryFrom((string) ($data['priority'] ?? ''));

        if ($outcome === null || $priority === null) {
            $this->auditLogger->denied($actor, 'ticket.triage', $ticket, 'Data triase tidak lengkap atau tidak valid.');

            throw ValidationException::withMessages([
                'ticket' => 'Data triase tidak lengkap atau tidak valid.',
            ]);
        }

        $failure = null;
        $failureField = 'ticket';
        $result = $this->database->transaction(function () use (
            $actor,
            $ticket,
            $data,
            $outcome,
            $priority,
            &$failure,
            &$failureField,
        ): ?Ticket {
            $lockedTicket = Ticket::query()
                ->with(['problemCategory', 'serviceType'])
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $this->auditLogger->denied($actor, 'ticket.triage', $ticket, $failure);

                return null;
            }

            if (! in_array($lockedTicket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true)
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()
                || $lockedTicket->assigned_tier !== Role::AgenTier1->value) {
                $failure = 'Tiket harus ditugaskan kepada Anda sebagai Agen Tier 1 sebelum ditriase.';
                $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                return null;
            }

            $categorySelected = filled($data['problem_category_id'] ?? null);
            $categoryId = $categorySelected
                ? (int) $data['problem_category_id']
                : (int) ($lockedTicket->problem_category_id ?? 0);
            $category = $categoryId > 0
                ? ProblemCategory::query()->active()->with('skills')->find($categoryId)
                : null;

            if ($categorySelected && $category === null) {
                $failure = 'Kategori masalah tidak aktif atau tidak tersedia.';
                $failureField = 'problem_category_id';
                $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                return null;
            }

            $categoryChanged = $categorySelected
                && (int) ($lockedTicket->problem_category_id ?? 0) !== (int) ($category?->getKey() ?? 0);
            $priorityChanged = $lockedTicket->priority !== $priority;
            $fromCategory = $lockedTicket->problemCategory;
            $fromCategoryId = $lockedTicket->problem_category_id;
            $fromCategoryName = $fromCategory?->name;
            $fromStatus = $lockedTicket->status;

            if ($categoryChanged && ! filled($data['category_reason'] ?? null)) {
                $failure = 'Alasan perubahan kategori wajib diisi.';
                $failureField = 'category_reason';
                $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                return null;
            }

            if ($priorityChanged && ! filled($data['priority_reason'] ?? null)) {
                $failure = 'Alasan perubahan prioritas wajib diisi.';
                $failureField = 'priority_reason';
                $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                return null;
            }

            $suggestions = $this->skillSuggestions->forServiceType(
                $lockedTicket->serviceType,
                null,
                $category,
            );
            $fromUserId = $lockedTicket->assigned_to_id;
            $fromTier = $lockedTicket->assigned_tier;
            $selectedUser = null;
            $newStatus = $outcome === TicketTriageOutcome::Reject
                ? TicketStatus::Ditolak
                : TicketStatus::Dikerjakan;
            $newTier = $fromTier;
            $newAssigneeId = $fromUserId;

            if ($outcome === TicketTriageOutcome::Self) {
                $newAssigneeId = $actor->getKey();
                $newTier = Role::AgenTier1->value;
            } elseif ($outcome === TicketTriageOutcome::TierTwo) {
                $selectedUser = $this->resolveEligibleTierTwoUser($data['assigned_to_id'] ?? null);

                if ($selectedUser === null) {
                    $failure = 'Teknisi Tier 2 yang dipilih tidak aktif atau tidak memiliki peran Agen Tier 2.';
                    $failureField = 'assigned_to_id';
                    $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                    return null;
                }

                $newAssigneeId = $selectedUser->getKey();
                $newTier = Role::AgenTier2->value;
            } else {
                $newAssigneeId = $fromUserId;
                $newTier = $fromTier ?: Role::AgenTier1->value;
            }

            $rejectionReason = $outcome === TicketTriageOutcome::Reject
                ? trim((string) ($data['rejection_reason'] ?? ''))
                : null;

            if ($outcome === TicketTriageOutcome::Reject && $rejectionReason === '') {
                $failure = 'Alasan penolakan wajib diisi.';
                $failureField = 'rejection_reason';
                $this->auditLogger->denied($actor, 'ticket.triage', $lockedTicket, $failure);

                return null;
            }

            $before = $this->snapshot($lockedTicket);
            $occurredAt = now();
            $lockedTicket->forceFill([
                'status' => $newStatus,
                'assigned_to_id' => $newAssigneeId,
                'assigned_tier' => $newTier,
                'last_triaged_by_id' => $actor->getKey(),
                'problem_category_id' => $category?->getKey() ?? $lockedTicket->problem_category_id,
                'problem_category_name_snapshot' => $category?->name ?? $lockedTicket->problem_category_name_snapshot,
                'priority' => $priority,
                'rejection_reason' => $rejectionReason,
            ])->save();

            if ($outcome === TicketTriageOutcome::Reject) {
                $this->sla->stop($lockedTicket, $occurredAt);
            }

            if ($categoryChanged) {
                $this->recordCategoryHistory(
                    $lockedTicket,
                    $fromCategory,
                    $category,
                    $actor,
                    trim((string) $data['category_reason']),
                    $occurredAt,
                    $fromCategoryId,
                    $fromCategoryName,
                );
            }

            if ($priorityChanged) {
                $this->recordPriorityHistory(
                    $lockedTicket,
                    $before['priority'],
                    $priority,
                    $actor,
                    trim((string) $data['priority_reason']),
                    $occurredAt,
                );
            }

            $assignmentAction = match ($outcome) {
                TicketTriageOutcome::Self => TicketAssignmentAction::TriagedSelf,
                TicketTriageOutcome::TierTwo => TicketAssignmentAction::TriagedTier2,
                TicketTriageOutcome::Reject => TicketAssignmentAction::TriagedRejected,
            };
            $this->recordAssignmentHistory(
                $lockedTicket,
                $assignmentAction,
                $fromUserId,
                $newAssigneeId,
                $fromTier,
                $newTier,
                $actor,
                $outcome === TicketTriageOutcome::Reject ? $rejectionReason : null,
                $suggestions->all(),
                $occurredAt,
            );
            $this->recordStatusHistory(
                $lockedTicket,
                $fromStatus,
                $newStatus,
                'ticket.triaged',
                $actor,
                $outcome === TicketTriageOutcome::Reject ? $rejectionReason : null,
                [
                    'outcome' => $outcome->value,
                    'assigned_to_id' => $newAssigneeId,
                    'assigned_tier' => $newTier,
                    'suggestions' => $suggestions->all(),
                ],
                $occurredAt,
            );

            $lockedTicket->load(['problemCategory', 'serviceType']);
            $after = $this->snapshot($lockedTicket);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.triaged',
                $lockedTicket,
                $outcome->label().'.',
                $before,
                $after,
            );

            if ($categoryChanged) {
                $this->auditLogger->succeeded(
                    $actor,
                    'ticket.category.changed',
                    $lockedTicket,
                    trim((string) $data['category_reason']),
                    ['problem_category_id' => $before['problem_category_id'], 'problem_category' => $before['problem_category']],
                    ['problem_category_id' => $after['problem_category_id'], 'problem_category' => $after['problem_category']],
                );
            }

            if ($priorityChanged) {
                $this->auditLogger->succeeded(
                    $actor,
                    'ticket.priority.changed',
                    $lockedTicket,
                    trim((string) $data['priority_reason']),
                    ['priority' => $before['priority']],
                    ['priority' => $after['priority']],
                );
            }

            if ($outcome === TicketTriageOutcome::Reject) {
                $this->notifications->send(
                    $lockedTicket,
                    'ticket_rejected',
                    'Tiket ditolak',
                    "Tiket {$lockedTicket->ticket_number} ditolak saat triase. Alasan: {$rejectionReason}",
                    [$lockedTicket->requester_id, $lockedTicket->created_by_id],
                    "ticket:{$lockedTicket->getKey()}:triaged:{$lockedTicket->statusHistories()->latest('id')->value('id')}",
                );
            } else {
                $isEscalated = $outcome === TicketTriageOutcome::TierTwo;
                $assignedName = $isEscalated ? $selectedUser->name : $actor->name;
                $this->notifications->send(
                    $lockedTicket,
                    $isEscalated ? 'ticket_escalated' : 'ticket_assigned',
                    $isEscalated ? 'Tiket dieskalasi ke Tier 2' : 'Tiket ditugaskan',
                    $isEscalated
                        ? "Tiket {$lockedTicket->ticket_number} dieskalasi kepada {$assignedName}."
                        : "Tiket {$lockedTicket->ticket_number} ditugaskan kepada {$assignedName}.",
                    [$lockedTicket->requester_id, $lockedTicket->created_by_id, $lockedTicket->assigned_to_id],
                    "ticket:{$lockedTicket->getKey()}:triaged:{$lockedTicket->statusHistories()->latest('id')->value('id')}",
                );
            }

            return $lockedTicket->fresh([
                'requester',
                'creator',
                'assignee',
                'lastTriagedBy',
                'serviceType',
                'serviceType.skills',
                'problemCategory',
            ]);
        });

        if ($result === null) {
            throw ValidationException::withMessages([$failureField => $failure ?: 'Tiket tidak dapat ditriase.']);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignTierTwo(User $actor, Ticket $ticket, array $data): Ticket
    {
        $this->authorization->authorize($actor, 'assignTierTwo', $ticket, 'ticket.assign_tier_2');

        $failure = null;
        $failureField = 'assigned_to_id';
        $result = $this->database->transaction(function () use ($actor, $ticket, $data, &$failure, &$failureField): ?Ticket {
            $lockedTicket = Ticket::query()->with(['problemCategory', 'serviceType'])->whereKey($ticket->getKey())->lockForUpdate()->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $failureField = 'ticket';
                $this->auditLogger->denied($actor, 'ticket.assign_tier_2', $ticket, $failure);

                return null;
            }

            if ($lockedTicket->status !== TicketStatus::Dikerjakan
                || $lockedTicket->assigned_tier !== Role::AgenTier1->value
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $failure = 'Hanya Agen Tier 1 yang sedang menangani tiket dapat menugaskannya ke Tier 2.';
                $failureField = 'ticket';
                $this->auditLogger->denied($actor, 'ticket.assign_tier_2', $lockedTicket, $failure);

                return null;
            }

            $selectedUser = $this->resolveEligibleTierTwoUser($data['assigned_to_id'] ?? null);

            if ($selectedUser === null) {
                $failure = 'Teknisi Tier 2 yang dipilih tidak aktif atau tidak memiliki peran Agen Tier 2.';
                $this->auditLogger->denied($actor, 'ticket.assign_tier_2', $lockedTicket, $failure);

                return null;
            }

            $suggestions = $this->skillSuggestions->forServiceType(
                $lockedTicket->serviceType,
                null,
                $lockedTicket->problemCategory,
            );
            $before = $this->snapshot($lockedTicket);
            $occurredAt = now();
            $lockedTicket->forceFill([
                'assigned_to_id' => $selectedUser->getKey(),
                'assigned_tier' => Role::AgenTier2->value,
            ])->save();

            $this->recordAssignmentHistory(
                $lockedTicket,
                TicketAssignmentAction::AssignedTier2,
                $actor->getKey(),
                $selectedUser->getKey(),
                Role::AgenTier1->value,
                Role::AgenTier2->value,
                $actor,
                filled($data['reason'] ?? null) ? trim((string) $data['reason']) : null,
                $suggestions->all(),
                $occurredAt,
            );
            $this->recordStatusHistory(
                $lockedTicket,
                TicketStatus::Dikerjakan,
                TicketStatus::Dikerjakan,
                'ticket.assign_tier_2',
                $actor,
                filled($data['reason'] ?? null) ? trim((string) $data['reason']) : null,
                ['assigned_to_id' => $selectedUser->getKey(), 'assigned_tier' => Role::AgenTier2->value, 'suggestions' => $suggestions->all()],
                $occurredAt,
            );

            $lockedTicket->load(['problemCategory', 'serviceType']);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.assign_tier_2',
                $lockedTicket,
                'Tiket ditugaskan kepada Agen Tier 2.',
                $before,
                $this->snapshot($lockedTicket),
            );

            $this->notifications->send(
                $lockedTicket,
                'ticket_escalated',
                'Tiket dieskalasi ke Tier 2',
                "Tiket {$lockedTicket->ticket_number} dieskalasi kepada {$selectedUser->name}.",
                [$lockedTicket->requester_id, $lockedTicket->created_by_id, $lockedTicket->assigned_to_id],
                "ticket:{$lockedTicket->getKey()}:assigned:{$lockedTicket->statusHistories()->latest('id')->value('id')}",
            );

            return $lockedTicket->fresh(['assignee', 'problemCategory', 'serviceType', 'serviceType.skills', 'lastTriagedBy']);
        });

        if ($result === null) {
            throw ValidationException::withMessages([$failureField => $failure ?: 'Tiket tidak dapat ditugaskan.']);
        }

        return $result;
    }

    public function returnToTierOne(User $actor, Ticket $ticket, string $reason): Ticket
    {
        $this->authorization->authorize($actor, 'returnToTierOne', $ticket, 'ticket.return_to_tier_1');

        $failure = null;
        $result = $this->database->transaction(function () use ($actor, $ticket, $reason, &$failure): ?Ticket {
            $lockedTicket = Ticket::query()->with('lastTriagedBy')->whereKey($ticket->getKey())->lockForUpdate()->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $this->auditLogger->denied($actor, 'ticket.return_to_tier_1', $ticket, $failure);

                return null;
            }

            if ($lockedTicket->status !== TicketStatus::Dikerjakan
                || $lockedTicket->assigned_tier !== Role::AgenTier2->value
                || (int) $lockedTicket->assigned_to_id !== (int) $actor->getKey()) {
                $failure = 'Tiket tidak dapat dikembalikan dari penugasan saat ini.';
                $this->auditLogger->denied($actor, 'ticket.return_to_tier_1', $lockedTicket, $failure);

                return null;
            }

            $target = $lockedTicket->lastTriagedBy;

            if ($target === null || ! $target->isActive() || ! $target->hasRole(Role::AgenTier1)) {
                $failure = 'Agen Tier 1 terakhir yang melakukan triase tidak lagi tersedia.';
                $this->auditLogger->denied($actor, 'ticket.return_to_tier_1', $lockedTicket, $failure);

                return null;
            }

            $before = $this->snapshot($lockedTicket);
            $occurredAt = now();
            $lockedTicket->forceFill([
                'status' => TicketStatus::Diproses,
                'assigned_to_id' => $target->getKey(),
                'assigned_tier' => Role::AgenTier1->value,
            ])->save();

            $this->recordAssignmentHistory(
                $lockedTicket,
                TicketAssignmentAction::ReturnedTier1,
                $actor->getKey(),
                $target->getKey(),
                Role::AgenTier2->value,
                Role::AgenTier1->value,
                $actor,
                trim($reason),
                null,
                $occurredAt,
            );
            $this->recordStatusHistory(
                $lockedTicket,
                TicketStatus::Dikerjakan,
                TicketStatus::Diproses,
                'ticket.return_to_tier_1',
                $actor,
                trim($reason),
                ['returned_to_user_id' => $target->getKey()],
                $occurredAt,
            );

            $lockedTicket->load(['assignee', 'lastTriagedBy']);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.return_to_tier_1',
                $lockedTicket,
                'Tiket dikembalikan kepada Agen Tier 1 terakhir yang melakukan triase.',
                $before,
                $this->snapshot($lockedTicket),
            );

            $this->notifications->send(
                $lockedTicket,
                'ticket_assigned',
                'Tiket dikembalikan ke Tier 1',
                "Tiket {$lockedTicket->ticket_number} dikembalikan kepada {$target->name}.",
                [$lockedTicket->requester_id, $lockedTicket->created_by_id, $lockedTicket->assigned_to_id],
                "ticket:{$lockedTicket->getKey()}:returned:{$lockedTicket->statusHistories()->latest('id')->value('id')}",
            );

            return $lockedTicket->fresh(['assignee', 'lastTriagedBy', 'problemCategory']);
        });

        if ($result === null) {
            throw ValidationException::withMessages(['ticket' => $failure ?: 'Tiket tidak dapat dikembalikan.']);
        }

        return $result;
    }

    private function resolveEligibleTierTwoUser(mixed $userId): ?User
    {
        if (! filled($userId)) {
            return null;
        }

        return User::query()
            ->whereKey((int) $userId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('roles.slug', Role::AgenTier2->value))
            ->first();
    }

    private function recordStatusHistory(
        Ticket $ticket,
        ?TicketStatus $from,
        TicketStatus $to,
        string $action,
        User $actor,
        ?string $reason,
        ?array $metadata,
        mixed $occurredAt,
    ): void {
        $ticket->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'action' => $action,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>|null  $suggestions
     */
    private function recordAssignmentHistory(
        Ticket $ticket,
        TicketAssignmentAction $action,
        ?int $fromUserId,
        ?int $toUserId,
        ?string $fromTier,
        ?string $toTier,
        User $actor,
        ?string $reason,
        ?array $suggestions,
        mixed $occurredAt,
    ): void {
        $ticket->assignmentHistories()->create([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'to_user_name_snapshot' => $toUserId !== null
                ? User::query()->whereKey($toUserId)->value('name')
                : null,
            'from_tier' => $fromTier,
            'to_tier' => $toTier,
            'action' => $action->value,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'suggestions' => $suggestions,
            'occurred_at' => $occurredAt,
        ]);
    }

    private function recordPriorityHistory(
        Ticket $ticket,
        ?string $from,
        Priority $to,
        User $actor,
        string $reason,
        mixed $occurredAt,
    ): void {
        $ticket->priorityHistories()->create([
            'from_priority' => $from,
            'to_priority' => $to->value,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'occurred_at' => $occurredAt,
        ]);
    }

    private function recordCategoryHistory(
        Ticket $ticket,
        ?ProblemCategory $from,
        ?ProblemCategory $to,
        User $actor,
        string $reason,
        mixed $occurredAt,
        mixed $fromId,
        ?string $fromName,
    ): void {
        $ticket->categoryHistories()->create([
            'from_category_id' => $from?->getKey() ?? ($fromId ?: null),
            'to_category_id' => $to?->getKey(),
            'from_category_name' => $from?->name ?? $fromName,
            'to_category_name' => $to?->name,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'occurred_at' => $occurredAt,
        ]);
    }

    /** @return array<string, mixed> */
    private function snapshot(Ticket $ticket): array
    {
        $category = $ticket->problemCategory;

        return [
            'ticket_id' => $ticket->getKey(),
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status?->value,
            'priority' => $ticket->priority?->value,
            'problem_category_id' => $ticket->problem_category_id,
            'problem_category' => $category?->name,
            'assigned_to_id' => $ticket->assigned_to_id,
            'assigned_tier' => $ticket->assigned_tier,
            'last_triaged_by_id' => $ticket->last_triaged_by_id,
            'rejection_reason' => $ticket->rejection_reason,
        ];
    }
}
