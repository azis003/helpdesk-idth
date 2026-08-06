<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIHATI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    @php
        $currentUser = auth()->user()->loadMissing('roles');
        $isSuperAdmin = $currentUser->hasRole(\App\Enums\Role::SuperAdmin);
    @endphp

    <div class="ui-shell lg:flex">
        <aside id="app-sidebar" data-sidebar class="ui-sidebar hidden shrink-0 flex-col px-4 py-5 lg:flex" aria-label="Navigasi utama">
            <a href="{{ route('dashboard') }}" class="ui-sidebar-brand" aria-label="Dasbor SIHATI" title="SIHATI">
                <span class="ui-brand-mark">SI</span>
                <span class="ui-brand-copy"><span class="block text-sm font-extrabold tracking-[0.08em] text-[#18252b]">SIHATI</span><span class="mt-0.5 block text-[0.64rem] text-[#829198]">Portal Layanan TI</span></span>
            </a>

            <nav class="mt-9 flex flex-col gap-2" aria-label="Navigasi utama">
                <p class="ui-sidebar-label">Ruang kerja</p>
                <a href="{{ route('dashboard') }}" class="ui-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" aria-label="Dasbor" title="Dasbor">
                    <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 9-8 9 8M5 10v10h5v-6h4v6h5V10" /></svg></span>
                    <span class="ui-nav-label">Dasbor</span>
                </a>
            </nav>

            @if ($isSuperAdmin)
                <nav class="mt-5 flex flex-col gap-2" aria-label="Navigasi administrasi">
                    <p class="ui-sidebar-label">Administrasi</p>
                    <a href="{{ route('admin.users.index') }}" class="ui-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" aria-label="Pengguna" title="Pengguna">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5.5-6.5a3 3 0 0 1 0 5.8M17 14.3a3.5 3.5 0 0 1 3 3.4V19" /></svg></span>
                        <span class="ui-nav-label">Pengguna</span>
                    </a>
                    <a href="{{ route('admin.teams.index') }}" class="ui-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}" aria-label="Tim kerja" title="Tim kerja">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8M3.5 9.5h17M8.5 12.5h.01M12 12.5h.01M15.5 12.5h.01M8.5 16h.01M12 16h.01M15.5 16h.01" /></svg></span>
                        <span class="ui-nav-label">Tim kerja</span>
                    </a>
                    <a href="{{ route('admin.skills.index') }}" class="ui-nav-link {{ request()->routeIs('admin.skills.*', 'admin.categories.*') ? 'is-active' : '' }}" aria-label="Data keahlian" title="Data keahlian">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /><path stroke-linecap="round" d="M4 20.2h16" /></svg></span>
                        <span class="ui-nav-label">Data keahlian</span>
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="ui-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}" aria-label="Audit log" title="Audit log">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8M8 18h5" /></svg></span>
                        <span class="ui-nav-label">Audit log</span>
                    </a>
                </nav>
            @endif

            <div class="ui-sidebar-account mt-auto">
                <span class="ui-avatar !h-9 !w-9 !rounded-full" title="{{ $currentUser->name }}">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                <span class="ui-account-copy min-w-0"><span class="block truncate text-xs font-bold text-[#344850]">{{ $currentUser->name }}</span><span class="mt-0.5 block truncate text-[0.65rem] text-[#89989e]">{{ $currentUser->username }}</span></span>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="ui-topbar sticky top-0 z-30 flex min-h-[4.5rem] items-center justify-between gap-4 px-4 sm:px-7 lg:px-9">
                <div class="flex items-center gap-3">
                    <button type="button" class="ui-menu-button hidden lg:inline-flex" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="true" aria-label="Sembunyikan menu" title="Sembunyikan menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg><span class="sr-only" data-sidebar-toggle-label>Sembunyikan menu</span></button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 lg:border-l lg:border-[#e5ebee] lg:pl-4" aria-label="Dasbor SIHATI">
                        <span class="ui-brand-mark !h-9 !w-9 !rounded-lg text-xs lg:hidden">SI</span>
                        <span class="text-lg font-extrabold tracking-[-0.05em] text-[#18252b]">SIHATI</span>
                        <span class="hidden text-sm text-[#718088] sm:inline">Portal Layanan TI</span>
                    </a>
                    <span class="hidden h-5 w-px bg-[#dfe7eb] lg:block" aria-hidden="true"></span>
                    <span class="hidden text-sm text-[#6c7c83] lg:inline">@yield('header_title', 'Ruang kerja')</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-bold text-[#344850]">{{ $currentUser->name }}</p>
                        <p class="mt-0.5 text-[0.68rem] text-[#89989e]">{{ $currentUser->username }}</p>
                    </div>
                    <span class="ui-avatar !h-9 !w-9 !rounded-full">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                    <span class="ui-header-divider hidden sm:block" aria-hidden="true"></span>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="ui-header-logout" aria-label="Keluar dari SIHATI" title="Keluar dari SIHATI">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4M14 8l4 4-4 4m4-4H9" /></svg>
                            <span class="sr-only">Keluar dari SIHATI</span>
                        </button>
                    </form>
                    <details class="relative lg:hidden">
                        <summary class="ui-mobile-menu-button" aria-label="Buka menu">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                            <span class="sr-only">Menu</span>
                        </summary>
                        <nav class="absolute right-0 top-11 z-40 w-56 rounded-xl border border-[#dce7eb] bg-white p-2 shadow-xl" aria-label="Navigasi mobile">
                            <a href="{{ route('dashboard') }}" class="ui-mobile-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dasbor</a>
                            @if ($isSuperAdmin)
                                <a href="{{ route('admin.users.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Pengguna</a>
                                <a href="{{ route('admin.teams.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}">Tim kerja</a>
                                <a href="{{ route('admin.skills.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.skills.*', 'admin.categories.*') ? 'is-active' : '' }}">Data keahlian</a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}">Audit log</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[#edf2f4] pt-1">
                                @csrf
                                <button type="submit" class="ui-mobile-nav-link w-full text-left">Keluar</button>
                            </form>
                        </nav>
                    </details>
                </div>
            </header>

            <main class="ui-main">
                @include('components.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
