<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\Ticket;
use App\Services\ApproverAssignmentService;
use App\Services\TicketApprovalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ApproverAssignmentService $approvers,
        private readonly TicketApprovalService $approvals,
    ) {}

    public function __invoke(Request $request): mixed
    {
        $user = $request->user()->load('roles');
        $requiresPasswordChange = $user->requiresPasswordChange();
        $hasOperationalRole = $user->hasOperationalRole();
        $canAccessTickets = $user->hasAnyRole([Role::Pemohon, Role::AgenTier1, Role::AgenTier2]);
        $canCreateTickets = $user->hasAnyRole([Role::Pemohon, Role::AgenTier1]);
        $ticketScope = function (Builder $query) use ($user): void {
            if ($user->hasRole(Role::AgenTier1)) {
                $query->where(function (Builder $query) use ($user): void {
                    $query->where('requester_id', $user->getKey())
                        ->orWhere('created_by_id', $user->getKey())
                        ->orWhere('assigned_to_id', $user->getKey());
                });

                return;
            }

            if ($user->hasRole(Role::AgenTier2)) {
                $query->where('assigned_to_id', $user->getKey());

                return;
            }

            $query->where('requester_id', $user->getKey());
        };
        $myTicketsQuery = Ticket::query()->where($ticketScope);
        $canReviewApprovals = ! $requiresPasswordChange
            && $this->approvers->isCurrentApprover($user);

        return view('dashboard', [
            'user' => $user,
            'requiresPasswordChange' => $requiresPasswordChange,
            'assignedTicketCount' => $hasOperationalRole && ! $requiresPasswordChange ? $user->assignedTickets()->count() : null,
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'newTicketCount' => $user->hasRole(Role::AgenTier1) && ! $requiresPasswordChange
                ? Ticket::query()->newQueue()->count()
                : null,
            'canAccessTickets' => $canAccessTickets && ! $requiresPasswordChange,
            'canCreateTickets' => $canCreateTickets && ! $requiresPasswordChange,
            'myTicketCount' => $canAccessTickets && ! $requiresPasswordChange ? (clone $myTicketsQuery)->count() : null,
            'myTickets' => $canAccessTickets && ! $requiresPasswordChange
                ? $myTicketsQuery->with('serviceType')->orderByDesc('submitted_at')->orderByDesc('id')->limit(5)->get()
                : collect(),
            'canReviewApprovals' => $canReviewApprovals,
            'pendingApprovals' => $canReviewApprovals ? $this->approvals->pendingFor($user) : collect(),
            'announcements' => Announcement::query()
                ->activeAt(now())
                ->orderByDesc('starts_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
