@extends('layouts.app')

@section('title', 'Detail tiket '.$ticket->label)
@section('header_title', 'Detail tiket')

@php
    $needsReply = $actions['reply'];
    $needsConfirmation = $actions['confirm'] || $actions['notSatisfied'];
    $canReopen = $actions['reopen'];
    $isClosed = $ticket->status?->isClosed() ?? false;
    $facts = collect($ticket->facts)->keyBy('label');
    $submittedFields = collect($ticket->submittedFields);
    $impactField = $submittedFields->first(function (array $field): bool {
        $label = mb_strtolower($field['label']);

        return str_contains($label, 'dampak') || str_contains($label, 'cakupan');
    });
    $submittedAt = $facts->get('Diajukan')['value'] ?? 'Belum tersedia';
    $serviceLabel = $facts->get('Layanan')['value'] ?? 'Layanan tidak tercatat';
    $assigneeName = $facts->get('Ditangani oleh')['value'] ?? 'Menunggu petugas';
    $hasAssignee = $assigneeName !== 'Menunggu petugas';
    $assigneeInitials = strtoupper(collect(preg_split('/\s+/', trim($assigneeName)))->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode(''));
    $currentUserName = auth()->user()->name;
    $currentUserInitials = strtoupper(collect(preg_split('/\s+/', trim($currentUserName)))->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode(''));
    $activity = collect($ticket->timeline)
        ->sortBy(fn (array $event): int => $event['occurredAt']?->getTimestamp() ?? 0)
        ->values();
@endphp

