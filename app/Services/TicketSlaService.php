<?php

namespace App\Services;

use App\Enums\TicketSlaSegmentState;
use App\Models\ServiceCalendar;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketSlaSegment;
use Illuminate\Support\Carbon;

class TicketSlaService
{
    public function __construct(
        private readonly OperationalPolicyService $policies,
        private readonly WorkingCalendarService $calendar,
    ) {}

    public function start(Ticket $ticket, Carbon $at): ?TicketSlaSegment
    {
        $open = $this->openSegment($ticket);

        if ($open !== null) {
            return $open;
        }

        $policy = $this->policyFor($ticket);

        if ($policy === null || ! $policy->uses_sla) {
            return null;
        }

        $cycle = max(1, (int) ($ticket->sla_cycle ?? 1));
        $segment = $this->createSegment(
            $ticket,
            TicketSlaSegmentState::Active,
            'ticket_created',
            $policy,
            $at,
            $cycle,
        );
        $ticket->forceFill([
            'sla_cycle' => $cycle,
            'sla_compliant' => null,
            'sla_elapsed_working_minutes' => null,
        ])->save();

        return $segment;
    }

    public function startNewCycle(Ticket $ticket, Carbon $at): ?TicketSlaSegment
    {
        $this->stop($ticket, $at);

        $policy = $this->policyFor($ticket);
        $cycle = $this->latestCycle($ticket) + 1;

        $ticket->forceFill([
            'sla_cycle' => $cycle,
            'sla_compliant' => null,
            'sla_elapsed_working_minutes' => null,
        ])->save();

        if ($policy === null || ! $policy->uses_sla) {
            return null;
        }

        return $this->createSegment(
            $ticket,
            TicketSlaSegmentState::Active,
            'ticket_reopened',
            $policy,
            $at,
            $cycle,
        );
    }

    public function pause(Ticket $ticket, string $reason, Carbon $at): void
    {
        $open = $this->openSegment($ticket, true);

        if ($open?->state === TicketSlaSegmentState::Paused) {
            return;
        }

        // Tickets created before SLA segments were introduced can still enter
        // a waiting state. Initialize their first segment from submission time.
        $active = $open?->state === TicketSlaSegmentState::Active
            ? $open
            : ($this->activeSegment($ticket, true) ?? $this->ensureActive($ticket, $at));

        if ($active === null) {
            return;
        }

        $active->forceFill(['ended_at' => $at])->save();
        $this->createSegmentFromSnapshot(
            $ticket,
            TicketSlaSegmentState::Paused,
            $reason,
            $active,
            $at,
        );
    }

    public function resume(Ticket $ticket, Carbon $at): void
    {
        $paused = TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->where('cycle', $this->latestCycle($ticket))
            ->where('state', TicketSlaSegmentState::Paused->value)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->lockForUpdate()
            ->first();

        if ($paused === null) {
            return;
        }

        $paused->forceFill(['ended_at' => $at])->save();
        $this->createSegmentFromSnapshot(
            $ticket,
            TicketSlaSegmentState::Active,
            'resumed',
            $paused,
            $at,
        );
    }

