<?php

namespace App\Enums;

enum TeamPosition: string
{
    case Member = 'member';
    case Chair = 'chair';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Anggota',
            self::Chair => 'Ketua Tim Kerja',
        };
    }

    public function isChair(): bool
    {
        return $this === self::Chair;
    }
}
