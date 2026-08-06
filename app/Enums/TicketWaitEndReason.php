<?php

namespace App\Enums;

enum TicketWaitEndReason: string
{
    case RequesterReplied = 'requester_replied';
    case Timeout = 'timeout';
    case ThirdPartyCompleted = 'third_party_completed';

    public function label(): string
    {
        return match ($this) {
            self::RequesterReplied => 'Pemohon membalas',
            self::Timeout => 'Waktu tunggu berakhir',
            self::ThirdPartyCompleted => 'Ketergantungan selesai',
        };
    }
}
