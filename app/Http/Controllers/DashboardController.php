<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): mixed
    {
        $user = $request->user()->load('roles');
        $requiresPasswordChange = $user->requiresPasswordChange();
        $hasOperationalRole = $user->hasOperationalRole();

        return view('dashboard', [
            'user' => $user,
            'requiresPasswordChange' => $requiresPasswordChange,
            'assignedTicketCount' => $hasOperationalRole && ! $requiresPasswordChange ? $user->assignedTickets()->count() : null,
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'newTicketCount' => $hasOperationalRole && ! $requiresPasswordChange ? Ticket::query()->where('status', 'baru')->count() : null,
        ]);
    }
}
