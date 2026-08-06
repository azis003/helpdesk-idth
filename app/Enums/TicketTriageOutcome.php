<?php

namespace App\Enums;

enum TicketTriageOutcome: string
{
    case Self = 'self';
    case TierTwo = 'tier_2';
    case Reject = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::Self => 'Kerjakan sendiri',
            self::TierTwo => 'Tugaskan Tier 2',
            self::Reject => 'Tolak',
        };
    }
}
