@extends('layouts.app')

@php
    $now = now();
    $activeCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at <= $now && ($announcement->ends_at === null || $announcement->ends_at >= $now))->count();
    $scheduledCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at > $now)->count();
    $isCreateError = ! filled(old('_announcement_id')) && $errors->any();
@endphp

@section('title', 'Pengumuman — '.$branding['application_name'])
@section('header_kicker', 'Pusat informasi')
@section('header_title', 'Pengumuman')

@section('content')
    <x-page-header
        eyebrow="Komunikasi layanan"
        title="Pengumuman layanan TI"
        description="Sampaikan kabar penting di dasbor dan sebelum pengguna membuat tiket. Atur waktunya agar pesan tetap relevan."
    />

    <section class="ui-announcement-hero mt-6" aria-labelledby="announcement-hero-heading">
        <div class="relative z-10 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(13rem,16rem)] lg:items-center">
            <div>
                <p class="ui-announcement-hero-label">Papan informasi global</p>
                <h2 id="announcement-hero-heading" class="ui-announcement-hero-title">Satu pesan, satu konteks, waktu tampil yang jelas.</h2>
                <p class="ui-announcement-hero-copy">Gunakan untuk gangguan, pemeliharaan, atau perubahan layanan. Tulis inti informasinya, lalu biarkan jadwal membantu pesan berhenti pada waktunya.</p>
            </div>
            <aside class="ui-announcement-signal" aria-label="Panduan singkat menulis pengumuman">
                <p class="ui-announcement-signal-label">Pesan yang mudah dipindai</p>
                <ul class="ui-announcement-signal-list">
                    <li>Mulai dari hal yang perlu diketahui</li>
                    <li>Sebutkan tindakan bila ada</li>
                    <li>Tentukan kapan pesan selesai</li>
                </ul>
            </aside>
        </div>
    </section>

    <section class="mt-5 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan pengumuman">
        <article class="ui-stat-card ui-announcement-stat">
            <span class="ui-stat-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M18.5 10a3 3 0 0 1 0 4" /></svg></span>
            <div><p class="ui-stat-label">Tampil sekarang</p><p class="ui-stat-value tabular-nums">{{ $activeCount }}</p><p class="ui-announcement-stat-meta">Terlihat sesuai jadwal aktif.</p></div>
        </article>
        <article class="ui-stat-card ui-announcement-stat">
            <span class="ui-stat-icon !bg-[color:var(--tm-warning-50)] !text-[color:var(--tm-warning-700)]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="5" width="15" height="14" rx="1.5" /><path stroke-linecap="round" d="M8 3.5v3M16 3.5v3M7.5 10h9M8 14h3" /></svg></span>
            <div><p class="ui-stat-label">Menunggu jadwal</p><p class="ui-stat-value tabular-nums">{{ $scheduledCount }}</p><p class="ui-announcement-stat-meta">Akan tampil otomatis saat waktunya tiba.</p></div>
        </article>
        <article class="ui-stat-card ui-announcement-stat">
            <span class="ui-stat-icon !bg-[color:var(--tm-info-50)] !text-[color:var(--tm-info-700)]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 5h12M6 12h12M6 19h8" /><path stroke-linecap="round" d="M4 5h.01M4 12h.01M4 19h.01" /></svg></span>
            <div><p class="ui-stat-label">Semua pengumuman</p><p class="ui-stat-value tabular-nums">{{ $announcements->count() }}</p><p class="ui-announcement-stat-meta">Termasuk yang nonaktif dan selesai.</p></div>
        </article>
    </section>

    <section class="mt-6 grid items-start gap-5 xl:grid-cols-[minmax(20rem,0.82fr)_minmax(0,1.18fr)]">
        <form id="announcement-create" method="POST" action="{{ route('admin.announcements.store') }}" class="ui-panel overflow-hidden" aria-labelledby="announcement-create-heading">
            @csrf
            <div class="ui-panel-header">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[color:var(--tm-brand-400)] !shadow-[0_0_0_4px_var(--tm-brand-100)]" aria-hidden="true"></span>Mulai dari sini</p>
                <h2 id="announcement-create-heading" class="mt-2 ui-section-title">Tulis pengumuman baru</h2>
                <p class="ui-section-description">Jelaskan apa yang perlu diketahui, kapan mulai terlihat, dan kapan selesai ditampilkan.</p>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <div class="flex items-start gap-2.5 rounded-[var(--tm-r-md)] border border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] px-3.5 py-3 text-xs leading-5 text-[color:var(--tm-brand-800)]">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9V15h7v-1.1A6 6 0 0 0 12 3Z" /></svg>
                    <span><span class="font-extrabold">Tip cepat:</span> satu pengumuman sebaiknya menyampaikan satu kabar utama.</span>
                </div>

                <div>
                    <label for="announcement-title" class="ui-field-label">Judul <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                    <input id="announcement-title" name="title" type="text" value="{{ old('title') }}" maxlength="150" required class="ui-input mt-2" placeholder="Contoh: Pemeliharaan jaringan malam ini" aria-describedby="announcement-title-help @if ($isCreateError && $errors->has('title')) announcement-title-error @endif" @if ($isCreateError && $errors->has('title')) aria-invalid="true" @endif>
                    <p id="announcement-title-help" class="ui-field-help">Mulai dengan inti informasinya. Maksimal 150 karakter.</p>
                    @if ($isCreateError)
                        @error('title')<x-field-error id="announcement-title-error" :message="$message" />@enderror
                    @endif
                </div>

                <div>
                    <label for="announcement-body" class="ui-field-label">Isi pengumuman <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                    <textarea id="announcement-body" name="body" rows="5" maxlength="5000" required class="ui-textarea mt-2" placeholder="Tulis apa yang terjadi dan apa yang perlu dilakukan pengguna." aria-describedby="announcement-body-help @if ($isCreateError && $errors->has('body')) announcement-body-error @endif" @if ($isCreateError && $errors->has('body')) aria-invalid="true" @endif>{{ old('body') }}</textarea>
                    <p id="announcement-body-help" class="ui-field-help">Gunakan kalimat pendek. Jika ada tindakan untuk pengguna, tulis dengan jelas.</p>
                    @if ($isCreateError)
                        @error('body')<x-field-error id="announcement-body-error" :message="$message" />@enderror
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="announcement-starts" class="ui-field-label">Mulai ditampilkan <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="announcement-starts" name="starts_at" type="datetime-local" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required class="ui-input mt-2" aria-describedby="announcement-starts-help @if ($isCreateError && $errors->has('starts_at')) announcement-starts-error @endif" @if ($isCreateError && $errors->has('starts_at')) aria-invalid="true" @endif>
                        <p id="announcement-starts-help" class="ui-field-help">Pesan mulai terlihat pada waktu ini.</p>
                        @if ($isCreateError)
                            @error('starts_at')<x-field-error id="announcement-starts-error" :message="$message" />@enderror
                        @endif
                    </div>
                    <div>
                        <label for="announcement-ends" class="ui-field-label">Berhenti ditampilkan <span class="font-normal text-[color:var(--tm-text-faint)]">(opsional)</span></label>
                        <input id="announcement-ends" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="ui-input mt-2" aria-describedby="announcement-ends-help @if ($isCreateError && $errors->has('ends_at')) announcement-ends-error @endif" @if ($isCreateError && $errors->has('ends_at')) aria-invalid="true" @endif>
                        <p id="announcement-ends-help" class="ui-field-help">Kosongkan jika belum ada batas akhir.</p>
                        @if ($isCreateError)
                            @error('ends_at')<x-field-error id="announcement-ends-error" :message="$message" />@enderror
                        @endif
                    </div>
                </div>

                <div>
                    <label class="flex min-h-12 cursor-pointer items-start gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5 text-xs text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-300)] has-[:checked]:bg-[color:var(--tm-brand-50)]">
                        <input type="checkbox" name="is_active" value="1" @checked($isCreateError ? old('is_active') === '1' : true) class="ui-checkbox mt-0.5" @if ($isCreateError && $errors->has('is_active')) aria-invalid="true" aria-describedby="announcement-active-error" @endif>
                        <span><span class="block font-extrabold text-[color:var(--tm-text)]">Aktifkan sesuai jadwal</span><span class="mt-0.5 block leading-5 text-[color:var(--tm-text-muted)]">Pengumuman akan tampil hanya dalam rentang waktu yang Anda tentukan.</span></span>
                    </label>
                    @if ($isCreateError)
                        @error('is_active')<x-field-error id="announcement-active-error" :message="$message" />@enderror
                    @endif
                </div>

                <button type="submit" class="ui-btn ui-btn-primary w-full">Simpan pengumuman</button>

                <div class="border-t border-[color:var(--tm-border-subtle)] pt-4">
                    <p class="text-xs font-extrabold text-[color:var(--tm-text-secondary)]">Akan tampil di mana?</p>
                    <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Pengumuman aktif muncul di dasbor dan di atas formulir pembuatan tiket.</p>
                </div>
            </div>
        </form>

        <section class="ui-panel overflow-hidden" aria-labelledby="announcement-list-heading">
            <div class="ui-panel-header flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[color:var(--tm-warning-600)] !shadow-[0_0_0_4px_var(--tm-warning-100)]" aria-hidden="true"></span>Yang sudah dibuat</p>
                    <h2 id="announcement-list-heading" class="mt-2 ui-section-title">Daftar pengumuman</h2>
                    <p class="ui-section-description">Buka item untuk membaca isi, mengubah jadwal, atau mengubah statusnya.</p>
                </div>
                <span class="ui-announcement-count tabular-nums">{{ $announcements->count() }} total</span>
            </div>

            <div class="space-y-3 p-4 sm:p-5">
                @forelse ($announcements as $announcement)
                    @php
                        $isCurrentlyVisible = $announcement->is_active && $announcement->starts_at <= $now && ($announcement->ends_at === null || $announcement->ends_at >= $now);
                        $isScheduled = $announcement->is_active && $announcement->starts_at > $now;
                        $statusLabel = $isCurrentlyVisible ? 'Tampil sekarang' : ($isScheduled ? 'Terjadwal' : ($announcement->is_active ? 'Masa tampil selesai' : 'Nonaktif'));
                        $statusClass = $isCurrentlyVisible ? 'ui-status-active' : ($isScheduled ? 'ui-status-warning' : 'ui-status-inactive');
                        $isEditError = (string) old('_announcement_id') === (string) $announcement->id;
                        $startsAtLabel = $announcement->starts_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i');
                        $endsAtLabel = $announcement->ends_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i');
                    @endphp

                    <details class="ui-announcement-item" @if ($isEditError) open @endif>
                        <summary class="ui-disclosure-summary flex items-start justify-between gap-3 p-4 sm:p-5">
                            <span class="flex min-w-0 items-start gap-3">
                                <span class="ui-announcement-item-marker" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M18.5 10a3 3 0 0 1 0 4M8 16.5 9.5 20h2L10 16.5" /></svg></span>
                                <span class="min-w-0">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="block max-w-full truncate text-sm font-extrabold text-[color:var(--tm-text)]">{{ $announcement->title }}</span>
                                        <span class="ui-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </span>
                                    <span class="mt-1 block text-[0.68rem] leading-5 tabular-nums text-[color:var(--tm-text-faint)]">Mulai {{ $startsAtLabel }} <span aria-hidden="true">&middot;</span> {{ $endsAtLabel ? 'Berakhir '.$endsAtLabel : 'Tanpa batas akhir' }} <span aria-hidden="true">&middot;</span> {{ $announcement->creator?->name ?? 'Pembuat tidak tersedia' }}</span>
                                </span>
                            </span>
                        </summary>

                        <div class="border-t border-[color:var(--tm-border-subtle)] p-4 sm:p-5">
                            <div class="ui-announcement-preview">
                                <p class="ui-announcement-preview-label">Pesan yang akan dibaca pengguna</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $announcement->body }}</p>
                            </div>

                            <div class="mt-5 border-t border-[color:var(--tm-border-subtle)] pt-5">
                                <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[color:var(--tm-brand-400)] !shadow-[0_0_0_4px_var(--tm-brand-100)]" aria-hidden="true"></span>Atur kembali</p>
                                <h3 class="mt-2 text-base font-extrabold tracking-tight text-[color:var(--tm-text)]">Perbarui pengumuman</h3>
                                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Perubahan jadwal dan isi tersimpan sebagai pengaturan terbaru untuk pesan ini.</p>

                                <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="mt-4 space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_announcement_id" value="{{ $announcement->id }}">

                                    <div>
                                        <label for="edit-announcement-title-{{ $announcement->id }}" class="ui-field-label">Judul <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                                        <input id="edit-announcement-title-{{ $announcement->id }}" name="title" type="text" value="{{ $isEditError ? old('title') : $announcement->title }}" maxlength="150" required class="ui-input mt-2" @if ($isEditError && $errors->has('title')) aria-invalid="true" aria-describedby="edit-announcement-title-error-{{ $announcement->id }}" @endif>
                                        @if ($isEditError)
                                            @error('title')<x-field-error id="edit-announcement-title-error-{{ $announcement->id }}" :message="$message" />@enderror
                                        @endif
                                    </div>

                                    <div>
                                        <label for="edit-announcement-body-{{ $announcement->id }}" class="ui-field-label">Isi pengumuman <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                                        <textarea id="edit-announcement-body-{{ $announcement->id }}" name="body" rows="4" maxlength="5000" required class="ui-textarea mt-2" @if ($isEditError && $errors->has('body')) aria-invalid="true" aria-describedby="edit-announcement-body-error-{{ $announcement->id }}" @endif>{{ $isEditError ? old('body') : $announcement->body }}</textarea>
                                        @if ($isEditError)
                                            @error('body')<x-field-error id="edit-announcement-body-error-{{ $announcement->id }}" :message="$message" />@enderror
                                        @endif
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="edit-announcement-starts-{{ $announcement->id }}" class="ui-field-label">Mulai ditampilkan <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                                            <input id="edit-announcement-starts-{{ $announcement->id }}" name="starts_at" type="datetime-local" value="{{ $isEditError ? old('starts_at') : $announcement->starts_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') }}" required class="ui-input mt-2" @if ($isEditError && $errors->has('starts_at')) aria-invalid="true" aria-describedby="edit-announcement-starts-error-{{ $announcement->id }}" @endif>
                                            @if ($isEditError)
                                                @error('starts_at')<x-field-error id="edit-announcement-starts-error-{{ $announcement->id }}" :message="$message" />@enderror
                                            @endif
                                        </div>
                                        <div>
                                            <label for="edit-announcement-ends-{{ $announcement->id }}" class="ui-field-label">Berhenti ditampilkan <span class="font-normal text-[color:var(--tm-text-faint)]">(opsional)</span></label>
                                            <input id="edit-announcement-ends-{{ $announcement->id }}" name="ends_at" type="datetime-local" value="{{ $isEditError ? old('ends_at') : $announcement->ends_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') }}" class="ui-input mt-2" @if ($isEditError && $errors->has('ends_at')) aria-invalid="true" aria-describedby="edit-announcement-ends-error-{{ $announcement->id }}" @endif>
                                            @if ($isEditError)
                                                @error('ends_at')<x-field-error id="edit-announcement-ends-error-{{ $announcement->id }}" :message="$message" />@enderror
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <label class="flex min-h-12 cursor-pointer items-start gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5 text-xs text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-300)] has-[:checked]:bg-[color:var(--tm-brand-50)]">
                                            <input type="checkbox" name="is_active" value="1" @checked($isEditError ? old('is_active') === '1' : $announcement->is_active) class="ui-checkbox mt-0.5" @if ($isEditError && $errors->has('is_active')) aria-invalid="true" aria-describedby="edit-announcement-active-error-{{ $announcement->id }}" @endif>
                                            <span><span class="block font-extrabold text-[color:var(--tm-text)]">Aktifkan sesuai jadwal</span><span class="mt-0.5 block leading-5 text-[color:var(--tm-text-muted)]">Matikan sementara jika pesan belum perlu terlihat atau sudah tidak relevan.</span></span>
                                        </label>
                                        @if ($isEditError)
                                            @error('is_active')<x-field-error id="edit-announcement-active-error-{{ $announcement->id }}" :message="$message" />@enderror
                                        @endif
                                    </div>

                                    <button type="submit" class="ui-btn ui-btn-primary !min-h-10 !text-xs">Simpan perubahan</button>
                                </form>

                                <div class="mt-5 flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-xs leading-5 text-[color:var(--tm-text-muted)]">{{ $announcement->is_active ? 'Nonaktifkan sementara tanpa menghapus isi dan jadwal.' : 'Aktifkan kembali agar jadwalnya dapat bekerja.' }}</p>
                                    <form method="POST" action="{{ route('admin.announcements.status', [$announcement, $announcement->is_active ? 'deactivate' : 'activate']) }}" @if ($announcement->is_active) data-swal-confirm="Nonaktifkan pengumuman ini? Pengguna tidak akan melihatnya sampai Anda mengaktifkannya kembali." @endif>
                                        @csrf
                                        <button type="submit" class="ui-btn {{ $announcement->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-10 !text-xs">{{ $announcement->is_active ? 'Nonaktifkan sementara' : 'Aktifkan kembali' }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </details>
                @empty
                    <x-empty-state title="Belum ada pengumuman." description="Tulis informasi pertama untuk memberi konteks sebelum pengguna membuat tiket. Pengumuman aktif juga akan tampil di atas formulir tiket." />
                @endforelse
            </div>
        </section>
    </section>
@endsection
