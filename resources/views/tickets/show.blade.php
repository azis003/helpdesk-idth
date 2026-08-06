@extends('layouts.app')

@section('title', ($ticket->ticket_number ?? 'Detail tiket').' — SIHATI')
@section('header_kicker', 'Tiket')
@section('header_title', 'Detail tiket')

@php
    $actor = auth()->user();
    $ticketLabel = $ticket->ticket_number ?? 'Tiket #'.$ticket->id;
    $serviceLabel = $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name ?? 'Layanan belum tersedia';
    $locationLabel = collect([$ticket->building_name_snapshot, $ticket->floor_name_snapshot, $ticket->room_name_snapshot])->filter()->implode(' · ');
    $canCancel = $actor->can('cancel', $ticket);
    $canTriage = $canTriage ?? false;
    $canAssignTierTwo = $canAssignTierTwo ?? false;
    $canReturnToTierOne = $canReturnToTierOne ?? false;
    $triageCategories = $triageCategories ?? collect();
    $tierTwoUsers = $tierTwoUsers ?? collect();
    $suggestionsByCategory = $suggestionsByCategory ?? [];
    $priorityOptions = $priorityOptions ?? \App\Enums\Priority::labels();
    $timeline = $timeline ?? [];
    $canCommentPublic = $canCommentPublic ?? false;
    $canCommentInternal = $canCommentInternal ?? false;
    $canRequestInformation = $canRequestInformation ?? false;
    $canRequesterReply = $canRequesterReply ?? false;
    $canStartThirdParty = $canStartThirdParty ?? false;
    $canResumeThirdParty = $canResumeThirdParty ?? false;
    $commentPublicPolicies = $commentPublicPolicies ?? collect();
    $commentInternalPolicies = $commentInternalPolicies ?? collect();
    $activeWait = $activeWait ?? null;
    $lastTimedOutWait = $lastTimedOutWait ?? null;
    $currentCategory = old('problem_category_id', $ticket->problem_category_id);
    $currentPriority = old('priority', $ticket->priority?->value);
    $currentOutcome = old('outcome', 'self');
    [$nextStepTitle, $nextStepDescription] = match ($ticket->status) {
        \App\Enums\TicketStatus::Baru => ['Tiket menunggu diproses', 'Tiket baru masuk ke antrean Tier 1 dan belum memiliki penanggung jawab.'],
        \App\Enums\TicketStatus::Diproses => ['Tiket sedang ditriase', 'Agen Tier 1 sedang memeriksa kategori, prioritas, dan jalur penanganan tiket.'],
        \App\Enums\TicketStatus::Dikerjakan => ['Tiket sedang dikerjakan', 'Penanggung jawab aktif melanjutkan pekerjaan sesuai jalur penanganan yang dipilih.'],
        \App\Enums\TicketStatus::MenungguPemohon => ['Menunggu balasan Pemohon', $activeWait?->due_at ? 'Pemohon perlu melengkapi informasi sebelum '. $activeWait->due_at->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i').'.' : 'Agen sedang menunggu informasi tambahan dari Pemohon.'],
        \App\Enums\TicketStatus::MenungguPihakKetiga => ['Menunggu pihak ketiga', $activeWait?->third_party_name ? 'Menunggu tindak lanjut dari '.$activeWait->third_party_name.'.' : 'Agen sedang menunggu tindak lanjut dari pihak ketiga.'],
        \App\Enums\TicketStatus::Ditolak => ['Tiket ditolak', $ticket->rejection_reason ?: 'Permintaan ini tidak dilanjutkan oleh Agen Tier 1.'],
        \App\Enums\TicketStatus::Dibatalkan => ['Tiket telah dibatalkan', 'Tiket ini tidak akan masuk ke proses penanganan lebih lanjut.'],
        default => ['Status tiket diperbarui', 'Tim TI akan melanjutkan tiket sesuai status dan kewenangan penanganannya.'],
    };
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <a href="{{ $actor->hasRole(\App\Enums\Role::AgenTier1) ? route('tickets.queue') : route('tickets.index') }}" class="ui-action-link">← {{ $actor->hasRole(\App\Enums\Role::AgenTier1) ? 'Kembali ke antrean' : 'Kembali ke tiket saya' }}</a>
            <h1 class="ui-page-title">{{ $ticketLabel }}</h1>
            <p class="ui-page-description">Detail permintaan, kepemilikan tiket, jalur triase, dan histori penanganan.</p>
        </div>
        @if ($canCancel)
            <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" onsubmit="return window.confirm('Batalkan tiket ini? Tiket hanya dapat dibatalkan saat status Baru dan tidak dapat diproses lebih lanjut.');">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger">Batalkan tiket</button>
            </form>
        @endif
    </div>

    <div class="mt-8 grid gap-5 xl:grid-cols-[1.28fr_0.72fr]">
        <div class="space-y-5">
            <section class="ui-panel" aria-labelledby="ticket-summary-heading">
                <div class="ui-panel-header flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Ringkasan permintaan</p>
                        <h2 id="ticket-summary-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">{{ $ticket->subject }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge :status="$ticket->status" />
                        <x-priority-badge :priority="$ticket->priority" />
                    </div>
                </div>
                <div class="space-y-5 p-5 sm:p-6">
                    @if ($ticket->status === \App\Enums\TicketStatus::Ditolak && filled($ticket->rejection_reason))
                        <div class="rounded-xl border border-rose-200 bg-[#fff5f6] p-4" role="status">
                            <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#be123c]">Alasan penolakan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#7f1d1d]">{{ $ticket->rejection_reason }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Deskripsi</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $ticket->description ?: 'Deskripsi belum tersedia.' }}</p>
                    </div>
                    <dl class="grid gap-4 border-t border-[#edf2f4] pt-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Layanan</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->service_type_code_snapshot ?? $ticket->serviceType?->code }} — {{ $serviceLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Kategori masalah</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->problemCategory?->name ?? 'Belum dikategorikan' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Dibuat</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ ($ticket->submitted_at ?? $ticket->created_at)?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Penanggung jawab</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">
                                {{ $ticket->assignee?->name ?? 'Belum ada — menunggu antrean Tier 1' }}
                                @if ($ticket->assignee && $ticket->assigned_tier)
                                    <span class="mt-1 block text-xs font-normal text-[#78909a]">{{ \App\Enums\Role::tryFrom($ticket->assigned_tier)?->label() ?? $ticket->assigned_tier }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Prioritas</dt>
                            <dd class="mt-1"><x-priority-badge :priority="$ticket->priority" /></dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="ui-panel overflow-hidden" aria-labelledby="conversation-heading">
                <div class="ui-panel-header flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#75d5f3] !shadow-[0_0_0_4px_#d9f6ff]" aria-hidden="true"></span>Ruang komunikasi</p>
                        <h2 id="conversation-heading" class="mt-2 ui-section-title">Percakapan tiket</h2>
                        <p class="ui-section-description">Balasan ke Pemohon dan Catatan Internal dipisahkan dengan jelas. Catatan internal tidak pernah terlihat oleh Pemohon.</p>
                    </div>
                    @if ($ticket->comments->isNotEmpty())
                        <span class="rounded-full bg-[#f1fbfe] px-3 py-1 text-xs font-extrabold text-[#147a79]">{{ $ticket->comments->count() }} pesan</span>
                    @endif
                </div>

                @if ($lastTimedOutWait)
                    <div class="mx-5 mt-5 rounded-xl border border-[#f0d28c] bg-[#fff9e9] p-4 sm:mx-6" role="status">
                        <div class="flex gap-3">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#ffe8a3] text-[#9a6700]" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 7.5v5l3 1.8" /></svg>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold text-[#7c5800]">Batas tunggu Pemohon terlewati</p>
                                <p class="mt-1 text-xs leading-5 text-[#946f16]">Tiket otomatis dikembalikan ke status Dikerjakan pada {{ $lastTimedOutWait->ended_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}. Penanda timeout disimpan di histori tiket.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4 p-5 sm:p-6">
                    @forelse ($ticket->comments as $comment)
                        @php
                            $isInternalComment = $comment->visibility?->value === 'internal';
                        @endphp
                        <article class="ui-comment-card rounded-2xl border p-4 {{ $isInternalComment ? 'border-[#f0d28c] bg-[#fffaf0]' : 'border-[#cdeef7] bg-[#f5fcfe]' }}" aria-label="{{ $comment->visibility?->label() }} dari {{ $comment->author?->name ?? 'Sistem' }}">
                            <header class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-extrabold {{ $isInternalComment ? 'bg-[#ffe8a3] text-[#8b6100]' : 'bg-[#d7f5fc] text-[#147a79]' }}">{{ strtoupper(substr($comment->author?->name ?? 'S', 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-extrabold text-[#35505b]">{{ $comment->author?->name ?? 'Sistem' }}</p>
                                        <p class="mt-0.5 text-xs text-[#78909a]">{{ $comment->visibility?->label() }}</p>
                                    </div>
                                </div>
                                <time class="text-xs text-[#78909a]" datetime="{{ $comment->created_at?->toIso8601String() }}">{{ $comment->created_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                            </header>
                            <p class="mt-4 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $comment->body }}</p>
                            @if ($comment->attachments->isNotEmpty())
                                <div class="mt-4 border-t {{ $isInternalComment ? 'border-[#f4dfae]' : 'border-[#dceff4]' }} pt-3">
                                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Lampiran pesan</p>
                                    <ul class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($comment->attachments as $attachment)
                                            <li><a href="{{ route('attachments.download', $attachment) }}" class="inline-flex max-w-full items-center gap-2 rounded-lg border border-[#dfe8ec] bg-white px-3 py-2 text-xs font-bold text-[#35505b] hover:border-[#75d5f3] hover:text-[#147a79]"><svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0 4-4m-4 4-4-4M5 19.5h14" /></svg><span class="truncate">{{ $attachment->original_name }}</span></a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#cfe0e5] bg-[#fbfdfd] p-6 text-center">
                            <p class="text-sm font-extrabold text-[#526f79]">Belum ada percakapan</p>
                            <p class="mt-1 text-xs leading-5 text-[#78909a]">Pesan pertama akan menjadi bagian dari histori komunikasi tiket.</p>
                        </div>
                    @endforelse
                </div>

                @if ($canRequesterReply)
                    <div class="border-t border-[#edf2f4] bg-[#f8fbfc] p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#147a79]">Balasan Pemohon</p>
                            <p class="mt-1 text-sm leading-6 text-[#526f79]">Lengkapi pertanyaan agen agar tiket kembali diproses oleh penanggung jawab sebelumnya.</p>
                        </div>
                        <form method="POST" action="{{ route('tickets.requester-reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4" data-ticket-communication-form>
                            @csrf
                            <div>
                                <label for="requester-reply-body" class="ui-field-label">Informasi tambahan <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <textarea id="requester-reply-body" name="body" rows="5" required class="ui-textarea mt-2" placeholder="Tuliskan jawaban atau informasi yang diminta agen.">{{ old('body') }}</textarea>
                            </div>
                            @if ($commentPublicPolicies->isNotEmpty())
                                <fieldset class="space-y-3">
                                    <legend class="ui-field-label">Lampiran pendukung</legend>
                                    @foreach ($commentPublicPolicies as $policy)
                                        @php
                                            $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                                        @endphp
                                        <div>
                                            <label for="requester-reply-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                            <input id="requester-reply-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-2" @if ($accept !== '') accept="{{ $accept }}" @endif>
                                            <p class="ui-field-help">Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas.</p>
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
                                        <p class="mt-1 text-xs leading-5 text-[#78909a]">Pesan ini terlihat oleh Pemohon dan tercatat permanen.</p>
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
                                        <p class="mt-1 text-xs leading-5 text-[#78909a]">Hanya dapat dibaca oleh tim TI yang berwenang.</p>
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
                                        <p class="mt-1 text-xs leading-5 text-[#78909a]">Pertanyaan dikirim sebagai balasan publik, status menjadi Menunggu Pemohon, dan SLA aktif dijeda.</p>
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
            </section>

            <section class="ui-panel" aria-labelledby="requester-heading">
                <div class="ui-panel-header">
                    <h2 id="requester-heading" class="ui-section-title">Identitas tiket</h2>
                    <p class="ui-section-description">Pemohon dan pembuat tiket disimpan terpisah untuk membedakan tiket mandiri dan pencatatan oleh Agen Tier 1.</p>
                </div>
                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-lg bg-[#f8fbfc] p-4">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pemohon</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">NIP: {{ $ticket->requester_nip_snapshot ?? $ticket->requester?->nip ?? 'Tidak tersedia' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">Tim: {{ $ticket->requester_team_snapshot ?: 'Belum memiliki tim' }}</dd>
                    </div>
                    <div class="rounded-lg bg-[#f8fbfc] p-4">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pembuat tiket</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->creator?->name ?? 'Belum tercatat' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">{{ $ticket->is_self_created ? 'Dibuat mandiri oleh Pemohon' : 'Dicatat atas nama pegawai oleh Agen Tier 1' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ui-panel" aria-labelledby="service-fields-heading">
                <div class="ui-panel-header">
                    <h2 id="service-fields-heading" class="ui-section-title">Informasi layanan</h2>
                    <p class="ui-section-description">Nilai field disimpan bersama label dan versi definisi saat tiket dibuat.</p>
                </div>
                @if ($ticket->fieldValues->isEmpty())
                    <p class="p-5 text-sm text-[#78909a] sm:p-6">Tidak ada field tambahan pada layanan ini.</p>
                @else
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
                @endif
            </section>

            <section class="ui-panel" aria-labelledby="timeline-heading">
                <div class="ui-panel-header">
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Jejak pekerjaan</p>
                    <h2 id="timeline-heading" class="mt-2 ui-section-title">Histori tiket</h2>
                    <p class="ui-section-description">Perubahan status, penugasan, kategori, dan prioritas disusun berdasarkan waktu kejadian.</p>
                </div>
                @if ($timeline === [])
                    <p class="p-5 text-sm leading-6 text-[#78909a] sm:p-6">Belum ada histori operasional pada tiket ini.</p>
                @else
                    <ol class="space-y-0 p-5 sm:p-6">
                        @foreach ($timeline as $entry)
                            <li class="relative flex gap-4 pb-6 last:pb-0">
                                @if (! $loop->last)
                                    <span class="absolute left-[0.45rem] top-5 h-full w-px bg-[#dfe8ec]" aria-hidden="true"></span>
                                @endif
                                <span class="relative mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $entry['kind'] === 'status' ? 'bg-[#75d5f3]' : ($entry['kind'] === 'priority' ? 'bg-[#e4a72c]' : 'bg-[#2bb8aa]') }} ring-4 ring-white" aria-hidden="true"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <p class="text-sm font-extrabold text-[#35505b]">{{ $entry['title'] }}</p>
                                        <time class="text-xs text-[#78909a]" datetime="{{ $entry['occurred_at']?->toIso8601String() }}">{{ $entry['occurred_at']?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-[#526f79]">{{ $entry['description'] }}</p>
                                    @if (filled($entry['actor']))
                                        <p class="mt-1 text-xs text-[#78909a]">Oleh {{ $entry['actor'] }}</p>
                                    @endif
                                    @if (filled($entry['reason']))
                                        <p class="mt-2 rounded-lg bg-[#f8fbfc] px-3 py-2 text-xs leading-5 text-[#526f79]">Alasan: {{ $entry['reason'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="ui-panel ui-panel--accent p-5 sm:p-6" aria-labelledby="next-step-heading">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Status saat ini</p>
                <h2 id="next-step-heading" class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">{{ $nextStepTitle }}</h2>
                <p class="mt-2 text-sm leading-6 text-[#52747b]">{{ $nextStepDescription }}</p>
                <div class="mt-4"><x-status-badge :status="$ticket->status" /></div>
            </section>

            @if ($canStartThirdParty || $canResumeThirdParty)
                <section class="ui-panel border-l-4 border-l-[#2bb8aa]" aria-labelledby="third-party-heading">
                    <div class="ui-panel-header">
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#2bb8aa] !shadow-[0_0_0_4px_#d7f7f1]" aria-hidden="true"></span>Ketergantungan eksternal</p>
                        <h2 id="third-party-heading" class="mt-2 ui-section-title">{{ $canResumeThirdParty ? 'Menunggu pihak ketiga' : 'Tunggu pihak ketiga' }}</h2>
                        <p class="ui-section-description">Simpan nama pihak yang ditunggu dan tanggal follow-up tanpa menghapus jejak status sebelumnya.</p>
                    </div>
                    @if ($canResumeThirdParty && $activeWait)
                        <div class="space-y-3 px-5 pb-1 sm:px-6">
                            <dl class="grid gap-3 rounded-xl bg-[#f4fbfa] p-4 text-sm">
                                <div><dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Pihak ketiga</dt><dd class="mt-1 font-extrabold text-[#35505b]">{{ $activeWait->third_party_name }}</dd></div>
                                <div><dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Mulai menunggu</dt><dd class="mt-1 font-bold text-[#526f79]">{{ $activeWait->started_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}@if ($activeWait->follow_up_date) · Follow-up {{ $activeWait->follow_up_date->translatedFormat('d M Y') }}@endif</dd></div>
                            </dl>
                            <form method="POST" action="{{ route('tickets.resume-third-party', $ticket) }}" class="space-y-3" data-ticket-communication-form>
                                @csrf
                                <div>
                                    <label for="third-party-resume-reason" class="ui-field-label">Catatan penyelesaian</label>
                                    <textarea id="third-party-resume-reason" name="reason" rows="3" class="ui-textarea mt-2" placeholder="Opsional: tulis hasil follow-up pihak ketiga.">{{ old('reason') }}</textarea>
                                </div>
                                <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-communication-submit>Lanjutkan pengerjaan</button>
                            </form>
                        </div>
                    @elseif ($canStartThirdParty)
                        <form method="POST" action="{{ route('tickets.wait-third-party', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                            @csrf
                            <div>
                                <label for="third-party-name" class="ui-field-label">Nama pihak ketiga <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <input id="third-party-name" name="third_party_name" value="{{ old('third_party_name') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Vendor jaringan">
                            </div>
                            <div>
                                <label for="third-party-follow-up" class="ui-field-label">Tanggal follow-up</label>
                                <input id="third-party-follow-up" type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="ui-input mt-2">
                            </div>
                            <div>
                                <label for="third-party-note" class="ui-field-label">Catatan</label>
                                <textarea id="third-party-note" name="note" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Opsional: detail permintaan atau nomor referensi.">{{ old('note') }}</textarea>
                            </div>
                            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-communication-submit>Tandai menunggu pihak ketiga</button>
                        </form>
                    @endif
                </section>
            @endif

            @if ($canTriage)
                <section class="ui-panel overflow-hidden" aria-labelledby="triage-heading">
                    <div class="ui-panel-header">
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff0b9]" aria-hidden="true"></span>Keputusan operasional</p>
                        <h2 id="triage-heading" class="mt-2 ui-section-title">Triase tiket</h2>
                        <p class="ui-section-description">Pilih satu hasil. Perubahan kategori atau prioritas membutuhkan alasan.</p>
                    </div>
                    <form method="POST" action="{{ route('tickets.triage', $ticket) }}" class="space-y-5 p-5 sm:p-6" data-ticket-triage-form>
                        @csrf
                        <fieldset>
                            <legend class="ui-field-label">Hasil triase <span class="text-rose-600" aria-hidden="true">*</span></legend>
                            <div class="mt-2 grid gap-2">
                                @foreach ([['self', 'Kerjakan sendiri', 'Tetapkan tiket kepada Anda sebagai Agen Tier 1.'], ['tier_2', 'Tugaskan Tier 2', 'Pilih teknisi Tier 2 untuk melanjutkan pekerjaan.'], ['reject', 'Tolak', 'Akhiri tiket dengan alasan yang terlihat Pemohon.']] as [$value, $label, $description])
                                    <label class="flex cursor-pointer gap-3 rounded-xl border border-[#dfe8ec] bg-[#fbfdfd] p-3 transition hover:border-[#8bd7ee] has-[:checked]:border-[#75d5f3] has-[:checked]:bg-[#f1fbfe]">
                                        <input type="radio" name="outcome" value="{{ $value }}" class="mt-1 h-4 w-4 border-[#a9bbc2] text-[#147a79] focus:ring-[#2bb8aa]" @checked($currentOutcome === $value) data-ticket-triage-outcome>
                                        <span><span class="block text-sm font-extrabold text-[#35505b]">{{ $label }}</span><span class="mt-1 block text-xs leading-5 text-[#78909a]">{{ $description }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error('outcome')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </fieldset>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="problem-category" class="ui-field-label">Kategori masalah</label>
                                <select id="problem-category" name="problem_category_id" class="ui-select mt-2" data-ticket-triage-category aria-describedby="problem-category-help">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($triageCategories as $category)
                                        <option value="{{ $category->id }}" @selected((string) $currentCategory === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <p id="problem-category-help" class="ui-field-help">Wajib untuk Kerjakan sendiri atau Tugaskan Tier 2.</p>
                                @error('problem_category_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="triage-priority" class="ui-field-label">Prioritas <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <select id="triage-priority" name="priority" required class="ui-select mt-2">
                                    @foreach ($priorityOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($currentPriority === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('priority')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="category-reason" class="ui-field-label">Alasan perubahan kategori</label>
                                <textarea id="category-reason" name="category_reason" rows="3" class="ui-textarea mt-2" placeholder="Isi jika kategori berubah.">{{ old('category_reason') }}</textarea>
                                @error('category_reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="priority-reason" class="ui-field-label">Alasan perubahan prioritas</label>
                                <textarea id="priority-reason" name="priority_reason" rows="3" class="ui-textarea mt-2" placeholder="Isi jika prioritas berubah.">{{ old('priority_reason') }}</textarea>
                                @error('priority_reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
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
                                <p id="tier-two-help" class="ui-field-help">Saran di bawah dihitung dari skill kategori. Pilihan manual tetap diperbolehkan.</p>
                                @error('assigned_to_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div aria-live="polite">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#607681]">Saran teknisi</p>
                                    <span class="text-[0.68rem] font-bold text-[#78909a]">Berdasarkan skill</span>
                                </div>
                                <ul class="mt-2 space-y-2" data-ticket-suggestion-list>
                                    @foreach (($suggestionsByCategory[(string) $currentCategory] ?? []) as $suggestion)
                                        <li class="rounded-lg border border-[#dcebef] bg-white px-3 py-2">
                                            <p class="text-xs font-extrabold text-[#35505b]">{{ $suggestion['user_name'] }} <span class="ml-1 rounded-full bg-[#e8faf4] px-1.5 py-0.5 text-[0.62rem] text-[#087f5b]">{{ $suggestion['match_count'] }} skill</span></p>
                                            <p class="mt-1 text-[0.68rem] text-[#78909a]">{{ implode(', ', $suggestion['matching_skill_names']) }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs leading-5 text-[#78909a] {{ isset($suggestionsByCategory[(string) $currentCategory]) && count($suggestionsByCategory[(string) $currentCategory]) > 0 ? 'hidden' : '' }}" data-ticket-suggestion-empty>Pilih kategori untuk melihat saran teknisi.</p>
                            </div>
                        </div>

                        <div data-ticket-triage-panel="reject" class="rounded-xl border border-rose-200 bg-[#fff7f8] p-4" @if ($currentOutcome !== 'reject') hidden @endif>
                            <label for="rejection-reason" class="ui-field-label !text-[#9f1239]">Alasan penolakan</label>
                            <textarea id="rejection-reason" name="rejection_reason" rows="4" class="ui-textarea mt-2 !border-rose-200 !bg-white" placeholder="Jelaskan alasan yang perlu diketahui Pemohon.">{{ old('rejection_reason') }}</textarea>
                            <p class="ui-field-help !text-[#9f1239]">Alasan ini akan terlihat pada detail tiket Pemohon.</p>
                            @error('rejection_reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-triage-submit>
                            <span data-ticket-triage-submit-label>Simpan triase</span>
                            <span class="hidden" data-ticket-triage-submit-loading aria-hidden="true">Menyimpan…</span>
                        </button>
                    </form>
                    <script type="application/json" data-ticket-suggestions-map>@json($suggestionsByCategory)</script>
                </section>
            @endif

            @if ($canAssignTierTwo)
                <section class="ui-panel" aria-labelledby="assign-tier-two-heading">
                    <div class="ui-panel-header">
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Alih penanganan</p>
                        <h2 id="assign-tier-two-heading" class="mt-2 ui-section-title">Tugaskan ke Tier 2</h2>
                        <p class="ui-section-description">Status tetap Dikerjakan dan histori perpindahan akan disimpan.</p>
                    </div>
                    <form method="POST" action="{{ route('tickets.assign-tier-2', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-assignment-form>
                        @csrf
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
            @endif

            @if ($canReturnToTierOne)
                <section class="ui-panel border-l-4 border-l-[#e4a72c]" aria-labelledby="return-tier-one-heading">
                    <div class="ui-panel-header">
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Perlu dikembalikan</p>
                        <h2 id="return-tier-one-heading" class="mt-2 ui-section-title">Kembalikan ke Tier 1</h2>
                        <p class="ui-section-description">Tujuan: {{ $ticket->lastTriagedBy?->name ?? 'Agen Tier 1 terakhir' }}.</p>
                    </div>
                    <form method="POST" action="{{ route('tickets.return-to-tier-1', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-return-form>
                        @csrf
                        <div>
                            <label for="return-tier-one-reason" class="ui-field-label">Alasan pengembalian <span class="text-rose-600" aria-hidden="true">*</span></label>
                            <textarea id="return-tier-one-reason" name="reason" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau tindakan yang masih diperlukan.">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-return-submit>Kembalikan ke Tier 1</button>
                    </form>
                </section>
            @endif

            <section class="ui-panel" aria-labelledby="attachments-heading">
                <div class="ui-panel-header">
                    <h2 id="attachments-heading" class="ui-section-title">Lampiran</h2>
                    <p class="ui-section-description">Berkas yang dapat Anda akses pada tiket ini.</p>
                </div>
                @if ($ticket->attachments->isEmpty())
                    <p class="p-5 text-sm leading-6 text-[#78909a] sm:p-6">Tidak ada lampiran pada tiket ini.</p>
                @else
                    <ul class="divide-y divide-[#edf2f4]">
                        @foreach ($ticket->attachments as $attachment)
                            <li class="flex items-start justify-between gap-3 p-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-[#35505b]" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">{{ $attachment->type_label_snapshot }} · {{ number_format($attachment->size_bytes / 1024, 0, ',', '.') }} KB</p>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="ui-action-link shrink-0">Unduh<span class="sr-only"> {{ $attachment->original_name }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
@endsection
