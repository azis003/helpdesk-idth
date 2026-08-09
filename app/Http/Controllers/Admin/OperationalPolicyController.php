<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproverAssignmentRequest;
use App\Http\Requests\Admin\OperationalSettingRequest;
use App\Http\Requests\Admin\ServiceCalendarRequest;
use App\Models\SlaPolicy;
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
