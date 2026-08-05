<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Dikerjakan = 'dikerjakan';
}
