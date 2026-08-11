@extends('layouts.app')

@section('title', $ticket->ticketLabel().' — '.$branding['application_name'])
@section('header_kicker', 'Pemantauan tim')
@section('header_title', 'Detail tiket')

@php
    $ticketLabel = $ticket->ticketLabel();
    $submittedAt = $ticket->submittedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Belum tersedia';
    $updatedAt = $ticket->updatedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Belum tersedia';
    $assigneeName = $ticket->assigneeLabel();
    $hasAssignee = filled($ticket->assigneeName);
    $assigneeInitials = strtoupper(collect(preg_split('/\s+/', trim($assigneeName)))->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode(''));
    $sla = $ticket->sla;
    $formatMinutes = static function ($minutes): string {
        if ($minutes === null) {
            return 'Tidak tersedia';
        }

        $minutes = max(0, (int) $minutes);
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0 ? $hours.' jam '.$remaining.' menit' : $remaining.' menit';
    };
    $slaState = ! $sla || ! ($sla['uses_sla'] ?? false)
        ? 'Tidak menggunakan SLA'
        : (($sla['paused'] ?? false)
            ? 'Dijeda karena status menunggu'
            : (($sla['overdue'] ?? false) ? 'Melewati target SLA' : (($sla['near_limit'] ?? false) ? 'Mendekati batas SLA' : 'SLA berjalan')));
    $activity = $ticket->publicComments
        ->map(fn ($comment): array => [
            'title' => 'Balasan publik',
            'description' => $comment->body.($comment->authorName ? ' Oleh '.$comment->authorName.'.' : ''),
            'occurredAt' => $comment->createdAt,
            'tone' => 'progress',
        ])
        ->values();
@endphp

