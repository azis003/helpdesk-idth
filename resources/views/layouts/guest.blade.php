<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIHATI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-slate-900 antialiased">
    <div class="ui-login-shell">
        <div class="ui-login-accent" aria-hidden="true"></div>

        <main class="ui-login-main">
            <div class="ui-login-content">
                <a href="{{ url('/') }}" class="ui-login-logo" aria-label="Beranda SIHATI">
                    <span class="ui-login-wordmark">
                        <span class="ui-login-wordmark-accent" aria-hidden="true"></span>
                        SIHATI
                    </span>
                    <span class="ui-login-logo-caption">Portal Layanan TI</span>
                </a>

                @include('components.flash')

                @yield('content')

                <footer class="ui-login-footer">
                    <p>SIHATI - Portal Layanan TI internal</p>
                    <p>Gunakan akun internal yang telah diberikan kepada Anda.</p>
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
