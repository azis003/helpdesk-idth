@extends('layouts.app')

@section('title', 'Dasbor — '.$branding['application_name'])
@section('header_kicker', 'Ruang kerja')
@section('header_title', 'Dasbor')

@php
    $isTeamChair = $user->hasRole(\App\Enums\Role::KetuaTimKerja);
    $isRequesterOnly = $user->hasRole(\App\Enums\Role::Pemohon)
        && ! $user->hasAnyRole([
            \App\Enums\Role::SuperAdmin,
            \App\Enums\Role::AgenTier1,
            \App\Enums\Role::AgenTier2,
            \App\Enums\Role::Approver,
            \App\Enums\Role::KetuaTimKerja,
        ]);
    $isHelpdeskDashboard = $agentDashboard['visible']
        && ! $isSuperAdmin
        && ! $isTeamChair
        && $user->hasAnyRole([\App\Enums\Role::AgenTier1, \App\Enums\Role::AgenTier2]);
    $formatDate = static fn ($value): string => $value?->timezone($periodTimezone)->locale('id')->translatedFormat('d M Y, H:i') ?? 'Belum tercatat';
    $formatMinutes = static function (?int $minutes): string {
        if ($minutes === null) {
            return 'Tidak tersedia';
        }

        $minutes = max(0, $minutes);
        $days = intdiv($minutes, 480);
        $hours = intdiv($minutes % 480, 60);
        $remainingMinutes = $minutes % 60;
        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' hari';
        }
        if ($hours > 0) {
            $parts[] = $hours.' jam';
        }
        if ($remainingMinutes > 0 || $parts === []) {
            $parts[] = $remainingMinutes.' menit';
        }

        return implode(' ', $parts);
    };
@endphp

