@extends('layouts.app')

@section('title', 'Dasbor — SIHATI')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Dasbor</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Selamat datang, {{ $user->name }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Ringkasan akses Anda dan pintasan ke pekerjaan yang tersedia sesuai peran.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($user->roleLabels() as $roleLabel)
                <x-role-badge :label="$roleLabel" />
            @endforeach
        </div>
    </div>

    <section class="mt-8 grid gap-5 md:grid-cols-3" aria-label="Ringkasan akun">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Status akun</p>
            <p class="mt-3 text-xl font-semibold text-emerald-700">Aktif</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">Sesi Anda telah melewati pemeriksaan akses server.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Tiket ditugaskan</p>
            @if ($user->hasOperationalRole() && ! $requiresPasswordChange)
                <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $assignedTicketCount }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Tiket yang menjadi tanggung jawab Anda.</p>
            @elseif ($requiresPasswordChange)
                <p class="mt-3 text-xl font-semibold text-amber-700">Menunggu penggantian</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Data operasional tersedia setelah password diganti.</p>
            @else
                <p class="mt-3 text-xl font-semibold text-slate-700">Tidak berlaku</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Peran Super Admin saja tidak memberi kewenangan operasional.</p>
            @endif
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Antrean Baru</p>
            @if ($user->hasOperationalRole() && ! $requiresPasswordChange)
                <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $newTicketCount }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Tiket yang menunggu klaim Agen Tier 1.</p>
            @elseif ($requiresPasswordChange)
                <p class="mt-3 text-xl font-semibold text-amber-700">Terkunci sementara</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Antrean akan tersedia setelah password diganti.</p>
            @else
                <p class="mt-3 text-xl font-semibold text-slate-700">Terbatas</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Akses antrean membutuhkan peran operasional eksplisit.</p>
            @endif
        </article>
    </section>

    <section class="mt-8 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Akses yang tersedia</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">SIHATI menggabungkan hak dari role yang diberikan, tetapi setiap aksi tetap diperiksa di server.</p>
            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-sm font-semibold text-slate-800">Identitas</dt>
                    <dd class="mt-1 text-sm text-slate-500">{{ $user->username }}{{ $user->nip ? ' · '.$user->nip : '' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-sm font-semibold text-slate-800">Password</dt>
                    <dd class="mt-1 text-sm text-slate-500">Terakhir diperbarui {{ $user->password_changed_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'belum pernah' }}</dd>
                </div>
            </dl>
        </article>

        @if ($isSuperAdmin)
            <article class="rounded-2xl border border-cyan-200 bg-cyan-50 p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-cyan-800">Administrasi</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Kelola akses pengguna</h2>
                <p class="mt-2 text-sm leading-6 text-slate-700">Reset password dilakukan dengan alasan operasional dan wajib diikuti penggantian password oleh pengguna.</p>
                <a href="{{ route('admin.users.index') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">Buka daftar pengguna</a>
            </article>
        @else
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">Bantuan</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Perlu perubahan akses?</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Hubungi Super Admin. Penambahan role tidak dapat dilakukan dari sisi pengguna.</p>
            </article>
        @endif
    </section>

    @if ($requiresPasswordChange)
        <div
            data-mandatory-password-modal
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mandatory-password-title"
            aria-describedby="mandatory-password-description"
            tabindex="-1"
        >
            <div class="flex min-h-full items-center justify-center px-4 py-8 sm:px-6">
                <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm" aria-hidden="true"></div>

                <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="border-b border-slate-200 bg-slate-950 px-6 py-6 text-white sm:px-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-300">Keamanan akun</p>
                        <h2 id="mandatory-password-title" class="mt-2 text-2xl font-semibold tracking-tight">Ganti password untuk melanjutkan</h2>
                        <p id="mandatory-password-description" class="mt-3 text-sm leading-6 text-slate-300">Password awal wajib diganti sebelum Anda dapat menggunakan fitur SIHATI.</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5 p-6 sm:p-8">
                        @csrf
                        @method('PUT')
                        <x-form-field name="current_password" label="Password saat ini" type="password" autocomplete="current-password" autofocus required />
                        <x-form-field
                            name="password"
                            label="Password baru"
                            type="password"
                            autocomplete="new-password"
                            help="Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol."
                            required
                        />
                        <x-form-field name="password_confirmation" label="Konfirmasi password baru" type="password" autocomplete="new-password" required />

                        <button type="submit" class="w-full rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan password baru</button>
                    </form>

                    <div class="flex items-center justify-between gap-4 border-t border-slate-200 px-6 py-4 sm:px-8">
                        <p class="text-xs leading-5 text-slate-500">Modal ini tidak dapat ditutup sebelum password diganti.</p>
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
