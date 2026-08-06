<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\User;

class ApprovalRequestPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isCurrentApprover($actor);
    }

    public function view(User $actor, ApprovalRequest $approvalRequest): bool
    {
        return $this->isCurrentApprover($actor)
            && $approvalRequest->isPending()
            && (int) $approvalRequest->approver_id === (int) $actor->getKey();
    }

    public function decide(User $actor, ApprovalRequest $approvalRequest): bool
    {
        return $this->view($actor, $approvalRequest);
    }

    private function isCurrentApprover(User $actor): bool
    {
        return $actor->isActive()
            && ! $actor->requiresPasswordChange()
            && $actor->hasRole(Role::Approver)
            && ApproverAssignment::query()
                ->active()
                ->where('user_id', $actor->getKey())
                ->exists();
    }
}
