@extends('layouts.app')

@php
    $autoOpenForm = old('_user_form');
@endphp

@section('title', 'Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Pengguna')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold tracking-tight text-[#18252b]">Pengguna</h1>
        <p class="mt-2 text-sm text-[#718088]">Pengguna dan akses dikelola dari satu daftar sederhana.</p>
    </div>

    <section class="overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="users-heading">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <h2 id="users-heading" class="text-xl font-extrabold tracking-tight">Daftar Pengguna</h2>
            <button type="button" data-ui-modal-open="user-create-modal" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#7138e8] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#075998]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah
            </button>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-[#17212b]">
                    <label for="user-per-page" class="font-bold">Tampilkan</label>
                    <select id="user-per-page" name="per_page" class="h-10 rounded-lg border border-[#d7e0e4] bg-white px-3 text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15" onchange="this.form.submit()">
                        @foreach ([10, 25, 50] as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <label for="user-search" class="shrink-0 text-sm font-bold text-[#17212b]">Search:</label>
                    <input id="user-search" name="q" type="search" value="{{ $search }}" class="h-10 w-full min-w-0 rounded-lg border border-[#d7e0e4] bg-[#f8fafb] px-3 text-sm text-[#17212b] outline-none placeholder:text-[#9baab0] focus:border-[#0a87c9] focus:bg-white focus:ring-2 focus:ring-[#0a87c9]/15 sm:w-56" placeholder="Cari pengguna" aria-label="Cari pengguna">
                    <button type="submit" class="sr-only">Cari pengguna</button>
                </div>
            </form>

            <div class="mt-4 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
                <table class="min-w-[850px] w-full border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar pengguna beserta email, role, tim kerja, dan aksi</caption>
                    <thead class="bg-[#fbfcfd] text-[#34495a]">
                        <tr>
                            <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Nama Pegawai</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Email</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Role</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Tim Kerja</th>
                            <th scope="col" class="w-36 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $listedUser)
                            <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <p class="font-semibold text-[#112b49]">{{ $listedUser->name }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">{{ '@'.$listedUser->username }}</p>
                                    <span class="mt-2 inline-flex items-center rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $listedUser->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">
                                        {{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-[#172d45]">{{ $listedUser->email ?: '—' }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    @if ($listedUser->roles->isNotEmpty())
                                        <ul class="list-disc space-y-1 pl-4 text-[#172d45]">
                                            @foreach ($listedUser->roles as $role)
                                                <li>{{ $role->managementLabel() }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-[#78909a]">Belum ada role</span>
                                    @endif
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-[#172d45]">{{ $listedUser->currentTeamMembership?->workTeam?->name ?? '—' }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="user-edit-modal-{{ $listedUser->id }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Edit pengguna {{ $listedUser->name }}" title="Edit pengguna">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        @if ($listedUser->isNot(auth()->user()))
                                            <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-[#718088]">
                                    {{ $search !== '' ? 'Tidak ada pengguna yang cocok dengan pencarian.' : 'Belum ada pengguna yang terdaftar.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($users->total() > 0)
                        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                    @else
                        Tidak ada data pengguna
                    @endif
                </p>
                @if ($users->hasPages())
                    <div>{{ $users->links() }}</div>
                @endif
            </div>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($users as $listedUser)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-[#112b49]">{{ $listedUser->name }}</h3>
                            <p class="mt-1 text-xs text-[#78909a]">{{ '@'.$listedUser->username }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $listedUser->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 rounded-lg bg-[#f8fafb] p-4 text-sm">
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Email</dt><dd class="mt-1 break-all text-[#172d45]">{{ $listedUser->email ?: '—' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Role</dt><dd class="mt-1 text-[#172d45]">{{ $listedUser->roles->map(fn ($role) => $role->managementLabel())->join(', ') ?: 'Belum ada role' }}</dd></div>
                        <div><dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Tim Kerja</dt><dd class="mt-1 text-[#172d45]">{{ $listedUser->currentTeamMembership?->workTeam?->name ?? '—' }}</dd></div>
                    </dl>
                    <div class="mt-4 flex gap-2">
                        <button type="button" data-ui-modal-open="user-edit-modal-{{ $listedUser->id }}" class="ui-btn ui-btn-primary min-w-0 flex-1">Edit pengguna</button>
                        @if ($listedUser->isNot(auth()->user()))
                            <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="ui-btn ui-btn-warning w-12 shrink-0 px-0" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /></svg>
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-[#718088]">Belum ada pengguna yang terdaftar.</p>
            @endforelse
        </div>
    </section>

    <div id="user-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="user-create-title" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                    <h2 id="user-create-title" class="text-lg font-extrabold">Tambah Pengguna</h2>
                    <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog tambah pengguna">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>
                @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'formId' => 'create', 'prefix' => 'user-create', 'isEdit' => false, 'isModal' => true])
            </section>
        </div>
    </div>

    @foreach ($users as $listedUser)
        <div id="user-edit-modal-{{ $listedUser->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'edit-'.$listedUser->id && $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="user-edit-title-{{ $listedUser->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                        <h2 id="user-edit-title-{{ $listedUser->id }}" class="text-lg font-extrabold">Edit Pengguna</h2>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog edit pengguna">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>
                    @include('admin.users._form', ['action' => route('admin.users.update', $listedUser), 'method' => 'PUT', 'formId' => 'edit-'.$listedUser->id, 'prefix' => 'user-edit-'.$listedUser->id, 'user' => $listedUser, 'isEdit' => true, 'isModal' => true])
                </section>
            </div>
        </div>
    @endforeach

    <div id="password-reset-modal" data-password-reset-modal data-auto-user="{{ old('reset_user_id') }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-password-reset-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <section role="dialog" aria-modal="true" aria-labelledby="password-reset-title" class="w-full max-w-md rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                    <h2 id="password-reset-title" data-password-reset-title class="text-lg font-extrabold">Ganti password</h2>
                    <button type="button" data-password-reset-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog ganti password">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>
                <form method="POST" data-password-reset-form class="p-5 sm:p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="confirm_reset" value="1">
                    <input type="hidden" name="temporary_password_confirmation" data-password-reset-confirmation>
                    <input type="hidden" name="reset_user_id" data-password-reset-user-id>
                    <label for="temporary_password" class="ui-field-label">Password sementara</label>
                    <input id="temporary_password" name="temporary_password" type="password" autocomplete="new-password" required data-password-reset-input class="ui-input mt-2">
                    @error('temporary_password')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    <p class="ui-field-help">Password wajib diganti oleh pengguna saat login berikutnya.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" data-password-reset-close class="ui-btn ui-btn-ghost">Batal</button>
                        <button type="submit" data-password-reset-submit class="ui-btn ui-btn-warning">Simpan password</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