@section('content')
    @if ($isHelpdeskDashboard)
        @include('dashboard.helpdesk')
    @else
    @if ($isRequesterOnly)
        {{-- Section A: Service Action Area (Calm Service Portal Header) --}}
        <section class="ui-panel p-5 sm:p-7" aria-labelledby="dashboard-title">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Portal Layanan TI</p>
                    <h1 id="dashboard-title" class="ui-page-title mt-1.5 text-2xl font-extrabold tracking-tight text-[#161c22]">Butuh bantuan TI?</h1>
                    <p class="mt-2 text-sm leading-relaxed text-[#4e5a66]">Laporkan kendala atau ajukan kebutuhan layanan TI Anda di sini.</p>
                </div>
                <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                    @if ($canCreateTickets)
                        <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary inline-flex w-full items-center justify-center gap-2 sm:w-auto">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                            <span>Buat tiket</span>
                        </a>
                    @endif
                    <a href="{{ route('tickets.index') }}" class="ui-btn ui-btn-secondary inline-flex w-full items-center justify-center gap-2 sm:w-auto">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg>
                        <span>Lihat tiket saya</span>
                    </a>
                    <button type="button" class="ui-btn ui-btn-ghost inline-flex w-full items-center justify-center gap-1.5 text-[#4e5a66] hover:text-[#161c22] sm:w-auto" data-reporting-guide-trigger aria-haspopup="dialog">
                        <svg class="h-4 w-4 text-[#667381]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M9.8 9.2a2.3 2.3 0 1 1 3.8 1.7c-.9.7-1.6 1.2-1.6 2.4M12 16.5h.01" /></svg>
                        <span>Tata cara pelaporan</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- Section B: Announcements (if active) --}}
        @if ($announcements->isNotEmpty())
            <section class="mt-6" aria-labelledby="announcements-heading">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Informasi Layanan</p>
                        <h2 id="announcements-heading" class="mt-1 text-base font-bold text-[#161c22]">Pengumuman Layanan</h2>
                    </div>
                    <span class="text-xs font-semibold text-[#667381]">{{ $announcements->count() }} informasi aktif</span>
                </div>
                <div class="mt-3.5 grid gap-3 lg:grid-cols-2">
                    @foreach ($announcements as $announcement)
                        <article class="ui-panel border-l-4 border-l-[#e4a72c] p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-bold text-[#161c22]">{{ $announcement->title }}</h3>
                                <time class="shrink-0 text-[0.68rem] font-semibold text-[#667381]" datetime="{{ $announcement->starts_at?->toIso8601String() }}">{{ $formatDate($announcement->starts_at) }}</time>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-xs leading-relaxed text-[#4e5a66]">{{ $announcement->body }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section C: Ticket Status Summary --}}
        <section class="mt-6" aria-labelledby="requester-summary-heading">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 id="requester-summary-heading" class="text-base font-bold text-[#161c22]">Status Permintaan Saya</h2>
                    <p class="mt-0.5 text-xs text-[#667381]">Ringkasan keseluruhan status tiket yang Anda ajukan.</p>
                </div>
                <a href="{{ route('tickets.index') }}" class="ui-action-link text-xs font-semibold text-[#14738b] hover:text-[#135d72]">
                    Lihat semua tiket <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <dl class="mt-3.5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="ui-stat-card flex flex-col justify-between p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="ui-stat-label">Total tiket</dt>
                        <span class="ui-stat-icon flex h-8 w-8 items-center justify-center rounded-lg bg-[#eef1f4] text-[#3a444e]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7.5h14v11H5zM8 7.5V5h8v2.5M8.5 11h7M8.5 14.5h4" /></svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <dd class="ui-stat-value text-2xl font-extrabold tabular-nums text-[#161c22]">{{ $requesterDashboard['total_ticket_count'] }}</dd>
                        <span class="mt-1 block text-xs leading-normal text-[#667381]">Semua tiket yang Anda buat.</span>
                    </div>
                </div>

                <div class="ui-stat-card flex flex-col justify-between p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="ui-stat-label">Tiket aktif</dt>
                        <span class="ui-stat-icon flex h-8 w-8 items-center justify-center rounded-lg bg-[#eff9fb] text-[#135d72]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <dd class="ui-stat-value text-2xl font-extrabold tabular-nums !text-[#135d72]">{{ $requesterDashboard['total_active_ticket_count'] }}</dd>
                        <span class="mt-1 block text-xs leading-normal text-[#667381]">Sedang diproses atau menunggu tindak lanjut.</span>
                    </div>
                </div>

                <div class="ui-stat-card flex flex-col justify-between p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="ui-stat-label">Tiket selesai</dt>
                        <span class="ui-stat-icon flex h-8 w-8 items-center justify-center rounded-lg bg-[#fff7e8] text-[#8a5700]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5.5 12.5 4 4 9-9" /></svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <dd class="ui-stat-value text-2xl font-extrabold tabular-nums !text-[#8a5700]">{{ $requesterDashboard['total_completed_ticket_count'] }}</dd>
                        <span class="mt-1 block text-xs leading-normal text-[#667381]">Solusi tersedia, menunggu konfirmasi Anda.</span>
                    </div>
                </div>

                <div class="ui-stat-card flex flex-col justify-between p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="ui-stat-label">Tiket ditutup</dt>
                        <span class="ui-stat-icon flex h-8 w-8 items-center justify-center rounded-lg bg-[#e9f8f2] text-[#0c6249]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3">
                        <dd class="ui-stat-value text-2xl font-extrabold tabular-nums !text-[#0c6249]">{{ $requesterDashboard['total_closed_ticket_count'] }}</dd>
                        <span class="mt-1 block text-xs leading-normal text-[#667381]">Tiket sudah berstatus Ditutup.</span>
                    </div>
                </div>
            </dl>
        </section>

        {{-- Section D: Reporting Guide Template --}}
        <template data-reporting-guide-template>
            <div class="text-left">
                <ol class="mt-4 space-y-4">
                    <li class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#d7f0f5] text-xs font-extrabold text-[#135d72]">1</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#161c22]">Pilih layanan</p>
                            <p class="mt-1 text-xs leading-5 text-[#667381]">Pilih kategori yang paling mendekati kebutuhan Anda.</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#d7f0f5] text-xs font-extrabold text-[#135d72]">2</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#161c22]">Jelaskan kebutuhan</p>
                            <p class="mt-1 text-xs leading-5 text-[#667381]">Tuliskan kendala, dampak, lokasi, dan hasil yang diharapkan.</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#d7f0f5] text-xs font-extrabold text-[#135d72]">3</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#161c22]">Pantau dan konfirmasi</p>
                            <p class="mt-1 text-xs leading-5 text-[#667381]">Balas jika ada pertanyaan dan konfirmasi setelah solusi tersedia.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </template>
    @else
        <section class="ui-portal-hero" aria-labelledby="dashboard-title">
            <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                <div>
                    <p class="ui-eyebrow ui-eyebrow--inverse"><span class="ui-eyebrow-dot" aria-hidden="true"></span>{{ $branding['tagline'] ?: 'Portal layanan' }} · {{ $branding['application_name'] }}</p>
                    <h1 id="dashboard-title" class="ui-hero-title">Selamat datang, {{ $user->name }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#d7e8ed]">Pantau pekerjaan yang menjadi cakupan peran Anda dalam satu tampilan.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($user->roleLabels() as $roleLabel)
                        <span class="inline-flex items-center rounded-full border border-[#79bfd4] bg-[#376b81] px-2.5 py-1 text-xs font-bold text-white">{{ $roleLabel }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        @if (! $isSuperAdmin)
            <section class="ui-panel mt-6 p-4 sm:p-5" aria-labelledby="period-filter-heading">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Periode dasbor</p>
                    <h2 id="period-filter-heading" class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">{{ $periodLabel }}</h2>
                    <p class="mt-1 text-xs leading-5 text-[#6a8089]">Default bulan berjalan. Semua tanggal mengikuti zona waktu Asia/Jakarta.</p>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] sm:items-end" aria-describedby="period-filter-help">
                    <div>
                        <label for="dashboard-start-date" class="ui-field-label">Mulai</label>
                        <input id="dashboard-start-date" name="start_date" type="date" value="{{ $periodStart->format('Y-m-d') }}" class="ui-input mt-1" />
                    </div>
                    <div>
                        <label for="dashboard-end-date" class="ui-field-label">Sampai</label>
                        <input id="dashboard-end-date" name="end_date" type="date" value="{{ $periodEnd->format('Y-m-d') }}" class="ui-input mt-1" />
                    </div>
                    <button type="submit" class="ui-btn ui-btn-primary">Terapkan filter</button>
                    <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-ghost">Bulan berjalan</a>
                </form>
            </div>
            <p id="period-filter-help" class="mt-3 text-xs leading-5 text-[#78909a]">Filter hanya menghitung data dalam cakupan role Anda. Data yang tidak berwenang tidak ikut digunakan dalam agregasi.</p>
            @if ($errors->has('start_date') || $errors->has('end_date'))
                <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800" role="alert">
                    {{ $errors->first('start_date') ?: $errors->first('end_date') }}
                </div>
            @endif
            </section>
        @endif

    {{-- Approvals intentionally come first for the active Approver persona. --}}
    @if ($approverDashboard['visible'])
        <section class="mt-7" aria-labelledby="my-approvals-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Perlu tindakan saya</p>
                    <h2 id="my-approvals-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Perlu Tindakan Saya</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Persetujuan tertunda diurutkan dari permintaan yang paling lama menunggu.</p>
                </div>
                <a href="{{ route('approvals.index') }}" class="ui-btn ui-btn-secondary">Lihat semua persetujuan</a>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                @forelse ($approverDashboard['pending'] as $approval)
                    <article class="ui-panel border-l-4 border-l-[#e4a72c] p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-[#1d5d72]">{{ $approval->ticket?->ticket_number ?? 'Tiket #'.$approval->ticket_id }}</p>
                                <h4 class="mt-2 truncate text-sm font-extrabold text-[#35505b]">{{ $approval->ticket?->subject ?? 'Tiket tidak tersedia' }}</h4>
                            </div>
                            <span class="shrink-0 rounded-full bg-[#fff4d7] px-2.5 py-1 text-[0.68rem] font-extrabold text-[#9a6700]">Menunggu</span>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pemohon</dt><dd class="mt-1 truncate text-sm font-bold text-[#35505b]">{{ $approval->ticket?->requester?->name ?? 'Tidak tersedia' }}</dd></div>
                            <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Lama menunggu</dt><dd class="mt-1 text-sm font-bold text-[#526f79]">{{ $approval->requested_at?->copy()->locale('id')->diffForHumans(now($periodTimezone)) ?? 'Tidak tercatat' }}</dd></div>
                        </dl>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs text-[#78909a]">Diajukan {{ $formatDate($approval->requested_at) }}</span>
                            <a href="{{ route('tickets.show', $approval->ticket_id) }}" class="ui-action-link">Tinjau dan putuskan <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @empty
                    <x-empty-state class="lg:col-span-2" title="Belum ada persetujuan tertunda." description="Permintaan baru akan tampil ketika agen mengirim tiket untuk keputusan Anda." action="{{ route('approvals.index') }}" action-label="Buka daftar persetujuan" />
                @endforelse
            </div>
        </section>
    @endif

    @if ($announcements->isNotEmpty())
        <section class="mt-7" aria-labelledby="announcements-heading">
            <div class="flex items-end justify-between gap-4"><div><p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Informasi terbaru</p><h2 id="announcements-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Pengumuman layanan</h2></div><span class="text-xs font-bold text-[#86979e]">{{ $announcements->count() }} informasi aktif</span></div>
            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                @foreach ($announcements as $announcement)
                    <article class="ui-panel border-l-4 border-l-[#e4a72c] p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><h4 class="text-sm font-extrabold text-[#263a43]">{{ $announcement->title }}</h4><time class="shrink-0 text-[0.68rem] font-bold text-[#86979e]" datetime="{{ $announcement->starts_at?->toIso8601String() }}">{{ $formatDate($announcement->starts_at) }}</time></div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-[#526f79]">{{ $announcement->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($requesterDashboard['visible'] && ! $isRequesterOnly)
        <section class="mt-7" aria-labelledby="requester-dashboard-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Dasbor Pemohon</p>
                    <h2 id="requester-dashboard-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Tiket dan tindak lanjut saya</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Lihat status, perkembangan terbaru, dan permintaan yang membutuhkan jawaban Anda.</p>
                </div>
                @if ($canCreateTickets)
                    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary">Buat tiket</a>
                @endif
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article class="ui-stat-card items-start"><span class="ui-stat-icon" aria-hidden="true">▣</span><div><p class="ui-stat-label">Tiket saya</p><p class="ui-stat-value">{{ $requesterDashboard['ticket_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Dalam periode terpilih.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true">✓</span><div><p class="ui-stat-label">Masih berjalan</p><p class="ui-stat-value">{{ $requesterDashboard['active_ticket_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Belum berstatus akhir.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff4d7] !text-[#9a6700]" aria-hidden="true">!</span><div><p class="ui-stat-label">Perlu jawaban</p><p class="ui-stat-value">{{ $requesterDashboard['needs_reply_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Menunggu informasi dari Anda.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true">○</span><div><p class="ui-stat-label">Perlu konfirmasi</p><p class="ui-stat-value">{{ $requesterDashboard['needs_confirmation_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Periksa solusi yang tersedia.</p></div></article>
            </div>

            <div class="mt-5 grid gap-5 xl:grid-cols-2">
                <article class="ui-panel overflow-hidden" aria-labelledby="requester-reply-heading">
                    <div class="ui-panel-header"><h3 id="requester-reply-heading" class="ui-section-title">Tiket yang membutuhkan jawaban</h3><p class="ui-section-description">Balas pertanyaan petugas agar penanganan dapat dilanjutkan.</p></div>
                    <div class="divide-y divide-[#edf2f4]">
                        @forelse ($requesterDashboard['needs_reply_tickets'] as $ticket)
                            <a href="{{ route('tickets.show', $ticket) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4></div><x-status-badge :status="$ticket->status" /></div><p class="mt-2 text-xs text-[#78909a]">Diperbarui {{ $formatDate($ticket->updated_at) }}</p></a>
                        @empty
                            <p class="px-5 py-7 text-sm text-[#78909a]">Tidak ada tiket yang menunggu jawaban Anda.</p>
                        @endforelse
                    </div>
                </article>
                <article class="ui-panel overflow-hidden" aria-labelledby="requester-confirmation-heading">
                    <div class="ui-panel-header"><h3 id="requester-confirmation-heading" class="ui-section-title">Tiket yang membutuhkan konfirmasi</h3><p class="ui-section-description">Konfirmasi hasil atau sampaikan bila solusi belum sesuai.</p></div>
                    <div class="divide-y divide-[#edf2f4]">
                        @forelse ($requesterDashboard['needs_confirmation_tickets'] as $ticket)
                            <a href="{{ route('tickets.show', $ticket) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4></div><x-status-badge :status="$ticket->status" /></div><p class="mt-2 text-xs text-[#78909a]">Solusi tersedia sejak {{ $formatDate($ticket->confirmation_started_at ?? $ticket->updated_at) }}</p></a>
                        @empty
                            <p class="px-5 py-7 text-sm text-[#78909a]">Tidak ada tiket yang menunggu konfirmasi Anda.</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <article class="ui-panel mt-5 overflow-hidden" aria-labelledby="requester-tickets-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e7eef1] px-5 py-4 sm:px-6"><div><h3 id="requester-tickets-heading" class="ui-section-title">Perkembangan terbaru</h3><p class="ui-section-description">Tiket yang paling baru diperbarui dalam cakupan Anda.</p></div><a href="{{ route('tickets.index') }}" class="ui-action-link">Buka tiket saya <span aria-hidden="true">→</span></a></div>
                <div class="divide-y divide-[#edf2f4]">
                    @forelse ($requesterDashboard['tickets'] as $ticket)
                        @php($publicComment = $requesterDashboard['public_comments']->get($ticket->getKey()))
                        <a href="{{ route('tickets.show', $ticket) }}" class="block p-5 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa] sm:px-6"><div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }} · {{ $ticket->service_type_code_snapshot ?? $ticket->serviceType?->code ?? 'Layanan' }}</p><h4 class="mt-1 text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4>@if ($publicComment)<p class="mt-2 line-clamp-2 text-xs leading-5 text-[#6a8089]">Balasan terbaru: {{ $publicComment->body }}</p>@elseif ($ticket->solution)<p class="mt-2 line-clamp-2 text-xs leading-5 text-[#6a8089]">Solusi: {{ $ticket->solution }}</p>@endif</div><div class="flex shrink-0 flex-wrap items-center gap-2"><x-status-badge :status="$ticket->status" /><x-priority-badge :priority="$ticket->priority" /></div></div><p class="mt-3 text-xs text-[#78909a]">Pembaruan terakhir {{ $formatDate($ticket->updated_at) }}</p></a>
                    @empty
                        <x-empty-state title="Belum ada tiket pada periode ini." description="Tiket yang Anda buat atau ajukan akan tampil di sini setelah tercatat." :action="$canCreateTickets ? route('tickets.create') : null" action-label="Buat tiket" />
                    @endforelse
                </div>
            </article>

            <article class="ui-panel mt-5 overflow-hidden" aria-labelledby="requester-notifications-heading">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e7eef1] px-5 py-4 sm:px-6"><div><h3 id="requester-notifications-heading" class="ui-section-title">Notifikasi terbaru</h3><p class="ui-section-description">Pembaruan tiket yang dikirim ke akun Anda.</p></div><a href="{{ route('notifications.index') }}" class="ui-action-link">Lihat semua <span aria-hidden="true">→</span></a></div>
                <div class="divide-y divide-[#edf2f4]">
                    @forelse ($requesterDashboard['notifications'] as $notification)
                        <div class="flex items-start gap-3 px-5 py-4 sm:px-6"><span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-[#dfe8ec]' : 'bg-[#2bb8aa]' }}" aria-hidden="true"></span><div class="min-w-0"><p class="text-sm font-extrabold text-[#35505b]">{{ $notification->data['title'] ?? 'Notifikasi tiket' }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">{{ $notification->data['message'] ?? 'Ada pembaruan pada tiket.' }}</p><p class="mt-1 text-[0.68rem] text-[#9aabb0]">{{ $formatDate($notification->created_at) }}</p></div></div>
                    @empty
                        <p class="px-5 py-7 text-sm text-[#78909a] sm:px-6">Belum ada notifikasi pada periode ini.</p>
                    @endforelse
                </div>
            </article>
        </section>
    @endif

    @if ($agentDashboard['visible'])
        <section class="mt-7" aria-labelledby="agent-dashboard-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#2bb8aa] !shadow-[0_0_0_4px_#d6f5ef]" aria-hidden="true"></span>Dasbor Agen</p>
                    <h2 id="agent-dashboard-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Antrian dan tanggung jawab operasional</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Prioritaskan antrean, SLA, waktu tunggu, dan tiket yang membutuhkan tindakan Anda.</p>
                </div>
                <div class="flex flex-wrap gap-2">@if ($agentDashboard['is_tier_one'])<a href="{{ route('tickets.queue') }}" class="ui-btn ui-btn-secondary">Buka Monitoring Tiket</a>@endif<a href="{{ route('tickets.queue', ['tab' => 'mine']) }}" class="ui-btn ui-btn-ghost">Tiket Saya</a></div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @if ($agentDashboard['is_tier_one'])<article class="ui-stat-card items-start"><span class="ui-stat-icon" aria-hidden="true">▤</span><div><p class="ui-stat-label">Antrian Tiket</p><p class="ui-stat-value">{{ $agentDashboard['queue_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Menunggu klaim Tier 1.</p></div></article>@endif
                <article class="ui-stat-card items-start"><span class="ui-stat-icon" aria-hidden="true">◫</span><div><p class="ui-stat-label">Tanggung jawab saya</p><p class="ui-stat-value">{{ $agentDashboard['assigned_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Tiket yang sedang ditangani.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff4d7] !text-[#9a6700]" aria-hidden="true">!</span><div><p class="ui-stat-label">Mendekati SLA</p><p class="ui-stat-value">{{ $agentDashboard['near_sla_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Sisa aktif ≤ 20% target.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff1f2] !text-[#be123c]" aria-hidden="true">!</span><div><p class="ui-stat-label">Terlewat SLA</p><p class="ui-stat-value">{{ $agentDashboard['overdue_sla_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Perlu segera ditindaklanjuti.</p></div></article>
                <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true">◌</span><div><p class="ui-stat-label">Menunggu</p><p class="ui-stat-value">{{ $agentDashboard['waiting_requester_count'] + $agentDashboard['waiting_third_party_count'] }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Pemohon {{ $agentDashboard['waiting_requester_count'] }} · pihak ketiga {{ $agentDashboard['waiting_third_party_count'] }}.</p></div></article>
            </div>

            <div class="mt-5 grid gap-5 xl:grid-cols-2">
                @if ($agentDashboard['is_tier_one'])
                    <article class="ui-panel overflow-hidden" aria-labelledby="agent-queue-heading"><div class="ui-panel-header"><h3 id="agent-queue-heading" class="ui-section-title">Antrian Tiket</h3><p class="ui-section-description">Antrian bersama Agen Tier 1, diurutkan berdasarkan prioritas dan usia tiket.</p></div><div class="divide-y divide-[#edf2f4]">@forelse ($agentDashboard['queue_tickets'] as $ticket)<a href="{{ route('tickets.show', $ticket) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4></div><x-priority-badge :priority="$ticket->priority" /></div><p class="mt-2 text-xs text-[#78909a]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Pemohon tidak tersedia' }} · {{ $formatDate($ticket->submitted_at ?? $ticket->created_at) }}</p></a>@empty<p class="px-5 py-7 text-sm text-[#78909a]">Belum ada tiket pada antrean ini.</p>@endforelse</div></article>
                @endif
                <article class="ui-panel overflow-hidden" aria-labelledby="agent-assigned-heading"><div class="ui-panel-header"><h3 id="agent-assigned-heading" class="ui-section-title">Tiket tanggung jawab saya</h3><p class="ui-section-description">Status, pemohon, dan waktu pembaruan tiket yang ditugaskan kepada Anda.</p></div><div class="divide-y divide-[#edf2f4]">@forelse ($agentDashboard['assigned_tickets'] as $ticket)<a href="{{ route('tickets.show', $ticket) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4></div><x-status-badge :status="$ticket->status" /></div><p class="mt-2 text-xs text-[#78909a]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Pemohon tidak tersedia' }} · diperbarui {{ $formatDate($ticket->updated_at) }}</p></a>@empty<p class="px-5 py-7 text-sm text-[#78909a]">Belum ada tiket yang ditugaskan kepada Anda.</p>@endforelse</div></article>
            </div>

            <div class="mt-5 grid gap-5 xl:grid-cols-2">
                <article class="ui-panel overflow-hidden" aria-labelledby="agent-sla-heading"><div class="ui-panel-header"><h3 id="agent-sla-heading" class="ui-section-title">Risiko SLA</h3><p class="ui-section-description">Tiket dipisahkan antara mendekati batas dan sudah melewati batas.</p></div><div class="divide-y divide-[#edf2f4]">@forelse ($agentDashboard['overdue_sla_tickets']->merge($agentDashboard['near_sla_tickets'])->take(8) as $entry) @php($metrics = $entry['metrics'])<a href="{{ route('tickets.show', $entry['ticket']) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $entry['ticket']->ticket_number ?? 'Tiket #'.$entry['ticket']->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $entry['ticket']->subject }}</h4></div>@if ($metrics['overdue'])<span class="ui-status ui-status-danger">Terlewat SLA</span>@else<span class="ui-status ui-status-warning">Mendekati SLA</span>@endif</div><p class="mt-2 text-xs text-[#78909a]">Sisa aktif: {{ $metrics['paused'] ? 'Dijeda' : $formatMinutes($metrics['remaining_minutes']) }}</p></a>@empty<p class="px-5 py-7 text-sm text-[#78909a]">Tidak ada tiket mendekati atau melewati batas SLA.</p>@endforelse</div></article>
                <article class="ui-panel overflow-hidden" aria-labelledby="agent-waiting-heading"><div class="ui-panel-header"><h3 id="agent-waiting-heading" class="ui-section-title">Menunggu dan persetujuan</h3><p class="ui-section-description">Tiket yang tertahan oleh pemohon, pihak ketiga, atau keputusan persetujuan.</p></div><div class="divide-y divide-[#edf2f4]">@forelse ($agentDashboard['approval_waiting_tickets'] as $ticket)<a href="{{ route('tickets.show', $ticket) }}" class="block p-4 transition hover:bg-[#f8fcfd] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2bb8aa]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 truncate text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4></div><x-status-badge :status="$ticket->status" /></div><p class="mt-2 text-xs text-[#78909a]">Menunggu keputusan Approver aktif.</p></a>@empty<p class="px-5 py-7 text-sm text-[#78909a]">Tidak ada persetujuan yang sedang ditunggu.</p>@endforelse</div></article>
            </div>
        </section>
    @endif

    @if ($teamDashboard['visible'])
        <section class="mt-7" aria-labelledby="team-dashboard-heading">
            <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#7c83e6] !shadow-[0_0_0_4px_#e9e9ff]" aria-hidden="true"></span>Dasbor Ketua Tim Kerja</p><h2 id="team-dashboard-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Pemantauan tiket anggota tim</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Tampilan ini bersifat baca saja: hanya metadata, status, SLA, penanggung jawab, balasan publik, dan solusi yang ditampilkan.</p></div><span class="rounded-full bg-[#eef3ff] px-3 py-1.5 text-xs font-extrabold text-[#4f63a6]">{{ $teamDashboard['member_count'] }} anggota · {{ $teamDashboard['ticket_count'] }} tiket</span></div>
            @if ($teamDashboard['team_names'] !== [])<p class="mt-3 text-xs font-bold text-[#78909a]">Tim: {{ implode(', ', $teamDashboard['team_names']) }}</p>@endif
            <article class="ui-panel mt-4 overflow-hidden" aria-labelledby="team-tickets-heading">
                <div class="ui-panel-header"><h3 id="team-tickets-heading" class="ui-section-title">Tiket anggota dalam periode</h3><p class="ui-section-description">Catatan internal dan lampiran privat tidak tersedia dari cakupan Ketua Tim.</p></div>
                <div class="divide-y divide-[#edf2f4]">
                    @forelse ($teamDashboard['rows'] as $row)
                        @php($ticket = $row['ticket'])
                        @php($sla = $row['sla'])
                        @php($latestPublicReply = $row['public_reply'])
                        <article class="p-5 sm:p-6">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                @if ($isTeamChair)
                                    <div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticketLabel() }}</p><h4 class="mt-1 text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4><p class="mt-2 text-xs text-[#78909a]">Pemohon: {{ $ticket->requesterName ?? 'Tidak tersedia' }} &middot; Diperbarui {{ $formatDate($ticket->updatedAt) }}</p></div>
                                @else
                                <div class="min-w-0"><p class="text-xs font-extrabold text-[#1d5d72]">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</p><h4 class="mt-1 text-sm font-extrabold text-[#35505b]">{{ $ticket->subject }}</h4><p class="mt-2 text-xs text-[#78909a]">Pemohon: {{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Tidak tersedia' }} · Diperbarui {{ $formatDate($ticket->updated_at) }}</p></div>
                                <div class="flex shrink-0 flex-wrap gap-2"><x-status-badge :status="$ticket->status" />@if ($ticket->assigned_to_id)<span class="ui-chip">{{ $ticket->assignee?->name ?? 'Penanggung jawab' }}@if ($ticket->assigned_tier) &middot; {{ \App\Enums\Role::tryFrom($ticket->assigned_tier)?->label() ?? $ticket->assigned_tier }}@endif</span>@else<span class="ui-chip">Belum ditugaskan</span>@endif</div>
                                @endif
                            </div>
                            @if ($isTeamChair)
                                <div class="mt-3 flex shrink-0 flex-wrap gap-2"><x-status-badge :status="$ticket->status" />@if ($ticket->assigneeName)<span class="ui-chip">{{ $ticket->assigneeName }}@if ($ticket->assignedTierLabel()) &middot; {{ $ticket->assignedTierLabel() }}@endif</span>@else<span class="ui-chip">Belum ditugaskan</span>@endif</div>
                            @endif
                            <dl class="mt-4 grid gap-3 md:grid-cols-3">
                                <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">SLA</dt><dd class="mt-1 text-sm font-bold text-[#526f79]">
                                    @if (!$sla || !($sla['uses_sla'] ?? false))
                                        Tidak menggunakan SLA
                                    @elseif ($sla['paused'])
                                        Dijeda ({{ $formatMinutes($sla['remaining_minutes']) }} tersisa)
                                    @elseif ($sla['overdue'])
                                        Terlewat batas
                                    @else
                                        {{ $formatMinutes($sla['remaining_minutes']) }} tersisa
                                    @endif
                                </dd></div>
                                <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Balasan publik terbaru</dt><dd class="mt-1 line-clamp-2 text-sm font-bold text-[#526f79]">{{ $latestPublicReply?->body ?? 'Belum ada balasan publik.' }}</dd></div>
                                <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Solusi</dt><dd class="mt-1 line-clamp-2 text-sm font-bold text-[#526f79]">{{ $ticket->solution ?? 'Belum tersedia.' }}</dd></div>
                            </dl>
                            @if ($isTeamChair)
                                <div class="mt-4"><a href="{{ route('tickets.show', $ticket->id) }}" class="ui-action-link">Lihat metadata tiket <span aria-hidden="true">&rarr;</span></a></div>
                            @endif
                            @if (! $isTeamChair)
                            <div class="mt-4"><a href="{{ route('tickets.show', $ticket) }}" class="ui-action-link">Lihat metadata tiket <span aria-hidden="true">→</span></a></div>
                            @endif
                        </article>
                    @empty
                        <x-empty-state title="Belum ada tiket anggota pada periode ini." description="Tiket akan tampil setelah ada permintaan dari anggota tim yang dipantau." />
                    @endforelse
                </div>
            </article>
        </section>
    @endif

    {{-- Super Admin only needs the role-specific workspace above; keep the general dashboard blocks hidden. --}}
    @if (! $isSuperAdmin)
    @if ($overallDashboard['visible'])
        <section class="mt-7" aria-labelledby="overall-dashboard-heading">
            <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#eb3349] !shadow-[0_0_0_4px_#ffe1e6]" aria-hidden="true"></span>Dasbor menyeluruh</p><h2 id="overall-dashboard-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Gambaran operasional {{ $branding['application_name'] }}</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Agregasi ini hanya tersedia untuk role yang berwenang dan mengikuti periode terpilih.</p></div><span class="rounded-full bg-[#f1fbfe] px-3 py-1.5 text-xs font-extrabold text-[#147a79]">{{ $overallDashboard['ticket_count'] }} tiket</span></div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7"><article class="ui-stat-card items-start"><span class="ui-stat-icon" aria-hidden="true">▣</span><div><p class="ui-stat-label">Jumlah tiket</p><p class="ui-stat-value">{{ $overallDashboard['ticket_count'] }}</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true">✓</span><div><p class="ui-stat-label">SLA sesuai</p><p class="ui-stat-value">{{ $overallDashboard['sla']['compliant'] }}</p><p class="mt-1 text-xs text-[#78909a]">{{ $overallDashboard['sla']['tracked'] }} terukur.</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff4d7] !text-[#9a6700]" aria-hidden="true">!</span><div><p class="ui-stat-label">Mendekati SLA</p><p class="ui-stat-value">{{ $overallDashboard['sla']['near_limit'] }}</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff1f2] !text-[#be123c]" aria-hidden="true">!</span><div><p class="ui-stat-label">Terlewat SLA</p><p class="ui-stat-value">{{ $overallDashboard['sla']['overdue'] }}</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true">↻</span><div><p class="ui-stat-label">Dibuka kembali</p><p class="ui-stat-value">{{ $overallDashboard['reopened_count'] }}</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#fff8e8] !text-[#a16207]" aria-hidden="true">↕</span><div><p class="ui-stat-label">Perubahan prioritas</p><p class="ui-stat-value">{{ $overallDashboard['priority_change_count'] }}</p></div></article><article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#f2efff] !text-[#6254a4]" aria-hidden="true">◎</span><div><p class="ui-stat-label">Persetujuan mandiri</p><p class="ui-stat-value">{{ $overallDashboard['approval_self_count'] }}</p></div></article></div>

            <div class="mt-5 grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ([['id' => 'overall-service-heading', 'title' => 'Sebaran layanan', 'items' => $overallDashboard['service_distribution']], ['id' => 'overall-category-heading', 'title' => 'Sebaran kategori', 'items' => $overallDashboard['category_distribution']], ['id' => 'overall-status-heading', 'title' => 'Sebaran status', 'items' => $overallDashboard['status_distribution']], ['id' => 'overall-workload-heading', 'title' => 'Beban kerja per agen', 'items' => $overallDashboard['workload']], ['id' => 'overall-source-heading', 'title' => 'Sumber pembuatan tiket', 'items' => $overallDashboard['self_created']], ['id' => 'overall-building-heading', 'title' => 'Sebaran gedung', 'items' => $overallDashboard['building_distribution']], ['id' => 'overall-floor-heading', 'title' => 'Sebaran lantai', 'items' => $overallDashboard['floor_distribution']]] as $distribution)
                    <article class="ui-panel overflow-hidden" aria-labelledby="{{ $distribution['id'] }}"><div class="ui-panel-header"><h3 id="{{ $distribution['id'] }}" class="ui-section-title">{{ $distribution['title'] }}</h3><p class="ui-section-description">Jumlah tiket pada periode terpilih.</p></div><div class="divide-y divide-[#edf2f4]">@forelse ($distribution['items'] as $item)<div class="flex items-center justify-between gap-3 px-5 py-3"><span class="min-w-0 truncate text-sm font-bold text-[#526f79]">{{ $item['label'] }}</span><span class="shrink-0 rounded-full bg-[#f1fbfe] px-2.5 py-1 text-xs font-extrabold text-[#147a79]">{{ $item['count'] }}</span></div>@empty<p class="px-5 py-6 text-sm text-[#78909a]">Belum ada data pada periode ini.</p>@endforelse</div></article>
                @endforeach
                <article class="ui-panel overflow-hidden" aria-labelledby="overall-sla-heading"><div class="ui-panel-header"><h3 id="overall-sla-heading" class="ui-section-title">Ringkasan kepatuhan SLA</h3><p class="ui-section-description">Tiket tanpa SLA tetap dipisahkan dari tiket yang terukur.</p></div><dl class="grid gap-3 p-5 sm:grid-cols-2"><div class="rounded-lg bg-[#e8faf4] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#087f5b]">Sesuai target</dt><dd class="mt-1 text-2xl font-extrabold text-[#087f5b]">{{ $overallDashboard['sla']['compliant'] }}</dd></div><div class="rounded-lg bg-[#fff1f2] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#be123c]">Tidak sesuai</dt><dd class="mt-1 text-2xl font-extrabold text-[#be123c]">{{ $overallDashboard['sla']['breached'] }}</dd></div><div class="rounded-lg bg-[#f8fbfc] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Belum terukur</dt><dd class="mt-1 text-2xl font-extrabold text-[#526f79]">{{ $overallDashboard['sla']['not_available'] }}</dd></div><div class="rounded-lg bg-[#fff4d7] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#9a6700]">Terukur</dt><dd class="mt-1 text-2xl font-extrabold text-[#9a6700]">{{ $overallDashboard['sla']['tracked'] }}</dd></div></dl></article>
            </div>
        </section>
    @endif

    @endif

    @endif

    @endif
@endsection
