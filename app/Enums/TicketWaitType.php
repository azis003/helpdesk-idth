<?php

namespace App\Enums;

enum TicketWaitType: string
{
    case Requester = 'requester';
    case ThirdParty = 'third_party';

    public function label(): string
    {
        return match ($this) {
            self::Requester => 'Menunggu Pemohon',
            self::ThirdParty => 'Menunggu Pihak Ketiga',
        };
    }
}
