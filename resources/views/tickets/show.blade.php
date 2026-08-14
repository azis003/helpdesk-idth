@extends('layouts.app')

@section('title', ($ticket->ticket_number ?? 'Detail tiket').' — '.$branding['application_name'])
@section('header_title', 'Detail tiket')

@php
    $actor = auth()->user();
    $isRequester = (int) $ticket->requester_id === (int) $actor->getKey();
    $isOperationalAgent = $actor->hasAnyRole([\App\Enums\Role::AgenTier1, \App\Enums\Role::AgenTier2]);
    $fromAllTickets = request()->query('from') === 'all' && $actor->can('viewAll', \App\Models\Ticket::class);
    $requestedWorkAreaTab = request()->query('from');
    $canReturnToRequestedTab = $requestedWorkAreaTab === 'mine'
        ? $actor->can('viewAssigned', \App\Models\Ticket::class)
        : (in_array($requestedWorkAreaTab, ['queue', 'assigned', 'completed'], true)
            && $actor->can('viewQueue', \App\Models\Ticket::class));
    $workAreaTab = $canReturnToRequestedTab
        ? $requestedWorkAreaTab
        : ($ticket->status === \App\Enums\TicketStatus::Baru && $ticket->assigned_to_id === null ? 'queue' : 'mine');
    $workAreaUrl = $fromAllTickets
        ? route('tickets.all')
        : ($isOperationalAgent ? route('tickets.queue', ['tab' => $workAreaTab]) : route('tickets.index'));
    $workAreaLabel = $fromAllTickets
        ? 'Kembali ke Semua Tiket'
        : ($isOperationalAgent ? 'Kembali ke Monitoring Tiket' : 'Kembali ke Tiket Saya');
    $ticketLabel = $ticket->ticket_number ?? 'Tiket #'.$ticket->id;
    $submittedAt = ($ticket->submitted_at ?? $ticket->created_at)?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Belum tersedia';
    $serviceLabel = $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name ?? 'Layanan belum tersedia';
    $locationLabel = collect([$ticket->building_name_snapshot, $ticket->floor_name_snapshot, $ticket->room_name_snapshot])->filter()->implode(' - ');
    $submittedFields = $ticket->fieldValues->map(function ($fieldValue): array {
        $value = is_array($fieldValue->value)
            ? implode(', ', $fieldValue->value)
            : ($fieldValue->field_type_snapshot === 'boolean'
                ? ((filled($fieldValue->value) && $fieldValue->value !== '0') ? 'Ya' : 'Tidak')
                : $fieldValue->value);

        return [
            'label' => (string) $fieldValue->label_snapshot,
            'value' => filled($value) ? (string) $value : 'Tidak diisi',
        ];
    })->values();
    $impactField = $submittedFields->first(function (array $field): bool {
        $label = mb_strtolower($field['label']);

        return str_contains($label, 'dampak') || str_contains($label, 'cakupan');
    });
    $assigneeName = $ticket->assignee?->name ?? 'Menunggu petugas';
    $hasAssignee = $ticket->assignee !== null;
    $assigneeInitials = strtoupper(collect(preg_split('/\s+/', trim($assigneeName)))->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode(''));
    $currentUserInitials = strtoupper(collect(preg_split('/\s+/', trim($actor->name)))->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode(''));
    $isClosed = $ticket->status?->isClosed() ?? false;
    $canCancel = $actor->can('cancel', $ticket);
    $canTriage = $canTriage ?? false;
    $canChangePriority = $canChangePriority ?? false;
    $canReject = $canReject ?? false;
    $canAssignTierTwo = $canAssignTierTwo ?? false;
    $canReturnToTierOne = $canReturnToTierOne ?? false;
    $tierTwoUsers = $tierTwoUsers ?? collect();
    $ticketSuggestions = $ticketSuggestions ?? collect();
    $priorityOptions = $priorityOptions ?? \App\Enums\Priority::labels();
    $timeline = $timeline ?? [];
    $canCommentPublic = $canCommentPublic ?? false;
    $canCommentInternal = $canCommentInternal ?? false;
    $canRequestInformation = $canRequestInformation ?? false;
    $canRequesterReply = $canRequesterReply ?? false;
    $canStartThirdParty = $canStartThirdParty ?? false;
    $canResumeThirdParty = $canResumeThirdParty ?? false;
    $canRequestApproval = $canRequestApproval ?? false;
    $canComplete = $canComplete ?? false;
    $canUploadAttachments = $canUploadAttachments ?? false;
    $canStartDatabaseChange = $canStartDatabaseChange ?? false;
    $canVerifyDatabaseChange = $canVerifyDatabaseChange ?? false;
    $canConfirm = $canConfirm ?? false;
    $canNotSatisfied = $canNotSatisfied ?? false;
    $canReopen = $canReopen ?? false;
    $slaMetrics = $slaMetrics ?? null;
    $approvalRequest = $approvalRequest ?? null;
    $approvalDecisionNote = $approvalDecisionNote ?? $approvalRequest?->decision_note;
    $canDecideApproval = $canDecideApproval ?? false;
    $commentPublicPolicies = $commentPublicPolicies ?? collect();
    $commentInternalPolicies = $commentInternalPolicies ?? collect();
    $ticketAttachmentPolicies = $ticketAttachmentPolicies ?? collect();
    $ticketAttachments = $ticket->attachments->whereNull('ticket_comment_id')->values();
    $specialControlReadiness = $specialControlReadiness ?? ['kind' => null, 'ready' => true];
    $canSeeInternal = $canSeeInternal ?? false;
    $canUpdateInternalFields = $canUpdateInternalFields ?? false;
    $internalFieldDefinitions = $internalFieldDefinitions ?? collect();
    $internalFieldValues = $internalFieldValues ?? collect();
    $activeWait = $activeWait ?? null;
    $lastTimedOutWait = $lastTimedOutWait ?? null;
    $isNewQueueTicket = $ticket->status === \App\Enums\TicketStatus::Baru
        && $ticket->assigned_to_id === null
        && $ticket->assigned_tier === null;
    $isMyActiveTicket = $isOperationalAgent
        && (int) $ticket->assigned_to_id === (int) $actor->getKey()
        && in_array($ticket->status, [
            \App\Enums\TicketStatus::Diproses,
            \App\Enums\TicketStatus::Dikerjakan,
        ], true);
    $canTriage = $canTriage && $isNewQueueTicket;
    $canQueueTriage = $canTriage;
    $showQueueActions = $isNewQueueTicket
        && ($canChangePriority || $canReject || $canQueueTriage);
    $showMyTicketActions = $isMyActiveTicket
        && ($canComplete || $canRequestInformation || $canRequestApproval || $canStartThirdParty);
    $showActionPanel = $canQueueTriage
        || $canChangePriority
        || $canReject
        || $canAssignTierTwo
        || $canReturnToTierOne
        || $canCommentInternal
        || $canRequestInformation
        || $canStartThirdParty
        || $canResumeThirdParty
        || $canRequestApproval
        || $canComplete
        || $canConfirm
        || $canNotSatisfied
        || $canReopen
        || $canStartDatabaseChange
        || $canVerifyDatabaseChange
        || $canDecideApproval
        || $approvalRequest;
    $showHeaderActions = $showQueueActions
        || $showMyTicketActions
        || $canCancel
        || $canRequesterReply
        || $canResumeThirdParty
        || $canDecideApproval
        || $canConfirm
        || $canNotSatisfied
        || $canReopen;
    $currentPriority = old('priority', $ticket->priority?->value);
    $currentOutcome = old('outcome', 'self');
    $estimateDeadline = $slaMetrics['deadline_at'] ?? null;
    $estimateLabel = $slaMetrics && ($slaMetrics['uses_sla'] ?? false)
        ? ($estimateDeadline instanceof \Illuminate\Support\Carbon
            ? $estimateDeadline->copy()->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y')
            : (($slaMetrics['target_working_days'] ?? null) !== null ? $slaMetrics['target_working_days'].' hari kerja' : 'Sesuai standar layanan'))
        : null;
    $activity = collect($timeline)
        ->map(function (array $entry): array {
            $description = (string) ($entry['description'] ?? 'Aktivitas tiket dicatat.');

            if (filled($entry['actor'] ?? null)) {
                $description .= ' Oleh '.(string) $entry['actor'].'.';
            }

            if (filled($entry['reason'] ?? null)) {
                $description .= ' Alasan: '.(string) $entry['reason'];
            }

            return [
                'title' => (string) ($entry['title'] ?? 'Aktivitas tiket'),
                'description' => $description,
                'occurredAt' => $entry['occurred_at'] ?? null,
                'tone' => match ($entry['kind'] ?? null) {
                    'priority', 'approval' => 'attention',
                    'status' => 'progress',
                    default => 'success',
                },
            ];
        })
        ->sortBy(fn (array $entry): int => $entry['occurredAt']?->getTimestamp() ?? 0)
        ->values();
