<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketNumberSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_class',
        'ticket_year',
        'last_sequence',
    ];

    protected function casts(): array
    {
        return [
            'ticket_year' => 'integer',
            'last_sequence' => 'integer',
        ];
    }
}
