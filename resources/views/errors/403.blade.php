<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak — {{ $branding['application_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-error-page min-h-screen font-sans antialiased">
    <main class="ui-error-main mx-auto flex min-h-screen max-w-xl items-center px-5 py-12">
        <section class="ui-error-card w-full p-7 text-center sm:p-10">
            <p class="ui-error-eyebrow">403 · Akses ditolak</p>
            <h1 class="ui-error-title mt-3">Aksi ini tidak tersedia untuk akun Anda.</h1>
            <p class="ui-error-copy mt-4">Hak akses diperiksa di server berdasarkan status akun, role, dan aturan domain.</p>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="ui-error-action mt-7">{{ auth()->check() ? 'Kembali ke dasbor' : 'Kembali ke login' }}</a>
        </section>
    </main>
</body>
</html>
