<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function create(User $actor): bool
    {
        return $this->canManage($actor);
    }

    public function update(User $actor, Announcement $announcement): bool
    {
        return $this->canManage($actor);
    }

    private function canManage(User $actor): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1]);
    }
}
