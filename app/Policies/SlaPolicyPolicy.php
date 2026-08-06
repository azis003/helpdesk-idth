<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SlaPolicy;
use App\Models\User;

class SlaPolicyPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, ?SlaPolicy $policy = null): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
