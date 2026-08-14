<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak — {{ $branding['application_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/css/theme-modern.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-[color:var(--tm-n-50)] text-slate-900">
    <a href="#main-content" class="ui-skip-link">Lewati ke konten utama</a>
    
    <div class="ui-login-shell flex min-h-screen">
        <div class="ui-login-accent w-1.5 shrink-0 bg-[color:var(--tm-danger-600)]" aria-hidden="true"></div>

        <main id="main-content" tabindex="-1" class="ui-login-main flex flex-1 min-w-0 justify-center items-center p-4 sm:p-8 focus:outline-none">
            <div class="ui-login-content w-full max-w-[28rem] rounded-2xl border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-6 sm:p-10 shadow-xl flex flex-col items-center text-center gap-6">
                
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-600)] ring-1 ring-[color:var(--tm-danger-100)]" aria-hidden="true">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="4.5" y="10.5" width="15" height="10" rx="2.5" />
                        <path stroke-linecap="round" d="M8 10.5V7.75a4 4 0 0 1 8 0V10.5" />
                        <path stroke-linecap="round" d="M12 14.5v2.5" />
                    </svg>
                </span>

                <div class="space-y-1.5">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-[color:var(--tm-danger-600)]">403 · Akses ditolak</p>
                    <h1 class="text-xl font-extrabold tracking-tight text-[color:var(--tm-text)]">Aksi ini tidak tersedia untuk akun Anda.</h1>
                    <p class="text-sm leading-relaxed text-[color:var(--tm-text-secondary)]">Hak akses diperiksa di server berdasarkan status akun, role, dan aturan domain.</p>
                </div>

                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ui-btn ui-btn-primary w-full justify-center">
                    {{ auth()->check() ? 'Kembali ke dasbor' : 'Kembali ke login' }}
                </a>

                <footer class="ui-login-footer w-full mt-4 border-t border-[color:var(--tm-border-subtle)] pt-4 text-center text-xs text-[color:var(--tm-text-muted)]">
                    <p>&copy; {{ now()->year }} {{ $branding['organization_name'] }}. Hak cipta dilindungi.</p>
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
