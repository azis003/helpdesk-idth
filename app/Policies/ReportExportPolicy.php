<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ReportExport;
use App\Models\User;
use App\Services\ApproverAssignmentService;

class ReportExportPolicy
{
    public function __construct(private readonly ApproverAssignmentService $approvers) {}

    public function viewAny(User $actor): bool
    {
        return $actor->isActive()
            && ! $actor->hasRole(Role::KetuaTimKerja)
            && ($actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1])
                || $this->approvers->isCurrentApprover($actor));
    }

    public function view(User $actor, ReportExport $reportExport): bool
    {
        return $this->viewAny($actor);
    }
}
