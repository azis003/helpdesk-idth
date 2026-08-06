<?php

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TicketPriorityHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'from_priority',
        'to_priority',
        'reason',
        'actor_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'from_priority' => Priority::class,
            'to_priority' => Priority::class,
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Histori prioritas tiket bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Histori prioritas tiket bersifat append-only.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
