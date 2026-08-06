<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(RoleAssignmentHistory::class);
    }

    public function isOperational(): bool
    {
        return RoleEnum::tryFrom($this->slug)?->isOperational() ?? false;
    }
}
