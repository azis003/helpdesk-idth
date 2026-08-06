@extends('layouts.app')

@section('title', 'Dasbor — SIHATI')
@section('header_kicker', 'Ruang kerja')
@section('header_title', 'Portal layanan SIHATI')

@section('content')
    <section class="ui-portal-hero" aria-labelledby="dashboard-title">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.16em] text-[#ffe98f]">Portal layanan SIHATI</p>
                <h1 id="dashboard-title" class="mt-2 text-2xl font-extrabold tracking-[-0.04em] sm:text-3xl">Selamat datang, {{ $user->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#d7e8ed]">Pilih area kerja yang ingin Anda buka atau lihat ringkasan akses Anda.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($user->roleLabels() as $roleLabel)
                    <span class="inline-flex items-center rounded-full border border-[#79bfd4] bg-[#376b81] px-2.5 py-1 text-xs font-bold text-white">{{ $roleLabel }}</span>
                @endforeach
            </div>
        </div>
    </section>

    @if ($isSuperAdmin)
        <section class="mt-7" aria-labelledby="shortcuts-heading">
            <div class="flex items-end justify-between gap-4">
                <div><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Akses cepat</p><h2 id="shortcuts-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Pusat administrasi</h2></div>
                <span class="hidden text-xs font-bold text-[#86979e] sm:block">4 area tersedia</span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.users.index') }}" class="ui-catalog-card group">
                    <span class="ui-catalog-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" /></svg></span>
                    <span class="mt-4 flex items-center justify-between gap-2"><span><span class="block text-sm font-extrabold text-[#263a43]">Pengguna</span><span class="mt-1 block text-xs leading-5 text-[#84959c]">Identitas dan hak akses</span></span><span class="text-lg text-[#56bedf] transition-transform group-hover:translate-x-1" aria-hidden="true">→</span></span>
                </a>
                <a href="{{ route('admin.teams.index') }}" class="ui-catalog-card group">
                    <span class="ui-catalog-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8M3.5 9.5h17" /></svg></span>
                    <span class="mt-4 flex items-center justify-between gap-2"><span><span class="block text-sm font-extrabold text-[#263a43]">Tim kerja</span><span class="mt-1 block text-xs leading-5 text-[#84959c]">Struktur dan anggota</span></span><span class="text-lg text-[#56bedf] transition-transform group-hover:translate-x-1" aria-hidden="true">→</span></span>
                </a>
                <a href="{{ route('admin.skills.index') }}" class="ui-catalog-card group">
                    <span class="ui-catalog-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg></span>
                    <span class="mt-4 flex items-center justify-between gap-2"><span><span class="block text-sm font-extrabold text-[#263a43]">Data keahlian</span><span class="mt-1 block text-xs leading-5 text-[#84959c]">Keahlian dan kategori</span></span><span class="text-lg text-[#56bedf] transition-transform group-hover:translate-x-1" aria-hidden="true">→</span></span>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="ui-catalog-card group">
                    <span class="ui-catalog-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8" /></svg></span>
                    <span class="mt-4 flex items-center justify-between gap-2"><span><span class="block text-sm font-extrabold text-[#263a43]">Audit log</span><span class="mt-1 block text-xs leading-5 text-[#84959c]">Jejak perubahan sistem</span></span><span class="text-lg text-[#56bedf] transition-transform group-hover:translate-x-1" aria-hidden="true">→</span></span>
                </a>
            </div>
        </section>
    @endif

    <section class="mt-8" aria-labelledby="summary-heading">
        <div class="flex items-end justify-between gap-4"><div><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Ringkasan</p><h2 id="summary-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Status akses Anda</h2></div></div>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg></span><div><p class="ui-stat-label">Status akun</p><p class="mt-1 text-lg font-extrabold text-[#087f5b]">Aktif</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Sesi telah melewati pemeriksaan akses.</p></div></article>
            <article class="ui-stat-card items-start"><span class="ui-stat-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19v-8M12 19V5M19 19v-5" /></svg></span><div><p class="ui-stat-label">Tiket ditugaskan</p>@if ($user->hasOperationalRole() && ! $requiresPasswordChange)<p class="ui-stat-value">{{ $assignedTicketCount }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Tanggung jawab Anda.</p>@elseif ($requiresPasswordChange)<p class="mt-1 text-lg font-extrabold text-[#a16207]">Menunggu ganti</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Tersedia setelah password diganti.</p>@else<p class="mt-1 text-lg font-extrabold text-[#607681]">Tidak berlaku</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Membutuhkan role operasional.</p>@endif</div></article>
            <article class="ui-stat-card items-start"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M6.5 4.5h11a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" d="M8 12h8M8 15.5h5" /></svg></span><div><p class="ui-stat-label">Antrean baru</p>@if ($user->hasOperationalRole() && ! $requiresPasswordChange)<p class="ui-stat-value">{{ $newTicketCount }}</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Menunggu klaim Agen Tier 1.</p>@elseif ($requiresPasswordChange)<p class="mt-1 text-lg font-extrabold text-[#a16207]">Terkunci sementara</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Tersedia setelah password diganti.</p>@else<p class="mt-1 text-lg font-extrabold text-[#607681]">Terbatas</p><p class="mt-1 text-xs leading-5 text-[#78909a]">Membutuhkan role operasional.</p>@endif</div></article>
        </div>
    </section>

    <section class="mt-8 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
        <article class="ui-panel p-5 sm:p-6"><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Profil akses</p><h2 class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">Akses yang tersedia</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Hak akses digabungkan dari role yang diberikan. Setiap aksi tetap diperiksa kembali di server.</p><dl class="mt-6 grid gap-3 sm:grid-cols-2"><div class="rounded-lg bg-[#f7fafb] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Identitas</dt><dd class="mt-2 text-sm font-bold text-[#35505b]">{{ '@'.$user->username }}{{ $user->nip ? ' · '.$user->nip : '' }}</dd></div><div class="rounded-lg bg-[#f7fafb] p-4"><dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Password</dt><dd class="mt-2 text-sm font-bold text-[#35505b]">{{ $user->password_changed_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Belum pernah diperbarui' }}</dd></div></dl></article>
        @if ($isSuperAdmin)<article class="ui-panel ui-panel--accent p-5 sm:p-6"><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Administrasi</p><h2 class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">Butuh ruang administrasi?</h2><p class="mt-2 text-sm leading-6 text-[#52747b]">Semua pengelolaan akun dan struktur organisasi tersedia dari pintasan di atas.</p><a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-primary mt-5">Buka pengguna <span aria-hidden="true">→</span></a></article>@else<article class="ui-panel p-5 sm:p-6"><p class="ui-eyebrow !text-[#607681]"><span class="ui-eyebrow-dot !bg-[#94aab2] !shadow-[0_0_0_4px_#edf2f4]" aria-hidden="true"></span>Bantuan</p><h2 class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">Perlu perubahan akses?</h2><p class="mt-2 text-sm leading-6 text-[#6a8089]">Hubungi Super Admin. Penambahan role tidak dapat dilakukan dari sisi pengguna.</p></article>@endif
    </section>

    @if ($requiresPasswordChange)
        <div data-mandatory-password-modal class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="mandatory-password-title" aria-describedby="mandatory-password-description" tabindex="-1"><div class="flex min-h-full items-center justify-center px-4 py-8 sm:px-6"><div class="fixed inset-0 bg-[#243c48]/75" aria-hidden="true"></div><div class="relative w-full max-w-lg overflow-hidden rounded-xl border border-[#dce7eb] bg-white shadow-2xl"><div class="border-b border-[#e5ebee] bg-[#f8fafb] px-6 py-5 sm:px-8"><p class="text-[0.68rem] font-extrabold uppercase tracking-[0.16em] text-[#346478]">Keamanan akun</p><h2 id="mandatory-password-title" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Ganti password untuk melanjutkan</h2><p id="mandatory-password-description" class="mt-2 text-sm leading-6 text-[#6a8089]">Password awal wajib diganti sebelum Anda dapat menggunakan fitur SIHATI.</p></div><form method="POST" action="{{ route('password.update') }}" class="space-y-5 p-6 sm:p-8">@csrf @method('PUT')<x-form-field name="current_password" label="Password saat ini" type="password" autocomplete="current-password" autofocus required /><x-form-field name="password" label="Password baru" type="password" autocomplete="new-password" help="Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol." required /><x-form-field name="password_confirmation" label="Konfirmasi password baru" type="password" autocomplete="new-password" required /><button type="submit" class="ui-btn ui-btn-primary w-full !py-3.5">Simpan password baru</button></form><div class="flex items-center justify-between gap-4 border-t border-[#e7eef1] px-6 py-4 sm:px-8"><p class="text-xs leading-5 text-[#78909a]">Modal ini tidak dapat ditutup sebelum password diganti.</p><form method="POST" action="{{ route('logout') }}" class="shrink-0">@csrf<button type="submit" class="ui-action-link">Keluar</button></form></div></div></div></div>
    @endif
@endsection
