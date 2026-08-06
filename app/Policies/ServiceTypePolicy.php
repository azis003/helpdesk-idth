<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ServiceType;
use App\Models\User;

class ServiceTypePolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, ServiceType $serviceType): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
