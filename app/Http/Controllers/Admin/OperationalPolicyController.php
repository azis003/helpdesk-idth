<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproverAssignmentRequest;
use App\Http\Requests\Admin\OperationalSettingRequest;
use App\Http\Requests\Admin\ServiceCalendarRequest;
use App\Http\Requests\Admin\SlaPolicyRequest;
use App\Models\ServiceType;
use App\Models\SlaPolicy;
use App\Models\User;
use App\Services\ApproverAssignmentService;
use App\Services\DomainAuthorization;
use App\Services\OperationalPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationalPolicyController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly OperationalPolicyService $policies,
        private readonly ApproverAssignmentService $approvers,
    ) {}

    public function index(Request $request): mixed
    {
        $this->authorizeView($request);

        $currentApprover = $this->approvers->current();

        return view('admin.operational-policies.index', [
            'serviceTypes' => ServiceType::query()
                ->with('activeSlaPolicy')
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get(),
            'calendar' => $this->policies->currentCalendar(),
            'settings' => $this->policies->settings(),
            'currentApprover' => $currentApprover,
            'pendingApprovalCount' => $this->approvers->pendingCount($currentApprover?->user_id),
            'approverCandidates' => User::query()
                ->where('is_active', true)
                ->where('must_change_password', false)
                ->whereNotNull('password_changed_at')
                ->whereHas('roles', fn ($query) => $query->where('slug', Role::Approver->value))
                ->with('roles')
                ->orderBy('name')
                ->get(),
            'workingDayLabels' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ],
        ]);
    }

    public function updateSla(SlaPolicyRequest $request): RedirectResponse
    {
        $this->authorizeView($request);
        $this->policies->updateSlaPolicies($request->user(), $request->payload());

        return back()->with('success', 'Target SLA layanan berhasil disimpan sebagai versi kebijakan baru.');
    }

    public function updateCalendar(ServiceCalendarRequest $request): RedirectResponse
    {
        $this->authorizeView($request);
        $this->policies->updateCalendar($request->user(), $request->payload());

        return back()->with('success', 'Kalender jam layanan dan hari libur berhasil disimpan.');
    }

    public function updateSettings(OperationalSettingRequest $request): RedirectResponse
    {
        $this->authorizeView($request);
        $this->policies->updateSettings($request->user(), $request->payload());

        return back()->with('success', 'Batas waktu operasional berhasil disimpan sebagai versi kebijakan baru.');
    }

    public function replaceApprover(ApproverAssignmentRequest $request): RedirectResponse
    {
        $this->authorizeView($request);
        $this->approvers->replace(
            $request->user(),
            (int) $request->validated('replacement_user_id'),
            true,
            (string) $request->validated('reason'),
        );

        return back()->with('success', 'Manajer TI/Approver aktif berhasil ditetapkan dan approval tertunda dipindahkan.');
    }

    private function authorizeView(Request $request): void
    {
        $this->authorization->authorize(
            $request->user(),
            'viewAny',
            SlaPolicy::class,
            'admin.operational-policies.view',
        );
    }
}
