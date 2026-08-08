<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $branding['application_name'])</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-slate-900 antialiased">
    <div class="ui-login-shell">
        <div class="ui-login-accent" aria-hidden="true"></div>

        <main class="ui-login-main">
            <div class="ui-login-content">
                <a href="{{ url('/') }}" class="ui-login-logo" aria-label="Beranda {{ $branding['application_name'] }}">
                    @if ($branding['logo_url'])
                        <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['organization_name'] }}" class="max-h-16 max-w-[16rem] object-contain object-left">
                    @else
                        <span class="ui-login-wordmark">
                            <span class="ui-login-wordmark-accent" aria-hidden="true"></span>
                            {{ $branding['application_name'] }}
                        </span>
                    @endif
                    <span class="ui-login-logo-caption">{{ $branding['organization_name'] }}{{ $branding['tagline'] ? ' · '.$branding['tagline'] : '' }}</span>
                </a>

                @include('components.flash')

                @yield('content')

                <footer class="ui-login-footer">
                    <p>{{ $branding['organization_name'] }} · {{ $branding['application_name'] }}</p>
                    <p>{{ $branding['footer_text'] }}</p>
                    <p>Gunakan akun internal yang telah diberikan kepada Anda.</p>
                </footer>
            </div>
        </main>
    </div>

    @include('components.global-loading-overlay')
</body>
</html>
