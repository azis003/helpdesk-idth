<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Floor;
use App\Models\User;

class FloorPolicy
{
    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, Floor $floor): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
