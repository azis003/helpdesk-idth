<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ProblemCategory;
use App\Models\User;

class ProblemCategoryPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, ProblemCategory $category): bool
    {
        return $this->canManage($actor);
    }

    public function delete(User $actor, ProblemCategory $category): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
