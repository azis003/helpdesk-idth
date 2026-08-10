@extends('layouts.app')

@section('title', 'Detail tiket '.$ticket->label)
@section('header_kicker', 'Tiket saya')
@section('header_title', 'Detail tiket')

@php
    $needsReply = $actions['reply'];
    $needsConfirmation = $actions['confirm'] || $actions['notSatisfied'];
    $canReopen = $actions['reopen'];
    $showSolutionPanel = $ticket->hasSolution() && ! $needsConfirmation;
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <a href="{{ route('tickets.index') }}" class="ui-action-link">&larr; Kembali ke tiket saya</a>
            <h1 class="ui-page-title">{{ $ticket->label }}</h1>
            <p class="ui-page-description">{{ $ticket->subject }}</p>
        </div>

        @if ($actions['cancel'])
            <form method="POST" action="{{ route('tickets.cancel', $ticket->id) }}" data-swal-confirm="Batalkan tiket ini? Tiket hanya dapat dibatalkan selama statusnya masih Baru.">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger">Batalkan tiket</button>
            </form>
        @endif
    </div>

    {{-- Satu tempat untuk menjawab: sekarang tiket saya bagaimana? --}}
    <section class="ui-panel ui-panel--accent mt-8 overflow-hidden" aria-labelledby="ticket-status-heading">
        <div class="flex flex-wrap items-start justify-between gap-4 p-5 sm:p-6">
            <div class="max-w-2xl">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Status saat ini</p>
                <h2 id="ticket-status-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">{{ $ticket->statusHeadline }}</h2>
                <p class="mt-2 text-sm leading-6 text-[#52747b]">{{ $ticket->statusNarrative }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-status-badge :status="$ticket->status" />
                <x-priority-badge :priority="$ticket->priority" />
            </div>
        </div>

        @if ($ticket->hasJourney())
            <div class="border-t border-[#cdeef7] px-5 py-5 sm:px-6">
                <x-tickets.journey :steps="$ticket->journey" />
            </div>
        @endif

        @if ($ticket->sla)
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#cdeef7] bg-white/70 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-sm font-extrabold text-[#35505b]">{{ $ticket->sla['headline'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-[#78909a]">{{ $ticket->sla['detail'] }}</p>
                </div>
                <span class="ui-chip">Target {{ $ticket->sla['target'] }}</span>
            </div>
        @endif
    </section>

    @if ($ticket->hasNotice())
        <div class="mt-5 rounded-2xl border border-[#fecdd3] bg-[#fff5f6] p-5 sm:p-6" role="status">
            <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#be123c]">{{ $ticket->noticeTitle }}</p>
            <p class="mt-2 whitespace-pre-line text-sm leading-7 text-[#7f1d1d]">{{ $ticket->noticeBody }}</p>
        </div>
    @endif

    {{-- Kartu tindakan hanya muncul saat memang ada yang perlu Anda lakukan. --}}
    @if ($needsReply)
        <section class="ui-panel mt-5 border-l-4 border-l-[#75d5f3]" aria-labelledby="ticket-action-heading">
            <div class="ui-panel-header">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Perlu tindakan Anda</p>
                <h2 id="ticket-action-heading" class="ui-section-title mt-2">Balas permintaan informasi</h2>
                <p class="ui-section-description">Jawaban Anda langsung mengaktifkan kembali penanganan oleh petugas.</p>
            </div>

            <form method="POST" action="{{ route('tickets.requester-reply', $ticket->id) }}" enctype="multipart/form-data" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                @csrf

                <div>
                    <label for="requester-reply-body" class="ui-field-label">Balasan Anda</label>
                    <textarea id="requester-reply-body" name="body" rows="4" required class="ui-textarea mt-1.5" placeholder="Tuliskan informasi yang diminta petugas...">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1.5 text-xs font-bold text-[#be123c]">{{ $message }}</p>
                    @enderror
                </div>

                @if ($replyAttachmentPolicies->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($replyAttachmentPolicies as $policy)
                            <div>
                                <label for="reply-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                                <input id="reply-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-1.5" accept="{{ collect($policy->allowed_extensions)->map(fn ($extension) => '.'.$extension)->implode(',') }}">
                                <p class="ui-field-help">Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas.</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="ui-btn ui-btn-primary" data-ticket-communication-submit>Kirim balasan</button>
                </div>
            </form>
        </section>
    @elseif ($needsConfirmation)
        <section class="ui-panel mt-5 border-l-4 border-l-[#2bb8aa]" aria-labelledby="ticket-action-heading">
            <div class="ui-panel-header">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Perlu tindakan Anda</p>
                <h2 id="ticket-action-heading" class="ui-section-title mt-2">Apakah hasilnya sudah sesuai?</h2>
                <p class="ui-section-description">Baca hasil pekerjaan di bawah, lalu pilih salah satu.</p>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                @if ($ticket->hasSolution())
                    <div class="rounded-2xl border border-[#cfe0e5] bg-[#f8fbfc] p-4 sm:p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Hasil pekerjaan</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $ticket->solution }}</p>
                    </div>
                @endif

                <div class="grid gap-4 lg:grid-cols-2">
                    @if ($actions['confirm'])
                        <form method="POST" action="{{ route('tickets.confirm', $ticket->id) }}" class="flex h-full flex-col justify-between gap-4 rounded-2xl border border-[#c4ebe5] bg-[#effcf9] p-4 sm:p-5" data-swal-confirm="Konfirmasi bahwa hasil pekerjaan sudah sesuai? Tiket akan ditutup.">
                            @csrf
                            <div>
                                <p class="text-sm font-extrabold text-[#0f6862]">Ya, sudah sesuai</p>
                                <p class="mt-1 text-xs leading-5 text-[#4b7f79]">Tiket akan ditutup dan tercatat selesai.</p>
                            </div>
                            <button type="submit" class="ui-btn ui-btn-primary w-full">Konfirmasi selesai</button>
                        </form>
                    @endif

                    @if ($actions['notSatisfied'])
                        <form method="POST" action="{{ route('tickets.not-satisfied', $ticket->id) }}" class="flex h-full flex-col justify-between gap-3 rounded-2xl border border-[#f0d28c] bg-[#fff9e9] p-4 sm:p-5" data-ticket-resolution-form>
                            @csrf
                            <div>
                                <p class="text-sm font-extrabold text-[#8b6100]">Belum sesuai</p>
                                <label for="not-satisfied-reason" class="mt-1 block text-xs leading-5 text-[#9a6700]">Jelaskan bagian yang masih bermasalah agar petugas bisa menindaklanjuti.</label>
                                <textarea id="not-satisfied-reason" name="reason" rows="3" required class="ui-textarea mt-2" placeholder="Contoh: koneksi masih terputus di ruang rapat.">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="mt-1.5 text-xs font-bold text-[#be123c]">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-resolution-submit>Minta perbaikan</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    @elseif ($canReopen)
        <section class="ui-panel mt-5 border-l-4 border-l-[#e4a72c]" aria-labelledby="ticket-action-heading">
            <div class="ui-panel-header">
                <h2 id="ticket-action-heading" class="ui-section-title">Masalah muncul lagi?</h2>
                <p class="ui-section-description">Buka kembali tiket ini agar petugas melanjutkan penanganan tanpa Anda membuat tiket baru.</p>
            </div>

            <form method="POST" action="{{ route('tickets.reopen', $ticket->id) }}" class="space-y-4 p-5 sm:p-6" data-ticket-resolution-form>
                @csrf

                <div>
                    <label for="reopen-reason" class="ui-field-label">Alasan membuka kembali</label>
                    <textarea id="reopen-reason" name="reason" rows="3" class="ui-textarea mt-1.5" placeholder="Contoh: masalah yang sama muncul kembali sejak pagi ini.">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1.5 text-xs font-bold text-[#be123c]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="ui-btn ui-btn-secondary" data-ticket-resolution-submit>Buka kembali tiket</button>
                </div>
            </form>
        </section>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        <div class="space-y-5">
            <section class="ui-panel" aria-labelledby="ticket-request-heading">
                <div class="ui-panel-header">
                    <h2 id="ticket-request-heading" class="ui-section-title">Permintaan Anda</h2>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $ticket->description }}</p>
                </div>
            </section>

            @if ($ticket->hasSubmittedFields())
                <section class="ui-panel" aria-labelledby="ticket-fields-heading">
                    <div class="ui-panel-header">
                        <h2 id="ticket-fields-heading" class="ui-section-title">Informasi yang Anda kirim</h2>
                    </div>
                    <dl class="grid gap-x-6 gap-y-4 p-5 sm:grid-cols-2 sm:p-6">
                        @foreach ($ticket->submittedFields as $field)
                            <div>
                                <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">{{ $field['label'] }}</dt>
                                <dd class="mt-1 text-sm leading-6 text-[#35505b]">{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            @if ($showSolutionPanel)
                <section class="ui-panel" aria-labelledby="ticket-solution-heading">
                    <div class="ui-panel-header">
                        <h2 id="ticket-solution-heading" class="ui-section-title">Hasil pekerjaan</h2>
                    </div>
                    <div class="p-5 sm:p-6">
                        <p class="whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $ticket->solution }}</p>
                    </div>
                </section>
            @endif

            <section class="ui-panel" aria-labelledby="ticket-conversation-heading">
                <div class="ui-panel-header">
                    <h2 id="ticket-conversation-heading" class="ui-section-title">Percakapan</h2>
                    <p class="ui-section-description">Pesan antara Anda dan Tim TI.</p>
                </div>

                <div class="p-5 sm:p-6">
                    @if ($ticket->hasMessages())
                        <div class="space-y-4">
                            @foreach ($ticket->messages as $message)
                                <x-tickets.message :message="$message" />
                            @endforeach
                        </div>
                    @else
                        <div class="ui-empty">Belum ada percakapan pada tiket ini.</div>
                    @endif
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="ui-panel" aria-labelledby="ticket-summary-heading">
                <div class="ui-panel-header">
                    <h2 id="ticket-summary-heading" class="ui-section-title">Ringkasan</h2>
                </div>
                <x-tickets.fact-list :facts="$ticket->facts" />
            </section>

            <section class="ui-panel" aria-labelledby="ticket-attachments-heading">
                <div class="ui-panel-header">
                    <h2 id="ticket-attachments-heading" class="ui-section-title">Lampiran</h2>
                </div>
                <div class="p-5 sm:p-6">
                    @if ($ticket->hasAttachments())
                        <ul class="space-y-2">
                            @foreach ($ticket->attachments as $attachment)
                                <li>
                                    <a href="{{ $attachment['url'] }}" class="flex items-center justify-between gap-3 rounded-xl border border-[#dce7eb] bg-[#f8fbfc] px-3.5 py-3 hover:border-[#98dff3]">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-bold text-[#35505b]">{{ $attachment['name'] }}</span>
                                            <span class="mt-0.5 block text-xs text-[#78909a]">{{ $attachment['meta'] }}</span>
                                        </span>
                                        <span class="shrink-0 text-xs font-extrabold text-[#0f766e]">Unduh</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="ui-empty">Belum ada lampiran.</div>
                    @endif
                </div>
            </section>

            <section class="ui-panel" aria-labelledby="ticket-history-heading">
                <div class="ui-panel-header">
                    <h2 id="ticket-history-heading" class="ui-section-title">Riwayat</h2>
                </div>
                @if ($ticket->hasTimeline())
                    <x-tickets.timeline :events="$ticket->timeline" />
                @else
                    <div class="p-5 sm:p-6">
                        <div class="ui-empty">Belum ada riwayat.</div>
                    </div>
                @endif
            </section>
        </aside>
    </div>
@endsection
