<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamChairAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_team_id',
        'user_id',
        'assigned_by',
        'started_at',
        'ended_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'work_team_id' => 'integer',
            'user_id' => 'integer',
            'assigned_by' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function workTeam(): BelongsTo
    {
        return $this->belongsTo(WorkTeam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
