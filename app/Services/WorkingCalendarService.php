<?php

namespace App\Services;

use App\Models\ServiceCalendar;
use Illuminate\Support\Carbon;

class WorkingCalendarService
{
    public function __construct(private readonly OperationalPolicyService $policies) {}

    public function deadlineAfterWorkingDays(Carbon $start, int $workingDays, ?ServiceCalendar $calendar = null): Carbon
    {
        $calendar = $this->resolveCalendar($calendar);
        $timezone = (string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE);
        $cursor = $start->copy()->setTimezone($timezone)->startOfDay();
        $remaining = max(0, $workingDays);

        while ($remaining > 0) {
            $cursor->addDay();

            if ($this->isWorkingDay($cursor, $calendar)) {
                $remaining--;
            }
        }

        return $cursor->setTimeFromTimeString(substr((string) $calendar->closes_at, 0, 8));
    }

    public function isWorkingDay(Carbon $date, ?ServiceCalendar $calendar = null): bool
    {
        $calendar = $this->resolveCalendar($calendar);
        $day = $date->setTimezone((string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE));

        if (! in_array($day->dayOfWeekIso, array_map('intval', $calendar->working_days ?? []), true)) {
            return false;
        }

        $holiday = collect($calendar->holidays ?? [])
            ->contains(fn ($item): bool => $item->holiday_date?->format('Y-m-d') === $day->toDateString());

        return ! $holiday;
    }

    private function resolveCalendar(?ServiceCalendar $calendar): ServiceCalendar
    {
        if ($calendar !== null) {
            $calendar->loadMissing('holidays');

            return $calendar;
        }

        $current = $this->policies->currentCalendar();

        if ($current !== null) {
            return $current;
        }

        return new ServiceCalendar([
            'timezone' => OperationalPolicyService::TIMEZONE,
            'working_days' => OperationalPolicyService::DEFAULT_WORKING_DAYS,
            'opens_at' => '08:00:00',
            'closes_at' => '16:00:00',
            'version' => 0,
            'holidays' => collect(),
        ]);
    }
}
