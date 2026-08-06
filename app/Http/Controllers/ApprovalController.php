<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovalDecisionRequest;
use App\Models\ApprovalRequest;
use App\Services\DomainAuthorization;
use App\Services\TicketApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly TicketApprovalService $approvals,
    ) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', ApprovalRequest::class, 'approval.list');

        return view('approvals.index', [
            'approvals' => $this->approvals->pendingFor($actor),
        ]);
    }

    public function approve(ApprovalDecisionRequest $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $ticket = $this->approvals->approve(
            $request->user(),
            $approvalRequest,
            $request->validated('decision_note'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Persetujuan untuk tiket {$ticket->ticket_number} berhasil diberikan.");
    }

    public function reject(ApprovalDecisionRequest $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $ticket = $this->approvals->reject(
            $request->user(),
            $approvalRequest,
            (string) $request->validated('decision_note'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Persetujuan untuk tiket {$ticket->ticket_number} ditolak.");
    }
}
