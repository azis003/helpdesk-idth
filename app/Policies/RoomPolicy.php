<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, Room $room): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::SuperAdmin);
    }
}