@endphp

@section('content')
    <div class="ticket-reference-content ticket-reference-content--operational">
        <x-tickets.detail-header
            :back-url="$workAreaUrl"
            :back-label="$workAreaLabel"
            :ticket-label="$ticketLabel"
            :status="$ticket->status"
            :priority="$ticket->priority"
            :submitted-at="$submittedAt"
            :show-actions="$showHeaderActions"
        >
            <x-slot:actions>
                    @if ($showQueueActions)
                        @if ($canChangePriority)
                            <button type="button" data-ui-modal-open="ticket-priority-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--neutral">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M5 7h14M5 12h14M5 17h14" /><circle cx="9" cy="7" r="2" fill="currentColor" stroke="none" /><circle cx="15" cy="12" r="2" fill="currentColor" stroke="none" /><circle cx="11" cy="17" r="2" fill="currentColor" stroke="none" /></svg>
                                <span>Ubah Prioritas</span>
                            </button>
                        @endif

                        @if ($canReject)
                            <button type="button" data-ui-modal-open="ticket-reject-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--danger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="m9 9 6 6m0-6-6 6" /></svg>
                                <span>Tolak</span>
                            </button>
                        @endif

                        @if ($canQueueTriage)
                            <button type="button" data-ui-modal-open="ticket-triage-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14M8 12h8m-5 6h2" /></svg>
                                <span>Triase</span>
                            </button>
                        @endif
                    @elseif ($showMyTicketActions)
                        @if ($canComplete)
                            <button type="button" data-ui-modal-open="ticket-complete-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--success">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" /></svg>
                                <span>Selesai</span>
                            </button>
                        @endif

                        @if ($canRequestInformation)
                            <button type="button" data-ui-modal-open="ticket-request-information-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--warning">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M9.8 9.2a2.4 2.4 0 1 1 3.4 2.2c-.8.4-1.2.9-1.2 1.6m0 3h.01" /></svg>
                                <span>Kembalikan ke Pelapor</span>
                            </button>
                        @endif

                        @if ($canRequestApproval)
                            <button type="button" data-ui-modal-open="ticket-request-approval-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3.5 7 2.5v5.3c0 4.1-2.8 7.5-7 9.2-4.2-1.7-7-5.1-7-9.2V6l7-2.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.8 11.8 2.1 2.1 4.4-4.4" /></svg>
                                <span>Minta Approval</span>
                            </button>
                        @endif

                        @if ($canStartThirdParty)
                            <button type="button" data-ui-modal-open="ticket-third-party-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--neutral">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="8" cy="8" r="3" /><circle cx="17" cy="10" r="2.5" /><path stroke-linecap="round" d="M3.5 19a4.5 4.5 0 0 1 9 0m1.5-1a3.5 3.5 0 0 1 7 0" /></svg>
                                <span>Pending</span>
                            </button>
                        @endif
                    @elseif ($canConfirm || $canNotSatisfied)
                        <button type="button" data-ui-modal-open="ticket-confirmation-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" /></svg>
                            <span>Konfirmasi</span>
                        </button>
                    @elseif ($canResumeThirdParty)
                        <button type="button" data-ui-modal-open="ticket-third-party-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h13m-5-5 5 5-5 5" /></svg>
                            <span>Lanjutkan</span>
                        </button>
                    @elseif ($canReopen)
                        <button type="button" data-ui-modal-open="ticket-reopen-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v5h5M4.5 12A8 8 0 1 0 7 6.4L4 9" /></svg>
                            <span>Buka Kembali</span>
                        </button>
                    @endif

                    @if ($canRequesterReply)
                        <a href="#ticket-reply-heading" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v11H8l-4 3v-14Z" /><path stroke-linecap="round" d="M8 9h8M8 12h5" /></svg>
                            <span>Balas ke Tim TI</span>
                        </a>
                    @endif

                    @if ($canDecideApproval && $approvalRequest)
                        <button type="button" data-ui-modal-open="ticket-approval-decision-modal" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3.5 7 2.5v5.3c0 4.1-2.8 7.5-7 9.2-4.2-1.7-7-5.1-7-9.2V6l7-2.5Z" /></svg>
                            <span>Putuskan</span>
                        </button>
                    @endif

                    @if ($canCancel)
                        <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" class="ticket-reference-action-menu-form" data-swal-confirm="Batalkan tiket ini? Tiket hanya dapat dibatalkan selama statusnya masih Baru.">
                            @csrf
                            <button type="submit" class="ticket-reference-action-menu-item ticket-reference-action-menu-item--danger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="m9 9 6 6m0-6-6 6" /></svg>
                                <span>Batalkan</span>
                            </button>
                        </form>
                    @endif
            </x-slot:actions>
        </x-tickets.detail-header>

        @if ($ticket->status === \App\Enums\TicketStatus::Ditolak && filled($ticket->rejection_reason))
            <div class="mt-6 flex gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] p-4 shadow-[var(--tm-sh-xs)]" role="status">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-danger-100)] text-[color:var(--tm-danger-700)]" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="m9 9 6 6m0-6-6 6" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-[color:var(--tm-danger-700)]">Alasan penolakan</p>
                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $ticket->rejection_reason }}</p>
                </div>
            </div>
        @endif

        @if ($ticket->status === \App\Enums\TicketStatus::TidakDisetujui && filled($approvalDecisionNote))
            <div class="mt-6 flex gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-4 shadow-[var(--tm-sh-xs)]" role="status">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-warning-100)] text-[color:var(--tm-warning-700)]" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5 3.5 19h17L12 4.5Z" /><path stroke-linecap="round" d="M12 10v3.5m0 2.5h.01" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-[color:var(--tm-warning-700)]">Catatan keputusan</p>
                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $approvalDecisionNote }}</p>
                </div>
            </div>
        @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.75fr)_minmax(19rem,0.85fr)] lg:items-start xl:gap-8">
        <div class="min-w-0 space-y-6">
            <section class="ticket-reference-card" aria-labelledby="ticket-description-heading">
                <div class="ticket-reference-card-body">
                    <h2 id="ticket-description-heading" class="ticket-reference-subject">{{ $ticket->subject }}</h2>
                    <div class="ticket-reference-description mt-5">
                        {!! nl2br(e($ticket->description ?: 'Deskripsi belum tersedia.')) !!}
                    </div>

                    @if ($submittedFields->isNotEmpty())
                        <div class="ticket-reference-subsection mt-6">
                            <h3 class="ticket-reference-info-label">Informasi yang Anda kirim</h3>
                            <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($submittedFields as $field)
                                    <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3.5 py-3">
                                        <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-muted)]">{{ $field['label'] }}</dt>
                                        <dd class="mt-1.5 whitespace-pre-line text-sm font-semibold leading-6 text-[color:var(--tm-text)]">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    @if (filled($ticket->solution) && ! ($canConfirm || $canNotSatisfied))
                        <div class="mt-6 flex gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] p-4" role="status">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-success-100)] text-[color:var(--tm-success-700)]" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" /></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-[color:var(--tm-success-700)]">Hasil pekerjaan</h3>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $ticket->solution }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if ($ticket->comments->isNotEmpty())
            <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-conversation-heading" @if (! $isClosed) open @endif>
                <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                    <span class="flex min-w-0 items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v11H8l-4 3v-14Z" />
                            <path stroke-linecap="round" d="M8 9h8M8 12h5" />
                        </svg>
                        <span id="ticket-conversation-heading" class="ticket-reference-section-title">Percakapan</span>
                        <span class="rounded-[var(--tm-r-full)] bg-[color:var(--tm-n-100)] px-2 py-0.5 text-xs font-bold tabular-nums text-[color:var(--tm-text-muted)]">{{ $ticket->comments->count() }}</span>
                    </span>
                </summary>

                @if ($lastTimedOutWait)
                    <div class="mx-5 mt-5 flex gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-4 sm:mx-6" role="status">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-warning-100)] text-[color:var(--tm-warning-700)]" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 7.5v5l3 1.8" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-[color:var(--tm-warning-700)]">Batas tunggu Pemohon terlewati</p>
                            <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-secondary)]">Dikembalikan ke Dikerjakan pada <span class="tabular-nums">{{ $lastTimedOutWait->ended_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</span>.</p>
                        </div>
                    </div>
                @endif

                <div class="ticket-reference-card-body space-y-4">
                    @foreach ($ticket->comments as $comment)
                        <x-tickets.message :message="$comment" variant="reference" :requester-id="$ticket->requester_id" />
                    @endforeach
                </div>

            </details>
            @endif

            <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-reply-heading" @if (! $isClosed) open @endif>
                <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                    <span id="ticket-reply-heading" class="ticket-reference-section-title flex min-w-0 items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v11H8l-4 3v-14Z" />
                            <path stroke-linecap="round" d="M8 9h8M8 12h5" />
                        </svg>
                        <span>Tambahkan Balasan</span>
                    </span>
                </summary>

                <div class="ticket-reference-card-body">
                    @if ($canRequesterReply || $canCommentPublic)
                        <form method="POST" action="{{ $canRequesterReply ? route('tickets.requester-reply', $ticket) : route('tickets.comments.public', $ticket) }}" enctype="multipart/form-data" class="ticket-reference-reply mt-5" data-ticket-communication-form>
                            @csrf
                            <div class="ticket-reference-avatar" aria-hidden="true">{{ $currentUserInitials }}</div>
                            <div class="min-w-0 flex-1">
                                <label for="ticket-public-reply-body" class="ticket-reference-field-label">{{ $canRequesterReply ? 'Balasan ke Tim TI' : 'Balasan ke Pemohon' }}</label>
                                <textarea id="ticket-public-reply-body" name="body" rows="5" required class="ticket-reference-textarea mt-1.5" placeholder="Ketik pesan atau informasi tambahan di sini...">{{ old('body') }}</textarea>
                                @error('body')
                                    <x-field-error :message="$message" />
                                @enderror

                                @if ($commentPublicPolicies->isNotEmpty())
                                    <div class="mt-3 space-y-2">
                                        @foreach ($commentPublicPolicies as $policy)
                                            <input id="public-reply-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ticket-reference-file-input" accept="{{ collect($policy->allowed_extensions)->map(fn ($extension) => '.'.ltrim($extension, '.'))->implode(',') }}" aria-describedby="public-reply-attachment-help-{{ $policy->id }}">
                                            <div>
                                                <label for="public-reply-attachment-{{ $policy->id }}" class="ticket-reference-attach-label">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                                    </svg>
                                                    Lampirkan File
                                                </label>
                                                <p id="public-reply-attachment-help-{{ $policy->id }}" class="ticket-reference-file-help">{{ $policy->label }} · Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas.</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                                    <button type="submit" class="ui-btn ui-btn-primary" data-ticket-communication-submit>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12 20 4.5 15.5 20l-4-6.5-7-1.5Z" /></svg>
                                        Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="ticket-reference-reply mt-5">
                            <div class="ticket-reference-avatar" aria-hidden="true">{{ $currentUserInitials }}</div>
                            <div class="min-w-0 flex-1">
                                <label for="ticket-public-reply-disabled" class="ticket-reference-field-label">{{ $isRequester ? 'Balasan ke Tim TI' : 'Balasan ke Pemohon' }}</label>
                                <textarea id="ticket-public-reply-disabled" rows="5" disabled class="ticket-reference-textarea mt-1.5 disabled:cursor-not-allowed disabled:bg-[color:var(--tm-sunken)] disabled:text-[color:var(--tm-text-faint)]" placeholder="Ketik pesan atau informasi tambahan di sini..."></textarea>
                                <p class="mt-2 flex items-start gap-1.5 text-xs leading-5 text-[color:var(--tm-text-muted)]">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 11v5m0-8.5h.01" /></svg>
                                    <span>{{ $isRequester && $isClosed ? 'Balasan tidak tersedia karena tiket sudah berstatus akhir.' : 'Balasan tidak tersedia pada status tiket ini.' }}</span>
                                </p>
                            </div>
                        </div>
                    @endif

                @if (false)
                @if ($canRequesterReply)
                    <div class="border-t border-[#edf2f4] bg-[#f8fbfc] p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#147a79]">Balasan Pemohon</p>
                        </div>
                        <form method="POST" action="{{ route('tickets.requester-reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4" data-ticket-communication-form>
                            @csrf
                            <div>
                                <label for="requester-reply-body" class="ui-field-label">Informasi tambahan <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <textarea id="requester-reply-body" name="body" rows="5" required class="ui-textarea mt-2" placeholder="Tuliskan jawaban atau informasi yang diminta agen.">{{ old('body') }}</textarea>
                            </div>
                            @if ($commentPublicPolicies->isNotEmpty())
                                <fieldset class="space-y-3">
                                    <!-- <legend class="ui-field-label">Lampiran pendukung</legend> -->
                                    @foreach ($commentPublicPolicies as $policy)
                                        @php
                                            $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                                            $fileSize = $policy->max_file_size_kb % 1024 === 0
                                                ? intdiv($policy->max_file_size_kb, 1024).' MB'
                                                : $policy->max_file_size_kb.' KB';
                                        @endphp
                                        <div>
                                            <label for="requester-reply-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                            <input id="requester-reply-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-2" @if ($accept !== '') accept="{{ $accept }}" @endif>
                                            <p class="ui-field-help">Maks. {{ $policy->max_file_count }} berkas · {{ $fileSize }} per berkas.</p>
                                        </div>
                                    @endforeach
                                </fieldset>
                            @endif
                            <button type="submit" class="ui-btn ui-btn-primary" data-ticket-communication-submit>Kirim informasi dan lanjutkan tiket</button>
                        </form>
                    </div>
                @elseif ($canCommentPublic || $canRequestInformation || $canCommentInternal)
                    <div class="grid gap-4 border-t border-[#edf2f4] bg-[#f8fbfc] p-5 sm:p-6 lg:grid-cols-2">
                        @if ($canCommentPublic)
                            <form method="POST" action="{{ route('tickets.comments.public', $ticket) }}" enctype="multipart/form-data" class="rounded-2xl border border-[#cdeef7] bg-white p-4" data-ticket-communication-form>
                                @csrf
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-[#35505b]">Balasan ke Pemohon</p>
                                    </div>
                                    <span class="rounded-full bg-[#e8f9fd] px-2 py-1 text-[0.62rem] font-extrabold text-[#147a79]">Publik</span>
                                </div>
                                <label for="public-comment-body" class="sr-only">Isi balasan ke Pemohon</label>
                                <textarea id="public-comment-body" name="body" rows="5" required class="ui-textarea mt-4" placeholder="Tulis balasan yang dapat dibaca Pemohon.">{{ old('body') }}</textarea>
                                @if ($commentPublicPolicies->isNotEmpty())
                                    <fieldset class="mt-4 space-y-3">
                                        <legend class="ui-field-label">Lampiran</legend>
                                        @foreach ($commentPublicPolicies as $policy)
                                            @php
                                                $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                                            @endphp
                                            <div>
                                                <label for="public-comment-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                                <input id="public-comment-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-2" @if ($accept !== '') accept="{{ $accept }}" @endif>
                                            </div>
                                        @endforeach
                                    </fieldset>
                                @endif
                                <button type="submit" class="ui-btn ui-btn-primary mt-4 w-full" data-ticket-communication-submit>Kirim balasan</button>
                            </form>
                        @endif

                        @if ($canCommentInternal)
                            <form method="POST" action="{{ route('tickets.comments.internal', $ticket) }}" enctype="multipart/form-data" class="rounded-2xl border border-[#f0d28c] bg-[#fffdf7] p-4" data-ticket-communication-form>
                                @csrf
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-[#35505b]">Catatan Internal</p>
                                    </div>
                                    <span class="rounded-full bg-[#fff4d7] px-2 py-1 text-[0.62rem] font-extrabold text-[#9a6700]">Internal</span>
                                </div>
                                <label for="internal-comment-body" class="sr-only">Isi catatan internal</label>
                                <textarea id="internal-comment-body" name="body" rows="5" required class="ui-textarea mt-4 !border-[#ecd89f]" placeholder="Tulis konteks untuk tim TI.">{{ old('body') }}</textarea>
                                @if ($commentInternalPolicies->isNotEmpty())
                                    <fieldset class="mt-4 space-y-3">
                                        <legend class="ui-field-label">Lampiran internal</legend>
                                        @foreach ($commentInternalPolicies as $policy)
                                            @php
                                                $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                                            @endphp
                                            <div>
                                                <label for="internal-comment-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                                <input id="internal-comment-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-2" @if ($accept !== '') accept="{{ $accept }}" @endif>
                                            </div>
                                        @endforeach
                                    </fieldset>
                                @endif
                                <button type="submit" class="ui-btn ui-btn-warning mt-4 w-full" data-ticket-communication-submit>Simpan catatan internal</button>
                            </form>
                        @endif

                        @if ($canRequestInformation)
                            <form method="POST" action="{{ route('tickets.request-information', $ticket) }}" enctype="multipart/form-data" class="rounded-2xl border border-[#b9e4ed] bg-[#f5fcfe] p-4 lg:col-span-2" data-ticket-communication-form>
                                @csrf
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-[#35505b]">Minta informasi tambahan</p>
                                    </div>
                                    <span class="rounded-full bg-[#e8f9fd] px-2 py-1 text-[0.62rem] font-extrabold text-[#147a79]">Tunggu 3 hari kerja</span>
                                </div>
                                <label for="request-information-body" class="ui-field-label mt-4">Pertanyaan untuk Pemohon <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <textarea id="request-information-body" name="body" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau bukti yang perlu dilengkapi Pemohon.">{{ old('body') }}</textarea>
                                <button type="submit" class="ui-btn ui-btn-secondary mt-4" data-ticket-communication-submit>Minta informasi &amp; tunggu Pemohon</button>
                            </form>
                        @endif
                    </div>
                @endif
                @endif
                </div>
            </details>

            @if (false && $ticket->fieldValues->isNotEmpty())
            <section class="ui-ticket-detail-section" aria-labelledby="service-fields-heading">
                <div class="ui-panel-header">
                    <h2 id="service-fields-heading" class="ui-section-title">Informasi layanan</h2>
                </div>
                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    @foreach ($ticket->fieldValues as $fieldValue)
                        @php
                            $displayValue = is_array($fieldValue->value)
                                ? implode(', ', $fieldValue->value)
                                : ($fieldValue->field_type_snapshot === 'boolean' ? ((bool) $fieldValue->value ? 'Ya' : 'Tidak') : $fieldValue->value);
                        @endphp
                        <div class="rounded-lg border border-[#edf2f4] p-4">
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">{{ $fieldValue->label_snapshot }}</dt>
                            <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-[#35505b]">{{ filled($displayValue) ? $displayValue : 'Tidak diisi' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
            @endif

            @if ($canSeeInternal && ($ticket->service_type_code_snapshot ?? $ticket->serviceType?->code) === 'SVC-07' && ($internalFieldDefinitions->isNotEmpty() || $internalFieldValues->isNotEmpty()))
                @php
                    $internalValuesByKey = $internalFieldValues->keyBy('field_key');
                @endphp
                <section class="ui-ticket-detail-section overflow-hidden border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="internal-fields-heading">
                    <div class="ui-panel-header">
                        <h2 id="internal-fields-heading" class="ui-section-title flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-[color:var(--tm-warning-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4.5" y="10.5" width="15" height="9.5" rx="2" /><path stroke-linecap="round" d="M8 10.5V8a4 4 0 1 1 8 0v2.5" /></svg>
                            <span>Bagian internal SVC-07</span>
                        </h2>
                    </div>

                    @if ($canUpdateInternalFields && $internalFieldDefinitions->isNotEmpty())
                        <form method="POST" action="{{ route('tickets.internal-fields.update', $ticket) }}" class="space-y-4 bg-[color:var(--tm-warning-50)] p-5 sm:p-6" data-ticket-internal-fields-form>
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 lg:grid-cols-2">
                                @foreach ($internalFieldDefinitions as $field)
                                    <x-tickets.internal-field :field="$field" :value="$internalValuesByKey->get($field->key)" />
                                @endforeach
                            </div>
                            @error('internal_fields')<x-field-error :message="$message" />@enderror
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[color:var(--tm-warning-200)] pt-4">
                                <button type="submit" class="ui-btn ui-btn-warning" data-ticket-internal-fields-submit>
                                    <span data-ticket-internal-fields-label>Simpan bagian internal</span>
                                    <span class="hidden" data-ticket-internal-fields-loading aria-hidden="true">Menyimpan…</span>
                                </button>
                            </div>
                        </form>
                    @else
                        @if ($internalFieldValues->isEmpty())
                            <p class="p-5 text-sm leading-6 text-[color:var(--tm-text-muted)] sm:p-6">Belum ada nilai internal yang disimpan oleh Tim TI.</p>
                        @else
                            <dl class="grid gap-4 bg-[color:var(--tm-warning-50)] p-5 sm:grid-cols-2 sm:p-6">
                                @foreach ($internalFieldValues as $fieldValue)
                                    @php
                                        $displayValue = is_array($fieldValue->value)
                                            ? implode(', ', $fieldValue->value)
                                            : ($fieldValue->field_type_snapshot === 'boolean' ? ((bool) $fieldValue->value ? 'Ya' : 'Tidak') : $fieldValue->value);
                                    @endphp
                                    <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-n-0)] p-4">
                                        <dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-warning-700)]">{{ $fieldValue->label_snapshot }}</dt>
                                        <dd class="mt-2 whitespace-pre-line text-sm font-semibold leading-6 text-[color:var(--tm-text)]">{{ filled($displayValue) ? $displayValue : 'Tidak diisi' }}</dd>
                                        <dd class="mt-2 inline-flex items-center gap-1 rounded-[var(--tm-r-full)] bg-[color:var(--tm-warning-100)] px-2 py-0.5 text-[0.68rem] font-bold tabular-nums text-[color:var(--tm-warning-700)]">Definisi versi {{ $fieldValue->version_snapshot }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    @endif
                </section>
            @endif

            @if (false)
            <section id="ticket-attachments" class="ui-ticket-detail-section" aria-labelledby="attachments-heading">
                <div class="ui-panel-header flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="ui-ticket-section-kicker">Berkas</p>
                        <h2 id="attachments-heading" class="mt-1 ui-section-title">Lampiran</h2>
                    </div>
                    @if ($ticket->attachments->isNotEmpty())
                        <span class="ui-ticket-count">{{ $ticket->attachments->count() }}</span>
                    @endif
                </div>
                @if ($canUploadAttachments && $ticketAttachmentPolicies->isNotEmpty())
                    <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data" class="space-y-4 border-b border-[#edf2f4] bg-[#f8fbfc] p-5 sm:p-6" data-ticket-attachment-form>
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($ticketAttachmentPolicies as $policy)
                                @php
                                    $accept = collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.'))->implode(',');
                                    $fileSize = $policy->max_file_size_kb % 1024 === 0
                                        ? intdiv($policy->max_file_size_kb, 1024).' MB'
                                        : $policy->max_file_size_kb.' KB';
                                @endphp
                                <div>
                                    <label for="ticket-attachment-policy-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                    <p id="ticket-attachment-policy-{{ $policy->id }}-help" class="ui-field-help">Maks. {{ $policy->max_file_count }} berkas · {{ $fileSize }} per berkas{{ $policy->visibility === 'internal' ? ' · internal' : '' }}.</p>
                                    <input id="ticket-attachment-policy-{{ $policy->id }}" name="attachments[{{ $policy->id }}][]" type="file" class="ui-file-input mt-2" multiple @if ($accept !== '') accept="{{ $accept }}" @endif aria-describedby="ticket-attachment-policy-{{ $policy->id }}-help">
                                </div>
                            @endforeach
                        </div>
                        @error('attachments')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
                        <button type="submit" class="ui-btn ui-btn-secondary w-full sm:w-auto" data-ticket-attachment-submit>Tambah lampiran</button>
                    </form>
                @endif
                @if ($ticket->attachments->isEmpty())
                    <p class="p-5 text-sm leading-6 text-[#78909a] sm:p-6">Belum ada lampiran.</p>
                @else
                    <ul class="divide-y divide-[#edf2f4]">
                        @foreach ($ticket->attachments as $attachment)
                            <li class="flex items-start justify-between gap-3 p-4 sm:px-6">
                                <div class="flex min-w-0 items-start gap-3">
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eef7f8] text-[#147a79]" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h7l3 3V20.25H7zM14 3.75v3h3M9.5 11h5M9.5 14.5h5M9.5 18h3" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-[#35505b]" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                        <p class="mt-1 text-xs text-[#78909a]">{{ $attachment->type_label_snapshot }} · {{ number_format($attachment->size_bytes / 1024, 0, ',', '.') }} KB</p>
                                    </div>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="ui-action-link shrink-0">Unduh<span class="sr-only"> {{ $attachment->original_name }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            @endif

        </div>

        <aside class="min-w-0 space-y-6 lg:sticky lg:top-6">
            <section class="ticket-reference-card overflow-hidden" aria-labelledby="ticket-information-heading">
                <div class="ticket-reference-card-header">
                    <h2 id="ticket-information-heading" class="ticket-reference-card-heading">Informasi Tiket</h2>
                </div>
                <dl class="ticket-reference-info-list">
                    <div class="ticket-reference-info-item">
                        <dt class="ticket-reference-info-label">Kategori</dt>
                        <dd class="ticket-reference-info-value">{{ $serviceLabel }}</dd>
                    </div>

                    @if ($impactField)
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Dampak</dt>
                            <dd class="ticket-reference-info-value">{{ $impactField['value'] }}</dd>
                        </div>
                    @endif

                    @if ($estimateLabel)
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Estimasi Selesai</dt>
                            <dd class="ticket-reference-info-value tabular-nums">{{ $estimateLabel }}</dd>
                        </div>
                    @endif

                    @if (filled($locationLabel))
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Lokasi</dt>
                            <dd class="ticket-reference-info-value">{{ $locationLabel }}</dd>
                        </div>
                    @endif

                    <div class="ticket-reference-info-divider" aria-hidden="true"></div>

                    <div class="ticket-reference-info-item">
                        <dt class="ticket-reference-info-label">Ditangani Oleh</dt>
                        @if ($hasAssignee)
                            <dd class="ticket-reference-assignee">
                                <span class="ticket-reference-assignee-avatar" aria-hidden="true">{{ $assigneeInitials }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-[color:var(--tm-text)]">{{ $assigneeName }}</span>
                                    <span class="mt-0.5 block text-xs text-[color:var(--tm-text-muted)]">Tim TI</span>
                                </span>
                            </dd>
                        @else
                            <dd class="ticket-reference-info-value">Menunggu petugas ditugaskan</dd>
                        @endif
                    </div>
                </dl>
            </section>

            <details class="ticket-reference-card ticket-reference-collapsible overflow-hidden" aria-labelledby="ticket-attachments-heading" @if (! $isClosed) open @endif>
                <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                    <span class="flex min-w-0 items-center gap-2">
                        <span id="ticket-attachments-heading" class="ticket-reference-card-heading">Lampiran</span>
                        @if ($ticketAttachments->isNotEmpty())
                            <span class="rounded-[var(--tm-r-full)] bg-[color:var(--tm-n-100)] px-2 py-0.5 text-xs font-bold tabular-nums text-[color:var(--tm-text-muted)]">{{ $ticketAttachments->count() }}</span>
                        @endif
                    </span>
                </summary>
                <div class="ticket-reference-card-body">
                    @if ($canUploadAttachments && $ticketAttachmentPolicies->isNotEmpty())
                        <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data" class="space-y-4 border-b border-[color:var(--tm-border-subtle)] pb-4" data-ticket-attachment-form>
                            @csrf
                            @foreach ($ticketAttachmentPolicies as $policy)
                                @php
                                    $accept = collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.'))->implode(',');
                                    $fileSize = $policy->max_file_size_kb % 1024 === 0
                                        ? intdiv($policy->max_file_size_kb, 1024).' MB'
                                        : $policy->max_file_size_kb.' KB';
                                @endphp
                                <div>
                                    <label for="ticket-attachment-policy-{{ $policy->id }}" class="ticket-reference-field-label">{{ $policy->label }}</label>
                                    <p id="ticket-attachment-policy-{{ $policy->id }}-help" class="ticket-reference-file-help mt-1">Maks. {{ $policy->max_file_count }} berkas, {{ $fileSize }} per berkas{{ $policy->visibility === 'internal' ? ' · internal' : '' }}.</p>
                                    <input id="ticket-attachment-policy-{{ $policy->id }}" name="attachments[{{ $policy->id }}][]" type="file" class="ui-file-input mt-2 w-full" multiple @if ($accept !== '') accept="{{ $accept }}" @endif aria-describedby="ticket-attachment-policy-{{ $policy->id }}-help">
                                </div>
                            @endforeach
                            @error('attachments')<x-field-error :message="$message" />@enderror
                            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-attachment-submit>Tambah lampiran</button>
                        </form>
                    @endif

                    @if ($ticketAttachments->isEmpty())
                        <div class="{{ $canUploadAttachments && $ticketAttachmentPolicies->isNotEmpty() ? 'mt-4' : '' }} flex flex-col items-center gap-2 rounded-[var(--tm-r-md)] border border-dashed border-[color:var(--tm-border)] bg-[color:var(--tm-sunken)] px-4 py-6 text-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-[var(--tm-r-full)] bg-[color:var(--tm-n-100)] text-[color:var(--tm-text-faint)]" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" /></svg>
                            </span>
                            <p class="text-xs font-semibold text-[color:var(--tm-text-muted)]">Belum ada lampiran.</p>
                        </div>
                    @else
                        <ul class="mt-4 space-y-2">
                            @foreach ($ticketAttachments as $attachment)
                                <li>
                                    <a href="{{ route('attachments.download', $attachment) }}" class="ticket-reference-attachment-link rounded-[var(--tm-r-md)] transition-[background-color,border-color] duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-brand-50)]">
                                        <span class="ticket-reference-attachment-icon" aria-hidden="true">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="4" width="17" height="16" rx="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m6.5 16 3.2-3.4 2.5 2.6 2-2.1 3.3 2.9M8.5 9.3h.01" /></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-[color:var(--tm-text)]">{{ $attachment->original_name }}</span>
                                            <span class="mt-0.5 block text-xs tabular-nums text-[color:var(--tm-text-muted)]">{{ $attachment->type_label_snapshot }} - {{ number_format($attachment->size_bytes / 1024, 0, ',', '.') }} KB</span>
                                        </span>
                                        <span class="flex shrink-0 items-center gap-1 text-xs font-bold text-[color:var(--tm-brand-700)]">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v10m0 0 3.5-3.5M12 14.5 8.5 11M5 17.5v1a1.5 1.5 0 0 0 1.5 1.5h11a1.5 1.5 0 0 0 1.5-1.5v-1" /></svg>
                                            Unduh<span class="sr-only"> {{ $attachment->original_name }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </details>

            <section class="ticket-reference-card" aria-labelledby="ticket-activity-heading">
                <div class="ticket-reference-card-body">
                    <h2 id="ticket-activity-heading" class="ticket-reference-section-title flex items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 7.5v5l3 1.8" /></svg>
                        <span>Aktivitas Tiket</span>
                    </h2>

                    @if ($activity->isNotEmpty())
                        <x-tickets.timeline :events="$activity" variant="reference" />
                    @else
                        <p class="ticket-reference-empty mt-5">Belum ada aktivitas pada tiket ini.</p>
                    @endif
                </div>
            </section>

        </aside>
    </div>
    </div>

    @push('modals')
        @include('tickets._action-modals')
    @endpush
@endsection
