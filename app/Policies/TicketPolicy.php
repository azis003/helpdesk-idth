<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([Role::Pemohon, Role::AgenTier1, Role::AgenTier2]);
    }

    public function create(User $actor): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([Role::Pemohon, Role::AgenTier1]);
    }

    public function createSelf(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::Pemohon);
    }

    public function createForOther(User $actor, User $requester): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::AgenTier1)
            && $requester->isActive();
    }

    public function view(User $actor, Ticket $ticket): bool
    {
        if (! $actor->isActive()) {
            return false;
        }

        if ((int) $ticket->requester_id === (int) $actor->getKey()
            || (int) $ticket->created_by_id === (int) $actor->getKey()) {
            return true;
        }

        if ($actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1])) {
            return true;
        }

        return $actor->hasRole(Role::AgenTier2)
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function claim(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::AgenTier1);
    }

    public function viewQueue(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(Role::AgenTier1);
    }

    public function triage(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::AgenTier1)
            && $ticket->assigned_tier === Role::AgenTier1->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey()
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function assignTierTwo(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::AgenTier1)
            && $ticket->status === TicketStatus::Dikerjakan
            && $ticket->assigned_tier === Role::AgenTier1->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function returnToTierOne(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::AgenTier2)
            && $ticket->status === TicketStatus::Dikerjakan
            && $ticket->assigned_tier === Role::AgenTier2->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey()
            && $ticket->last_triaged_by_id !== null;
    }

    public function handle(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function cancel(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey()
            && $ticket->status === TicketStatus::Baru;
    }
}
