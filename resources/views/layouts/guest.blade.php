<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $branding['application_name'])</title>
    @vite(['resources/css/app.css', 'resources/css/theme-modern.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-slate-900 antialiased bg-[color:var(--tm-n-50)]">
    <a href="#main-content" class="ui-skip-link">Lewati ke konten utama</a>
    <div class="ui-login-shell flex min-h-screen">
        <div class="ui-login-accent w-1.5 shrink-0 bg-[color:var(--tm-brand-600)]" aria-hidden="true"></div>

        <main id="main-content" tabindex="-1" class="ui-login-main flex flex-1 min-w-0 justify-center items-center p-4 sm:p-8 focus:outline-none">
            <div class="ui-login-content w-full max-w-[26rem] rounded-2xl border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-6 sm:p-8 shadow-xl flex flex-col justify-center items-center">
                <div class="ui-login-inner w-full max-w-[20rem] mx-auto flex flex-col gap-6">
                    <a href="{{ url('/') }}" class="flex items-center justify-center gap-2.5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--tm-brand-500)] rounded-xl" aria-label="Beranda {{ $branding['application_name'] }}">
                        @if ($branding['logo_url'])
                            <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['organization_name'] }}" class="ui-guest-brand-logo">
                        @else
                            <span class="ui-brand-mark flex h-11 w-11 items-center justify-center rounded-xl bg-[color:var(--tm-brand-600)] text-sm font-bold text-white shrink-0" aria-hidden="true">{{ $branding['monogram'] }}</span>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="text-base font-extrabold tracking-tight text-[color:var(--tm-text)] leading-tight">{{ $branding['application_name'] }}</span>
                            <span class="text-xs font-semibold text-[color:var(--tm-text-muted)] truncate leading-tight">{{ $branding['organization_name'] }}</span>
                        </div>
                    </a>

                    @include('components.flash')

                    @yield('content')

                    <footer class="ui-login-footer mt-4 border-t border-[color:var(--tm-border-subtle)] pt-4 text-center text-xs text-[color:var(--tm-text-muted)]">
                        <p>&copy; {{ now()->year }} {{ $branding['organization_name'] }}. Hak cipta dilindungi.</p>
                    </footer>
                </div>
            </div>
        </main>
    </div>

    @include('components.global-loading-overlay')
</body>
</html>
