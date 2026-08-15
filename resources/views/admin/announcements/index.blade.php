@extends('layouts.app')

@php
    $now = now();
    $activeCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at <= $now && ($announcement->ends_at === null || $announcement->ends_at >= $now))->count();
    $scheduledCount = $announcements->filter(fn ($announcement) => $announcement->is_active && $announcement->starts_at > $now)->count();
    $totalCount = $announcements->count();
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

    <!-- Compact Operational Summary -->
    <section class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3" aria-label="Ringkasan pengumuman">
        <article class="flex items-center gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-4 py-3 shadow-[var(--tm-sh-xs)]">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-success-50)] text-[color:var(--tm-success-700)]" aria-hidden="true">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M18.5 10a3 3 0 0 1 0 4" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold uppercase tracking-wider text-[color:var(--tm-text-muted)]">Tampil Sekarang</p>
                <p class="text-lg font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $activeCount }}</p>
            </div>
        </article>

        <article class="flex items-center gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-4 py-3 shadow-[var(--tm-sh-xs)]">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-warning-50)] text-[color:var(--tm-warning-700)]" aria-hidden="true">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4.5" y="5" width="15" height="14" rx="1.5" /><path stroke-linecap="round" d="M8 3.5v3M16 3.5v3M7.5 10h9M8 14h3" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold uppercase tracking-wider text-[color:var(--tm-text-muted)]">Menunggu Jadwal</p>
                <p class="text-lg font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $scheduledCount }}</p>
            </div>
        </article>

        <article class="flex items-center gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-4 py-3 shadow-[var(--tm-sh-xs)]">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]" aria-hidden="true">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 5h12M6 12h12M6 19h8" /><path stroke-linecap="round" d="M4 5h.01M4 12h.01M4 19h.01" /></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold uppercase tracking-wider text-[color:var(--tm-text-muted)]">Total Pengumuman</p>
                <p class="text-lg font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $totalCount }}</p>
            </div>
        </article>
    </section>

    <!-- Two-Column Workbench Section -->
    <section class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(20rem,0.85fr)_minmax(0,1.15fr)]">
        <!-- Create Form Column -->
        <form id="announcement-create" method="POST" action="{{ route('admin.announcements.store') }}" class="ui-panel overflow-hidden" aria-labelledby="announcement-create-heading">
            @csrf
            <div class="border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                <h2 id="announcement-create-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Tulis Pengumuman Baru</h2>
                <p class="mt-0.5 text-xs text-[color:var(--tm-text-muted)]">Sampaikan kabar penting yang relevan untuk pengguna.</p>
            </div>

            <div class="space-y-4 p-5 sm:p-6">
                <div class="flex items-start gap-2 rounded-[var(--tm-r-md)] border border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] px-3 py-2 text-xs text-[color:var(--tm-brand-800)]">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    <span>Satu pengumuman sebaiknya menyampaikan satu kabar utama.</span>
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
                    <textarea id="announcement-body" name="body" rows="4" maxlength="5000" required class="ui-textarea mt-2" placeholder="Tulis apa yang terjadi dan apa yang perlu dilakukan pengguna." aria-describedby="announcement-body-help @if ($isCreateError && $errors->has('body')) announcement-body-error @endif" @if ($isCreateError && $errors->has('body')) aria-invalid="true" @endif>{{ old('body') }}</textarea>
                    <p id="announcement-body-help" class="ui-field-help">Gunakan kalimat pendek dan jelas.</p>
                    @if ($isCreateError)
                        @error('body')<x-field-error id="announcement-body-error" :message="$message" />@enderror
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="announcement-starts" class="ui-field-label">Mulai ditampilkan <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="announcement-starts" name="starts_at" type="datetime-local" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required class="ui-input mt-2" aria-describedby="announcement-starts-help @if ($isCreateError && $errors->has('starts_at')) announcement-starts-error @endif" @if ($isCreateError && $errors->has('starts_at')) aria-invalid="true" @endif>
                        <p id="announcement-starts-help" class="ui-field-help">Waktu mulai terlihat.</p>
                        @if ($isCreateError)
                            @error('starts_at')<x-field-error id="announcement-starts-error" :message="$message" />@enderror
                        @endif
                    </div>
                    <div>
                        <label for="announcement-ends" class="ui-field-label">Berhenti ditampilkan <span class="font-normal text-[color:var(--tm-text-faint)]">(opsional)</span></label>
                        <input id="announcement-ends" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="ui-input mt-2" aria-describedby="announcement-ends-help @if ($isCreateError && $errors->has('ends_at')) announcement-ends-error @endif" @if ($isCreateError && $errors->has('ends_at')) aria-invalid="true" @endif>
                        <p id="announcement-ends-help" class="ui-field-help">Kosongkan jika tanpa batas akhir.</p>
                        @if ($isCreateError)
                            @error('ends_at')<x-field-error id="announcement-ends-error" :message="$message" />@enderror
                        @endif
                    </div>
                </div>

                <div>
                    <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5 text-xs text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-300)] has-[:checked]:bg-[color:var(--tm-brand-50)]">
                        <input type="checkbox" name="is_active" value="1" @checked($isCreateError ? old('is_active') === '1' : true) class="ui-checkbox mt-0.5" @if ($isCreateError && $errors->has('is_active')) aria-invalid="true" aria-describedby="announcement-active-error" @endif>
                        <span>
                            <span class="block font-bold text-[color:var(--tm-text)]">Aktifkan sesuai jadwal</span>
                            <span class="mt-0.5 block text-[0.68rem] leading-4 text-[color:var(--tm-text-muted)]">Pesan hanya tampil dalam rentang waktu yang ditentukan.</span>
                        </span>
                    </label>
                    @if ($isCreateError)
                        @error('is_active')<x-field-error id="announcement-active-error" :message="$message" />@enderror
                    @endif
                </div>

                <button type="submit" class="ui-btn ui-btn-primary w-full">Simpan pengumuman</button>

                <div class="border-t border-[color:var(--tm-border-subtle)] pt-3.5">
                    <p class="text-xs font-bold text-[color:var(--tm-text-secondary)]">Akan tampil di mana?</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-[color:var(--tm-text-muted)]">Pengumuman aktif muncul di dasbor dan di atas formulir pembuatan tiket.</p>
                </div>
            </div>
        </form>

        <!-- Announcement List Column -->
        <section class="ui-panel overflow-hidden" aria-labelledby="announcement-list-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                <div>
                    <h2 id="announcement-list-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Daftar Pengumuman</h2>
                    <p class="mt-0.5 text-xs text-[color:var(--tm-text-muted)]">Buka item untuk melihat detail, mengubah jadwal, atau status.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-[color:var(--tm-brand-50)] px-2.5 py-0.5 text-xs font-bold text-[color:var(--tm-brand-700)] tabular-nums">{{ $announcements->count() }} total</span>
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

                    <details class="ui-announcement-item group rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] transition-all duration-[var(--tm-dur-fast)] open:shadow-[var(--tm-sh-sm)]" @if ($isEditError) open @endif>
                        <summary class="ui-disclosure-summary flex cursor-pointer list-none items-start justify-between gap-3 p-4 select-none sm:p-5">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[var(--tm-r-md)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)] transition-transform duration-[var(--tm-dur-fast)] group-open:rotate-90" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" /></svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-[color:var(--tm-text)] sm:text-base">{{ $announcement->title }}</h3>
                                        <span class="ui-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-[color:var(--tm-text-faint)] tabular-nums">
                                        Mulai {{ $startsAtLabel }} <span aria-hidden="true">&middot;</span> {{ $endsAtLabel ? 'Berakhir '.$endsAtLabel : 'Tanpa batas akhir' }} <span aria-hidden="true">&middot;</span> {{ $announcement->creator?->name ?? 'Pembuat tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </summary>

                        <div class="border-t border-[color:var(--tm-border-subtle)] p-4 sm:p-5">
                            <!-- Preview body -->
                            <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-3.5 sm:p-4">
                                <p class="text-[0.68rem] font-bold uppercase tracking-wider text-[color:var(--tm-text-muted)]">Pesan yang akan dibaca pengguna</p>
                                <p class="mt-2 whitespace-pre-line text-xs sm:text-sm leading-relaxed text-[color:var(--tm-text-secondary)]">{{ $announcement->body }}</p>
                            </div>

                            <!-- Edit form -->
                            <div class="mt-5 border-t border-[color:var(--tm-border-subtle)] pt-5">
                                <h4 class="text-sm font-bold text-[color:var(--tm-text)]">Perbarui Pengumuman</h4>
                                <p class="mt-0.5 text-xs text-[color:var(--tm-text-muted)]">Perubahan jadwal dan isi tersimpan sebagai pengaturan terbaru untuk pesan ini.</p>

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
                                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5 text-xs text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-300)] has-[:checked]:bg-[color:var(--tm-brand-50)]">
                                            <input type="checkbox" name="is_active" value="1" @checked($isEditError ? old('is_active') === '1' : $announcement->is_active) class="ui-checkbox mt-0.5" @if ($isEditError && $errors->has('is_active')) aria-invalid="true" aria-describedby="edit-announcement-active-error-{{ $announcement->id }}" @endif>
                                            <span>
                                                <span class="block font-bold text-[color:var(--tm-text)]">Aktifkan sesuai jadwal</span>
                                                <span class="mt-0.5 block text-[0.68rem] leading-4 text-[color:var(--tm-text-muted)]">Matikan sementara jika pesan belum perlu terlihat atau sudah tidak relevan.</span>
                                            </span>
                                        </label>
                                        @if ($isEditError)
                                            @error('is_active')<x-field-error id="edit-announcement-active-error-{{ $announcement->id }}" :message="$message" />@enderror
                                        @endif
                                    </div>

                                    <button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs font-semibold">Simpan perubahan</button>
                                </form>

                                <div class="mt-5 flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-xs text-[color:var(--tm-text-muted)]">{{ $announcement->is_active ? 'Nonaktifkan sementara tanpa menghapus isi dan jadwal.' : 'Aktifkan kembali agar jadwalnya dapat bekerja.' }}</p>
                                    <form method="POST" action="{{ route('admin.announcements.status', [$announcement, $announcement->is_active ? 'deactivate' : 'activate']) }}" @if ($announcement->is_active) data-swal-confirm="Nonaktifkan pengumuman ini? Pengguna tidak akan melihatnya sampai Anda mengaktifkannya kembali." @endif>
                                        @csrf
                                        <button type="submit" class="ui-btn {{ $announcement->is_active ? 'ui-btn-ghost !text-[color:var(--tm-warning-700)] hover:bg-[color:var(--tm-warning-50)]' : 'ui-btn-ghost !text-[color:var(--tm-success-700)] hover:bg-[color:var(--tm-success-50)]' }} !min-h-9 !text-xs font-semibold">
                                            {{ $announcement->is_active ? 'Nonaktifkan sementara' : 'Aktifkan kembali' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-8">
                        <x-empty-state title="Belum ada pengumuman." description="Tulis informasi pertama untuk memberi konteks sebelum pengguna membuat tiket. Pengumuman aktif juga akan tampil di atas formulir tiket." />
                    </div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
