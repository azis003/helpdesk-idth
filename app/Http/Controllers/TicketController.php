<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\CompleteTicketRequest;
use App\Http\Requests\InternalTicketFieldRequest;
use App\Http\Requests\NotSatisfiedTicketRequest;
use App\Http\Requests\ReopenTicketRequest;
use App\Http\Requests\RequestApprovalRequest;
use App\Http\Requests\ReturnTicketRequest;
use App\Http\Requests\StartDatabaseChangeExecutionRequest;
use App\Http\Requests\StoreTicketAttachmentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TriageTicketRequest;
use App\Http\Requests\VerifyDatabaseChangeRequest;
use App\Models\ApprovalRequest;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DatabaseChangeControlService;
use App\Services\DomainAuthorization;
use App\Services\RequesterTicketList;
use App\Services\RequesterTicketPresenter;
use App\Services\SkillSuggestionService;
use App\Services\TeamChairTicketProjection;
use App\Services\TicketApprovalService;
use App\Services\TicketAttachmentService;
use App\Services\TicketCancellationService;
use App\Services\TicketCreationService;
use App\Services\TicketInternalFieldService;
use App\Services\TicketResolutionService;
use App\Services\TicketSlaService;
use App\Services\TicketWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly TicketCreationService $creation,
        private readonly TicketInternalFieldService $internalFields,
        private readonly TicketCancellationService $cancellation,
        private readonly TicketWorkflowService $workflow,
        private readonly SkillSuggestionService $skillSuggestions,
        private readonly TicketAttachmentService $attachments,
        private readonly TicketApprovalService $approvals,
        private readonly TicketResolutionService $resolution,
        private readonly TicketSlaService $sla,
        private readonly DatabaseChangeControlService $specialControls,
        private readonly TeamChairTicketProjection $teamChairProjection,
        private readonly RequesterTicketPresenter $requesterPresenter,
        private readonly RequesterTicketList $requesterList,
    ) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', Ticket::class, 'ticket.list');

        if ($actor->hasRole(Role::KetuaTimKerja)) {
            return view('tickets.index', [
                'tickets' => $this->teamChairProjection->paginate($actor),
                'canViewQueue' => false,
                'isTeamChair' => true,
                'canAccessTickets' => false,
                'showFilters' => false,
                'search' => '',
                'perPage' => 15,
            ]);
        }

        if ($this->isRequesterOnlyList($actor)) {
            $data = $this->requesterList->build($request, $actor);
            $data['requesterActions'] = $data['tickets']->getCollection()
                ->mapWithKeys(fn (Ticket $ticket): array => [
                    $ticket->getKey() => $this->requesterActionFor($actor, $ticket),
                ])
                ->all();

            return view('tickets.requester-index', $data);
        }

        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $query = Ticket::query()
            ->with(['serviceType', 'requester', 'creator'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        $query->where(function (Builder $scopeQuery) use ($actor): void {
            $firstScope = true;
            $addScope = function (callable $scope) use (&$firstScope, $scopeQuery): void {
                $method = $firstScope ? 'where' : 'orWhere';
                $scopeQuery->{$method}($scope);
                $firstScope = false;
            };

            if ($actor->hasRole(Role::Pemohon)) {
                $addScope(fn (Builder $query): Builder => $query->where('requester_id', $actor->getKey()));
            }

            if ($actor->hasRole(Role::AgenTier1)) {
                $addScope(function (Builder $query) use ($actor): void {
                    $query->where(function (Builder $query) use ($actor): void {
                        $query->where('requester_id', $actor->getKey())
                            ->orWhere('created_by_id', $actor->getKey())
                            ->orWhere('assigned_to_id', $actor->getKey());
                    });
                });
            }

            if ($actor->hasRole(Role::AgenTier2)) {
                $addScope(fn (Builder $query): Builder => $query->where('assigned_to_id', $actor->getKey()));
            }

        });

        $query->when($search !== '', function (Builder $ticketQuery) use ($search): void {
            $like = "%{$search}%";

            $ticketQuery->where(function (Builder $searchQuery) use ($like): void {
                $searchQuery
                    ->where('ticket_number', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('service_type_code_snapshot', 'like', $like)
                    ->orWhere('service_type_name_snapshot', 'like', $like)
                    ->orWhere('requester_name_snapshot', 'like', $like)
                    ->orWhere('requester_nip_snapshot', 'like', $like)
                    ->orWhereHas('serviceType', function (Builder $serviceQuery) use ($like): void {
                        $serviceQuery
                            ->where('code', 'like', $like)
                            ->orWhere('name', 'like', $like);
                    });
            });
        });

        $tickets = $query->paginate($perPage)->withQueryString();
        $requesterActions = $tickets->getCollection()
            ->mapWithKeys(fn (Ticket $ticket): array => [
                $ticket->getKey() => $this->requesterActionFor($actor, $ticket),
            ])
            ->all();

        return view('tickets.index', [
            'tickets' => $tickets,
            'canViewQueue' => $actor->hasRole(Role::AgenTier1),
            'isTeamChair' => $actor->hasRole(Role::KetuaTimKerja),
            'canAccessTickets' => $actor->hasAnyRole([
                Role::Pemohon,
                Role::AgenTier1,
                Role::AgenTier2,
            ]),
            'showFilters' => true,
            'search' => $search,
            'perPage' => $perPage,
            'requesterActions' => $requesterActions,
        ]);
    }

    /**
     * Pemohon murni memakai halaman "Tiket saya" yang khusus. Agen, Ketua Tim
     * Kerja, dan Super Admin tetap memakai daftar operasional walaupun mereka
     * juga memiliki tiket sendiri.
     */
    private function isRequesterOnlyList(User $actor): bool
    {
        return $actor->hasRole(Role::Pemohon)
            && ! $actor->hasAnyRole([
                Role::SuperAdmin,
                Role::AgenTier1,
                Role::AgenTier2,
                Role::KetuaTimKerja,
            ]);
    }

    /**
     * Return the first requester action that needs a focused follow-up from
     * the ticket list. The detail page remains the source of the full action
     * form and its validation.
     *
     * @return array{label: string, description: string}|null
     */
    private function requesterActionFor(User $actor, Ticket $ticket): ?array
    {
        if ($actor->can('replyRequester', $ticket)) {
            return [
                'label' => 'Balas',
                'description' => 'Balas informasi yang diminta agen.',
            ];
        }

        if ($actor->can('confirm', $ticket)) {
            return [
                'label' => 'Tinjau hasil',
                'description' => 'Tinjau hasil pekerjaan dan pilih konfirmasi.',
            ];
        }

        if ($actor->can('reopen', $ticket)) {
            return [
                'label' => 'Buka kembali',
                'description' => 'Tinjau opsi untuk membuka kembali tiket.',
            ];
        }

        if ($actor->can('cancel', $ticket)) {
            return [
                'label' => 'Batalkan',
                'description' => 'Tinjau pembatalan tiket yang masih Baru.',
            ];
        }

        return null;
    }

    public function queue(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewQueue', Ticket::class, 'ticket.queue.view');

        $tickets = Ticket::query()
            ->newQueue()
            ->with(['serviceType', 'requester', 'problemCategory'])
            ->orderForTierOneQueue()
            ->paginate(20)
            ->withQueryString();

        return view('tickets.queue', [
            'tickets' => $tickets,
        ]);
    }

    public function create(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Ticket::class, 'ticket.create');
        $actor->loadMissing('currentTeamMembership.workTeam');

        $serviceTypes = ServiceType::query()
            ->active()
            ->with(['activeFieldDefinitions.options'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $selectedServiceId = $request->session()->getOldInput('service_type_id');

        if (! filled($selectedServiceId)) {
            $selectedServiceId = $request->query('service_type_id');
        }

        $selectedServiceType = $serviceTypes->first(
            fn (ServiceType $serviceType): bool => (string) $serviceType->getKey() === (string) $selectedServiceId,
        );
        $includeInternal = $actor->hasRole(Role::AgenTier1);
        $attachmentPolicies = collect();
        $buildings = collect();
        $requesters = collect();

        if ($selectedServiceType !== null) {
            $attachmentPolicies = AttachmentPolicy::query()
                ->active()
                ->where('type_key', '!=', Attachment::DATA_EXPORT_RESULT_TYPE)
                ->when(! $includeInternal, fn ($query) => $query->whereIn('visibility', ['requester', 'both']))
                ->with('serviceType')
                ->orderByRaw('service_type_id IS NOT NULL')
                ->orderBy('type_key')
                ->get();
            $buildings = Building::query()
                ->active()
                ->with(['floors' => fn ($query) => $query->active()])
                ->orderBy('name')
                ->get();
            $requesters = $actor->hasRole(Role::AgenTier1)
                ? User::query()->where('is_active', true)->orderBy('name')->get()
                : collect([$actor]);
        }

        return view('tickets.create', [
            'actor' => $actor,
            'serviceTypes' => $serviceTypes,
            'selectedServiceType' => $selectedServiceType,
            'attachmentPolicies' => $attachmentPolicies,
            'buildings' => $buildings,
            'requesters' => $requesters,
            'canCreateForOthers' => $actor->hasRole(Role::AgenTier1),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->creation->create(
            $request->user(),
            $request->validated(),
            $request->file('attachments', []),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil dibuat.");
    }

    public function show(Request $request, Ticket $ticket): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'view', $ticket, 'ticket.view');

        if ($actor->hasRole(Role::KetuaTimKerja)) {
            $teamChairTicket = $this->teamChairProjection->find($actor, (int) $ticket->getKey());

            if ($teamChairTicket === null) {
                abort(404, 'Tiket tidak ditemukan dalam cakupan tim Anda.');
            }

            return view('tickets.team-chair-show', [
                'ticket' => $teamChairTicket,
            ]);
        }

        $ticket->load([
            'requester',
            'creator',
            'assignee',
            'lastTriagedBy',
            'serviceType',
            'serviceTypeVariant',
            'serviceType.activeFieldDefinitions.options',
            'serviceType.skills',
            'problemCategory',
            'floor.building',
            'room.floor.building',
            'fieldValues',
            'fieldValueHistories.actor',
            'attachments.uploadedBy',
            'comments.author',
            'comments.attachments.uploadedBy',
            'waits.startedBy',
            'waits.fromAssignee',
            'waits.endedBy',
            'slaSegments',
            'statusHistories.actor',
            'assignmentHistories.fromUser',
            'assignmentHistories.toUser',
            'assignmentHistories.actor',
            'priorityHistories.actor',
            'categoryHistories.actor',
            'approvalRequests.approver',
            'approvalRequests.requestedBy',
            'approvalRequests.previousAssignee',
            'databaseChangeControl.executionStartedBy',
            'databaseChangeControl.verifier',
            'databaseChangeControl.histories.actor',
        ]);

        $approvalRequest = $ticket->approvalRequests
            ->sortByDesc(fn (ApprovalRequest $approval): string => $approval->requested_at?->toIso8601String() ?? '')
            ->first();
        $canDecideApproval = $approvalRequest !== null
            && $approvalRequest->isPending()
            && $actor->can('decide', $approvalRequest);
        $canSeeInternal = $actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1, Role::AgenTier2])
            || $canDecideApproval;

        $internalFieldValues = $canSeeInternal
            ? $ticket->fieldValues->filter(fn ($fieldValue): bool => $fieldValue->isInternal())->values()
            : collect();
        $ticket->setRelation(
            'fieldValues',
            $ticket->fieldValues
                ->filter(fn ($fieldValue): bool => $fieldValue->isRequesterVisible())
                ->values(),
        );

        if (! $canSeeInternal) {
            $ticket->setRelation(
                'attachments',
                $ticket->attachments->whereIn('visibility', ['requester', 'both'])->values(),
            );
            $ticket->setRelation(
                'comments',
                $ticket->comments
                    ->where('visibility', TicketCommentVisibility::Public)
                    ->values(),
            );
        }

        $ticket->comments->each(function ($comment) use ($canSeeInternal): void {
            $comment->setRelation(
                'attachments',
                $comment->attachments
                    ->filter(fn ($attachment): bool => $canSeeInternal || $attachment->visibility !== 'internal')
                    ->values(),
            );
        });

        $canTriage = $actor->can('triage', $ticket);
        $canAssignTierTwo = $actor->can('assignTierTwo', $ticket);
        $canReturnToTierOne = $actor->can('returnToTierOne', $ticket);
        $canCommentPublic = $actor->can('commentPublic', $ticket);
        $canCommentInternal = $canSeeInternal && $actor->can('commentInternal', $ticket);
        $canRequestInformation = $actor->can('requestInformation', $ticket);
        $canRequesterReply = $actor->can('replyRequester', $ticket);
        $canStartThirdParty = $actor->can('startThirdPartyWait', $ticket);
        $canResumeThirdParty = $actor->can('resumeThirdPartyWait', $ticket);
        $canRequestApproval = $actor->can('requestApproval', $ticket);
        $canComplete = $actor->can('complete', $ticket);
        $canUploadAttachments = $actor->can('uploadAttachment', $ticket);
        $canUpdateInternalFields = $actor->can('updateInternalFields', $ticket);
        $canStartDatabaseChange = $actor->can('startDatabaseChange', $ticket);
        $canVerifyDatabaseChange = $actor->can('verifyDatabaseChange', $ticket);
        $canConfirm = $actor->can('confirm', $ticket);
        $canNotSatisfied = $actor->can('notSatisfied', $ticket);
        $canReopen = $actor->can('reopen', $ticket);
        $canCancel = $actor->can('cancel', $ticket);
        $slaMetrics = $this->sla->metrics($ticket);
        $commentPublicPolicies = $ticket->serviceType
            ? $this->attachments->policiesFor($ticket->serviceType, true, 'public')
            : collect();
        $commentInternalPolicies = $canSeeInternal && $ticket->serviceType
            ? $this->attachments->policiesFor($ticket->serviceType, true, 'internal')
            : collect();
        $ticketAttachmentPolicies = $canUploadAttachments && $ticket->serviceType
            ? $this->attachments->policiesFor($ticket->serviceType, true)
            : collect();
        $specialControlReadiness = $this->specialControls->readiness($ticket);
        $activeWait = $ticket->waits->first(fn ($wait): bool => $wait->ended_at === null);
        $lastTimedOutWait = $ticket->waits->filter(fn ($wait): bool => $wait->timed_out)->last();
        $tierTwoUsers = ($canTriage || $canAssignTierTwo)
            ? $this->skillSuggestions->eligibleTierTwoUsers()
            : collect();
        $ticketSuggestions = ($canTriage || $canAssignTierTwo)
            ? $this->skillSuggestions->forServiceType($ticket->serviceType, $tierTwoUsers, $ticket->problemCategory)
            : collect();

        if ($this->isRequesterOnly($actor, $ticket, $canSeeInternal)) {
            return view('tickets.requester-show', [
                'ticket' => $this->requesterPresenter->present(
                    $ticket,
                    $slaMetrics,
                    $approvalRequest?->decision_note,
                ),
                'actions' => [
                    'cancel' => $canCancel,
                    'reply' => $canRequesterReply,
                    'confirm' => $canConfirm,
                    'notSatisfied' => $canNotSatisfied,
                    'reopen' => $canReopen,
                ],
                'replyAttachmentPolicies' => $commentPublicPolicies,
            ]);
        }

        return view('tickets.show', [
            'ticket' => $ticket,
            'canSeeInternal' => $canSeeInternal,
            'canTriage' => $canTriage,
            'canAssignTierTwo' => $canAssignTierTwo,
            'canReturnToTierOne' => $canReturnToTierOne,
            'canCommentPublic' => $canCommentPublic,
            'canCommentInternal' => $canCommentInternal,
            'canRequestInformation' => $canRequestInformation,
            'canRequesterReply' => $canRequesterReply,
            'canStartThirdParty' => $canStartThirdParty,
            'canResumeThirdParty' => $canResumeThirdParty,
            'canRequestApproval' => $canRequestApproval,
            'canComplete' => $canComplete,
            'canUploadAttachments' => $canUploadAttachments,
            'canUpdateInternalFields' => $canUpdateInternalFields,
            'internalFieldDefinitions' => $ticket->serviceType?->activeFieldDefinitions
                ->where('visibility', 'internal')
                ->values() ?? collect(),
            'internalFieldValues' => $internalFieldValues,
            'canStartDatabaseChange' => $canStartDatabaseChange,
            'canVerifyDatabaseChange' => $canVerifyDatabaseChange,
            'canConfirm' => $canConfirm,
            'canNotSatisfied' => $canNotSatisfied,
            'canReopen' => $canReopen,
            'slaMetrics' => $slaMetrics,
            'approvalRequest' => $approvalRequest,
            'canDecideApproval' => $canDecideApproval,
            'commentPublicPolicies' => $commentPublicPolicies,
            'commentInternalPolicies' => $commentInternalPolicies,
            'ticketAttachmentPolicies' => $ticketAttachmentPolicies,
            'specialControlReadiness' => $specialControlReadiness,
            'activeWait' => $activeWait,
            'lastTimedOutWait' => $lastTimedOutWait,
            'tierTwoUsers' => $tierTwoUsers,
            'ticketSuggestions' => $ticketSuggestions,
            'priorityOptions' => Priority::labels(),
            'timeline' => $this->buildTimeline($ticket, $canSeeInternal),
        ]);
    }

    /**
     * The dedicated requester screen is only for the ticket owner without any
     * internal capability. Agents, approvers and Super Admin keep the full
     * operational view even when they happen to own the ticket.
     */
    private function isRequesterOnly(User $actor, Ticket $ticket, bool $canSeeInternal): bool
    {
        return ! $canSeeInternal
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey();
    }

    public function requestApproval(RequestApprovalRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->approvals->request(
            $request->user(),
            $ticket,
            $request->validated('reason'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dikirim untuk persetujuan Manajer TI.');
    }

    public function complete(CompleteTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->resolution->complete(
            $request->user(),
            $ticket,
            (string) $request->validated('solution'),
            $request->file('data_export_result'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Solusi berhasil disimpan. Tiket sekarang Menunggu Konfirmasi.');
    }

    public function uploadAttachment(StoreTicketAttachmentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->attachments->uploadToTicket(
            $request->user(),
            $ticket,
            $request->file('attachments', []),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Lampiran berhasil ditambahkan dan metadata aksesnya dicatat.');
    }

    public function updateInternalFields(InternalTicketFieldRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->internalFields->update(
            $request->user(),
            $ticket,
            $request->validated(),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Field internal SVC-07 berhasil disimpan.');
    }

    public function startDatabaseChange(StartDatabaseChangeExecutionRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->specialControls->startExecution($request->user(), $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Mulai Eksekusi berhasil dicatat bersama pelaku dan waktu eksekusi.');
    }

    public function verifyDatabaseChange(VerifyDatabaseChangeRequest $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validated();
        $result = $data['verification_result'] ?? $data['result'] ?? null;
        $notes = $data['verification_notes'] ?? $data['notes'] ?? null;

        $this->specialControls->verify(
            $request->user(),
            $ticket,
            is_string($result) ? $result : null,
            is_string($notes) ? $notes : null,
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Verifikasi hasil SVC-03 berhasil disimpan.');
    }

    public function confirm(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->resolution->confirm($request->user(), $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Hasil tiket dikonfirmasi dan tiket berhasil ditutup.');
    }

    public function notSatisfied(NotSatisfiedTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->resolution->notSatisfied(
            $request->user(),
            $ticket,
            (string) $request->validated('reason'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket kembali Dikerjakan oleh penanggung jawab terakhir.');
    }

    public function reopen(ReopenTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->resolution->reopen(
            $request->user(),
            $ticket,
            $request->validated('reason'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dibuka kembali dan SLA baru dimulai.');
    }

    public function cancel(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->cancellation->cancel($request->user(), $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil dibatalkan.");
    }

    public function claim(Request $request, Ticket $ticket): RedirectResponse
    {
        $claimed = $this->workflow->claim($request->user(), $ticket);

        if (! $claimed) {
            return back()->withErrors(['ticket' => 'Tiket sudah diambil oleh agen lain atau tidak tersedia.']);
        }

        return back()->with('success', 'Tiket berhasil diambil dari antrean.');
    }

    public function handle(Request $request, Ticket $ticket): RedirectResponse
    {
        $handled = $this->workflow->startHandling($request->user(), $ticket);

        if (! $handled) {
            return back()->withErrors(['ticket' => 'Tiket tidak lagi tersedia untuk mulai dikerjakan.']);
        }

        return back()->with('success', 'Tiket ditandai sedang dikerjakan.');
    }

    public function triage(TriageTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->workflow->triage($request->user(), $ticket, $request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Triase tiket berhasil disimpan.');
    }

    public function assignTierTwo(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->workflow->assignTierTwo($request->user(), $ticket, $request->validated());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil ditugaskan kepada Agen Tier 2.');
    }

    public function returnToTierOne(ReturnTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->workflow->returnToTierOne($request->user(), $ticket, $request->validated()['reason']);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Tiket dikembalikan kepada Agen Tier 1 terakhir yang melakukan triase.');
    }

    /** @return list<array<string, mixed>> */
    private function buildTimeline(Ticket $ticket, bool $canSeeInternal = false): array
    {
        $timeline = collect();

        foreach ($ticket->statusHistories as $history) {
            $from = $history->from_status?->label() ?? 'Tiket dibuat';
            $to = $history->to_status?->label() ?? 'Status tidak diketahui';
            $statusTitle = match ($history->action) {
                'ticket.completed' => 'Solusi disimpan',
                'ticket.closed' => 'Tiket ditutup',
                'ticket.auto_closed' => 'Tiket ditutup otomatis',
                'ticket.confirmation.not_satisfied' => 'Hasil belum sesuai',
                'ticket.reopened' => 'Tiket dibuka kembali',
                default => 'Status diperbarui',
            };
            $timeline->push([
                'occurred_at' => $history->occurred_at,
                'title' => $statusTitle,
                'description' => $from.' → '.$to,
                'actor' => $history->actor?->name,
                'reason' => $history->reason,
                'kind' => 'status',
            ]);
        }

        foreach ($ticket->assignmentHistories as $history) {
            $from = $history->fromUser?->name ?? 'Belum ditugaskan';
            $to = $history->toUser?->name ?? 'Tidak ada penanggung jawab';
            $timeline->push([
                'occurred_at' => $history->occurred_at,
                'title' => $history->action?->label() ?? 'Penugasan diperbarui',
                'description' => $from.' → '.$to,
                'actor' => $history->actor?->name,
                'reason' => $history->reason,
                'kind' => 'assignment',
            ]);
        }

        foreach ($ticket->priorityHistories as $history) {
            $timeline->push([
                'occurred_at' => $history->occurred_at,
                'title' => 'Prioritas diperbarui',
                'description' => ($history->from_priority?->label() ?? 'Belum ditentukan').' → '.$history->to_priority?->label(),
                'actor' => $history->actor?->name,
                'reason' => $history->reason,
                'kind' => 'priority',
            ]);
        }

        foreach ($ticket->categoryHistories as $history) {
            $timeline->push([
                'occurred_at' => $history->occurred_at,
                'title' => 'Kategori diperbarui',
                'description' => ($history->from_category_name ?? 'Belum dikategorikan').' → '.($history->to_category_name ?? 'Belum dikategorikan'),
                'actor' => $history->actor?->name,
                'reason' => $history->reason,
                'kind' => 'category',
            ]);
        }

        foreach ($ticket->attachments as $attachment) {
            $timeline->push([
                'occurred_at' => $attachment->created_at,
                'title' => 'Lampiran ditambahkan',
                'description' => $attachment->type_label_snapshot.' · '.$attachment->original_name,
                'actor' => $attachment->uploadedBy?->name,
                'reason' => null,
                'kind' => 'attachment',
            ]);
        }

        if ($canSeeInternal) {
            foreach ($ticket->fieldValueHistories as $history) {
                $timeline->push([
                    'occurred_at' => $history->occurred_at,
                    'title' => $history->change_type === 'created'
                        ? 'Field internal SVC-07 diisi'
                        : 'Field internal SVC-07 diperbarui',
                    'description' => "{$history->label_snapshot} disimpan menggunakan definisi versi {$history->version_snapshot}.",
                    'actor' => $history->actor?->name,
                    'reason' => null,
                    'kind' => 'internal',
                ]);
            }
        }

        if ($canSeeInternal && $ticket->databaseChangeControl !== null) {
            foreach ($ticket->databaseChangeControl->histories as $history) {
                $title = match ($history->action) {
                    'execution_started' => 'Eksekusi SVC-03 dimulai',
                    'execution_denied' => 'Mulai Eksekusi ditolak',
                    'verification_completed' => 'Verifikasi SVC-03 disimpan',
                    'verification_denied' => 'Verifikasi SVC-03 ditolak',
                    default => 'Kontrol SVC-03 diperbarui',
                };
                $timeline->push([
                    'occurred_at' => $history->occurred_at,
                    'title' => $title,
                    'description' => 'Aktivitas kontrol perubahan database dicatat tanpa membuka berkas privat.',
                    'actor' => $history->actor?->name,
                    'reason' => $history->reason,
                    'kind' => 'control',
                ]);
            }
        }

        foreach ($ticket->approvalRequests as $approval) {
            $timeline->push([
                'occurred_at' => $approval->requested_at,
                'title' => 'Persetujuan diminta',
                'description' => 'Permintaan diarahkan kepada '.($approval->approver?->name ?? 'Manajer TI/Approver aktif').'.',
                'actor' => $approval->requestedBy?->name,
                'reason' => null,
                'kind' => 'approval',
            ]);

            if ($approval->decided_at !== null) {
                $approved = $approval->status === ApprovalRequest::STATUS_APPROVED;
                $timeline->push([
                    'occurred_at' => $approval->decided_at,
                    'title' => $approved ? 'Persetujuan disetujui' : 'Persetujuan tidak disetujui',
                    'description' => $approved
                        ? 'State tiket sebelum persetujuan dipulihkan.'
                        : 'Tiket ditetapkan sebagai Tidak Disetujui.',
                    'actor' => $approval->approver?->name,
                    'reason' => $approval->decision_note,
                    'kind' => 'approval',
                ]);
            }
        }

        return $timeline
            ->sortBy(fn (array $entry): int => $entry['occurred_at']?->getTimestamp() ?? 0)
            ->values()
            ->all();
    }
}
