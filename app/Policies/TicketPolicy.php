<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function claim(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::AgenTier1)
            && $ticket->status === TicketStatus::Baru
            && $ticket->assigned_to_id === null;
    }

    public function handle(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }
}
