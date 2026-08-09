<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Announcement;
use App\Services\ApproverAssignmentService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ApproverAssignmentService $approvers,
        private readonly DashboardService $dashboard,
    ) {}

    public function __invoke(Request $request): mixed
    {
        [$periodStart, $periodEnd] = $this->resolvePeriod($request);
        $user = $request->user()->load('roles');
        $dashboardData = $this->dashboard->build($user, $periodStart, $periodEnd);
        $canReviewApprovals = ! $user->hasRole(Role::KetuaTimKerja)
            && $this->approvers->isCurrentApprover($user);
        $agentDashboard = $dashboardData['agentDashboard'];
        $requesterDashboard = $dashboardData['requesterDashboard'];

        return view('dashboard', array_merge([
            'user' => $user,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodTimezone' => DashboardService::TIMEZONE,
            'periodLabel' => $this->periodLabel($periodStart, $periodEnd),
            'assignedTicketCount' => $agentDashboard['visible']
                ? $agentDashboard['assigned_count']
                : null,
            'isSuperAdmin' => $user->hasRole(Role::SuperAdmin),
            'newTicketCount' => $agentDashboard['visible']
                && $agentDashboard['is_tier_one']
                ? $agentDashboard['queue_count']
                : null,
            'canAccessTickets' => ! $user->hasRole(Role::KetuaTimKerja)
                && $user->hasAnyRole([
                    Role::Pemohon,
                    Role::AgenTier1,
                    Role::AgenTier2,
                ]),
            'canCreateTickets' => ! $user->hasRole(Role::KetuaTimKerja)
                && $user->hasAnyRole([Role::Pemohon, Role::AgenTier1]),
            'myTicketCount' => $requesterDashboard['visible']
                ? $requesterDashboard['ticket_count']
                : ($agentDashboard['visible'] ? $agentDashboard['assigned_count'] : null),
            'myTickets' => $requesterDashboard['visible']
                ? $requesterDashboard['tickets']
                : ($agentDashboard['visible'] ? $agentDashboard['assigned_tickets'] : collect()),
            'canReviewApprovals' => $canReviewApprovals,
            'pendingApprovals' => $dashboardData['approverDashboard']['pending'],
            'announcements' => Announcement::query()
                ->activeAt(now())
                ->orderByDesc('starts_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ], $dashboardData));
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function resolvePeriod(Request $request): array
    {
        $startInput = $request->query('start_date', $request->query('from'));
        $endInput = $request->query('end_date', $request->query('to'));

        Validator::make(
            [
                'start_date' => $startInput,
                'end_date' => $endInput,
            ],
            [
                'start_date' => ['nullable', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date_format:Y-m-d'],
            ],
            [
                'start_date.date_format' => 'Tanggal mulai harus menggunakan format YYYY-MM-DD.',
                'end_date.date_format' => 'Tanggal akhir harus menggunakan format YYYY-MM-DD.',
            ],
        )->validate();

        $now = Carbon::now(DashboardService::TIMEZONE);
        $start = filled($startInput)
            ? Carbon::createFromFormat('!Y-m-d', (string) $startInput, DashboardService::TIMEZONE)
            : $now->copy()->startOfMonth();
        $end = filled($endInput)
            ? Carbon::createFromFormat('!Y-m-d', (string) $endInput, DashboardService::TIMEZONE)
            : $now->copy()->endOfMonth();

        if ($end->lessThan($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal akhir tidak boleh lebih awal daripada tanggal mulai.',
            ]);
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        $startLabel = $start->copy()->locale('id')->translatedFormat('d M Y');
        $endLabel = $end->copy()->locale('id')->translatedFormat('d M Y');

        return $startLabel === $endLabel ? $startLabel : $startLabel.' – '.$endLabel;
    }
}
