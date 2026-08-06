<?php

namespace App\Models;

use App\Enums\TicketAssignmentAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TicketAssignmentHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'from_user_id',
        'to_user_id',
        'from_tier',
        'to_tier',
        'action',
        'actor_id',
        'reason',
        'suggestions',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => TicketAssignmentAction::class,
            'suggestions' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Histori penugasan tiket bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Histori penugasan tiket bersifat append-only.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
