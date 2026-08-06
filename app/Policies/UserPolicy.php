<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->canManage($actor);
    }

    public function activate(User $actor, User $target): bool
    {
        return $this->canManage($actor) && $actor->isNot($target);
    }

    public function deactivate(User $actor, User $target): bool
    {
        return $this->canManage($actor) && $actor->isNot($target);
    }

    public function manageRoles(User $actor, User $target): bool
    {
        return $this->canManage($actor);
    }

    public function manageTeam(User $actor, User $target): bool
    {
        return $this->canManage($actor);
    }

    public function manageSkills(User $actor, User $target): bool
    {
        return $this->canManage($actor);
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $this->canManage($actor) && $actor->isNot($target);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