@section('content')
    <div class="ticket-reference-content ticket-reference-content--operational">
        <x-tickets.detail-header
            :back-url="route('tickets.index')"
            back-label="Kembali ke Tiket Tim"
            :ticket-label="$ticketLabel"
            :status="$ticket->status"
            :priority="$ticket->priority"
            :submitted-at="$submittedAt"
        />

        <div class="mt-5 flex items-center gap-2 text-xs font-bold text-[#78909a]" role="note" aria-label="Batas akses Ketua Tim Kerja">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#fff0b9] text-[#8b6100]" aria-hidden="true">i</span>
            <span>Mode baca saja untuk pemantauan tim. Data internal dan lampiran privat tidak ditampilkan.</span>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.75fr)_minmax(18rem,0.85fr)] lg:items-start">
            <div class="min-w-0 space-y-6">
                <section class="ticket-reference-card" aria-labelledby="ticket-description-heading">
                    <div class="ticket-reference-card-body">
                        <h2 id="ticket-description-heading" class="ticket-reference-subject">{{ $ticket->subject }}</h2>
                        <p class="ticket-reference-description mt-5">Detail deskripsi dibatasi pada akses pemantauan Ketua Tim Kerja.</p>

                        @if (filled($ticket->solution))
                            <div class="ticket-reference-subsection ticket-reference-subsection--solution mt-6" role="status">
                                <h3 class="ticket-reference-info-label">Hasil pekerjaan</h3>
                                <p class="ticket-reference-body-copy mt-2 whitespace-pre-line">{{ $ticket->solution }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                <details class="ticket-reference-card ticket-reference-collapsible" aria-labelledby="ticket-conversation-heading" open>
                    <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                        <span class="min-w-0">
                            <span id="ticket-conversation-heading" class="ticket-reference-section-title block">Percakapan</span>
                            <span class="ticket-reference-section-description block">Balasan publik antara Pemohon dan Tim TI.</span>
                        </span>
                    </summary>
                    <div class="ticket-reference-card-body space-y-4">
                        @forelse ($ticket->publicComments as $comment)
                            <article class="rounded-xl border border-[#dfe8ec] bg-[#fbfdfd] p-4">
                                <header class="flex flex-wrap items-start justify-between gap-3">
                                    <p class="text-sm font-extrabold text-[#35505b]">{{ $comment->authorName ?? 'Sistem' }}</p>
                                    @if ($comment->createdAt)
                                        <time class="text-xs text-[#78909a]" datetime="{{ $comment->createdAt->toIso8601String() }}">{{ $comment->createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} WIB</time>
                                    @endif
                                </header>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $comment->body }}</p>
                            </article>
                        @empty
                            <p class="ticket-reference-empty">Belum ada balasan publik.</p>
                        @endforelse
                    </div>
                </details>

                <section class="ticket-reference-card" aria-labelledby="team-chair-sla-heading">
                    <div class="ticket-reference-card-header flex flex-wrap items-center justify-between gap-3">
                        <h2 id="team-chair-sla-heading" class="ticket-reference-card-heading">SLA tiket</h2>
                        <span class="rounded-full bg-[#f1f5f9] px-2.5 py-1 text-xs font-bold text-[#526f79]">{{ $slaState }}</span>
                    </div>
                    <dl class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
                        <div>
                            <dt class="ticket-reference-info-label">Target</dt>
                            <dd class="ticket-reference-info-value mt-1">{{ ($sla && ($sla['uses_sla'] ?? false)) ? (($sla['target_working_days'] ?? null) !== null ? $sla['target_working_days'].' hari kerja' : $formatMinutes($sla['target_working_minutes'] ?? null)) : 'Tidak menggunakan SLA' }}</dd>
                        </div>
                        <div>
                            <dt class="ticket-reference-info-label">Sisa waktu aktif</dt>
                            <dd class="ticket-reference-info-value mt-1">{{ ($sla && ($sla['uses_sla'] ?? false)) ? $formatMinutes($sla['remaining_minutes'] ?? null) : 'Tidak tersedia' }}</dd>
                        </div>
                        <div>
                            <dt class="ticket-reference-info-label">Kepatuhan</dt>
                            <dd class="ticket-reference-info-value mt-1">{{ ! $sla || ($sla['compliant'] ?? null) === null ? 'Belum diukur' : (($sla['compliant'] ?? false) ? 'Sesuai target' : 'Tidak sesuai target') }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="min-w-0 space-y-6">
                <section class="ticket-reference-card overflow-hidden" aria-labelledby="ticket-information-heading">
                    <div class="ticket-reference-card-header">
                        <h2 id="ticket-information-heading" class="ticket-reference-card-heading">Informasi Tiket</h2>
                    </div>
                    <dl class="ticket-reference-info-list">
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Kategori</dt>
                            <dd class="ticket-reference-info-value">{{ $ticket->serviceLabel() }}</dd>
                        </div>
                        @if (filled($ticket->categoryName))
                            <div class="ticket-reference-info-item">
                                <dt class="ticket-reference-info-label">Kategori Masalah</dt>
                                <dd class="ticket-reference-info-value">{{ $ticket->categoryName }}</dd>
                            </div>
                        @endif
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Pemohon</dt>
                            <dd class="ticket-reference-info-value">{{ $ticket->requesterName ?? 'Belum tercatat' }}</dd>
                        </div>
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Tim Kerja</dt>
                            <dd class="ticket-reference-info-value">{{ $ticket->teamName ?? 'Belum memiliki tim' }}</dd>
                        </div>
                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Pembaruan Terakhir</dt>
                            <dd class="ticket-reference-info-value">{{ $updatedAt }} WIB</dd>
                        </div>

                        <div class="ticket-reference-info-divider" aria-hidden="true"></div>

                        <div class="ticket-reference-info-item">
                            <dt class="ticket-reference-info-label">Ditangani Oleh</dt>
                            @if ($hasAssignee)
                                <dd class="ticket-reference-assignee">
                                    <span class="ticket-reference-assignee-avatar" aria-hidden="true">{{ $assigneeInitials }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-[#0b1c30]">{{ $assigneeName }}</span>
                                        <span class="mt-0.5 block text-xs text-[#434655]">{{ $ticket->assignedTierLabel() ?? 'Tim TI' }}</span>
                                    </span>
                                </dd>
                            @else
                                <dd class="ticket-reference-info-value">Menunggu petugas ditugaskan</dd>
                            @endif
                        </div>
                    </dl>
                </section>

                <details class="ticket-reference-card ticket-reference-collapsible overflow-hidden" aria-labelledby="ticket-attachments-heading" open>
                    <summary class="ticket-reference-card-header ticket-reference-collapsible-summary flex items-center justify-between gap-4">
                        <span id="ticket-attachments-heading" class="ticket-reference-card-heading">Lampiran</span>
                    </summary>
                    <div class="ticket-reference-card-body !p-4">
                        <p class="ticket-reference-empty">Lampiran tidak tersedia pada akses pemantauan.</p>
                    </div>
                </details>

                <section class="ticket-reference-card" aria-labelledby="ticket-activity-heading">
                    <div class="ticket-reference-card-body">
                        <h2 id="ticket-activity-heading" class="ticket-reference-section-title">Aktivitas Tiket</h2>
                        @if ($activity->isNotEmpty())
                            <x-tickets.timeline :events="$activity" variant="reference" />
                        @else
                            <p class="ticket-reference-empty mt-5">Belum ada aktivitas publik pada tiket ini.</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
