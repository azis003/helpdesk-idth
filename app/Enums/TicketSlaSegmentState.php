<?php

namespace App\Enums;

enum TicketSlaSegmentState: string
{
    case Active = 'active';
    case Paused = 'paused';
}
