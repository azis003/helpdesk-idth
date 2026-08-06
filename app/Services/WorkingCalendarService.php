<?php

namespace App\Services;

use App\Models\ServiceCalendar;
use Illuminate\Support\Carbon;

class WorkingCalendarService
{
    public function __construct(private readonly OperationalPolicyService $policies) {}

    public function dailyWorkingMinutes(?ServiceCalendar $calendar = null): int
    {
        $calendar = $this->resolveCalendar($calendar);
        $timezone = (string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE);
        $reference = Carbon::now($timezone)->startOfDay();
        $window = $this->dayWindow($reference, $calendar);

        return max(0, intdiv($window['close']->getTimestamp() - $window['open']->getTimestamp(), 60));
    }

    /**
     * Return the amount of time that falls inside the selected calendar's
     * working windows. Paused SLA segments never call this method, so the
     * result represents active SLA time rather than wall-clock time.
     */
    public function workingMinutesBetween(
        Carbon $start,
        Carbon $end,
        ?ServiceCalendar $calendar = null,
    ): int {
        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $calendar = $this->resolveCalendar($calendar);
        $timezone = (string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE);
        $from = $start->copy()->setTimezone($timezone);
        $to = $end->copy()->setTimezone($timezone);
        $cursor = $from->copy()->startOfDay();
        $minutes = 0;

        while ($cursor->lessThanOrEqualTo($to)) {
            if ($this->isWorkingDay($cursor, $calendar)) {
                $window = $this->dayWindow($cursor, $calendar);
                $windowStart = $from->greaterThan($window['open']) ? $from : $window['open'];
                $windowEnd = $to->lessThan($window['close']) ? $to : $window['close'];

                if ($windowEnd->greaterThan($windowStart)) {
                    $minutes += intdiv(
                        $windowEnd->getTimestamp() - $windowStart->getTimestamp(),
                        60,
                    );
                }
            }

            $cursor->addDay()->startOfDay();
        }

        return $minutes;
    }

    /**
     * Add active working minutes to a timestamp, skipping weekends,
     * configured holidays, and time outside the service window.
     */
    public function addWorkingMinutes(
        Carbon $start,
        int $minutes,
        ?ServiceCalendar $calendar = null,
    ): Carbon {
        $calendar = $this->resolveCalendar($calendar);
        $timezone = (string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE);
        $cursor = $start->copy()->setTimezone($timezone);
        $remainingSeconds = max(0, $minutes) * 60;

        if ($remainingSeconds === 0) {
            return $cursor;
        }

        while ($remainingSeconds > 0) {
            if (! $this->isWorkingDay($cursor, $calendar)) {
                $cursor->addDay()->startOfDay();

                continue;
            }

            $window = $this->dayWindow($cursor, $calendar);
            $candidate = $cursor->lessThan($window['open']) ? $window['open'] : $cursor;

            if ($candidate->greaterThanOrEqualTo($window['close'])) {
                $cursor->addDay()->startOfDay();

                continue;
            }

            $availableSeconds = $window['close']->getTimestamp() - $candidate->getTimestamp();

            if ($availableSeconds >= $remainingSeconds) {
                return $candidate->copy()->addSeconds($remainingSeconds);
            }

            $remainingSeconds -= $availableSeconds;
            $cursor->addDay()->startOfDay();
        }

        return $cursor;
    }

    public function isWithinWorkingDays(
        Carbon $from,
        Carbon $at,
        int $workingDays,
        ?ServiceCalendar $calendar = null,
    ): bool {
        if ($at->lessThanOrEqualTo($from)) {
            return true;
        }

        return $at->lessThanOrEqualTo($this->deadlineAfterWorkingDays($from, $workingDays, $calendar));
    }

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
        $day = $date->copy()->setTimezone((string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE));

        if (! in_array($day->dayOfWeekIso, array_map('intval', $calendar->working_days ?? []), true)) {
            return false;
        }

        $holiday = collect($calendar->holidays ?? [])
            ->contains(fn ($item): bool => $item->holiday_date?->format('Y-m-d') === $day->toDateString());

        return ! $holiday;
    }

    /** @return array{open:Carbon,close:Carbon} */
    private function dayWindow(Carbon $day, ServiceCalendar $calendar): array
    {
        $timezone = (string) ($calendar->timezone ?: OperationalPolicyService::TIMEZONE);
        $localDay = $day->copy()->setTimezone($timezone)->startOfDay();

        return [
            'open' => $localDay->copy()->setTimeFromTimeString(substr((string) $calendar->opens_at, 0, 8)),
            'close' => $localDay->copy()->setTimeFromTimeString(substr((string) $calendar->closes_at, 0, 8)),
        ];
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
