<?php

namespace App\Models;

use App\Enums\TicketSlaSegmentState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSlaSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'cycle',
        'state',
        'reason',
        'sla_policy_id',
        'target_working_days',
        'target_working_minutes',
        'calendar_id',
        'calendar_version',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => TicketSlaSegmentState::class,
            'cycle' => 'integer',
            'target_working_days' => 'integer',
            'target_working_minutes' => 'integer',
            'calendar_version' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(ServiceCalendar::class, 'calendar_id');
    }
}
