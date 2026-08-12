<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $branding['application_name'])</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen font-sans antialiased" data-livewire-navigation>
    @php
        $currentUser = auth()->user()->loadMissing('roles');
        $isSuperAdmin = $currentUser->hasRole(\App\Enums\Role::SuperAdmin);
        $isTier1 = $currentUser->hasRole(\App\Enums\Role::AgenTier1);
        $isTier2 = $currentUser->hasRole(\App\Enums\Role::AgenTier2);
        $isTeamChair = $currentUser->hasRole(\App\Enums\Role::KetuaTimKerja);
        $canAccessWorkQueue = ! $isTeamChair && ($isTier1 || $isTier2);
        $canViewAllTickets = $currentUser->can('viewAll', \App\Models\Ticket::class);
        $isAllTicketPage = request()->routeIs('tickets.all')
            || (request()->routeIs('tickets.show') && request()->query('from') === 'all');
        $canAccessTickets = ! $isTeamChair
            && $currentUser->hasAnyRole([\App\Enums\Role::Pemohon, \App\Enums\Role::AgenTier1, \App\Enums\Role::AgenTier2]);
        $ticketListLabel = $isTeamChair && $canAccessTickets
            ? 'Tiket saya dan tim'
            : ($isTeamChair ? 'Tiket tim' : 'Tiket saya');
        $canReviewApprovals = ! $isTeamChair
            && $currentUser->hasRole(\App\Enums\Role::Approver)
            && \App\Models\ApproverAssignment::query()->active()->where('user_id', $currentUser->getKey())->exists();
        $canViewReports = $currentUser->can('viewAny', \App\Models\ReportExport::class);
        $canManageAnnouncements = $isSuperAdmin || $isTier1;
        $catalogSection = request()->query('section', 'services');
        $isCatalogServices = request()->routeIs('admin.services.*')
            || (request()->routeIs('admin.catalog.*') && $catalogSection === 'services');
        $isCatalogLocations = request()->routeIs('admin.locations.*')
            || (request()->routeIs('admin.catalog.*') && $catalogSection === 'locations');
        $locationMenuUrl = request()->routeIs('admin.catalog.*') && $catalogSection === 'locations'
            ? route('admin.catalog.index', ['section' => 'locations'])
            : route('admin.locations.index');
        $unreadNotificationCount = $currentUser->unreadNotifications()->count();
        $latestNotifications = $currentUser->notifications()->latest()->limit(5)->get();
        $currentUserRoleText = $currentUser->roles
            ->map(fn (\App\Models\Role $role): string => \App\Enums\Role::tryFrom($role->slug)?->label() ?? $role->name)
            ->implode(', ');
    @endphp

    <div class="ui-shell lg:flex">
        <aside id="app-sidebar" data-sidebar class="ui-sidebar hidden shrink-0 flex-col lg:flex" aria-label="Navigasi utama">
            @if ($isSuperAdmin)
                <nav class="mt-9 flex flex-col gap-2" aria-label="Menu Utama">
                    <p class="ui-sidebar-label">Menu Utama</p>
                    <a href="{{ route('dashboard') }}" class="ui-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" aria-label="Dashboard" title="Dashboard">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 9-8 9 8M5 10v10h5v-6h4v6h5V10" /></svg></span>
                        <span class="ui-nav-label">Dashboard</span>
                    </a>
                </nav>

                <nav class="mt-5 flex flex-col gap-2" aria-label="Master Data">
                    <p class="ui-sidebar-label">Master Data</p>
                    <a href="{{ route('admin.users.index') }}" class="ui-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" aria-label="Manajemen Pengguna" title="Manajemen Pengguna">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5.5-6.5a3 3 0 0 1 0 5.8M17 14.3a3.5 3.5 0 0 1 3 3.4V19" /></svg></span>
                        <span class="ui-nav-label">Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('admin.teams.index') }}" class="ui-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}" aria-label="Manajemen Tim Kerja" title="Manajemen Tim Kerja">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8M3.5 9.5h17M8.5 12.5h.01M12 12.5h.01M15.5 12.5h.01M8.5 16h.01M12 16h.01M15.5 16h.01" /></svg></span>
                        <span class="ui-nav-label">Manajemen Tim Kerja</span>
                    </a>
                    <a href="{{ route('admin.skills.index') }}" class="ui-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}" aria-label="Manajemen Keahlian" title="Manajemen Keahlian">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /><path stroke-linecap="round" d="M4 20.2h16" /></svg></span>
                        <span class="ui-nav-label">Manajemen Keahlian</span>
                    </a>
                    <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-nav-link {{ $isCatalogServices ? 'is-active' : '' }}" aria-label="Manajemen Layanan" title="Manajemen Layanan">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6.5h14v11H5zM8 9.5h8M8 13h5" /><path stroke-linecap="round" d="M8 4.5h8" /></svg></span>
                        <span class="ui-nav-label">Manajemen Layanan</span>
                    </a>
                    <a href="{{ $locationMenuUrl }}" class="ui-nav-link {{ $isCatalogLocations ? 'is-active' : '' }}" aria-label="Manajemen Lokasi" title="Manajemen Lokasi">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20.5h14M6.5 20.5V6.2a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v14.3M9 8.5h6M9 12h6M9 15.5h3" /><path stroke-linecap="round" d="M4 20.5h16" /></svg></span>
                        <span class="ui-nav-label">Manajemen Lokasi</span>
                    </a>
                </nav>

                <nav class="mt-5 flex flex-col gap-2" aria-label="Konfigurasi">
                    <p class="ui-sidebar-label">Konfigurasi</p>
                    <a href="{{ route('admin.announcements.index') }}" class="ui-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" aria-label="Manajemen Pengumuman" title="Manajemen Pengumuman">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M8 16.5 9.5 20h2L10 16.5M18.5 10a3 3 0 0 1 0 4" /></svg></span>
                        <span class="ui-nav-label">Manajemen Pengumuman</span>
                    </a>
                    <a href="{{ route('admin.branding.index') }}" class="ui-nav-link {{ request()->routeIs('admin.branding.*') ? 'is-active' : '' }}" aria-label="Manajemen Aplikasi" title="Manajemen Aplikasi · Identitas Aplikasi">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 8.5h8M8 12h5M8 15.5h7" /><path stroke-linecap="round" d="M16.5 3.5v3M7.5 3.5v3" /></svg></span>
                        <span class="ui-nav-label">Manajemen Aplikasi</span>
                    </a>
                </nav>

                <nav class="mt-5 flex flex-col gap-2" aria-label="Laporan">
                    <p class="ui-sidebar-label">Laporan</p>
                    @if ($canViewReports)
                        <a href="{{ route('reports.index') }}" class="ui-nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}" aria-label="Laporan Bulanan" title="Laporan Bulanan">
                            <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5h14v15H5zM8.5 8h7M8.5 11.5h7M8.5 15h4" /><path stroke-linecap="round" d="M8.5 18.5h7" /></svg></span>
                            <span class="ui-nav-label">Laporan Bulanan</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.audit-logs.index') }}" class="ui-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}" aria-label="Audit Trail" title="Audit Trail">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8M8 18h5" /></svg></span>
                        <span class="ui-nav-label">Audit Trail</span>
                    </a>
                </nav>
            @else
            <nav class="mt-9 flex flex-col gap-2" aria-label="Navigasi utama">
                <p class="ui-sidebar-label">Ruang Kerja</p>
                <a href="{{ route('dashboard') }}" class="ui-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" aria-label="Dasbor" title="Dasbor">
                    <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 9-8 9 8M5 10v10h5v-6h4v6h5V10" /></svg></span>
                    <span class="ui-nav-label">Dasbor</span>
                </a>
                @if ($canAccessWorkQueue)
                    <a href="{{ route('tickets.queue', $isTier1 ? [] : ['tab' => 'mine']) }}" class="ui-nav-link {{ request()->routeIs('tickets.queue') || (request()->routeIs('tickets.show') && ! $isAllTicketPage) ? 'is-active' : '' }}" aria-label="Monitoring Tiket" title="Monitoring Tiket" @if (request()->routeIs('tickets.queue')) aria-current="page" @endif>
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M5 6.5h14M5 12h14M5 17.5h9" /><path stroke-linecap="round" d="M18 17.5h.01" /></svg></span>
                        <span class="ui-nav-label">Monitoring Tiket</span>
                    </a>
                @elseif ($canAccessTickets || $isTeamChair)
                    <a href="{{ route('tickets.index') }}" class="ui-nav-link {{ request()->routeIs('tickets.index', 'tickets.show', 'tickets.cancel') ? 'is-active' : '' }}" aria-label="{{ $ticketListLabel }}" title="{{ $ticketListLabel }}">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg></span>
                        <span class="ui-nav-label">{{ $ticketListLabel }}</span>
                    </a>
                @endif
                @if ($isTier1 && $canViewAllTickets)
                    <a href="{{ route('tickets.all') }}" class="ui-nav-link {{ $isAllTicketPage ? 'is-active' : '' }}" aria-label="Semua Tiket" title="Semua Tiket" @if ($isAllTicketPage) aria-current="page" @endif>
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg></span>
                        <span class="ui-nav-label">Semua Tiket</span>
                    </a>
                @endif
                @if ($canReviewApprovals)
                    <a href="{{ route('approvals.index') }}" class="ui-nav-link {{ request()->routeIs('approvals.*') ? 'is-active' : '' }}" aria-label="Persetujuan" title="Persetujuan">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3.5 7 2.5v5.3c0 4.1-2.8 7.5-7 9.2-4.2-1.7-7-5.1-7-9.2V6l7-2.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.8 11.8 2.1 2.1 4.4-4.4" /></svg></span>
                        <span class="ui-nav-label">Persetujuan</span>
                    </a>
                @endif
                @if ($canViewReports)
                    <a href="{{ route('reports.index') }}" class="ui-nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}" aria-label="Laporan" title="Laporan">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5h14v15H5zM8.5 8h7M8.5 11.5h7M8.5 15h4" /><path stroke-linecap="round" d="M8.5 18.5h7" /></svg></span>
                        <span class="ui-nav-label">Laporan</span>
                    </a>
                @endif
            </nav>

            @if ($isSuperAdmin)
                <nav class="mt-5 flex flex-col gap-2" aria-label="Navigasi administrasi">
                    <p class="ui-sidebar-label">Administrasi</p>
                    <a href="{{ route('admin.branding.index') }}" class="ui-nav-link {{ request()->routeIs('admin.branding.*') ? 'is-active' : '' }}" aria-label="Manajemen aplikasi" title="Manajemen aplikasi · Identitas Aplikasi">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 8.5h8M8 12h5M8 15.5h7" /><path stroke-linecap="round" d="M16.5 3.5v3M7.5 3.5v3" /></svg></span>
                        <span class="ui-nav-label">Manajemen aplikasi</span>
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="ui-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}" aria-label="Audit log" title="Audit log">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8M8 18h5" /></svg></span>
                        <span class="ui-nav-label">Audit log</span>
                    </a>
                </nav>

                <nav class="mt-5 flex flex-col gap-2" aria-label="Navigasi Data Master">
                    <p class="ui-sidebar-label">Data Master</p>
                    <a href="{{ route('admin.users.index') }}" class="ui-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" aria-label="Pengguna dan peran" title="Pengguna dan peran">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5.5-6.5a3 3 0 0 1 0 5.8M17 14.3a3.5 3.5 0 0 1 3 3.4V19" /></svg></span>
                        <span class="ui-nav-label">Pengguna &amp; peran</span>
                    </a>
                    <a href="{{ route('admin.teams.index') }}" class="ui-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}" aria-label="Tim kerja" title="Tim kerja">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8M3.5 9.5h17M8.5 12.5h.01M12 12.5h.01M15.5 12.5h.01M8.5 16h.01M12 16h.01M15.5 16h.01" /></svg></span>
                        <span class="ui-nav-label">Tim kerja</span>
                    </a>
                    <a href="{{ route('admin.skills.index') }}" class="ui-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}" aria-label="Data keahlian" title="Data keahlian">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /><path stroke-linecap="round" d="M4 20.2h16" /></svg></span>
                        <span class="ui-nav-label">Data keahlian</span>
                    </a>
                    <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-nav-link {{ $isCatalogServices ? 'is-active' : '' }}" aria-label="Manajemen layanan" title="Manajemen layanan">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6.5h14v11H5zM8 9.5h8M8 13h5" /><path stroke-linecap="round" d="M8 4.5h8" /></svg></span>
                        <span class="ui-nav-label">Manajemen layanan</span>
                    </a>
                    <a href="{{ $locationMenuUrl }}" class="ui-nav-link {{ $isCatalogLocations ? 'is-active' : '' }}" aria-label="Manajemen lokasi" title="Manajemen lokasi">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20.5h14M6.5 20.5V6.2a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v14.3M9 8.5h6M9 12h6M9 15.5h3" /><path stroke-linecap="round" d="M4 20.5h16" /></svg></span>
                        <span class="ui-nav-label">Manajemen lokasi</span>
                    </a>
                </nav>
            @endif

            @if ($canManageAnnouncements)
                <nav class="mt-5 flex flex-col gap-2" aria-label="Navigasi pengumuman">
                    <p class="ui-sidebar-label">Komunikasi</p>
                    <a href="{{ route('admin.announcements.index') }}" class="ui-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" aria-label="Pengumuman" title="Pengumuman">
                        <span class="ui-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M8 16.5 9.5 20h2L10 16.5M18.5 10a3 3 0 0 1 0 4" /></svg></span>
                        <span class="ui-nav-label">Pengumuman</span>
                    </a>
                </nav>
            @endif
            @endif

        </aside>

        <div class="ui-content-shell min-w-0 flex-1">
            <header class="ui-topbar fixed inset-x-0 top-0 z-30 flex min-h-[4.5rem] items-center justify-between gap-2 px-4 sm:gap-5 sm:px-7 lg:px-9">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="ui-topbar-brand flex min-w-0 items-center gap-2.5" aria-label="Dasbor {{ $branding['application_name'] }}">
                        @if ($branding['logo_url'])
                            <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['organization_name'] }}" class="ui-topbar-brand-logo">
                        @else
                            <span class="ui-brand-mark !h-11 !w-11 !rounded-xl text-sm">{{ $branding['monogram'] }}</span>
                        @endif
                        <span class="hidden max-w-[12rem] truncate text-lg font-extrabold tracking-[-0.05em] text-[#18252b] min-[520px]:inline">{{ $branding['application_name'] }}</span>
                        <span class="hidden max-w-[14rem] truncate text-sm text-[#718088] sm:inline">{{ $branding['organization_name'] }}</span>
                    </a>
                    <span class="hidden h-5 w-px bg-[#dfe7eb] lg:block" aria-hidden="true"></span>
                    <button type="button" class="ui-menu-button hidden lg:inline-flex" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="true" aria-label="Sembunyikan menu" title="Sembunyikan menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg><span class="sr-only" data-sidebar-toggle-label>Sembunyikan menu</span></button>
                    <span class="hidden h-5 w-px bg-[#dfe7eb] lg:block" aria-hidden="true"></span>
                    <span class="hidden truncate text-sm text-[#6c7c83] lg:inline">@yield('header_title', 'Ruang kerja')</span>
                </div>

                <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                    <details class="relative" data-help-menu>
                        <summary class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-full text-[#8a8f93] transition hover:bg-[#f1f3f4] hover:text-[#5f6a70] focus:outline-none focus:ring-2 focus:ring-[#75d5f3] focus:ring-offset-2 [&::-webkit-details-marker]:hidden" aria-label="Bantuan" title="Bantuan">
                            <svg class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.95.7-1.5 1.08-1.5 2.2M12 16.2h.01" /></svg>
                        </summary>
                        <div class="absolute right-0 top-9 z-40 w-64 overflow-hidden rounded-xl border border-[#dce7eb] bg-white shadow-[0_14px_32px_rgba(38,58,67,0.14)]">
                            <div class="px-4 py-3">
                                <p class="text-sm font-extrabold text-[#263a43]">Butuh bantuan?</p>
                                <p class="mt-1 text-xs leading-5 text-[#78909a]">Buat tiket untuk menghubungi Tim TI.</p>
                                @if (Route::has('tickets.create'))
                                    <a href="{{ route('tickets.create') }}" class="mt-3 inline-flex text-xs font-extrabold text-[#147a79] hover:text-[#0f5f5e]">Buat tiket <span class="ml-1" aria-hidden="true">→</span></a>
                                @endif
                            </div>
                        </div>
                    </details>
                    <details class="relative" data-notification-menu>
                        <summary class="flex h-8 w-8 cursor-pointer list-none items-center justify-center rounded-full text-[#6d7883] transition hover:bg-[#f1f5f7] hover:text-[#35505b] focus:outline-none focus:ring-2 focus:ring-[#75d5f3] focus:ring-offset-2 [&::-webkit-details-marker]:hidden" aria-label="Notifikasi">
                            <svg class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.8 9.5a5.2 5.2 0 0 1 10.4 0c0 5 2 5.8 2 7H4.8c0-1.2 2-2 2-7ZM9.7 19a2.5 2.5 0 0 0 4.6 0" /></svg>
                            @if ($unreadNotificationCount > 0)
                                <span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-[#ef4444] ring-2 ring-white" aria-label="{{ $unreadNotificationCount }} notifikasi belum dibaca" title="{{ $unreadNotificationCount }} notifikasi belum dibaca"></span>
                            @endif
                        </summary>
                        <div class="absolute right-0 top-12 z-40 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-[#dce7eb] bg-white shadow-[0_18px_45px_rgba(38,58,67,0.16)]">
                            <div class="flex items-center justify-between gap-3 border-b border-[#edf2f4] px-4 py-3">
                                <div>
                                    <p class="text-sm font-extrabold text-[#263a43]">Notifikasi</p>
                                    <p class="mt-0.5 text-[0.68rem] text-[#78909a]">{{ $unreadNotificationCount }} belum dibaca</p>
                                </div>
                                <a href="{{ route('notifications.index') }}" class="text-xs font-extrabold text-[#147a79] hover:text-[#0f5f5e]">Lihat semua</a>
                            </div>
                            @forelse ($latestNotifications as $notification)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="border-b border-[#f1f4f5] last:border-b-0">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-left transition hover:bg-[#f6fbfc] {{ $notification->read_at ? '' : 'bg-[#f1fbfe]' }}">
                                        <span class="flex items-start gap-2.5">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-[#dfe8ec]' : 'bg-[#2bb8aa]' }}" aria-hidden="true"></span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-xs font-extrabold text-[#35505b]">{{ $notification->data['title'] ?? 'Notifikasi tiket' }}</span>
                                                <span class="mt-1 block line-clamp-2 text-xs leading-5 text-[#78909a]">{{ $notification->data['message'] ?? 'Ada pembaruan pada tiket.' }}</span>
                                                <span class="mt-1 block text-[0.64rem] text-[#9aabb0]">{{ $notification->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
                                            </span>
                                        </span>
                                    </button>
                                </form>
                            @empty
                                <p class="px-4 py-7 text-center text-xs leading-5 text-[#78909a]">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </details>
                    <div class="flex min-w-0 items-center gap-2">
                        <details class="relative" data-account-menu>
                        <summary class="ui-avatar !h-8 !w-8 !cursor-pointer !rounded-full !bg-[#e5f3ff] !text-xs !text-[#087fc1] transition hover:!bg-[#d7edff] focus:outline-none focus:ring-2 focus:ring-[#75d5f3] focus:ring-offset-2 [&::-webkit-details-marker]:hidden" aria-label="Buka menu akun untuk {{ $currentUser->name }}" title="Menu akun">
                            {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                        </summary>
                        <div class="absolute right-0 top-12 z-40 w-64 overflow-hidden rounded-2xl border border-[#dce7eb] bg-white shadow-[0_18px_45px_rgba(38,58,67,0.16)]">
                            <div class="border-b border-[#edf2f4] px-4 py-3">
                                <p class="truncate text-sm font-extrabold text-[#263a43]">{{ $currentUser->name }}</p>
                                <p class="mt-0.5 truncate text-xs text-[#78909a]">{{ $currentUserRoleText ?: 'Tanpa role' }}</p>
                            </div>
                            <nav class="p-2" aria-label="Menu akun">
                                <a href="{{ route('profile.edit') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-[#35505b] transition hover:bg-[#f1fbfe] hover:text-[#147a79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#75d5f3]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 5.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM4.5 20a7.5 7.5 0 0 1 15 0" /></svg>
                                    Edit Profil
                                </a>
                                <a href="{{ route('password.change') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-[#35505b] transition hover:bg-[#f1fbfe] hover:text-[#147a79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#75d5f3]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" /><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2" /></svg>
                                    Ganti Password
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[#edf2f4] pt-1">
                                    @csrf
                                    <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-bold text-[#b4233c] transition hover:bg-[#fff4f5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f7b3be]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4M14 8l4 4-4 4m4-4H9" /></svg>
                                        Keluar
                                    </button>
                                </form>
                            </nav>
                        </div>
                        </details>
                        <div class="min-w-0 w-24 max-[340px]:hidden sm:w-auto">
                            <p class="truncate text-[0.78rem] font-extrabold leading-4 text-[#263a43] sm:max-w-[13rem]">{{ $currentUser->name }}</p>
                            <p class="mt-0.5 truncate text-[0.68rem] leading-4 text-[#7b8790] sm:max-w-[13rem]" title="{{ $currentUserRoleText }}">{{ $currentUserRoleText ?: 'Tanpa role' }}</p>
                        </div>
                    </div>
                    <details class="relative lg:hidden">
                        <summary class="ui-mobile-menu-button" aria-label="Buka menu">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                            <span class="sr-only">Menu</span>
                        </summary>
                        <nav class="absolute right-0 top-11 z-40 w-56 rounded-xl border border-[#dce7eb] bg-white p-2 shadow-xl" aria-label="Navigasi mobile">
                            @if ($isSuperAdmin)
                                <div class="ui-mobile-nav-group-label">Menu Utama</div>
                                <a href="{{ route('dashboard') }}" class="ui-mobile-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                                <a href="{{ route('notifications.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">Notifikasi
                                    @if ($unreadNotificationCount > 0)
                                        <span class="ml-1 rounded-full bg-[#e4a72c] px-1.5 py-0.5 text-[0.62rem] text-white">{{ $unreadNotificationCount }}</span>
                                    @endif
                                </a>

                                <div class="ui-mobile-nav-group-label">Master Data</div>
                                <a href="{{ route('admin.users.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Manajemen Pengguna</a>
                                <a href="{{ route('admin.teams.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}">Manajemen Tim Kerja</a>
                                <a href="{{ route('admin.skills.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}">Manajemen Keahlian</a>
                                <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-mobile-nav-link {{ $isCatalogServices ? 'is-active' : '' }}">Manajemen Layanan</a>
                                <a href="{{ $locationMenuUrl }}" class="ui-mobile-nav-link {{ $isCatalogLocations ? 'is-active' : '' }}">Manajemen Lokasi</a>

                                <div class="ui-mobile-nav-group-label">Konfigurasi</div>
                                <a href="{{ route('admin.announcements.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}">Manajemen Pengumuman</a>
                                <a href="{{ route('admin.branding.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.branding.*') ? 'is-active' : '' }}">Manajemen Aplikasi</a>

                                <div class="ui-mobile-nav-group-label">Laporan</div>
                                @if ($canViewReports)
                                    <a href="{{ route('reports.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">Laporan Bulanan</a>
                                @endif
                                <a href="{{ route('admin.audit-logs.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}">Audit Trail</a>
                            @else
                            <div class="ui-mobile-nav-group-label">Ruang Kerja</div>
                            <a href="{{ route('dashboard') }}" class="ui-mobile-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dasbor</a>
                            <a href="{{ route('notifications.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}">Notifikasi
                                @if ($unreadNotificationCount > 0)
                                    <span class="ml-1 rounded-full bg-[#e4a72c] px-1.5 py-0.5 text-[0.62rem] text-white">{{ $unreadNotificationCount }}</span>
                                @endif
                            </a>
                            @if ($canAccessWorkQueue)
                                <a href="{{ route('tickets.queue', $isTier1 ? [] : ['tab' => 'mine']) }}" class="ui-mobile-nav-link {{ request()->routeIs('tickets.queue') || (request()->routeIs('tickets.show') && ! $isAllTicketPage) ? 'is-active' : '' }}">Monitoring Tiket</a>
                            @elseif ($canAccessTickets || $isTeamChair)
                                <a href="{{ route('tickets.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('tickets.index', 'tickets.show', 'tickets.cancel') ? 'is-active' : '' }}">{{ $ticketListLabel }}</a>
                            @endif
                            @if ($isTier1 && $canViewAllTickets)
                                <a href="{{ route('tickets.all') }}" class="ui-mobile-nav-link {{ $isAllTicketPage ? 'is-active' : '' }}" @if ($isAllTicketPage) aria-current="page" @endif>Semua Tiket</a>
                            @endif
                            @if ($canReviewApprovals)
                                <a href="{{ route('approvals.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('approvals.*') ? 'is-active' : '' }}">Persetujuan</a>
                            @endif
                            @if ($canViewReports)
                                <a href="{{ route('reports.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">Laporan</a>
                            @endif
                            @if ($isSuperAdmin)
                                <div class="ui-mobile-nav-group-label">Administrasi</div>
                                <a href="{{ route('admin.branding.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.branding.*') ? 'is-active' : '' }}">Manajemen aplikasi</a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}">Audit log</a>
                                <div class="ui-mobile-nav-group-label">Data Master</div>
                                <a href="{{ route('admin.users.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Pengguna &amp; peran</a>
                                <a href="{{ route('admin.teams.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.teams.*') ? 'is-active' : '' }}">Tim kerja</a>
                                <a href="{{ route('admin.skills.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}">Data keahlian</a>
                                <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="ui-mobile-nav-link {{ $isCatalogServices ? 'is-active' : '' }}">Manajemen layanan</a>
                                <a href="{{ $locationMenuUrl }}" class="ui-mobile-nav-link {{ $isCatalogLocations ? 'is-active' : '' }}">Manajemen lokasi</a>
                            @endif
                            @if ($canManageAnnouncements)
                                <div class="ui-mobile-nav-group-label">Komunikasi</div>
                                <a href="{{ route('admin.announcements.index') }}" class="ui-mobile-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}">Pengumuman</a>
                            @endif
                            @endif
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

    @include('components.global-loading-overlay')
    @livewireScripts
</body>
</html>
