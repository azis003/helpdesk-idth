<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Attachment;
use App\Models\User;

class AttachmentAccessPolicy
{
    public function view(User $actor, Attachment $attachment): bool
    {
        if (! $actor->isActive()) {
            return false;
        }

        $attachment->loadMissing(['ticket', 'ticketComment']);
        $ticket = $attachment->ticket;
        $comment = $attachment->ticketComment;

        if ($ticket === null) {
            return false;
        }

        $isOwner = (int) $ticket->requester_id === (int) $actor->getKey()
            || (int) $ticket->created_by_id === (int) $actor->getKey();

        if ($comment?->visibility?->value === 'internal' || $attachment->visibility === 'internal') {
            return $actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1, Role::AgenTier2])
                && (! $actor->hasRole(Role::AgenTier2) || (int) $ticket->assigned_to_id === (int) $actor->getKey());
        }

        return $isOwner
            || $actor->hasRole(Role::SuperAdmin)
            || $actor->hasRole(Role::AgenTier1)
            || ($actor->hasRole(Role::AgenTier2) && (int) $ticket->assigned_to_id === (int) $actor->getKey());
    }
}
