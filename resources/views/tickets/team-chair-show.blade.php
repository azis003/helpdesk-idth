@extends('layouts.app')

@section('title', $ticket->ticketLabel().' &mdash; '.$branding['application_name'])
@section('header_kicker', 'Pemantauan tim')
@section('header_title', 'Detail tiket')

@php
    $sla = $ticket->sla;
    $formatMinutes = static function ($minutes): string {
        if ($minutes === null) {
            return 'Tidak tersedia';
        }

        $minutes = max(0, (int) $minutes);
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0 ? $hours.'j '.$remaining.'m' : $remaining.'m';
    };
    $slaState = ! $sla || ! ($sla['uses_sla'] ?? false)
        ? 'Tidak menggunakan SLA'
        : (($sla['paused'] ?? false)
            ? 'Dijeda karena status menunggu'
            : (($sla['overdue'] ?? false) ? 'Melewati target SLA' : (($sla['near_limit'] ?? false) ? 'Mendekati batas SLA' : 'SLA berjalan')));
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <a href="{{ route('tickets.index') }}" class="ui-action-link">&larr; Kembali ke tiket tim</a>
            <p class="ui-eyebrow mt-4"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Metadata yang disetujui</p>
            <h1 class="ui-page-title">{{ $ticket->ticketLabel() }}</h1>
            <p class="ui-page-description">Ringkasan tiket anggota tim untuk pemantauan Ketua Tim Kerja. Halaman ini bersifat baca saja.</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <span class="rounded-full border border-[#f2d996] bg-[#fff8e6] px-3 py-1.5 text-xs font-extrabold text-[#8b6100]">Mode baca saja</span>
        </div>
    </div>

    <div class="mt-8 space-y-5">
        <section class="rounded-2xl border border-[#f0d28c] bg-[#fffaf0] p-4 sm:p-5" role="note" aria-label="Batas akses Ketua Tim Kerja">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#ffe8a3] text-[#8b6100]" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 19 6v5.3c0 4.1-2.8 7.5-7 9.2-4.2-1.7-7-5.1-7-9.2V6l7-2.5Z" /><path stroke-linecap="round" d="M12 8v4.5m0 3h.01" /></svg>
                </span>
                <div>
                    <p class="text-sm font-extrabold text-[#7c5800]">Akses terbatas untuk pemantauan</p>
                    <p class="mt-1 text-sm leading-6 text-[#946f16]">Anda dapat melihat status, SLA, penanggung jawab, balasan publik, dan solusi. Komentar, perubahan status, lampiran, persetujuan, serta aksi operasional tidak tersedia pada peran ini.</p>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[1.28fr_0.72fr]">
            <section class="ui-panel" aria-labelledby="team-chair-ticket-summary">
                <div class="ui-panel-header flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Ringkasan tiket</p>
                        <h2 id="team-chair-ticket-summary" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">{{ $ticket->subject }}</h2>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <x-status-badge :status="$ticket->status" />
                        <x-priority-badge :priority="$ticket->priority" />
                    </div>
                </div>

                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-xl bg-[#f8fbfc] p-4 sm:col-span-2">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Layanan</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->serviceCode ?? 'Kode layanan tidak tersedia' }} <span class="font-normal text-[#78909a]">&mdash;</span> {{ $ticket->serviceName ?? 'Layanan belum tersedia' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Kategori masalah</dt>
                        <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->categoryName ?? 'Belum dikategorikan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pemohon</dt>
                        <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->requesterName ?? 'Belum tercatat' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Tim kerja</dt>
                        <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->teamName ?? 'Belum memiliki tim' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Dibuat</dt>
                        <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->submittedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Terakhir diperbarui</dt>
                        <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->updatedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Prioritas</dt>
                        <dd class="mt-1"><x-priority-badge :priority="$ticket->priority" /></dd>
                    </div>
                </dl>
            </section>

            <section class="ui-panel" aria-labelledby="team-chair-assignee-heading">
                <div class="ui-panel-header">
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#75d5f3] !shadow-[0_0_0_4px_#d9f6ff]" aria-hidden="true"></span>Penanganan</p>
                    <h2 id="team-chair-assignee-heading" class="mt-2 ui-section-title">Penanggung jawab</h2>
                    <p class="ui-section-description">Informasi kepemilikan tiket yang dapat dipantau oleh Ketua Tim.</p>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-lg font-extrabold text-[#35505b]">{{ $ticket->assigneeLabel() }}</p>
                    @if ($ticket->assignedTierLabel())
                        <p class="mt-1 text-sm text-[#78909a]">{{ $ticket->assignedTierLabel() }}</p>
                    @endif
                    <div class="mt-5 rounded-xl border border-[#dce7eb] bg-[#fbfdfd] p-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Status saat ini</p>
                        <div class="mt-2"><x-status-badge :status="$ticket->status" /></div>
                    </div>
                </div>
            </section>
        </div>

        <section class="ui-panel" aria-labelledby="team-chair-sla-heading">
            <div class="ui-panel-header flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#f3c64d] !shadow-[0_0_0_4px_#fff1c2]" aria-hidden="true"></span>Target layanan</p>
                    <h2 id="team-chair-sla-heading" class="mt-2 ui-section-title">SLA tiket</h2>
                </div>
                <span class="rounded-full bg-[#f8fbfc] px-3 py-1 text-xs font-extrabold text-[#526f79]">{{ $slaState }}</span>
            </div>
            <dl class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
                <div class="rounded-xl bg-[#f8fbfc] p-4">
                    <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Target</dt>
                    <dd class="mt-1 text-sm font-extrabold text-[#35505b]">{{ ($sla && ($sla['uses_sla'] ?? false)) ? (($sla['target_working_days'] ?? null) !== null ? $sla['target_working_days'].' hari kerja' : $formatMinutes($sla['target_working_minutes'] ?? null)) : 'Tidak menggunakan SLA' }}</dd>
                </div>
                <div class="rounded-xl bg-[#f8fbfc] p-4">
                    <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Sisa waktu aktif</dt>
                    <dd class="mt-1 text-sm font-extrabold text-[#35505b]">{{ ($sla && ($sla['uses_sla'] ?? false)) ? $formatMinutes($sla['remaining_minutes'] ?? null) : 'Tidak tersedia' }}</dd>
                </div>
                <div class="rounded-xl bg-[#f8fbfc] p-4">
                    <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Kepatuhan</dt>
                    <dd class="mt-1 text-sm font-extrabold text-[#35505b]">{{ ! $sla || ($sla['compliant'] ?? null) === null ? 'Belum diukur' : (($sla['compliant'] ?? false) ? 'Sesuai target' : 'Tidak sesuai target') }}</dd>
                </div>
            </dl>
        </section>

        <div class="grid gap-5 xl:grid-cols-2">
            <section class="ui-panel" aria-labelledby="team-chair-public-replies-heading">
                <div class="ui-panel-header">
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#75d5f3] !shadow-[0_0_0_4px_#d9f6ff]" aria-hidden="true"></span>Komunikasi</p>
                    <h2 id="team-chair-public-replies-heading" class="mt-2 ui-section-title">Balasan publik</h2>
                    <p class="ui-section-description">Hanya pesan yang ditujukan untuk Pemohon yang ditampilkan.</p>
                </div>
                <div class="space-y-3 p-5 sm:p-6">
                    @forelse ($ticket->publicComments as $comment)
                        <article class="rounded-2xl border border-[#cdeef7] bg-[#f5fcfe] p-4">
                            <header class="flex flex-wrap items-start justify-between gap-3">
                                <p class="text-sm font-extrabold text-[#35505b]">{{ $comment->authorName ?? 'Sistem' }}</p>
                                @if ($comment->createdAt)
                                    <time class="text-xs text-[#78909a]" datetime="{{ $comment->createdAt->toIso8601String() }}">{{ $comment->createdAt->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                                @endif
                            </header>
                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $comment->body }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#cfe0e5] bg-[#fbfdfd] p-6 text-center">
                            <p class="text-sm font-extrabold text-[#526f79]">Belum ada balasan publik.</p>
                            <p class="mt-1 text-xs text-[#78909a]">Catatan internal tidak termasuk dalam tampilan Ketua Tim.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="ui-panel" aria-labelledby="team-chair-solution-heading">
                <div class="ui-panel-header">
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#75d5f3] !shadow-[0_0_0_4px_#d9f6ff]" aria-hidden="true"></span>Penyelesaian</p>
                    <h2 id="team-chair-solution-heading" class="mt-2 ui-section-title">Solusi</h2>
                    <p class="ui-section-description">Solusi yang tercatat untuk tiket ini, tanpa detail operasional lainnya.</p>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="rounded-2xl border border-[#cdeef7] bg-[#f5fcfe] p-5">
                        <p class="whitespace-pre-line text-sm leading-7 text-[#35505b]">{{ $ticket->solution ?: 'Solusi belum tersedia.' }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
