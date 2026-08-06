<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DatabaseChangeControlHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'database_change_control_id',
        'ticket_id',
        'action',
        'actor_id',
        'reason',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Histori kontrol perubahan database bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Histori kontrol perubahan database bersifat append-only.'));
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(DatabaseChangeControl::class, 'database_change_control_id');
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
