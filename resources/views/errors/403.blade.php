<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak — {{ $branding['application_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/css/theme-modern.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-5 py-12">
        <section class="w-full rounded-[var(--tm-r-xl)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-7 text-center shadow-[var(--tm-sh-lg)] sm:p-10">
            <span class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-[var(--tm-r-full)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-600)] ring-1 ring-[color:var(--tm-danger-100)]" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <rect x="4.5" y="10.5" width="15" height="10" rx="2.5" />
                    <path stroke-linecap="round" d="M8 10.5V7.75a4 4 0 0 1 8 0V10.5" />
                    <path stroke-linecap="round" d="M12 14.5v2.5" />
                </svg>
            </span>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[color:var(--tm-danger-600)]">403 · Akses ditolak</p>
            <h1 class="mt-3 text-2xl font-extrabold tracking-[-0.02em] text-[color:var(--tm-text)] sm:text-3xl">Aksi ini tidak tersedia untuk akun Anda.</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-[color:var(--tm-text-muted)]">Hak akses diperiksa di server berdasarkan status akun, role, dan aturan domain.</p>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ui-btn ui-btn-primary mt-7">{{ auth()->check() ? 'Kembali ke dasbor' : 'Kembali ke login' }}</a>
        </section>
    </main>
</body>
</html>
