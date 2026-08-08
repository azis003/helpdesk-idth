<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'nip',
        'password',
        'is_active',
        'must_change_password',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function approverAssignments(): HasMany
    {
        return $this->hasMany(ApproverAssignment::class);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'approver_id');
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    public function currentTeamMembership(): HasOne
    {
        return $this->hasOne(TeamMembership::class)->where('is_active', true);
    }

    public function teamChairAssignments(): HasMany
    {
        return $this->hasMany(TeamChairAssignment::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function roleAssignmentHistories(): HasMany
    {
        return $this->hasMany(RoleAssignmentHistory::class);
    }

    public function hasRole(RoleEnum|string $role): bool
    {
        $slug = $role instanceof RoleEnum ? $role->value : $role;

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('slug', $slug);
        }

        return $this->roles()->where('slug', $slug)->exists();
    }

    /**
     * @param  iterable<RoleEnum|string>  $roles
     */
    public function hasAnyRole(iterable $roles): bool
    {
        $slugs = collect($roles)
            ->map(fn (RoleEnum|string $role) => $role instanceof RoleEnum ? $role->value : $role)
            ->values();

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role) => $slugs->contains($role->slug));
        }

        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function hasOperationalRole(): bool
    {
        return $this->hasAnyRole([RoleEnum::AgenTier1, RoleEnum::AgenTier2]);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function requiresPasswordChange(): bool
    {
        return $this->must_change_password === true || $this->password_changed_at === null;
    }

    /**
     * @return list<string>
     */
    public function roleLabels(): array
    {
        return $this->roles
            ->map(fn (Role $role): string => $role->managementLabel())
            ->values()
            ->all();
    }
}
