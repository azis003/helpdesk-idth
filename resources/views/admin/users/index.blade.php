@extends('layouts.app')

@section('title', 'Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Pengguna dan akses')

@section('content')
    <div class="ui-page-header">
        <div>
            <h1 class="ui-page-title">Pengguna dan Akses</h1>
            <p class="ui-page-description">Kelola pengguna, role, tim kerja, dan keahlian pegawai. Setiap perubahan tetap tercatat di audit log.</p>
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
            <div><p class="ui-stat-label">Perlu ganti password</p><p class="ui-stat-value">{{ $userStats['password_pending'] }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#f1f4f6] !text-[#607681]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M5 19V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87V19" /><path stroke-linecap="round" d="M9 12h.01M12 12h.01M15 12h.01" /></svg>
            </span>
            <div><p class="ui-stat-label">Belum punya tim kerja</p><p class="ui-stat-value">{{ $userStats['without_team'] }}</p></div>
        </article>
    </section>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="users-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <h2 id="users-heading" class="ui-section-title">Daftar pengguna</h2>
            <a href="{{ route('admin.users.create') }}" class="ui-btn ui-btn-primary w-full shrink-0 sm:w-auto">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah pengguna
            </a>
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
                                <p class="font-semibold text-[#35505b]">{{ $listedUser->currentTeamMembership?->workTeam?->name ?? 'Belum punya tim kerja' }}</p>
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
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $listedUser) }}" class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#dce7eb] bg-white text-[#55707a] transition hover:border-[#2bb8aa] hover:bg-[#effcf9] hover:text-[#0f766e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa] focus-visible:ring-offset-2" aria-label="Edit pengguna {{ $listedUser->name }}" title="Edit pengguna">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        <span class="sr-only">Edit pengguna {{ $listedUser->name }}</span>
                                        <span role="tooltip" class="pointer-events-none absolute right-full top-1/2 z-20 mr-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-[#17313c] px-2 py-1.5 text-[0.68rem] font-semibold text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">Edit pengguna</span>
                                    </a>
                                    @if ($listedUser->isNot(auth()->user()))
                                        <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#ead8ad] bg-white text-[#a16207] transition hover:border-[#e4a72c] hover:bg-[#fff8e8] hover:text-[#854d0e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e4a72c] focus-visible:ring-offset-2" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                                            <span class="sr-only">Ganti password {{ $listedUser->name }}</span>
                                            <span role="tooltip" class="pointer-events-none absolute right-full top-1/2 z-20 mr-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-[#7a4d07] px-2 py-1.5 text-[0.68rem] font-semibold text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">Ganti password</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="ui-empty">Belum ada pengguna yang terdaftar. Gunakan tombol <span class="font-bold">Tambah pengguna</span> untuk menambahkan akun pertama.</div></td></tr>
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
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('admin.users.edit', $listedUser) }}" class="ui-btn ui-btn-ghost min-w-0 flex-1">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            Edit pengguna
                        </a>
                        @if ($listedUser->isNot(auth()->user()))
                            <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="ui-btn ui-btn-ghost group relative w-11 shrink-0 px-0 text-[#a16207] hover:border-[#e4a72c] hover:bg-[#fff8e8] hover:text-[#854d0e]" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                                <span class="sr-only">Ganti password {{ $listedUser->name }}</span>
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="ui-empty m-4">Belum ada pengguna yang terdaftar.</div>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="border-t border-[#e7eef1] px-5 py-4 sm:px-6">{{ $users->links() }}</div>
        @endif
    </section>

    <div id="password-reset-modal" data-password-reset-modal data-auto-user="{{ old('reset_user_id') }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/40" data-password-reset-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <section role="dialog" aria-modal="true" aria-labelledby="password-reset-title" class="w-full max-w-md rounded-2xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="flex items-center justify-between gap-4 border-b border-[#e7eef1] px-5 py-4 sm:px-6">
                    <h2 id="password-reset-title" data-password-reset-title class="text-base font-extrabold text-[#17313c]">Ganti password</h2>
                    <button type="button" data-password-reset-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-[#78909a] transition hover:bg-[#f4f8f9] hover:text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa]" aria-label="Tutup">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" data-password-reset-form class="p-5 sm:p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="confirm_reset" value="1">
                    <input type="hidden" name="temporary_password_confirmation" data-password-reset-confirmation>
                    <input type="hidden" name="reset_user_id" data-password-reset-user-id>

                    <label for="temporary_password" class="ui-field-label">Password baru</label>
                    <input id="temporary_password" name="temporary_password" type="password" autocomplete="new-password" required data-password-reset-input class="ui-input mt-2">
                    @error('temporary_password')
                        <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" data-password-reset-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Batal</button>
                        <button type="submit" data-password-reset-submit class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Ganti password</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
