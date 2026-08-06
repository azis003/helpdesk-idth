<?php

namespace App\Enums;

enum TicketAssignmentAction: string
{
    case Claimed = 'claimed';
    case TriagedSelf = 'triaged_self';
    case TriagedTier2 = 'triaged_tier_2';
    case AssignedTier2 = 'assigned_tier_2';
    case ReturnedTier1 = 'returned_tier_1';
    case TriagedRejected = 'triaged_rejected';

    public function label(): string
    {
        return match ($this) {
            self::Claimed => 'Klaim antrean',
            self::TriagedSelf => 'Triase: kerjakan sendiri',
            self::TriagedTier2 => 'Triase: tugaskan Tier 2',
            self::AssignedTier2 => 'Penugasan ke Tier 2',
            self::ReturnedTier1 => 'Dikembalikan ke Tier 1',
            self::TriagedRejected => 'Triase: ditolak',
        };
    }
}
