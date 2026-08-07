@extends('layouts.app')

@section('title', 'Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Administrasi')
@section('header_title', 'Pengguna dan akses')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Direktori akses</p>
            <h1 class="ui-page-title">Pengguna dan akses</h1>
            <p class="ui-page-description">Kelola identitas, role, tim utama, dan bidang keahlian dari satu tempat. Setiap perubahan tetap tercatat di audit log.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.audit-logs.index') }}" class="ui-btn ui-btn-ghost">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8" /></svg>
                Audit log
            </a>
            <a href="{{ route('admin.users.create') }}" class="ui-btn ui-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat pengguna
            </a>
        </div>
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan pengguna">
        <article class="ui-stat-card">
            <span class="ui-stat-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" /></svg>
            </span>
            <div><p class="ui-stat-label">Total pengguna</p><p class="ui-stat-value">{{ $userStats['total'] }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
            <div><p class="ui-stat-label">Akun aktif</p><p class="ui-stat-value">{{ $userStats['active'] }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#fff6df] !text-[#a16207]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5a8.5 8.5 0 1 0 8.5 8.5M12 7v5l3 2" /></svg>
            </span>
            <div><p class="ui-stat-label">Menunggu ganti password</p><p class="ui-stat-value">{{ $userStats['password_pending'] }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#f1f4f6] !text-[#607681]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M5 19V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87V19" /><path stroke-linecap="round" d="M9 12h.01M12 12h.01M15 12h.01" /></svg>
            </span>
            <div><p class="ui-stat-label">Belum punya tim</p><p class="ui-stat-value">{{ $userStats['without_team'] }}</p></div>
        </article>
    </section>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="users-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 id="users-heading" class="ui-section-title">Direktori pengguna</h2>
                <p class="ui-section-description">{{ $users->total() }} akun terdaftar · menampilkan {{ $users->count() }} akun pada halaman ini</p>
            </div>
            <a href="{{ route('admin.teams.index') }}" class="ui-action-link">Lihat struktur tim <span aria-hidden="true">→</span></a>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table">
                <caption class="sr-only">Daftar pengguna, role, tim utama, keahlian, status, dan aksi</caption>
                <thead>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Akses</th>
                        <th scope="col">Organisasi</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $listedUser)
                        @php($initial = strtoupper(substr(trim($listedUser->name), 0, 1)))
                        <tr>
                            <td>
                                <div class="flex min-w-[14rem] items-start gap-3">
                                    <span class="ui-avatar">{{ $initial }}</span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-[#17313c]">{{ $listedUser->name }}</p>
                                        <p class="mt-1 text-xs text-[#78909a]">{{ '@'.$listedUser->username }}</p>
                                        @if ($listedUser->email)
                                            <p class="mt-1 truncate text-xs text-[#78909a]">{{ $listedUser->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex max-w-[13rem] flex-wrap gap-1.5">
                                    @forelse ($listedUser->roles as $role)
                                        <x-role-badge :label="$role->name" />
                                    @empty
                                        <span class="text-xs text-[#78909a]">Belum ada role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <p class="font-semibold text-[#35505b]">{{ $listedUser->currentTeamMembership?->workTeam?->name ?? 'Belum ada tim utama' }}</p>
                                <div class="mt-2 flex max-w-[14rem] flex-wrap gap-1.5">
                                    @forelse ($listedUser->skills->take(3) as $skill)
                                        <span class="ui-chip">{{ $skill->name }}</span>
                                    @empty
                                        <span class="text-xs text-[#78909a]">Belum ada keahlian</span>
                                    @endforelse
                                    @if ($listedUser->skills->count() > 3)
                                        <span class="ui-chip !border-[#dce7eb] !bg-[#f5f8f9] !text-[#6a8089]">+{{ $listedUser->skills->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="ui-status {{ $listedUser->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                @if ($listedUser->requiresPasswordChange())
                                    <p class="mt-2 text-[0.68rem] font-bold text-[#a16207]">Perlu ganti password</p>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.edit', $listedUser) }}" class="ui-action-link">Kelola <span aria-hidden="true">→</span></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="ui-empty">Belum ada pengguna yang terdaftar. Gunakan tombol <span class="font-bold">Buat pengguna</span> untuk menambahkan akun pertama.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#edf2f4] md:hidden">
            @forelse ($users as $listedUser)
                @php($initial = strtoupper(substr(trim($listedUser->name), 0, 1)))
                <article class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="ui-avatar">{{ $initial }}</span>
                            <div class="min-w-0">
                                <h2 class="truncate font-bold text-[#17313c]">{{ $listedUser->name }}</h2>
                                <p class="mt-1 text-xs text-[#78909a]">{{ '@'.$listedUser->username }}</p>
                            </div>
                        </div>
                        <span class="ui-status {{ $listedUser->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-4 rounded-xl bg-[#f7fafb] p-3.5">
                        <div><p class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Tim utama</p><p class="mt-1 text-sm font-semibold text-[#35505b]">{{ $listedUser->currentTeamMembership?->workTeam?->name ?? 'Belum ada' }}</p></div>
                        <div><p class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Role</p><p class="mt-1 text-sm font-semibold text-[#35505b]">{{ $listedUser->roles->pluck('name')->join(', ') ?: 'Belum ada' }}</p></div>
                    </div>
                    @if ($listedUser->requiresPasswordChange())
                        <p class="mt-3 text-xs font-bold text-[#a16207]">Perlu ganti password sebelum bekerja.</p>
                    @endif
                    <a href="{{ route('admin.users.edit', $listedUser) }}" class="ui-btn ui-btn-ghost mt-4 w-full">Kelola pengguna <span aria-hidden="true">→</span></a>
                </article>
            @empty
                <div class="ui-empty m-4">Belum ada pengguna yang terdaftar.</div>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="border-t border-[#e7eef1] px-5 py-4 sm:px-6">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
