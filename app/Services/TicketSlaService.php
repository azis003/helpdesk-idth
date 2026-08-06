<?php

namespace App\Services;

use App\Enums\TicketSlaSegmentState;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketSlaSegment;
use Illuminate\Support\Carbon;

class TicketSlaService
{
    public function __construct(private readonly OperationalPolicyService $policies) {}

    public function start(Ticket $ticket, Carbon $at): ?TicketSlaSegment
    {
        $active = $this->activeSegment($ticket);

        if ($active !== null) {
            return $active;
        }

        $policy = $this->policyFor($ticket);

        if ($policy === null || ! $policy->uses_sla) {
            return null;
        }

        return $this->createSegment($ticket, TicketSlaSegmentState::Active, 'ticket_created', $policy, $at);
    }

    public function pause(Ticket $ticket, string $reason, Carbon $at): void
    {
        // Tickets created before this feature may not have a segment yet. Seed
        // one from the original submission timestamp before pausing it.
        $active = $this->activeSegment($ticket, true) ?? $this->ensureActive($ticket, $at);

        if ($active === null) {
            return;
        }

        $active->forceFill(['ended_at' => $at])->save();
        TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'state' => TicketSlaSegmentState::Paused,
            'reason' => $reason,
            'sla_policy_id' => $active->sla_policy_id,
            'target_working_days' => $active->target_working_days,
            'calendar_id' => $active->calendar_id,
            'calendar_version' => $active->calendar_version,
            'started_at' => $at,
        ]);
    }

    public function resume(Ticket $ticket, Carbon $at): void
    {
        $paused = TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->where('state', TicketSlaSegmentState::Paused->value)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();

        if ($paused === null) {
            return;
        }

        $paused->forceFill(['ended_at' => $at])->save();
        TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'state' => TicketSlaSegmentState::Active,
            'reason' => 'resumed',
            'sla_policy_id' => $paused->sla_policy_id,
            'target_working_days' => $paused->target_working_days,
            'calendar_id' => $paused->calendar_id,
            'calendar_version' => $paused->calendar_version,
            'started_at' => $at,
        ]);
    }

    public function activeSegment(Ticket $ticket, bool $lock = false): ?TicketSlaSegment
    {
        $query = TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->where('state', TicketSlaSegmentState::Active->value)
            ->whereNull('ended_at')
            ->latest('started_at');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function ensureActive(Ticket $ticket, Carbon $at): ?TicketSlaSegment
    {
        $policy = $this->policyFor($ticket);

        if ($policy === null || ! $policy->uses_sla) {
            return null;
        }

        $startedAt = $ticket->submitted_at ?? $ticket->created_at ?? $at;

        return $this->createSegment($ticket, TicketSlaSegmentState::Active, 'legacy_ticket_initialized', $policy, $startedAt);
    }

    private function policyFor(Ticket $ticket): ?SlaPolicy
    {
        $ticket->loadMissing('serviceType');

        return $ticket->serviceType?->activeSlaPolicy()->first();
    }

    private function createSegment(
        Ticket $ticket,
        TicketSlaSegmentState $state,
        string $reason,
        SlaPolicy $policy,
        Carbon $at,
    ): TicketSlaSegment {
        $calendar = $this->policies->currentCalendar();

        return TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'state' => $state,
            'reason' => $reason,
            'sla_policy_id' => $policy->getKey(),
            'target_working_days' => $policy->target_working_days,
            'calendar_id' => $calendar?->getKey(),
            'calendar_version' => $calendar?->version,
            'started_at' => $at,
        ]);
    }
}
