<?php

namespace App\Services;

use App\Enums\TicketCommentVisibility;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\ViewModels\TeamChairCommentView;
use App\ViewModels\TeamChairTicketView;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeamChairTicketProjection
{
    /**
     * These are the only ticket columns that may be used to build the
     * Ketua Tim Kerja read model. Sensitive descriptions, identifiers,
     * location data, internal workflow fields, and audit snapshots stay out.
     *
     * @var list<string>
     */
    private const TICKET_COLUMNS = [
        'id',
        'ticket_number',
        'subject',
        'requester_id',
        'requester_name_snapshot',
        'requester_team_snapshot',
        'service_type_id',
        'service_type_code_snapshot',
        'service_type_name_snapshot',
        'problem_category_id',
        'problem_category_name_snapshot',
        'priority',
        'status',
        'assigned_to_id',
        'assigned_tier',
        'solution',
        'submitted_at',
        'created_at',
        'updated_at',
        'sla_compliant',
    ];

    /**
     * @var list<string>
     */
    private const SLA_SEGMENT_COLUMNS = [
        'id',
        'ticket_id',
        'cycle',
        'state',
        'target_working_days',
        'target_working_minutes',
        'calendar_id',
        'started_at',
        'ended_at',
    ];

    public function __construct(
        private readonly TeamScopeService $teamScope,
        private readonly TicketSlaService $sla,
    ) {}

    public function query(User $chair, bool $withSla = false): Builder
    {
        $query = Ticket::query()
            ->select(self::TICKET_COLUMNS)
            ->with([
                'requester:id,name',
                'assignee:id,name',
                'serviceType:id,code,name',
                'problemCategory:id,name',
            ]);

        if ($withSla) {
            $query->with([
                'slaSegments' => fn ($segmentQuery) => $segmentQuery
                    ->select(self::SLA_SEGMENT_COLUMNS),
            ]);
        }

        return $this->teamScope->constrain($query, $chair);
    }

    public function find(User $chair, int $ticketId): ?TeamChairTicketView
    {
        $ticket = $this->query($chair, true)->whereKey($ticketId)->first();

        if ($ticket === null) {
            return null;
        }

        $comments = $this->publicCommentsFor([$ticket->getKey()])
            ->get($ticket->getKey(), collect());

        return $this->toView($ticket, $comments);
    }

    /**
     * @return LengthAwarePaginator<int, TeamChairTicketView>
     */
    public function paginate(User $chair, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->query($chair, true)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (Ticket $ticket): TeamChairTicketView => $this->toView($ticket))
                ->values(),
        );

        return $paginator;
    }

    /**
     * @param  iterable<int|string>  $ticketIds
     * @return Collection<int, Collection<int, TeamChairCommentView>>
     */
    public function publicCommentsFor(iterable $ticketIds): Collection
    {
        $ids = collect($ticketIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return TicketComment::query()
            ->select(['id', 'ticket_id', 'author_id', 'body', 'created_at'])
            ->whereIn('ticket_id', $ids->all())
            ->where('visibility', TicketCommentVisibility::Public->value)
            ->with('author:id,name')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('ticket_id')
            ->map(fn (Collection $comments): Collection => $comments
                ->map(fn (TicketComment $comment): TeamChairCommentView => new TeamChairCommentView(
                    authorName: $comment->relationLoaded('author') ? $comment->author?->name : null,
                    body: (string) $comment->body,
                    createdAt: $comment->created_at,
                ))
                ->values(),
            );
    }

    /**
     * Convert an already projection-loaded model to the explicit read model.
     * No relation is lazily loaded here; an omitted relation is treated as
     * unavailable instead of expanding the server response accidentally.
     *
     * @param  Collection<int, TeamChairCommentView>|null  $publicComments
     */
    public function toView(Ticket $ticket, ?Collection $publicComments = null): TeamChairTicketView
    {
        $requester = $ticket->relationLoaded('requester') ? $ticket->requester : null;
        $assignee = $ticket->relationLoaded('assignee') ? $ticket->assignee : null;
        $serviceType = $ticket->relationLoaded('serviceType') ? $ticket->serviceType : null;
        $problemCategory = $ticket->relationLoaded('problemCategory') ? $ticket->problemCategory : null;
        $metrics = $this->safeSla($this->sla->metrics($ticket));

        return new TeamChairTicketView(
            id: (int) $ticket->getKey(),
            ticketNumber: $ticket->ticket_number,
            subject: (string) $ticket->subject,
            requesterName: $ticket->requester_name_snapshot ?: $requester?->name,
            teamName: $ticket->requester_team_snapshot,
            serviceCode: $ticket->service_type_code_snapshot ?: $serviceType?->code,
            serviceName: $ticket->service_type_name_snapshot ?: $serviceType?->name,
            categoryName: $ticket->problem_category_name_snapshot ?: $problemCategory?->name,
            priority: $ticket->priority,
            status: $ticket->status,
            submittedAt: $ticket->submitted_at ?? $ticket->created_at,
            updatedAt: $ticket->updated_at,
            assigneeName: $assignee?->name,
            assignedTier: $ticket->assigned_tier,
            solution: $ticket->solution,
            sla: $metrics,
            publicComments: $publicComments ?? collect(),
        );
    }

    /**
     * @param  array<string, mixed>|null  $metrics
     * @return array<string, mixed>|null
     */
    private function safeSla(?array $metrics): ?array
    {
        if ($metrics === null) {
            return null;
        }

        return [
            'uses_sla' => (bool) ($metrics['uses_sla'] ?? false),
            'target_working_days' => $metrics['target_working_days'] ?? null,
            'target_working_minutes' => $metrics['target_working_minutes'] ?? null,
            'remaining_minutes' => $metrics['remaining_minutes'] ?? null,
            'remaining_percent' => $metrics['remaining_percent'] ?? null,
            'near_limit' => (bool) ($metrics['near_limit'] ?? false),
            'overdue' => (bool) ($metrics['overdue'] ?? false),
            'paused' => (bool) ($metrics['paused'] ?? false),
            'compliant' => $metrics['compliant'] ?? null,
        ];
    }
}
