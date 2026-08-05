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
    @php($currentUser = auth()->user()->loadMissing('roles'))
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-4 sm:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="Dasbor SIHATI">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 font-bold text-cyan-300">SI</span>
                <span class="hidden text-base font-semibold tracking-wide sm:inline">SIHATI</span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-950' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Dasbor</a>
                @if ($currentUser->hasRole(\App\Enums\Role::SuperAdmin))
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-100 text-slate-950' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Pengguna</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-slate-100 text-slate-950' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Audit</a>
                @endif
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-900">{{ $currentUser->name }}</p>
                    <p class="text-xs text-slate-500">{{ $currentUser->username }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Keluar</button>
                </form>
            </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-3 lg:hidden sm:px-8">
            <details>
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Buka navigasi</summary>
                <nav class="mt-3 grid gap-1" aria-label="Navigasi mobile">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-950' : 'text-slate-600' }}">Dasbor</a>
                    @if ($currentUser->hasRole(\App\Enums\Role::SuperAdmin))
                        <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600">Pengguna</a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600">Audit</a>
                    @endif
                </nav>
            </details>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
        @include('components.flash')
        @yield('content')
    </main>
</body>
</html>
