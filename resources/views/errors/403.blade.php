<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses ditolak — SIHATI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-5 py-12">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-rose-700">403 · Akses ditolak</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Aksi ini tidak tersedia untuk akun Anda.</h1>
            <p class="mt-4 text-sm leading-6 text-slate-600">Hak akses diperiksa di server berdasarkan status akun, role, dan aturan domain.</p>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="mt-7 inline-flex rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">{{ auth()->check() ? 'Kembali ke dasbor' : 'Kembali ke login' }}</a>
        </section>
    </main>
</body>
</html>
