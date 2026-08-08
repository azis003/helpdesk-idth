@extends('layouts.app')

@php
    $now = now();
    $activeCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at <= $now && ($announcement->ends_at === null || $announcement->ends_at >= $now))->count();
    $scheduledCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at > $now)->count();
@endphp

@section('title', 'Pengumuman — '.$branding['application_name'])
@section('header_kicker', 'Komunikasi layanan')
@section('header_title', 'Pengumuman')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Informasi global</p>
            <h1 class="ui-page-title">Pengumuman layanan TI</h1>
            <p class="ui-page-description">Buat informasi yang tampil pada area layanan. Super Admin dan Agen Tier 1 aktif dapat mengelola pengumuman sesuai kebutuhan operasional.</p>
        </div>
        @if (auth()->user()->hasRole(\App\Enums\Role::SuperAdmin))
            <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-btn ui-btn-ghost">Lihat layanan &amp; formulir <span aria-hidden="true">→</span></a>
        @endif
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan pengumuman">
        <article class="ui-stat-card"><span class="ui-stat-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M18.5 10a3 3 0 0 1 0 4" /></svg></span><div><p class="ui-stat-label">Sedang tampil</p><p class="ui-stat-value">{{ $activeCount }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#fff6df] !text-[#a16207]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="5" width="15" height="14" rx="1.5" /><path stroke-linecap="round" d="M8 3.5v3M16 3.5v3M7.5 10h9M8 14h3" /></svg></span><div><p class="ui-stat-label">Terjadwal</p><p class="ui-stat-value">{{ $scheduledCount }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 5h12M6 12h12M6 19h8" /><path stroke-linecap="round" d="M4 5h.01M4 12h.01M4 19h.01" /></svg></span><div><p class="ui-stat-label">Total pengumuman</p><p class="ui-stat-value">{{ $announcements->count() }}</p></div></article>
    </section>

    <section class="mt-8 grid items-start gap-5 xl:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="ui-panel p-5 sm:p-6">
            @csrf
            <div><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Pengumuman baru</p><h2 class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">Buat informasi global</h2><p class="mt-2 text-sm leading-6 text-[#6a8089]">Gunakan kalimat singkat dan jelaskan masa aktifnya agar informasi tidak membingungkan.</p></div>
            <div class="mt-5 space-y-4"><div><label for="announcement-title" class="ui-field-label">Judul <span class="text-rose-600">*</span></label><input id="announcement-title" name="title" value="{{ old('title') }}" required class="ui-input mt-2" placeholder="Contoh: Pemeliharaan jaringan"></div><div><label for="announcement-body" class="ui-field-label">Isi pengumuman <span class="text-rose-600">*</span></label><textarea id="announcement-body" name="body" rows="5" required class="ui-textarea mt-2" placeholder="Tulis informasi yang perlu diketahui pengguna.">{{ old('body') }}</textarea></div><div class="grid gap-3 sm:grid-cols-2"><div><label for="announcement-starts" class="ui-field-label">Mulai tampil <span class="text-rose-600">*</span></label><input id="announcement-starts" name="starts_at" type="datetime-local" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required class="ui-input mt-2"></div><div><label for="announcement-ends" class="ui-field-label">Berakhir <span class="font-normal text-[#78909a]">(opsional)</span></label><input id="announcement-ends" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="ui-input mt-2"><p class="mt-1 text-[0.68rem] text-[#78909a]">Kosongkan bila belum ada batas akhir.</p></div></div><label class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_active" value="1" checked class="ui-checkbox"> Aktifkan sesuai masa tampil</label><button type="submit" class="ui-btn ui-btn-primary w-full">Simpan pengumuman</button></div>
        </form>

        <div class="ui-panel overflow-hidden">
            <div class="ui-panel-header"><h2 class="ui-section-title">Daftar pengumuman</h2><p class="ui-section-description">Status tampil mengikuti toggle aktif dan masa berlaku.</p></div>
            <div class="space-y-3 p-4 sm:p-5">
                @forelse ($announcements as $announcement)
                    @php
                        $isCurrentlyVisible = $announcement->is_active && $announcement->starts_at <= $now && ($announcement->ends_at === null || $announcement->ends_at >= $now);
                        $isScheduled = $announcement->is_active && $announcement->starts_at > $now;
                    @endphp
                    <details class="rounded-xl border border-[#e1eaed] bg-white">
                        <summary class="ui-disclosure-summary flex items-start justify-between gap-3 p-4"><span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $announcement->title }}</span><span class="mt-1 block text-[0.68rem] text-[#78909a]">Mulai {{ $announcement->starts_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} · {{ $announcement->creator?->name ?? 'Pengguna tidak tersedia' }}</span></span><span class="ui-status {{ $isCurrentlyVisible ? 'ui-status-active' : ($isScheduled ? 'ui-status-warning' : 'ui-status-inactive') }}">{{ $isCurrentlyVisible ? 'Tampil' : ($isScheduled ? 'Terjadwal' : ($announcement->is_active ? 'Di luar masa aktif' : 'Nonaktif')) }}</span></summary>
                        <div class="space-y-4 border-t border-[#edf2f4] p-4"><div class="rounded-lg bg-[#f8fbfc] p-3 text-sm leading-6 text-[#526f79]">{{ $announcement->body }}</div><form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="space-y-3">@csrf @method('PUT')<div><label for="edit-announcement-title-{{ $announcement->id }}" class="ui-field-label">Judul</label><input id="edit-announcement-title-{{ $announcement->id }}" name="title" value="{{ $announcement->title }}" required class="ui-input mt-2"></div><div><label for="edit-announcement-body-{{ $announcement->id }}" class="ui-field-label">Isi pengumuman</label><textarea id="edit-announcement-body-{{ $announcement->id }}" name="body" rows="4" required class="ui-textarea mt-2">{{ $announcement->body }}</textarea></div><div class="grid gap-3 sm:grid-cols-2"><div><label for="edit-announcement-starts-{{ $announcement->id }}" class="ui-field-label">Mulai tampil</label><input id="edit-announcement-starts-{{ $announcement->id }}" name="starts_at" type="datetime-local" value="{{ $announcement->starts_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') }}" required class="ui-input mt-2"></div><div><label for="edit-announcement-ends-{{ $announcement->id }}" class="ui-field-label">Berakhir</label><input id="edit-announcement-ends-{{ $announcement->id }}" name="ends_at" type="datetime-local" value="{{ $announcement->ends_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') }}" class="ui-input mt-2"></div></div><label class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_active" value="1" class="ui-checkbox" @checked($announcement->is_active)> Pengumuman aktif</label><button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Simpan perubahan</button></form><div class="flex justify-end border-t border-[#edf2f4] pt-3"><form method="POST" action="{{ route('admin.announcements.status', [$announcement, $announcement->is_active ? 'deactivate' : 'activate']) }}" @if ($announcement->is_active) data-swal-confirm="Nonaktifkan pengumuman ini?" @endif>@csrf<button type="submit" class="ui-btn {{ $announcement->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !text-xs">{{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div></div>
                    </details>
                @empty
                    <div class="ui-empty">Belum ada pengumuman. Buat informasi pertama dari formulir di sebelah kiri.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