    public function stop(Ticket $ticket, Carbon $at): void
    {
        $open = $this->openSegment($ticket, true);

        if ($open !== null) {
            $open->forceFill(['ended_at' => $at])->save();
        }
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

    /**
     * @return array<string, mixed>|null
     */
    public function metrics(Ticket $ticket, ?Carbon $at = null): ?array
    {
        $at ??= Carbon::now(config('app.timezone'));
        $segments = TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->with('calendar.holidays')
            ->orderBy('cycle')
            ->orderBy('started_at')
            ->orderBy('id')
            ->get();

        if ($segments->isEmpty()) {
            return null;
        }

        $cycle = $this->latestCycle($ticket, $segments);
        $cycleSegments = $segments->filter(fn (TicketSlaSegment $segment): bool => (int) $segment->cycle === $cycle)->values();
        $snapshot = $cycleSegments->first();

        if ($snapshot === null) {
            return null;
        }

        $targetMinutes = $snapshot->target_working_minutes;
        if ($targetMinutes === null && $snapshot->target_working_days !== null) {
            $targetMinutes = (int) $snapshot->target_working_days
                * $this->calendar->dailyWorkingMinutes($this->calendarForSegment($snapshot));
        }

        if ($targetMinutes === null || $targetMinutes < 1) {
            return [
                'uses_sla' => false,
                'cycle' => $cycle,
                'target_working_days' => null,
                'target_working_minutes' => null,
                'elapsed_working_minutes' => null,
                'remaining_working_minutes' => null,
                'remaining_minutes' => null,
                'remaining_percent' => null,
                'near_limit' => false,
                'overdue' => false,
                'paused' => false,
                'is_paused' => false,
                'compliant' => null,
                'within_target' => null,
                'started_at' => $snapshot->started_at,
                'deadline_at' => null,
                'sla_policy_id' => $snapshot->sla_policy_id,
                'calendar_id' => $snapshot->calendar_id,
                'calendar_version' => $snapshot->calendar_version,
            ];
        }

        $elapsed = 0;
        $paused = false;
        foreach ($cycleSegments as $segment) {
            if ($segment->state === TicketSlaSegmentState::Paused) {
                $paused = $paused || $segment->ended_at === null;

                continue;
            }

            $end = $segment->ended_at ?? $at;
            if ($end->greaterThan($at)) {
                $end = $at;
            }

            $elapsed += $this->calendar->workingMinutesBetween(
                $segment->started_at,
                $end,
                $this->calendarForSegment($segment),
            );
        }

        $remaining = max(0, $targetMinutes - $elapsed);
        $warningPercent = (int) ($this->policies->settings()['sla_warning_percent'] ?? 20);
        $nearLimit = $remaining > 0 && ($remaining * 100) <= ($targetMinutes * $warningPercent);
        $deadline = $this->deadlineFor($cycleSegments, $targetMinutes, $at);
        $compliant = $ticket->sla_compliant;
        if ($compliant === null && in_array($ticket->status?->value, ['menunggu_konfirmasi', 'ditutup'], true)) {
            $compliant = $elapsed <= $targetMinutes;
        }

        return [
            'uses_sla' => true,
            'cycle' => $cycle,
            'target_working_days' => $snapshot->target_working_days,
            'target_working_minutes' => $targetMinutes,
            'target_minutes' => $targetMinutes,
            'elapsed_working_minutes' => $elapsed,
            'elapsed_active_minutes' => $elapsed,
            'remaining_working_minutes' => $remaining,
            'remaining_minutes' => $remaining,
            'remaining_percent' => round(($remaining / $targetMinutes) * 100, 2),
            'near_limit' => $nearLimit,
            'overdue' => $remaining === 0 && ! $paused,
            'paused' => $paused,
            'is_paused' => $paused,
            'compliant' => $compliant,
            'within_target' => $elapsed <= $targetMinutes,
            'started_at' => $snapshot->started_at,
            'deadline_at' => $deadline,
            'sla_policy_id' => $snapshot->sla_policy_id,
            'calendar_id' => $snapshot->calendar_id,
            'calendar_version' => $snapshot->calendar_version,
        ];
    }

    /** @return array<string, mixed>|null */
    public function calculate(Ticket $ticket, ?Carbon $at = null): ?array
    {
        return $this->metrics($ticket, $at);
    }

    public function remainingMinutes(Ticket $ticket, ?Carbon $at = null): ?int
    {
        $metrics = $this->metrics($ticket, $at);

        return $metrics === null || $metrics['remaining_minutes'] === null
            ? null
            : (int) $metrics['remaining_minutes'];
    }

    public function remainingActiveMinutes(Ticket $ticket, ?Carbon $at = null): ?int
    {
        return $this->remainingMinutes($ticket, $at);
    }

    public function deadlineAt(Ticket $ticket, ?Carbon $at = null): ?Carbon
    {
        return $this->metrics($ticket, $at)['deadline_at'] ?? null;
    }

    public function isNearLimit(Ticket $ticket, ?Carbon $at = null): bool
    {
        return (bool) ($this->metrics($ticket, $at)['near_limit'] ?? false);
    }

    public function isOverdue(Ticket $ticket, ?Carbon $at = null): bool
    {
        return (bool) ($this->metrics($ticket, $at)['overdue'] ?? false);
    }

    public function isCompliant(Ticket $ticket, ?Carbon $at = null): ?bool
    {
        $compliant = $this->metrics($ticket, $at)['compliant'] ?? null;

        return $compliant === null ? null : (bool) $compliant;
    }

    private function ensureActive(Ticket $ticket, Carbon $at): ?TicketSlaSegment
    {
        $policy = $this->policyFor($ticket);

        if ($policy === null || ! $policy->uses_sla) {
            return null;
        }

        $startedAt = $ticket->submitted_at ?? $ticket->created_at ?? $at;
        $cycle = max(1, (int) ($ticket->sla_cycle ?? $this->latestCycle($ticket)));

        return $this->createSegment(
            $ticket,
            TicketSlaSegmentState::Active,
            'legacy_ticket_initialized',
            $policy,
            $startedAt,
            $cycle,
        );
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
        int $cycle,
    ): TicketSlaSegment {
        $calendar = $this->policies->currentCalendar();
        $targetDays = $policy->target_working_days;
        $targetMinutes = $targetDays === null
            ? null
            : $targetDays * $this->calendar->dailyWorkingMinutes($calendar);

        return TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'cycle' => $cycle,
            'state' => $state,
            'reason' => $reason,
            'sla_policy_id' => $policy->getKey(),
            'target_working_days' => $targetDays,
            'target_working_minutes' => $targetMinutes,
            'calendar_id' => $calendar?->getKey(),
            'calendar_version' => $calendar?->version,
            'started_at' => $at,
        ]);
    }

    private function createSegmentFromSnapshot(
        Ticket $ticket,
        TicketSlaSegmentState $state,
        string $reason,
        TicketSlaSegment $source,
        Carbon $at,
    ): TicketSlaSegment {
        return TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'cycle' => $source->cycle,
            'state' => $state,
            'reason' => $reason,
            'sla_policy_id' => $source->sla_policy_id,
            'target_working_days' => $source->target_working_days,
            'target_working_minutes' => $source->target_working_minutes,
            'calendar_id' => $source->calendar_id,
            'calendar_version' => $source->calendar_version,
            'started_at' => $at,
        ]);
    }

    private function openSegment(Ticket $ticket, bool $lock = false): ?TicketSlaSegment
    {
        $query = TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->whereNull('ended_at')
            ->latest('started_at');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function latestCycle(Ticket $ticket, mixed $segments = null): int
    {
        if ($segments !== null) {
            $max = collect($segments)->max(fn (TicketSlaSegment $segment): int => (int) ($segment->cycle ?? 1));

            return max(1, (int) $max);
        }

        $max = (int) TicketSlaSegment::query()
            ->where('ticket_id', $ticket->getKey())
            ->max('cycle');

        return max(1, $max, (int) ($ticket->sla_cycle ?? 1));
    }

    private function calendarForSegment(TicketSlaSegment $segment): ?ServiceCalendar
    {
        if ($segment->calendar_id === null) {
            return null;
        }

        if ($segment->relationLoaded('calendar')) {
            return $segment->calendar;
        }

        return ServiceCalendar::query()
            ->with('holidays')
            ->find($segment->calendar_id);
    }

    /** @param iterable<TicketSlaSegment> $segments */
    private function deadlineFor(iterable $segments, int $targetMinutes, Carbon $at): ?Carbon
    {
        $remaining = $targetMinutes;

        foreach ($segments as $segment) {
            if ($segment->state !== TicketSlaSegmentState::Active) {
                continue;
            }

            $calendar = $this->calendarForSegment($segment);
            $end = $segment->ended_at ?? $at;
            if ($end->greaterThan($at)) {
                $end = $at;
            }

            $available = $this->calendar->workingMinutesBetween($segment->started_at, $end, $calendar);

            if ($available >= $remaining) {
                return $this->calendar->addWorkingMinutes($segment->started_at, $remaining, $calendar);
            }

            $remaining -= $available;

            if ($segment->ended_at === null) {
                return $this->calendar->addWorkingMinutes($at, $remaining, $calendar);
            }
        }

        return $remaining <= 0 ? $at : null;
    }
}
