<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    public function currentMemberships(): HasMany
    {
        return $this->memberships()->where('is_active', true);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_memberships')
            ->withPivot(['assigned_by', 'started_at', 'ended_at', 'is_active'])
            ->withTimestamps();
    }

    public function currentMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('is_active', true);
    }

    public function chairAssignments(): HasMany
    {
        return $this->hasMany(TeamChairAssignment::class);
    }

    public function currentChair(): HasOne
    {
        return $this->hasOne(TeamChairAssignment::class)->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
