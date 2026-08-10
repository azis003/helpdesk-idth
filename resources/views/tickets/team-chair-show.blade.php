@extends('layouts.app')

@section('title', $ticket->ticketLabel().' &mdash; '.$branding['application_name'])
@section('header_kicker', 'Pemantauan tim')
@section('header_title', 'Detail tiket')

@php
    $ticketLabel = $ticket->ticketLabel();
    $isIncident = str_starts_with((string) $ticketLabel, 'IN-');
    $ticketTypeLabel = $isIncident ? 'Insiden' : 'Permintaan layanan';
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
    <header class="ui-ticket-detail-header">
        <div class="min-w-0">
            <a href="{{ route('tickets.index') }}" class="ui-action-link mb-4 inline-flex items-center gap-1.5">
                <span aria-hidden="true">&larr;</span>
                Kembali ke tiket tim
            </a>
            <div class="ui-ticket-detail-heading">
                <span class="ui-ticket-detail-icon {{ $isIncident ? 'ui-ticket-detail-icon--incident' : 'ui-ticket-detail-icon--request' }}" aria-hidden="true">
                    @if ($isIncident)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.75" y="5.25" width="16.5" height="13.5" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 7 5.5 7-5.5" /></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8.25" cy="15.75" r="3.25" /><path stroke-linecap="round" stroke-linejoin="round" d="m10.6 13.4 7.15-7.15m-1.65-.2h2.05v2.05m-4.6 2.35 2.2 2.2" /></svg>
                    @endif
                </span>
                <div class="min-w-0">
                    <p class="ui-ticket-detail-kicker">{{ $ticketTypeLabel }} &middot; {{ $ticket->serviceCode ?? 'Tiket' }}</p>
                    <h1 class="ui-ticket-detail-title">#{{ $ticketLabel }}: {{ $ticket->subject }}</h1>
                </div>
            </div>
        </div>
        <div class="ui-ticket-detail-actions">
            <x-status-badge :status="$ticket->status" />
            <x-priority-badge :priority="$ticket->priority" />
            <span class="rounded-full border border-[#f2d996] bg-[#fff8e6] px-3 py-1.5 text-xs font-extrabold text-[#8b6100]">Baca saja</span>
        </div>
    </header>

    <div class="mt-6 space-y-5">
        <div class="flex items-center gap-2 text-xs font-bold text-[#78909a]" role="note" aria-label="Batas akses Ketua Tim Kerja">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#fff0b9] text-[#8b6100]" aria-hidden="true">i</span>
            <span>Mode baca saja untuk pemantauan tim.</span>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.28fr_0.72fr]">
            <section id="team-chair-summary" class="ui-panel" aria-labelledby="team-chair-ticket-summary">
                <div class="ui-panel-header flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="ui-ticket-section-kicker">Ringkasan tiket</p>
                        <h2 id="team-chair-ticket-summary" class="mt-1 ui-section-title">Informasi utama</h2>
                    </div>
                </div>

                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-xl bg-[#f8fbfc] p-4 sm:col-span-2">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Layanan</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->serviceCode ?? 'Kode layanan tidak tersedia' }} <span class="font-normal text-[#78909a]">&mdash;</span> {{ $ticket->serviceName ?? 'Layanan belum tersedia' }}</dd>
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
                    <h2 id="team-chair-assignee-heading" class="ui-section-title">Penanggung jawab</h2>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-lg font-extrabold text-[#35505b]">{{ $ticket->assigneeLabel() }}</p>
                            @if ($ticket->assignedTierLabel())
                                <p class="mt-1 text-sm text-[#78909a]">{{ $ticket->assignedTierLabel() }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section id="team-chair-sla" class="ui-panel" aria-labelledby="team-chair-sla-heading">
            <div class="ui-panel-header flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="team-chair-sla-heading" class="ui-section-title">SLA tiket</h2>
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
            <section id="team-chair-replies" class="ui-panel" aria-labelledby="team-chair-public-replies-heading">
                <div class="ui-panel-header">
                    <h2 id="team-chair-public-replies-heading" class="ui-section-title">Balasan publik</h2>
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
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="team-chair-solution" class="ui-panel" aria-labelledby="team-chair-solution-heading">
                <div class="ui-panel-header">
                    <h2 id="team-chair-solution-heading" class="ui-section-title">Solusi</h2>
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
