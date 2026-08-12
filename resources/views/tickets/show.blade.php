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
            <div class="ticket-reference-alert mt-6" role="status">
                <p class="ticket-reference-alert-title">Alasan penolakan</p>
                <p class="ticket-reference-alert-body">{{ $ticket->rejection_reason }}</p>
            </div>
        @endif

        @if ($ticket->status === \App\Enums\TicketStatus::TidakDisetujui && filled($approvalDecisionNote))
            <div class="ticket-reference-alert mt-6" role="status">
                <p class="ticket-reference-alert-title">Catatan keputusan</p>
                <p class="ticket-reference-alert-body">{{ $approvalDecisionNote }}</p>
            </div>
        @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.75fr)_minmax(18rem,0.85fr)] lg:items-start">
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
                            <dl class="mt-3 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                                @foreach ($submittedFields as $field)
                                    <div>
                                        <dt class="ticket-reference-field-label">{{ $field['label'] }}</dt>
                                        <dd class="ticket-reference-field-value whitespace-pre-line">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    @if (filled($ticket->solution) && ! ($canConfirm || $canNotSatisfied))
                        <div class="ticket-reference-subsection ticket-reference-subsection--solution mt-6" role="status">
                            <h3 class="ticket-reference-info-label">Hasil pekerjaan</h3>
                            <p class="ticket-reference-body-copy mt-2 whitespace-pre-line">{{ $ticket->solution }}</p>
                        </div>
                    @endif
                </div>
            </section>

            @if ($ticket->comments->isNotEmpty())
            <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-conversation-heading" @if (! $isClosed) open @endif>
                <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                    <span class="min-w-0">
                        <span id="ticket-conversation-heading" class="ticket-reference-section-title block">Percakapan</span>
                        <span class="ticket-reference-section-description block">Pesan antara Pemohon dan Tim TI.</span>
                    </span>
                </summary>

                @if ($lastTimedOutWait)
                    <div class="mx-5 mt-5 rounded-xl border border-[#f0d28c] bg-[#fff9e9] p-4 sm:mx-6" role="status">
                        <div class="flex gap-3">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#ffe8a3] text-[#9a6700]" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 7.5v5l3 1.8" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-[#7c5800]">Batas tunggu Pemohon terlewati</p>
                                <p class="mt-1 text-xs leading-5 text-[#946f16]">Dikembalikan ke Dikerjakan pada {{ $lastTimedOutWait->ended_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}.</p>
                            </div>
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
                    <span id="ticket-reply-heading" class="ticket-reference-section-title flex min-w-0 items-center gap-2">
                        <svg class="h-6 w-6 shrink-0 text-[#0b1c30]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
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
                                    <p class="mt-1.5 text-xs font-bold text-[#ba1a1a]">{{ $message }}</p>
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
                                    <button type="submit" class="ui-btn bg-[#0037b0] text-white hover:bg-[#1d4ed8]" data-ticket-communication-submit>Kirim Pesan</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="ticket-reference-reply mt-5">
                            <div class="ticket-reference-avatar" aria-hidden="true">{{ $currentUserInitials }}</div>
                            <div class="min-w-0 flex-1">
                                <label for="ticket-public-reply-disabled" class="ticket-reference-field-label">{{ $isRequester ? 'Balasan ke Tim TI' : 'Balasan ke Pemohon' }}</label>
                                <textarea id="ticket-public-reply-disabled" rows="5" disabled class="ticket-reference-textarea mt-1.5 disabled:cursor-not-allowed disabled:bg-[#f3f5fa]" placeholder="Ketik pesan atau informasi tambahan di sini..."></textarea>
                                <p class="ticket-reference-file-help mt-2">{{ $isRequester && $isClosed ? 'Balasan tidak tersedia karena tiket sudah berstatus akhir.' : 'Balasan tidak tersedia pada status tiket ini.' }}</p>
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
                <section class="ui-ticket-detail-section border-l-4 border-l-[#e4a72c]" aria-labelledby="internal-fields-heading">
                    <div class="ui-panel-header">
                        <h2 id="internal-fields-heading" class="ui-section-title">Bagian internal SVC-07</h2>
                    </div>

                    @if ($canUpdateInternalFields && $internalFieldDefinitions->isNotEmpty())
                        <form method="POST" action="{{ route('tickets.internal-fields.update', $ticket) }}" class="space-y-4 bg-[#fffaf0] p-5 sm:p-6" data-ticket-internal-fields-form>
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 lg:grid-cols-2">
                                @foreach ($internalFieldDefinitions as $field)
                                    <x-tickets.internal-field :field="$field" :value="$internalValuesByKey->get($field->key)" />
                                @endforeach
                            </div>
                            @error('internal_fields')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#f0dca7] pt-4">
                                <button type="submit" class="ui-btn ui-btn-warning" data-ticket-internal-fields-submit>
                                    <span data-ticket-internal-fields-label>Simpan bagian internal</span>
                                    <span class="hidden" data-ticket-internal-fields-loading aria-hidden="true">Menyimpan…</span>
                                </button>
                            </div>
                        </form>
                    @else
                        @if ($internalFieldValues->isEmpty())
                            <p class="p-5 text-sm leading-6 text-[#8a7440] sm:p-6">Belum ada nilai internal yang disimpan oleh Tim TI.</p>
                        @else
                            <dl class="grid gap-4 bg-[#fffaf0] p-5 sm:grid-cols-2 sm:p-6">
                                @foreach ($internalFieldValues as $fieldValue)
                                    @php
                                        $displayValue = is_array($fieldValue->value)
                                            ? implode(', ', $fieldValue->value)
                                            : ($fieldValue->field_type_snapshot === 'boolean' ? ((bool) $fieldValue->value ? 'Ya' : 'Tidak') : $fieldValue->value);
                                    @endphp
                                    <div class="rounded-lg border border-[#f0dca7] bg-[#fffdf7] p-4">
                                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#8a7440]">{{ $fieldValue->label_snapshot }}</dt>
                                        <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-[#6f5314]">{{ filled($displayValue) ? $displayValue : 'Tidak diisi' }}</dd>
                                        <dd class="mt-2 text-[0.68rem] font-bold text-[#a18442]">Definisi versi {{ $fieldValue->version_snapshot }}</dd>
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

        <aside class="min-w-0 space-y-6">
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
                            <dd class="ticket-reference-info-value">{{ $estimateLabel }}</dd>
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
                                    <span class="block truncate text-sm font-semibold text-[#0b1c30]">{{ $assigneeName }}</span>
                                    <span class="mt-0.5 block text-xs text-[#434655]">Tim TI</span>
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
                    <span id="ticket-attachments-heading" class="ticket-reference-card-heading">Lampiran</span>
                </summary>
                <div class="ticket-reference-card-body !p-4">
                    @if ($canUploadAttachments && $ticketAttachmentPolicies->isNotEmpty())
                        <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data" class="space-y-4 border-b border-[#c4c5d7] pb-4" data-ticket-attachment-form>
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
                            @error('attachments')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
                            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-attachment-submit>Tambah lampiran</button>
                        </form>
                    @endif

                    @if ($ticketAttachments->isEmpty())
                        <p class="ticket-reference-empty {{ $canUploadAttachments && $ticketAttachmentPolicies->isNotEmpty() ? 'mt-4' : '' }}">Belum ada lampiran.</p>
                    @else
                        <ul class="mt-4 space-y-3">
                            @foreach ($ticketAttachments as $attachment)
                                <li>
                                    <a href="{{ route('attachments.download', $attachment) }}" class="ticket-reference-attachment-link">
                                        <span class="ticket-reference-attachment-icon" aria-hidden="true">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="4" width="17" height="16" rx="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m6.5 16 3.2-3.4 2.5 2.6 2-2.1 3.3 2.9M8.5 9.3h.01" /></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-[#0b1c30]">{{ $attachment->original_name }}</span>
                                            <span class="mt-0.5 block text-xs text-[#434655]">{{ $attachment->type_label_snapshot }} - {{ number_format($attachment->size_bytes / 1024, 0, ',', '.') }} KB</span>
                                        </span>
                                        <span class="shrink-0 text-xs font-semibold text-[#0037b0]">Unduh<span class="sr-only"> {{ $attachment->original_name }}</span></span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </details>

            <section class="ticket-reference-card" aria-labelledby="ticket-activity-heading">
                <div class="ticket-reference-card-body">
                    <h2 id="ticket-activity-heading" class="ticket-reference-section-title">Aktivitas Tiket</h2>

                    @if ($activity->isNotEmpty())
                        <x-tickets.timeline :events="$activity" variant="reference" />
                    @else
                        <p class="ticket-reference-empty mt-5">Belum ada aktivitas pada tiket ini.</p>
                    @endif
                </div>
            </section>

            @if ($showActionPanel)
            <div class="contents">

            @if ($canChangePriority)
                <x-ui.modal-panel id="ticket-priority-modal" labelledby="priority-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-priority-modal'">
                <section class="ui-panel" aria-labelledby="priority-heading">
                    <div class="ui-panel-header">
                        <div>
                            <h2 id="priority-heading" class="ui-section-title">Ubah Prioritas</h2>
                            <p class="ui-section-description">Pilih prioritas yang sesuai dengan dampak dan urgensi tiket.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tickets.priority.update', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-priority-form>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_action_modal" value="ticket-priority-modal">
                        <div>
                            <label for="ticket-priority" class="ui-field-label">Prioritas <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <select id="ticket-priority" name="priority" required class="ui-select mt-2">
                                @foreach ($priorityOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($currentPriority === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="priority-change-reason" class="ui-field-label">Alasan perubahan <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="priority-change-reason" name="reason" rows="3" required maxlength="1000" class="ui-textarea mt-2" placeholder="Jelaskan mengapa prioritas perlu disesuaikan.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-priority-submit>Simpan Prioritas</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canReject)
                <x-ui.modal-panel id="ticket-reject-modal" labelledby="reject-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-reject-modal'">
                <section class="ui-panel border-l-4 border-l-rose-500" aria-labelledby="reject-heading">
                    <div class="ui-panel-header">
                        <div>
                            <h2 id="reject-heading" class="ui-section-title">Tolak Tiket</h2>
                            <p class="ui-section-description">Tiket yang ditolak tidak lagi berada di antrean Helpdesk.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tickets.reject', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-reject-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-reject-modal">
                        <div>
                            <label for="ticket-reject-reason" class="ui-field-label">Alasan penolakan <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="ticket-reject-reason" name="reason" rows="4" required maxlength="2000" class="ui-textarea mt-2 !border-rose-200" placeholder="Jelaskan alasan yang perlu diketahui Pelapor.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-danger w-full" data-ticket-reject-submit>Tolak Tiket</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canCommentInternal)
                <x-ui.modal-panel id="ticket-internal-comment-modal" labelledby="internal-comment-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-internal-comment-modal'">
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="internal-comment-heading">
                    <div class="ui-panel-header">
                        <div>
                            <h2 id="internal-comment-heading" class="ui-section-title">Catatan Internal</h2>
                            <p class="ui-section-description">Hanya dapat dibaca oleh Tim TI.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tickets.comments.internal', $ticket) }}" enctype="multipart/form-data" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-internal-comment-modal">
                        <div>
                            <label for="internal-comment-body" class="ui-field-label">Isi catatan internal <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="internal-comment-body" name="body" rows="5" required class="ui-textarea mt-2 !border-[#ecd89f]" placeholder="Tulis konteks untuk Tim TI.">{{ old('body') }}</textarea>
                            @error('body')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>

                        @if ($commentInternalPolicies->isNotEmpty())
                            <fieldset class="space-y-3">
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

                        <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-communication-submit>Simpan catatan internal</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canRequestInformation)
                <x-ui.modal-panel id="ticket-request-information-modal" labelledby="request-information-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-request-information-modal'">
                <section class="ui-panel border-l-4 border-l-[#147a79]" aria-labelledby="request-information-heading">
                    <div class="ui-panel-header">
                        <div>
                            <h2 id="request-information-heading" class="ui-section-title">Kembalikan ke Pelapor</h2>
                            <p class="ui-section-description">Tiket akan menunggu balasan Pemohon maksimal 3 hari kerja.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tickets.request-information', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-request-information-modal">
                        <div>
                            <label for="request-information-body" class="ui-field-label">Pertanyaan untuk Pemohon <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="request-information-body" name="body" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau bukti yang perlu dilengkapi Pemohon.">{{ old('body') }}</textarea>
                            @error('body')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-communication-submit>Kirim ke Pelapor dan tunggu balasan</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canSeeInternal && ($specialControlReadiness['kind'] ?? null) === 'database_change')
                @php
                    $changeControl = $specialControlReadiness['control'] ?? null;
                    $controlLabels = [
                        'change_script' => 'Skrip perubahan',
                        'rollback_script' => 'Skrip pemulihan',
                        'backup_evidence' => 'Bukti backup',
                    ];
                @endphp
                <x-ui.modal-panel id="ticket-database-change-modal" labelledby="database-change-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-database-change-modal'" max-width="max-w-3xl">
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="database-change-heading">
                    <div class="ui-panel-header">
                        <h2 id="database-change-heading" class="ui-section-title">Kontrol SVC-03</h2>
                    </div>
                    <div class="space-y-4 p-5 sm:p-6">
                        <ul class="space-y-2" aria-label="Persyaratan bukti SVC-03">
                            @foreach (($specialControlReadiness['requirements'] ?? []) as $type => $requirement)
                                <li class="flex items-start justify-between gap-3 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 py-2.5 text-sm">
                                    <span class="min-w-0">
                                        <span class="block font-extrabold text-[#35505b]">{{ $controlLabels[$type] ?? $type }}</span>
                                        @if ($requirement['attachment'])
                                            <span class="mt-1 block truncate text-xs text-[#78909a]">{{ $requirement['attachment']->original_name }}</span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 text-xs font-extrabold {{ $requirement['valid'] ? 'text-[#087f5b]' : 'text-[#be123c]' }}">
                                        {{ $requirement['valid'] ? 'Tersedia dan valid' : 'Belum tersedia/valid' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($changeControl?->executionStarted())
                            <div class="rounded-lg border border-[#cdeef7] bg-[#f5fcfe] p-3 text-sm text-[#35505b]">
                                <p class="font-extrabold text-[#147a79]">Eksekusi sudah dimulai</p>
                                <p class="mt-1 text-xs leading-5 text-[#526f79]">Oleh {{ $changeControl->executionStartedBy?->name ?? 'Pengguna yang tercatat' }} pada {{ $changeControl->execution_started_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}.</p>
                            </div>
                        @elseif ($canStartDatabaseChange)
                            <form method="POST" action="{{ route('tickets.database-change.execute', $ticket) }}" class="space-y-3" data-ticket-database-change-form data-swal-confirm="Mulai Eksekusi SVC-03 setelah tiga bukti diverifikasi?">
                                @csrf
                                <input type="hidden" name="_action_modal" value="ticket-database-change-modal">
                                <p class="text-xs leading-5 text-[#78909a]">Tiga bukti harus valid sebelum eksekusi.</p>
                                <button type="submit" class="ui-btn ui-btn-warning w-full">Mulai Eksekusi</button>
                                @error('database_change')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
                            </form>
                        @elseif (! ($specialControlReadiness['evidence_ready'] ?? false))
                            <p class="rounded-lg border border-[#f0d28c] bg-[#fffaf0] p-3 text-xs leading-5 text-[#8a5a00]">Lengkapi dan unggah tiga bukti berbeda untuk mengaktifkan Mulai Eksekusi.</p>
                        @endif

                        @if ($canVerifyDatabaseChange && $changeControl?->executionStarted() && ! $changeControl?->verified())
                            <form method="POST" action="{{ route('tickets.database-change.verify', $ticket) }}" class="space-y-3 rounded-xl border border-[#dfe8ec] bg-[#fbfdfd] p-4" data-ticket-database-change-form>
                                @csrf
                                <input type="hidden" name="_action_modal" value="ticket-database-change-modal">
                                <div>
                                    <label for="database-change-result" class="ui-field-label">Hasil verifikasi <span class="text-rose-600" aria-hidden="true">*</span></label>
                                    <textarea id="database-change-result" name="verification_result" rows="3" required maxlength="20000" class="ui-textarea mt-2" placeholder="Jelaskan hasil pemeriksaan setelah perubahan dijalankan.">{{ old('verification_result') }}</textarea>
                                    @error('verification_result')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="database-change-notes" class="ui-field-label">Catatan verifikasi <span class="text-rose-600" aria-hidden="true">*</span></label>
                                    <textarea id="database-change-notes" name="verification_notes" rows="3" required maxlength="20000" class="ui-textarea mt-2" placeholder="Catat bukti pemeriksaan, dampak, atau tindak lanjut.">{{ old('verification_notes') }}</textarea>
                                    @error('verification_notes')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                                </div>
                                <button type="submit" class="ui-btn ui-btn-primary w-full">Simpan verifikasi hasil</button>
                            </form>
                        @elseif ($changeControl?->verified())
                            <div class="rounded-lg border border-[#cdeef7] bg-[#f5fcfe] p-3 text-sm text-[#35505b]">
                                <p class="font-extrabold text-[#147a79]">Verifikasi hasil selesai</p>
                                <p class="mt-1 whitespace-pre-line text-xs leading-5">{{ $changeControl->verification_result }}</p>
                                <p class="mt-2 text-xs leading-5 text-[#526f79]">Oleh {{ $changeControl->verifier?->name ?? 'Pengguna yang tercatat' }} pada {{ $changeControl->verified_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}.</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg bg-white px-3 py-2 text-xs leading-5 text-[#526f79]">Catatan: {{ $changeControl->verification_notes }}</p>
                            </div>
                        @endif
                    </div>
                </section>
                </x-ui.modal-panel>
            @elseif ($canSeeInternal && ($specialControlReadiness['kind'] ?? null) === 'data_export')
                {{-- Status hasil tarik data ditampilkan di dalam dialog penyelesaian. --}}
            @endif

            @if ($canComplete)
                <x-ui.modal-panel id="ticket-complete-modal" labelledby="complete-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-complete-modal'">
                <section class="ui-panel border-l-4 border-l-[#2bb8aa]" aria-labelledby="complete-heading">
                    @php
                        $isDataExport = ($specialControlReadiness['kind'] ?? null) === 'data_export';
                    @endphp
                    <div class="ui-panel-header">
                        <h2 id="complete-heading" class="ui-section-title">{{ $isDataExport ? 'Simpan solusi dan hasil tarik data' : 'Simpan solusi' }}</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.complete', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-resolution-form @if ($isDataExport) enctype="multipart/form-data" @endif>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-complete-modal">
                        <div>
                            <label for="ticket-solution" class="ui-field-label">Solusi <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="ticket-solution" name="solution" rows="6" required maxlength="20000" class="ui-textarea mt-2" placeholder="Jelaskan tindakan dan hasil penyelesaian tiket.">{{ old('solution') }}</textarea>
                            @error('solution')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        @if ($isDataExport)
                            <div class="rounded-xl border border-[#b9e4ed] bg-[#f5fcfe] p-4">
                                <label for="data-export-result" class="ui-field-label">Hasil tarik data <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <p id="data-export-result-help" class="ui-field-help">Unggah satu berkas hasil yang dapat diakses Pemohon. Ukuran maksimal 10 MB.</p>
                                <input id="data-export-result" name="data_export_result" type="file" required class="ui-file-input mt-2" aria-describedby="data-export-result-help">
                                @error('data_export_result')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        @endif
                        <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-resolution-submit>{{ $isDataExport ? 'Simpan solusi dan unggah hasil' : 'Simpan solusi dan minta konfirmasi' }}</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canConfirm || $canNotSatisfied)
                <x-ui.modal-panel id="ticket-confirmation-modal" labelledby="confirmation-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-confirmation-modal'">
                <section class="ui-panel border-l-4 border-l-[#2bb8aa]" aria-labelledby="confirmation-heading">
                    <div class="ui-panel-header">
                        <h2 id="confirmation-heading" class="ui-section-title">Apakah hasilnya sudah sesuai?</h2>
                    </div>
                    <div class="grid gap-3 p-5 sm:p-6">
                        @if ($canConfirm)
                            <form method="POST" action="{{ route('tickets.confirm', $ticket) }}" data-swal-confirm="Konfirmasi hasil ini dan tutup tiket?">
                                @csrf
                                <input type="hidden" name="_action_modal" value="ticket-confirmation-modal">
                                <button type="submit" class="ui-btn ui-btn-primary w-full">Hasil sudah sesuai dan tutup tiket</button>
                            </form>
                        @endif
                        @if ($canNotSatisfied)
                            <form method="POST" action="{{ route('tickets.not-satisfied', $ticket) }}" class="space-y-3 rounded-xl border border-[#f0d28c] bg-[#fffaf0] p-4" data-ticket-resolution-form>
                                @csrf
                                <input type="hidden" name="_action_modal" value="ticket-confirmation-modal">
                                <div>
                                    <label for="not-satisfied-reason" class="ui-field-label">Alasan hasil belum sesuai <span class="text-rose-600" aria-hidden="true">*</span></label>
                                    <textarea id="not-satisfied-reason" name="reason" rows="4" required maxlength="5000" class="ui-textarea mt-2" placeholder="Jelaskan bagian hasil yang masih perlu diperbaiki.">{{ old('reason') }}</textarea>
                                    @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                                </div>
                                <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-resolution-submit>Hasil belum sesuai</button>
                            </form>
                        @endif
                    </div>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canReopen)
                <x-ui.modal-panel id="ticket-reopen-modal" labelledby="reopen-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-reopen-modal'">
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="reopen-heading">
                    <div class="ui-panel-header">
                        <h2 id="reopen-heading" class="ui-section-title">Buka kembali tiket</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-resolution-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-reopen-modal">
                        <div>
                            <label for="reopen-reason" class="ui-field-label">Alasan buka kembali</label>
                            <textarea id="reopen-reason" name="reason" rows="3" maxlength="5000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan tindak lanjut yang masih diperlukan.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-resolution-submit>Buka kembali tiket</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($approvalRequest)
                <x-ui.modal-panel id="ticket-approval-decision-modal" labelledby="approval-heading-{{ $approvalRequest->id }}" :auto-open="$errors->any() && old('_action_modal') === 'ticket-approval-decision-modal'">
                <x-approval-panel :approval-request="$approvalRequest" :ticket="$ticket" :can-decide="$canDecideApproval" />
                </x-ui.modal-panel>
            @endif

            @if ($canRequestApproval)
                <x-ui.modal-panel id="ticket-request-approval-modal" labelledby="request-approval-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-request-approval-modal'">
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="request-approval-heading">
                    <div class="ui-panel-header">
                        <h2 id="request-approval-heading" class="ui-section-title">Butuh Persetujuan</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.request-approval', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-approval-request-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-request-approval-modal">
                        <div>
                            <label for="approval-request-reason" class="ui-field-label">Catatan permintaan</label>
                            <textarea id="approval-request-reason" name="reason" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan konteks yang perlu diputuskan Manajer TI.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-warning w-full" data-approval-request-submit>Butuh Persetujuan</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canStartThirdParty || $canResumeThirdParty)
                <x-ui.modal-panel id="ticket-third-party-modal" labelledby="third-party-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-third-party-modal'">
                <section class="ui-panel border-l-4 border-l-[#2bb8aa]" aria-labelledby="third-party-heading">
                    <div class="ui-panel-header">
                        <h2 id="third-party-heading" class="ui-section-title">{{ $canResumeThirdParty ? 'Pending tiket' : 'Pending tiket' }}</h2>
                    </div>
                    @if ($canResumeThirdParty && $activeWait)
                        <div class="space-y-3 px-5 pb-1 sm:px-6">
                            <dl class="grid gap-3 rounded-xl bg-[#f4fbfa] p-4 text-sm">
                                <div><dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Alasan pending</dt><dd class="mt-1 font-extrabold text-[#35505b]">{{ $activeWait->third_party_name }}</dd></div>
                                <div><dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Mulai menunggu</dt><dd class="mt-1 font-bold text-[#526f79]">{{ $activeWait->started_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}@if ($activeWait->follow_up_date) · Follow-up {{ $activeWait->follow_up_date->translatedFormat('d M Y') }}@endif</dd></div>
                            </dl>
                            <form method="POST" action="{{ route('tickets.resume-third-party', $ticket) }}" class="space-y-3" data-ticket-communication-form>
                                @csrf
                                <input type="hidden" name="_action_modal" value="ticket-third-party-modal">
                                <div>
                                    <label for="third-party-resume-reason" class="ui-field-label">Catatan pelanjutan</label>
                                    <textarea id="third-party-resume-reason" name="reason" rows="3" class="ui-textarea mt-2" placeholder="Opsional: tulis alasan tiket dapat dilanjutkan.">{{ old('reason') }}</textarea>
                                </div>
                                <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-communication-submit>Lanjutkan pengerjaan</button>
                            </form>
                        </div>
                    @elseif ($canStartThirdParty)
                        <form method="POST" action="{{ route('tickets.wait-third-party', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                            @csrf
                            <input type="hidden" name="_action_modal" value="ticket-third-party-modal">
                            <div>
                                <label for="third-party-name" class="ui-field-label">Alasan pending <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <input id="third-party-name" name="third_party_name" value="{{ old('third_party_name') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Menunggu vendor atau jadwal perubahan">
                            </div>
                            <div>
                                <label for="third-party-follow-up" class="ui-field-label">Tanggal follow-up</label>
                                <input id="third-party-follow-up" type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="ui-input mt-2">
                            </div>
                            <div>
                                <label for="third-party-note" class="ui-field-label">Catatan pending</label>
                                <textarea id="third-party-note" name="note" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan detail atau tindak lanjut yang dibutuhkan.">{{ old('note') }}</textarea>
                            </div>
                            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-communication-submit>Simpan Pending</button>
                        </form>
                    @endif
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canTriage)
                <x-ui.modal-panel id="ticket-triage-modal" labelledby="triage-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-triage-modal'" max-width="max-w-3xl">
                <section class="ui-panel overflow-hidden" aria-labelledby="triage-heading">
                    <div class="ui-panel-header">
                        <h2 id="triage-heading" class="ui-section-title">Triase tiket</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.triage', $ticket) }}" class="space-y-5 p-5 sm:p-6" data-ticket-triage-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-triage-modal">
                        <fieldset>
                            <legend class="ui-field-label">Hasil triase <span class="text-rose-600" aria-hidden="true">*</span></legend>
                            <div class="mt-2 grid gap-2">
                                @foreach ([['self', 'Ambil dan kerjakan sendiri'], ['tier_2', 'Tugaskan ke Agen Tier 2']] as [$value, $label])
                                    <label class="flex cursor-pointer gap-3 rounded-xl border border-[#dfe8ec] bg-[#fbfdfd] p-3 transition hover:border-[#8bd7ee] has-[:checked]:border-[#75d5f3] has-[:checked]:bg-[#f1fbfe]">
                                        <input type="radio" name="outcome" value="{{ $value }}" class="mt-1 h-4 w-4 border-[#a9bbc2] text-[#147a79] focus:ring-[#2bb8aa]" @checked($currentOutcome === $value) data-ticket-triage-outcome>
                                        <span class="block text-sm font-extrabold text-[#35505b]">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('outcome')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </fieldset>

                        <div class="rounded-xl border border-[#dce9ed] bg-[#f8fbfc] p-4">
                            <p class="ui-field-label">Layanan tiket</p>
                            <p class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $serviceLabel }}</p>
                        </div>

                        <div data-ticket-triage-panel="tier_2" class="space-y-4 rounded-xl border border-[#dfe8ec] bg-[#f8fbfc] p-4" @if ($currentOutcome !== 'tier_2') hidden @endif>
                            <div>
                                <label for="tier-two-assignee" class="ui-field-label">Teknisi Tier 2</label>
                                <select id="tier-two-assignee" name="assigned_to_id" class="ui-select mt-2" data-ticket-assignee-select aria-describedby="tier-two-help">
                                    <option value="">Pilih teknisi</option>
                                    @foreach ($tierTwoUsers as $tierTwoUser)
                                        <option value="{{ $tierTwoUser->id }}" @selected((string) old('assigned_to_id') === (string) $tierTwoUser->id)>{{ $tierTwoUser->name }}</option>
                                    @endforeach
                                </select>
                                <p id="tier-two-help" class="ui-field-help">Saran berdasarkan keahlian layanan.</p>
                                @error('assigned_to_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div aria-live="polite">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#607681]">Saran teknisi</p>
                                </div>
                                <ul class="mt-2 space-y-2" data-ticket-suggestion-list>
                                    @foreach ($ticketSuggestions as $suggestion)
                                        <li class="rounded-lg border border-[#dcebef] bg-white px-3 py-2">
                                            <p class="text-xs font-extrabold text-[#35505b]">{{ $suggestion['user_name'] }} <span class="ml-1 rounded-full bg-[#e8faf4] px-1.5 py-0.5 text-[0.62rem] text-[#087f5b]">{{ $suggestion['match_count'] }} keahlian</span></p>
                                            <p class="mt-1 text-[0.68rem] text-[#78909a]">{{ implode(', ', $suggestion['matching_skill_names']) }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs leading-5 text-[#78909a] {{ $ticketSuggestions->isNotEmpty() ? 'hidden' : '' }}" data-ticket-suggestion-empty>Belum ada teknisi Tier 2 dengan keahlian yang sesuai layanan ini.</p>
                            </div>
                        </div>

                        <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-triage-submit>
                            <span data-ticket-triage-submit-label>Simpan triase</span>
                            <span class="hidden" data-ticket-triage-submit-loading aria-hidden="true">Menyimpan…</span>
                        </button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canAssignTierTwo)
                <x-ui.modal-panel id="ticket-assign-tier-two-modal" labelledby="assign-tier-two-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-assign-tier-two-modal'">
                <section class="ui-panel" aria-labelledby="assign-tier-two-heading">
                    <div class="ui-panel-header">
                        <h2 id="assign-tier-two-heading" class="ui-section-title">Tugaskan ke Tier 2</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.assign-tier-2', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-assignment-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-assign-tier-two-modal">
                        <div>
                            <label for="assign-tier-two-user" class="ui-field-label">Teknisi Tier 2 <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <select id="assign-tier-two-user" name="assigned_to_id" required class="ui-select mt-2">
                                <option value="">Pilih teknisi</option>
                                @foreach ($tierTwoUsers as $tierTwoUser)
                                    <option value="{{ $tierTwoUser->id }}">{{ $tierTwoUser->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="assign-tier-two-reason" class="ui-field-label">Catatan penugasan</label>
                            <textarea id="assign-tier-two-reason" name="reason" rows="3" class="ui-textarea mt-2" placeholder="Opsional: jelaskan konteks alih penanganan.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-assignment-submit>
                            <span data-ticket-assignment-submit-label>Tugaskan ke Tier 2</span>
                            <span class="hidden" data-ticket-assignment-submit-loading aria-hidden="true">Menyimpan…</span>
                        </button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            @if ($canReturnToTierOne)
                <x-ui.modal-panel id="ticket-return-tier-one-modal" labelledby="return-tier-one-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-return-tier-one-modal'">
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="return-tier-one-heading">
                    <div class="ui-panel-header">
                        <h2 id="return-tier-one-heading" class="ui-section-title">Kembalikan ke Tier 1</h2>
                    </div>
                    <form method="POST" action="{{ route('tickets.return-to-tier-1', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-return-form>
                        @csrf
                        <input type="hidden" name="_action_modal" value="ticket-return-tier-one-modal">
                        <div>
                            <label for="return-tier-one-reason" class="ui-field-label">Alasan pengembalian <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="return-tier-one-reason" name="reason" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau tindakan yang masih diperlukan.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-return-submit>Kembalikan ke Tier 1</button>
                    </form>
                </section>
                </x-ui.modal-panel>
            @endif

            </div>
            @endif
        </aside>
    </div>
    </div>
@endsection
