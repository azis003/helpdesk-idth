<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketPriorityHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public const TIMEZONE = 'Asia/Jakarta';

    private const TERMINAL_STATUSES = [
        'ditutup',
        'ditolak',
        'tidak_disetujui',
        'dibatalkan',
    ];

    public function __construct(
        private readonly ApproverAssignmentService $approvers,
        private readonly TicketSlaService $sla,
        private readonly TeamScopeService $teamScope,
        private readonly TeamChairTicketProjection $teamChairProjection,
    ) {}

    /**
     * Build all dashboard blocks after the controller has resolved the period.
     * Every block is independently role-scoped so the view never has to infer
     * whether a record is safe to display.
     *
     * @return array<string, mixed>
     */
    public function build(User $user, Carbon $start, Carbon $end): array
    {
        $isTeamChair = $user->hasRole(Role::KetuaTimKerja);
        $canReviewApprovals = $this->approvers->isCurrentApprover($user);
        $canViewOverall = ! $isTeamChair
            && ($user->hasRole(Role::SuperAdmin)
            || $user->hasRole(Role::AgenTier1)
            || $canReviewApprovals);

        return [
            'requesterDashboard' => $user->hasRole(Role::Pemohon) && ! $isTeamChair
                ? $this->requesterDashboard($user, $start, $end)
                : $this->emptyRequesterDashboard(),
            'agentDashboard' => $user->hasAnyRole([Role::AgenTier1, Role::AgenTier2]) && ! $isTeamChair
                ? $this->agentDashboard($user, $start, $end)
                : $this->emptyAgentDashboard(),
            'approverDashboard' => $canReviewApprovals && ! $isTeamChair
                ? $this->approverDashboard($user, $start, $end)
                : $this->emptyApproverDashboard(),
            'teamDashboard' => $isTeamChair
                ? $this->teamDashboard($user, $start, $end)
                : $this->emptyTeamDashboard(),
            'overallDashboard' => $canViewOverall
                ? $this->overallDashboard($start, $end)
                : $this->emptyOverallDashboard(),
        ];
    }

    /** @return array<string, mixed> */
    private function requesterDashboard(User $user, Carbon $start, Carbon $end): array
    {
        $baseQuery = Ticket::query()->where('requester_id', $user->getKey());
        $periodQuery = $this->withinTicketPeriod(clone $baseQuery, $start, $end);
        $tickets = $this->ticketRelations($periodQuery)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $needsReplyTickets = $this->ticketRelations(clone $periodQuery)
            ->where('status', TicketStatus::MenungguPemohon->value)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
        $needsConfirmationTickets = $this->ticketRelations(clone $periodQuery)
            ->where('status', TicketStatus::MenungguKonfirmasi->value)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
        $displayTickets = $tickets
            ->merge($needsReplyTickets)
            ->merge($needsConfirmationTickets)
            ->unique(fn (Ticket $ticket): int => $ticket->getKey())
            ->values();
        $publicComments = $this->publicCommentsFor($displayTickets);
        $activeStatuses = array_diff(
            array_map(fn (TicketStatus $status): string => $status->value, TicketStatus::cases()),
            self::TERMINAL_STATUSES,
        );

        return [
            'visible' => true,
            'ticket_count' => (clone $periodQuery)->count(),
            'active_ticket_count' => (clone $periodQuery)->whereIn('status', $activeStatuses)->count(),
            'needs_reply_count' => (clone $periodQuery)->where('status', TicketStatus::MenungguPemohon->value)->count(),
            'needs_confirmation_count' => (clone $periodQuery)->where('status', TicketStatus::MenungguKonfirmasi->value)->count(),
            'tickets' => $tickets,
            'needs_reply_tickets' => $needsReplyTickets,
            'needs_confirmation_tickets' => $needsConfirmationTickets,
            'public_comments' => $publicComments,
            'notifications' => $user->notifications()
                ->whereBetween('created_at', [$start, $end])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    /** @return array<string, mixed> */
    private function agentDashboard(User $user, Carbon $start, Carbon $end): array
    {
        $assignedQuery = $this->withinTicketPeriod(
            Ticket::query()->where('assigned_to_id', $user->getKey()),
            $start,
            $end,
        );
        $assignedTickets = $this->ticketRelations(clone $assignedQuery)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $assignedCount = (clone $assignedQuery)->count();
        $waitingRequesterCount = (clone $assignedQuery)
            ->where('status', TicketStatus::MenungguPemohon->value)
            ->count();
        $waitingThirdPartyCount = (clone $assignedQuery)
            ->where('status', TicketStatus::MenungguPihakKetiga->value)
            ->count();
        $approvalWaitingQuery = (clone $assignedQuery)
            ->where('status', TicketStatus::MenungguPersetujuan->value);
        $approvalWaitingTickets = $this->ticketRelations(clone $approvalWaitingQuery)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $isTier1 = $user->hasRole(Role::AgenTier1);
        $queueQuery = $this->withinTicketPeriod(
            Ticket::query()->newQueue(),
            $start,
            $end,
        );
        $queueTickets = $isTier1
            ? $this->ticketRelations(clone $queueQuery)
                ->orderForTierOneQueue()
                ->limit(8)
                ->get()
            : collect();
        $queueCount = $isTier1 ? (clone $queueQuery)->count() : 0;

        $trackedQuery = $this->withinTicketPeriod(Ticket::query(), $start, $end)
            ->where(function (Builder $query) use ($user, $isTier1): void {
                $query->where('assigned_to_id', $user->getKey());

                if ($isTier1) {
                    $query->orWhere(function (Builder $query): void {
                        $query->where('status', TicketStatus::Baru->value)
                            ->whereNull('assigned_to_id');
                    });
                }
            });
        $trackedTickets = $this->ticketRelations($trackedQuery)
            ->with('slaSegments.calendar.holidays')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
        $slaEntries = $this->slaEntries($trackedTickets);
        $nearSla = $slaEntries
            ->filter(fn (array $entry): bool => (bool) ($entry['metrics']['near_limit'] ?? false))
            ->values();
        $overdueSla = $slaEntries
            ->filter(fn (array $entry): bool => (bool) ($entry['metrics']['overdue'] ?? false))
            ->values();

        $pendingApprovals = ApprovalRequest::query()
            ->pending()
            ->whereHas('ticket', function (Builder $query) use ($user, $start, $end): void {
                $this->withinTicketPeriod($query->where('assigned_to_id', $user->getKey()), $start, $end);
            })
            ->with(['ticket.requester', 'ticket.assignee', 'requestedBy'])
            ->orderBy('requested_at')
            ->orderBy('id')
            ->limit(5)
            ->get();

        return [
            'visible' => true,
            'is_tier_one' => $isTier1,
            'queue_count' => $queueCount,
            'queue_tickets' => $queueTickets,
            'assigned_count' => $assignedCount,
            'assigned_tickets' => $assignedTickets,
            'near_sla_count' => $nearSla->count(),
            'near_sla_tickets' => $nearSla,
            'overdue_sla_count' => $overdueSla->count(),
            'overdue_sla_tickets' => $overdueSla,
            'waiting_requester_count' => $waitingRequesterCount,
            'waiting_third_party_count' => $waitingThirdPartyCount,
            'approval_waiting_count' => (clone $approvalWaitingQuery)->count(),
            'approval_waiting_tickets' => $approvalWaitingTickets,
            'pending_approvals' => $pendingApprovals,
        ];
    }

    /** @return array<string, mixed> */
    private function approverDashboard(User $user, Carbon $start, Carbon $end): array
    {
        $pending = ApprovalRequest::query()
            ->pending()
            ->where('approver_id', $user->getKey())
            ->whereBetween('requested_at', [$start, $end])
            ->with(['ticket.requester', 'ticket.assignee', 'requestedBy'])
            ->orderBy('requested_at')
            ->orderBy('id')
            ->get();

        return [
            'visible' => true,
            'pending_count' => $pending->count(),
            'pending' => $pending,
        ];
    }

    /** @return array<string, mixed> */
    private function teamDashboard(User $user, Carbon $start, Carbon $end): array
    {
        $scope = $this->teamScope->scopeFor($user);
        $baseQuery = $this->teamChairProjection->query($user, true);
        $periodQuery = $this->withinTicketPeriod($baseQuery, $start, $end);
        $tickets = $periodQuery
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();
        $publicComments = $this->teamChairProjection->publicCommentsFor($tickets->modelKeys());
        $rows = $tickets->map(function (Ticket $ticket) use ($publicComments): array {
            $ticketView = $this->teamChairProjection->toView(
                $ticket,
                $publicComments->get($ticket->getKey(), collect()),
            );

            return [
                'ticket' => $ticketView,
                'public_reply' => $ticketView->latestPublicReply(),
                'sla' => $ticketView->sla,
            ];
        });

        return [
            'visible' => true,
            'team_names' => $scope['team_names'],
            'member_count' => count($scope['member_ids']),
            'ticket_count' => (clone $periodQuery)->count(),
            'rows' => $rows,
        ];
    }

    /** @return array<string, mixed> */
    private function overallDashboard(Carbon $start, Carbon $end): array
    {
        $tickets = $this->ticketRelations(
            $this->withinTicketPeriod(Ticket::query(), $start, $end),
        )
            ->with('slaSegments.calendar.holidays')
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $statusCounts = $tickets->countBy(fn (Ticket $ticket): string => $ticket->status?->value ?? 'unknown');
        $statusDistribution = collect(TicketStatus::cases())
            ->map(fn (TicketStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'count' => (int) $statusCounts->get($status->value, 0),
            ])
            ->filter(fn (array $item): bool => $item['count'] > 0)
            ->values();
        $slaEntries = $this->slaEntries($tickets);
        $slaTrackedCount = $tickets->filter(fn (Ticket $ticket): bool => $ticket->sla_compliant !== null)->count();

        return [
            'visible' => true,
            'ticket_count' => $tickets->count(),
            'service_distribution' => $this->distribution($tickets, fn (Ticket $ticket): string => $this->serviceLabel($ticket)),
            'category_distribution' => $this->distribution($tickets, fn (Ticket $ticket): string => $this->categoryLabel($ticket)),
            'status_distribution' => $statusDistribution,
            'sla' => [
                'tracked' => $slaTrackedCount,
                'compliant' => $tickets->where('sla_compliant', true)->count(),
                'breached' => $tickets->where('sla_compliant', false)->count(),
                'not_available' => $tickets->count() - $slaTrackedCount,
                'near_limit' => $slaEntries->filter(fn (array $entry): bool => (bool) ($entry['metrics']['near_limit'] ?? false))->count(),
                'overdue' => $slaEntries->filter(fn (array $entry): bool => (bool) ($entry['metrics']['overdue'] ?? false))->count(),
            ],
            'workload' => $this->distribution(
                $tickets->filter(fn (Ticket $ticket): bool => $ticket->assigned_to_id !== null),
                fn (Ticket $ticket): string => $ticket->assignee?->name ?? 'Penanggung jawab tidak tersedia',
            ),
            'reopened_count' => $tickets->filter(fn (Ticket $ticket): bool => (int) $ticket->reopen_count > 0)->count(),
            'priority_change_count' => TicketPriorityHistory::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->count(),
            'self_created' => collect([
                ['label' => 'Dibuat mandiri oleh Pemohon', 'count' => $tickets->where('is_self_created', true)->count()],
                ['label' => 'Dibuat Agen Tier 1 atas nama Pemohon', 'count' => $tickets->where('is_self_created', false)->count()],
            ]),
            'approval_self_count' => ApprovalRequest::query()
                ->whereColumn('requested_by', 'approver_id')
                ->whereBetween('requested_at', [$start, $end])
                ->count(),
            'building_distribution' => $this->distribution($tickets, fn (Ticket $ticket): string => $this->buildingLabel($ticket)),
            'floor_distribution' => $this->distribution($tickets, fn (Ticket $ticket): string => $this->floorLabel($ticket)),
        ];
    }

    /**
     * Apply the ticket reporting period. submitted_at is the business time;
     * created_at is only a fallback for legacy/test rows without a submission
     * timestamp.
     */
    private function withinTicketPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where(function (Builder $query) use ($start, $end): void {
            $query->whereBetween('submitted_at', [$start, $end])
                ->orWhere(function (Builder $query) use ($start, $end): void {
                    $query->whereNull('submitted_at')
                        ->whereBetween('created_at', [$start, $end]);
                });
        });
    }

    private function ticketRelations(Builder $query): Builder
    {
        return $query->with([
            'serviceType',
            'problemCategory',
            'requester',
            'assignee',
            'room.floor.building',
        ]);
    }

    /** @param Collection<int, Ticket> $tickets */
    private function publicCommentsFor(Collection $tickets): Collection
    {
        $ticketIds = $tickets->pluck('id')->filter()->values();

        if ($ticketIds->isEmpty()) {
            return collect();
        }

        return TicketComment::query()
            ->with('author')
            ->whereIn('ticket_id', $ticketIds)
            ->where('visibility', TicketCommentVisibility::Public->value)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('ticket_id')
            ->map(fn (Collection $comments): TicketComment => $comments->first());
    }

    /**
     * @param  Collection<int, Ticket>  $tickets
     * @return Collection<int, array{ticket:Ticket,metrics:array<string,mixed>}>
     */
    private function slaEntries(Collection $tickets): Collection
    {
        $now = Carbon::now(self::TIMEZONE);

        return $tickets
            ->map(function (Ticket $ticket) use ($now): ?array {
                $metrics = $this->sla->metrics($ticket, $now);

                if ($metrics === null || ! ($metrics['uses_sla'] ?? false)) {
                    return null;
                }

                return [
                    'ticket' => $ticket,
                    'metrics' => $metrics,
                ];
            })
            ->filter()
            ->values();
    }

    /** @param Collection<int, Ticket> $tickets */
    private function distribution(Collection $tickets, callable $label): Collection
    {
        return $tickets
            ->groupBy($label)
            ->map(fn (Collection $items, string $name): array => [
                'label' => $name,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values();
    }

    private function serviceLabel(Ticket $ticket): string
    {
        return $ticket->service_type_name_snapshot
            ?: $ticket->serviceType?->name
            ?: $ticket->service_type_code_snapshot
            ?: 'Layanan belum dikategorikan';
    }

    private function categoryLabel(Ticket $ticket): string
    {
        return $ticket->problemCategory?->name ?: 'Belum dikategorikan';
    }

    private function buildingLabel(Ticket $ticket): string
    {
        return $ticket->building_name_snapshot
            ?: $ticket->room?->floor?->building?->name
            ?: 'Lokasi belum diisi';
    }

    private function floorLabel(Ticket $ticket): string
    {
        return $ticket->floor_name_snapshot
            ?: $ticket->room?->floor?->name
            ?: 'Lantai belum diisi';
    }

    /** @return array<string, mixed> */
    private function emptyRequesterDashboard(): array
    {
        return [
            'visible' => false,
            'ticket_count' => 0,
            'active_ticket_count' => 0,
            'needs_reply_count' => 0,
            'needs_confirmation_count' => 0,
            'tickets' => collect(),
            'needs_reply_tickets' => collect(),
            'needs_confirmation_tickets' => collect(),
            'public_comments' => collect(),
            'notifications' => collect(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyAgentDashboard(): array
    {
        return [
            'visible' => false,
            'is_tier_one' => false,
            'queue_count' => 0,
            'queue_tickets' => collect(),
            'assigned_count' => 0,
            'assigned_tickets' => collect(),
            'near_sla_count' => 0,
            'near_sla_tickets' => collect(),
            'overdue_sla_count' => 0,
            'overdue_sla_tickets' => collect(),
            'waiting_requester_count' => 0,
            'waiting_third_party_count' => 0,
            'approval_waiting_count' => 0,
            'approval_waiting_tickets' => collect(),
            'pending_approvals' => collect(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyApproverDashboard(): array
    {
        return [
            'visible' => false,
            'pending_count' => 0,
            'pending' => collect(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyTeamDashboard(): array
    {
        return [
            'visible' => false,
            'team_names' => [],
            'member_count' => 0,
            'ticket_count' => 0,
            'rows' => collect(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyOverallDashboard(): array
    {
        return [
            'visible' => false,
            'ticket_count' => 0,
            'service_distribution' => collect(),
            'category_distribution' => collect(),
            'status_distribution' => collect(),
            'sla' => [
                'tracked' => 0,
                'compliant' => 0,
                'breached' => 0,
                'not_available' => 0,
                'near_limit' => 0,
                'overdue' => 0,
            ],
            'workload' => collect(),
            'reopened_count' => 0,
            'priority_change_count' => 0,
            'self_created' => collect(),
            'approval_self_count' => 0,
            'building_distribution' => collect(),
            'floor_distribution' => collect(),
        ];
    }
}
