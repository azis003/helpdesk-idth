<?php

namespace App\Enums;

enum TicketCommentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Balasan ke Pemohon',
            self::Internal => 'Catatan Internal',
        };
    }
}
