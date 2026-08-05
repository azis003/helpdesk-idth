<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIHATI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-[0.86fr_1.14fr]">
        <aside class="hidden bg-slate-950 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3" aria-label="Beranda SIHATI">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 font-bold text-slate-950">SI</span>
                    <span class="text-lg font-semibold tracking-wide">SIHATI</span>
                </a>
                <div class="mt-24 max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Helpdesk internal</p>
                    <h1 class="mt-5 text-4xl font-semibold leading-tight">Satu tempat untuk layanan TI yang tertata.</h1>
                    <p class="mt-6 text-base leading-7 text-slate-300">Ajukan, pantau, dan tangani kebutuhan layanan TI dengan alur yang jelas serta akses yang bertanggung jawab.</p>
                </div>
            </div>
        </aside>

        <main class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 font-bold text-cyan-300">SI</span>
                    <span class="font-semibold tracking-wide">SIHATI</span>
                </div>

                @include('components.flash')

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
