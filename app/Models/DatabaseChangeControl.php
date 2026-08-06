<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatabaseChangeControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'execution_started_by_id',
        'execution_started_at',
        'verified_by_id',
        'verified_at',
        'verification_result',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'execution_started_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function executionStartedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'execution_started_by_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DatabaseChangeControlHistory::class)
            ->orderBy('occurred_at')
            ->orderBy('id');
    }

    public function executionStarted(): bool
    {
        return $this->execution_started_at !== null;
    }

    public function verified(): bool
    {
        return $this->verified_at !== null
            && filled($this->verification_result)
            && filled($this->verification_notes);
    }
}
