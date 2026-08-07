<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ApprovalRequest;
use App\Models\Attachment;
use App\Models\Ticket;
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

        $isInternal = $comment?->visibility?->value === 'internal'
            || $attachment->visibility === 'internal';

        if ($isInternal) {
            return (
                $actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1, Role::AgenTier2])
                && (! $actor->hasRole(Role::AgenTier2) || (int) $ticket->assigned_to_id === (int) $actor->getKey())
            ) || $this->isPendingApprover($actor, $ticket);
        }

        if (! $attachment->isRequesterAccessible()) {
            return false;
        }

        return $isOwner
            || $actor->hasRole(Role::SuperAdmin)
            || $actor->hasRole(Role::AgenTier1)
            || ($actor->hasRole(Role::AgenTier2) && (int) $ticket->assigned_to_id === (int) $actor->getKey())
            || $this->isPendingApprover($actor, $ticket);
    }

    private function isPendingApprover(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $actor->requiresPasswordChange()
            && $actor->hasRole(Role::Approver)
            && ApprovalRequest::query()
                ->pending()
                ->where('ticket_id', $ticket->getKey())
                ->where('approver_id', $actor->getKey())
                ->exists();
    }
}