@section('content')
    <div class="ticket-reference-content">
        <nav class="mb-5" aria-label="Navigasi detail tiket">
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-3 py-1.5 text-xs font-semibold text-[color:var(--tm-text-secondary)] transition-colors hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
                </svg>
                Kembali ke Tiket Saya
            </a>
        </nav>

        <header class="ticket-reference-page-header">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="ticket-reference-number tabular-nums">{{ $ticket->label }}</h1>
                    <x-status-badge :status="$ticket->status" />
                    <x-priority-badge :priority="$ticket->priority" />
                </div>
                <p class="ticket-reference-subtitle inline-flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6v4.25l2.5 1.5" /></svg>
                    Dibuat pada {{ $submittedAt }} WIB
                </p>
            </div>

            @if ($actions['cancel'] || $actions['confirm'] || $actions['notSatisfied'] || $canReopen)
                <div class="ticket-reference-page-actions" role="group" aria-label="Tindakan tiket">
                    @if ($actions['cancel'])
                        <form method="POST" action="{{ route('tickets.cancel', $ticket->id) }}" class="ticket-reference-action-form" data-swal-confirm="Batalkan tiket ini? Tiket hanya dapat dibatalkan selama statusnya masih Baru.">
                            @csrf
                            <button type="submit" class="ticket-reference-action-button ticket-reference-action-button--danger" aria-label="Batalkan tiket" title="Batalkan tiket">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path stroke-linecap="round" d="m9 9 6 6m0-6-6 6" />
                                </svg>
                                <span>Batalkan</span>
                            </button>
                        </form>
                    @endif

                    @if ($actions['confirm'])
                        <a href="#ticket-action-heading" class="ticket-reference-action-button ticket-reference-action-button--success" aria-label="Konfirmasi selesai" title="Konfirmasi selesai">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" />
                            </svg>
                            <span>Konfirmasi</span>
                        </a>
                    @endif

                    @if ($actions['notSatisfied'])
                        <a href="#ticket-action-heading" class="ticket-reference-action-button ticket-reference-action-button--warning" aria-label="Minta perbaikan" title="Minta perbaikan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v7a2.5 2.5 0 0 1-2.5 2.5h-6l-4.5 3v-3h-.5A2.5 2.5 0 0 1 4 14.5v-7Z" />
                                <path stroke-linecap="round" d="M8 10h8M8 13h5" />
                            </svg>
                            <span>Perbaiki</span>
                        </a>
                    @endif

                    @if ($canReopen)
                        <a href="#ticket-reopen-heading" class="ticket-reference-action-button ticket-reference-action-button--reopen" aria-label="Buka kembali tiket" title="Buka kembali tiket">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v5h5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12A8 8 0 1 0 7 6.4L4 9" />
                            </svg>
                            <span>Buka</span>
                        </a>
                    @endif
                </div>
            @endif

        </header>

        @if ($ticket->hasNotice())
            <div class="ticket-reference-alert mt-6" role="status">
                <p class="ticket-reference-alert-title">{{ $ticket->noticeTitle }}</p>
                <p class="ticket-reference-alert-body">{{ $ticket->noticeBody }}</p>
            </div>
        @endif

        @if ($needsConfirmation)
            <section class="ticket-reference-card mt-6" aria-labelledby="ticket-action-heading">
                <div class="ticket-reference-card-header">
                    <p class="ticket-reference-eyebrow">Perlu tindakan Anda</p>
                    <h2 id="ticket-action-heading" class="ticket-reference-section-title mt-1">Apakah hasilnya sudah sesuai?</h2>
                    <p class="ticket-reference-section-description">Tinjau hasil pekerjaan di bawah, lalu pilih tindakan yang sesuai.</p>
                </div>

                <div class="ticket-reference-card-body space-y-5">
                    @if ($ticket->hasSolution())
                        <div class="ticket-reference-solution">
                            <p class="ticket-reference-info-label">Hasil pekerjaan</p>
                            <p class="ticket-reference-body-copy">{{ $ticket->solution }}</p>
                        </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($actions['confirm'])
                            <form method="POST" action="{{ route('tickets.confirm', $ticket->id) }}" class="ticket-reference-action ticket-reference-action--success" data-swal-confirm="Konfirmasi bahwa hasil pekerjaan sudah sesuai? Tiket akan ditutup.">
                                @csrf
                                <div>
                                    <h3 class="ticket-reference-action-title">Ya, sudah sesuai</h3>
                                    <p class="ticket-reference-action-copy">Tiket akan ditutup dan tercatat selesai.</p>
                                </div>
                                <button type="submit" class="ui-btn ui-btn-primary w-full justify-center">Konfirmasi selesai</button>
                            </form>
                        @endif

                        @if ($actions['notSatisfied'])
                            <form method="POST" action="{{ route('tickets.not-satisfied', $ticket->id) }}" class="ticket-reference-action ticket-reference-action--warning" data-ticket-resolution-form>
                                @csrf
                                <div>
                                    <h3 class="ticket-reference-action-title">Belum sesuai</h3>
                                    <label for="not-satisfied-reason" class="ticket-reference-action-copy">Jelaskan bagian yang masih bermasalah agar petugas dapat menindaklanjuti.</label>
                                    <textarea id="not-satisfied-reason" name="reason" rows="3" required class="ticket-reference-textarea mt-2" placeholder="Contoh: masalah yang sama masih muncul.">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-[color:var(--tm-danger-600)]">
                                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <button type="submit" class="ui-btn ui-btn-warning w-full justify-center" data-ticket-resolution-submit>Minta perbaikan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </section>
        @elseif ($canReopen)
            <section class="ticket-reference-card mt-6" aria-labelledby="ticket-reopen-heading">
                <div class="ticket-reference-card-header">
                    <p class="ticket-reference-eyebrow">Tindakan tiket</p>
                    <h2 id="ticket-reopen-heading" class="ticket-reference-section-title mt-1">Masalah muncul lagi?</h2>
                    <p class="ticket-reference-section-description">Buka kembali tiket ini agar Tim TI melanjutkan penanganan tanpa membuat tiket baru.</p>
                </div>

                <form method="POST" action="{{ route('tickets.reopen', $ticket->id) }}" class="ticket-reference-card-body space-y-4" data-ticket-resolution-form>
                    @csrf
                    <div>
                        <label for="reopen-reason" class="ticket-reference-field-label">Alasan membuka kembali</label>
                        <textarea id="reopen-reason" name="reason" rows="3" class="ticket-reference-textarea mt-1.5" placeholder="Contoh: masalah yang sama muncul kembali.">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-[color:var(--tm-danger-600)]">
                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="ui-btn ui-btn-secondary" data-ticket-resolution-submit>Buka kembali tiket</button>
                    </div>
                </form>
            </section>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.75fr)_minmax(18rem,0.85fr)] lg:items-start">
            <div class="min-w-0 space-y-6">
                <section class="ticket-reference-card" aria-labelledby="ticket-description-heading">
                    <div class="ticket-reference-card-body">
                        <h2 id="ticket-description-heading" class="ticket-reference-subject">{{ $ticket->subject }}</h2>
                        <div class="ticket-reference-description mt-5">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>

                        @if ($ticket->hasSubmittedFields())
                            <div class="ticket-reference-subsection mt-6">
                                <h3 class="ticket-reference-info-label">Informasi yang Anda kirim</h3>
                                <dl class="mt-3 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                                    @foreach ($submittedFields as $field)
                                        <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-4 py-3">
                                            <dt class="ticket-reference-field-label">{{ $field['label'] }}</dt>
                                            <dd class="ticket-reference-field-value">{{ $field['value'] }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif

                        @if ($ticket->hasSolution() && ! $needsConfirmation)
                            <div class="ticket-reference-subsection ticket-reference-subsection--solution mt-6">
                                <h3 class="ticket-reference-info-label">Hasil pekerjaan</h3>
                                <p class="ticket-reference-body-copy mt-2">{{ $ticket->solution }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                @if ($ticket->hasMessages())
                    <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-conversation-heading" @if (! $isClosed) open @endif>
                        <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                            <span class="min-w-0">
                                <span id="ticket-conversation-heading" class="ticket-reference-section-title block">Percakapan</span>
                                <span class="ticket-reference-section-description block">Pesan antara Anda dan Tim TI.</span>
                            </span>
                        </summary>
                        <div class="ticket-reference-card-body space-y-4">
                            @foreach ($ticket->messages as $message)
                                <x-tickets.message :message="$message" variant="reference" />
                            @endforeach
                        </div>
                    </details>
                @endif

                <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-reply-heading" @if (! $isClosed) open @endif>
                    <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                        <span id="ticket-reply-heading" class="ticket-reference-section-title flex min-w-0 items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v11H8l-4 3v-14Z" />
                                <path stroke-linecap="round" d="M8 9h8M8 12h5" />
                            </svg>
                            <span>Tambahkan Balasan</span>
                        </span>
                    </summary>

                    <div class="ticket-reference-card-body">
                        @if ($needsReply)
                            <form method="POST" action="{{ route('tickets.requester-reply', $ticket->id) }}" enctype="multipart/form-data" class="ticket-reference-reply mt-5" data-ticket-communication-form>
                                @csrf
                                <div class="ticket-reference-avatar" aria-hidden="true">{{ $currentUserInitials }}</div>
                                <div class="min-w-0 flex-1">
                                    <label for="requester-reply-body" class="ticket-reference-field-label">Balasan ke Tim TI</label>
                                    <textarea id="requester-reply-body" name="body" rows="5" required class="ticket-reference-textarea mt-1.5" placeholder="Ketik pesan atau informasi tambahan di sini...">{{ old('body') }}</textarea>
                                    @error('body')
                                        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-[color:var(--tm-danger-600)]">
                                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror

                                    @if ($replyAttachmentPolicies->isNotEmpty())
                                        <div class="mt-3 space-y-2">
                                            @foreach ($replyAttachmentPolicies as $policy)
                                                <input id="reply-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ticket-reference-file-input" accept="{{ collect($policy->allowed_extensions)->map(fn ($extension) => '.'.$extension)->implode(',') }}" aria-describedby="reply-attachment-help-{{ $policy->id }}">
                                                <div>
                                                    <label for="reply-attachment-{{ $policy->id }}" class="ticket-reference-attach-label">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                                        </svg>
                                                        Lampirkan File
                                                    </label>
                                                    <p id="reply-attachment-help-{{ $policy->id }}" class="ticket-reference-file-help">{{ $policy->label }} · Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas.</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                                        <button type="submit" class="ui-btn ui-btn-primary" data-ticket-communication-submit>Kirim Pesan</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="ticket-reference-reply mt-5">
                                <div class="ticket-reference-avatar" aria-hidden="true">{{ $currentUserInitials }}</div>
                                <div class="min-w-0 flex-1">
                                    <label for="requester-reply-disabled" class="ticket-reference-field-label">Balasan ke Tim TI</label>
                                    <textarea id="requester-reply-disabled" rows="5" disabled class="ticket-reference-textarea mt-1.5 disabled:cursor-not-allowed disabled:bg-[color:var(--tm-n-50)]" placeholder="Ketik pesan atau informasi tambahan di sini..."></textarea>
                                    <p class="ticket-reference-file-help mt-2">Balasan tidak tersedia karena tiket sudah berstatus akhir.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
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

                        @if ($ticket->sla)
                            <div class="ticket-reference-info-item">
                                <dt class="ticket-reference-info-label">Estimasi Selesai</dt>
                                <dd class="ticket-reference-info-value tabular-nums">{{ $ticket->sla['target'] }}</dd>
                            </div>
                        @endif

                        @if ($facts->has('Lokasi'))
                            <div class="ticket-reference-info-item">
                                <dt class="ticket-reference-info-label">Lokasi</dt>
                                <dd class="ticket-reference-info-value">{{ $facts->get('Lokasi')['value'] }}</dd>
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
                        <span id="ticket-attachments-heading" class="ticket-reference-card-heading">Lampiran</span>
                    </summary>
                    <div class="ticket-reference-card-body">
                        @if ($ticket->hasAttachments())
                            <ul class="space-y-2">
                                @foreach ($ticket->attachments as $attachment)
                                    <li>
                                        <a href="{{ $attachment['url'] }}" class="ticket-reference-attachment-link rounded-[var(--tm-r-md)] transition-colors hover:bg-[color:var(--tm-brand-50)]" download>
                                            <span class="ticket-reference-attachment-icon" aria-hidden="true">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <rect x="3.5" y="4" width="17" height="16" rx="1.5" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6.5 16 3.2-3.4 2.5 2.6 2-2.1 3.3 2.9M8.5 9.3h.01" />
                                                </svg>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-[color:var(--tm-text)]">{{ $attachment['name'] }}</span>
                                                <span class="mt-0.5 block text-xs text-[color:var(--tm-text-muted)]">{{ $attachment['meta'] }}</span>
                                            </span>
                                            <span class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-[color:var(--tm-brand-700)]">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 4v8" /><path d="m6.5 8.75 3.5 3.5 3.5-3.5" /><path d="M4.5 15.25h11" /></svg>
                                                Unduh
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="ticket-reference-empty">Belum ada lampiran.</p>
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
            </aside>
        </div>
    </div>
@endsection
