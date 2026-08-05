<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Pemohon = 'pemohon';
    case AgenTier1 = 'agen_tier_1';
    case AgenTier2 = 'agen_tier_2';
    case Approver = 'approver';
    case KetuaTimKerja = 'ketua_tim_kerja';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Pemohon => 'Pemohon',
            self::AgenTier1 => 'Agen Tier 1',
            self::AgenTier2 => 'Agen Tier 2',
            self::Approver => 'Approver',
            self::KetuaTimKerja => 'Ketua Tim Kerja',
        };
    }

    public function isOperational(): bool
    {
        return match ($this) {
            self::AgenTier1, self::AgenTier2 => true,
            default => false,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }
}
