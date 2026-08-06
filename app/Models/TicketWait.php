<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketWaitEndReason;
use App\Enums\TicketWaitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class TicketWait extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'kind',
        'started_by_id',
        'from_status',
        'from_assignee_id',
        'from_assigned_tier',
        'started_at',
        'due_at',
        'ended_at',
        'ended_by_id',
        'end_reason',
        'timed_out',
        'third_party_name',
        'follow_up_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'kind' => TicketWaitType::class,
            'from_status' => TicketStatus::class,
            'end_reason' => TicketWaitEndReason::class,
            'started_at' => 'datetime',
            'due_at' => 'datetime',
            'ended_at' => 'datetime',
            'follow_up_date' => 'date',
            'timed_out' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn () => throw new LogicException('Histori waktu tunggu tiket tidak dapat dihapus.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_id');
    }

    public function fromAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_assignee_id');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    public function scopeRequester(Builder $query): Builder
    {
        return $query->where('kind', TicketWaitType::Requester->value);
    }
}
