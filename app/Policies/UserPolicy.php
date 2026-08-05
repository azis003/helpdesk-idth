<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::SuperAdmin)
            && $actor->isNot($target);
    }
}
