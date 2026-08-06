<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ServiceFieldDefinition;
use App\Models\User;

class ServiceFieldDefinitionPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, ServiceFieldDefinition $field): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
