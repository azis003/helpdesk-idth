<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, Skill $skill): bool
    {
        return $this->canManage($actor);
    }

    public function delete(User $actor, Skill $skill): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
