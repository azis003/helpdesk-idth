@extends('layouts.app')

@php
    $autoOpenForm = old('_user_form');

    // Presentasional saja - tidak mengubah data, logika, maupun alur aplikasi.
    $userActionBase = 'inline-flex h-9 w-9 items-center justify-center rounded-[var(--tm-r-sm)] border transition-[background-color,border-color,color,box-shadow] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)]';
    $userActionNeutral = $userActionBase.' border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]';
    $userActionWarning = $userActionBase.' border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] text-[color:var(--tm-warning-700)] hover:bg-[color:var(--tm-warning-100)]';
    $userActionDanger = $userActionBase.' border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-700)] hover:bg-[color:var(--tm-danger-100)]';
    $userActionDisabled = $userActionBase.' cursor-not-allowed border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] text-[color:var(--tm-text-faint)]';

    $userModalOverlay = 'absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]';
    $userModalPanel = 'relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]';
    $userModalPanelSm = 'w-full max-w-md overflow-hidden rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]';
    $userModalHeader = 'sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6';
    $userModalHeaderStatic = 'flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6';
    $userModalTitle = 'text-base font-extrabold tracking-tight text-[color:var(--tm-text)]';
    $userModalIconTile = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $userModalClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:bg-[color:var(--tm-sunken)] hover:text-[color:var(--tm-text)]';

    $userMetaLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';

    $userEmptyTitle = $search !== '' ? 'Tidak ada pengguna yang cocok' : 'Belum ada pengguna';
    $userEmptyDescription = $search !== ''
        ? 'Coba kata kunci lain, atau kosongkan kolom pencarian untuk melihat seluruh pengguna.'
        : 'Tambahkan pengguna pertama untuk mulai mengatur akun, peran, dan akses aplikasi.';
@endphp

@section('title', 'Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Pengguna')

@section('content')
    <x-page-header
        eyebrow="Data Master · Akses pengguna"
        title="Manajemen Pengguna"
        description="Pengguna dan akses dikelola dari satu daftar terpusat. Kelola akun, peran, dan status aktif dengan jelas."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="users-heading">
        <div class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="users-heading" class="text-base font-extrabold tracking-tight text-[color:var(--tm-text)]">Daftar Pengguna</h2>
                    <span class="ui-count tabular-nums">{{ $users->total() }}</span>
                </div>
                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Kelola akun, peran, dan status akses dari daftar ini.</p>
            </div>
            <button type="button" data-ui-modal-open="user-create-modal" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah Pengguna
            </button>
        </div>

        <div class="border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 sm:px-8">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-[color:var(--tm-text-secondary)]">
                    <label for="user-per-page" class="font-semibold">Tampilkan</label>
                    <select id="user-per-page" name="per_page" class="ui-select w-auto tabular-nums" onchange="this.form.submit()">
                        @foreach ([10, 25, 50] as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <label for="user-search" class="shrink-0 text-sm font-semibold text-[color:var(--tm-text-secondary)]">Cari</label>
                    <input id="user-search" name="q" type="search" value="{{ $search }}" class="ui-input w-full min-w-0 sm:w-64" placeholder="Nama atau email pengguna" aria-label="Cari pengguna">
                    <button type="submit" class="ui-btn ui-btn-secondary shrink-0">Cari</button>
                </div>
            </form>
        </div>

        <div class="hidden p-5 sm:p-6 md:block">
            <div class="overflow-x-auto rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)]">
                <table class="ui-table w-full min-w-[64rem]">
                    <caption class="sr-only">Daftar pengguna beserta nama, email, role, status, dan aksi</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="w-14 text-center">No</th>
                            <th scope="col">Nama Pengguna</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col" class="w-28 text-center">Status</th>
                            <th scope="col" class="w-52 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $listedUser)
                            @php
                                $displayRoles = $listedUser->roles->reject(fn ($role): bool => $role->slug === \App\Enums\Role::KetuaTimKerja->value);
                            @endphp
                            <tr>
                                <td class="text-center font-semibold tabular-nums text-[color:var(--tm-text-muted)]">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="font-bold text-[color:var(--tm-text)]">{{ $listedUser->name }}</td>
                                <td class="break-all text-[color:var(--tm-text-secondary)]">{{ $listedUser->email ?: '—' }}</td>
                                <td>
                                    @if ($displayRoles->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($displayRoles as $role)
                                                <span class="ui-chip">{{ $role->managementLabel() }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[color:var(--tm-text-faint)]">Belum ada role</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="ui-status {{ $listedUser->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">
                                        {{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="user-view-modal-{{ $listedUser->id }}" class="{{ $userActionNeutral }}" aria-label="Lihat pengguna {{ $listedUser->name }}" title="Lihat pengguna">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                                        </button>
                                        <button type="button" data-ui-modal-open="user-edit-modal-{{ $listedUser->id }}" class="{{ $userActionNeutral }}" aria-label="Edit pengguna {{ $listedUser->name }}" title="Edit pengguna">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        @if ($listedUser->isNot(auth()->user()))
                                            <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="{{ $userActionWarning }}" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $userActionDisabled }}" aria-label="Akun sendiri tidak dapat diganti password dari sini" title="Akun sendiri tidak dapat diganti password dari sini">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                                            </button>
                                        @endif
                                        @if ($listedUser->isNot(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.destroy', $listedUser) }}" data-swal-confirm="Apakah Anda yakin ingin menghapus user ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="{{ $userActionDanger }}" aria-label="Hapus pengguna {{ $listedUser->name }}" title="Hapus pengguna">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="{{ $userActionDisabled }}" aria-label="Akun sendiri tidak dapat dihapus" title="Akun sendiri tidak dapat dihapus">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <x-empty-state :title="$userEmptyTitle" :description="$userEmptyDescription" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="divide-y divide-[color:var(--tm-border-subtle)] md:hidden">
            @forelse ($users as $listedUser)
                @php
                    $displayRoles = $listedUser->roles->reject(fn ($role): bool => $role->slug === \App\Enums\Role::KetuaTimKerja->value);
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="{{ $userMetaLabel }} tabular-nums">No. {{ ($users->firstItem() ?? 1) + $loop->index }}</p>
                            <h3 class="mt-1 text-sm font-extrabold text-[color:var(--tm-text)]">{{ $listedUser->name }}</h3>
                        </div>
                        <span class="ui-status shrink-0 {{ $listedUser->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4 text-sm">
                        <div>
                            <dt class="{{ $userMetaLabel }}">Email</dt>
                            <dd class="mt-1 break-all text-[color:var(--tm-text-secondary)]">{{ $listedUser->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $userMetaLabel }}">Role</dt>
                            <dd class="mt-1 text-[color:var(--tm-text-secondary)]">{{ $displayRoles->map(fn ($role) => $role->managementLabel())->join(', ') ?: 'Belum ada role' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" data-ui-modal-open="user-view-modal-{{ $listedUser->id }}" class="{{ $userActionNeutral }}" aria-label="Lihat pengguna {{ $listedUser->name }}" title="Lihat pengguna">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                        </button>
                        <button type="button" data-ui-modal-open="user-edit-modal-{{ $listedUser->id }}" class="{{ $userActionNeutral }}" aria-label="Edit pengguna {{ $listedUser->name }}" title="Edit pengguna">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                        </button>
                        @if ($listedUser->isNot(auth()->user()))
                            <button type="button" data-password-reset-open data-user-id="{{ $listedUser->id }}" data-user-name="{{ $listedUser->name }}" data-action="{{ route('admin.users.reset-password', $listedUser) }}" class="{{ $userActionWarning }}" aria-label="Ganti password {{ $listedUser->name }}" title="Ganti password">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                            </button>
                        @else
                            <button type="button" disabled class="{{ $userActionDisabled }}" aria-label="Akun sendiri tidak dapat diganti password dari sini" title="Akun sendiri tidak dapat diganti password dari sini">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                            </button>
                        @endif
                        @if ($listedUser->isNot(auth()->user()))
                            <form method="POST" action="{{ route('admin.users.destroy', $listedUser) }}" data-swal-confirm="Apakah Anda yakin ingin menghapus user ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="{{ $userActionDanger }}" aria-label="Hapus pengguna {{ $listedUser->name }}" title="Hapus pengguna">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="{{ $userActionDisabled }}" aria-label="Akun sendiri tidak dapat dihapus" title="Akun sendiri tidak dapat dihapus">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="p-5">
                    <x-empty-state :title="$userEmptyTitle" :description="$userEmptyDescription" />
                </div>
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p class="tabular-nums">
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
    </section>

    <div id="user-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="{{ $userModalOverlay }}" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="user-create-title" class="{{ $userModalPanel }}">
                <div class="{{ $userModalHeader }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="{{ $userModalIconTile }}" aria-hidden="true">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" /><circle cx="8.75" cy="7.5" r="3.5" /><path stroke-linecap="round" d="M18 8.5v6M15 11.5h6" /></svg>
                        </span>
                        <h2 id="user-create-title" class="{{ $userModalTitle }}">Tambah Pengguna</h2>
                    </div>
                    <button type="button" data-ui-modal-close class="{{ $userModalClose }}" aria-label="Tutup dialog tambah pengguna">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>
                @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'formId' => 'create', 'prefix' => 'user-create', 'isEdit' => false, 'isModal' => true])
            </section>
        </div>
    </div>

    @foreach ($users as $listedUser)
        <div id="user-view-modal-{{ $listedUser->id }}" data-ui-modal class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="{{ $userModalOverlay }}" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="user-view-title-{{ $listedUser->id }}" class="{{ $userModalPanel }}">
                    <div class="{{ $userModalHeader }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="{{ $userModalIconTile }}" aria-hidden="true">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20v-1.25a4.75 4.75 0 0 0-4.75-4.75h-4.5A4.75 4.75 0 0 0 5 18.75V20" /><circle cx="12" cy="7.75" r="3.75" /></svg>
                            </span>
                            <h2 id="user-view-title-{{ $listedUser->id }}" class="{{ $userModalTitle }}">Detail Pengguna</h2>
                        </div>
                        <button type="button" data-ui-modal-close class="{{ $userModalClose }}" aria-label="Tutup detail pengguna {{ $listedUser->name }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>
                    @include('admin.users._detail', ['user' => $listedUser])
                    <div class="flex justify-end border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 sm:px-6">
                        <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
                    </div>
                </section>
            </div>
        </div>
    @endforeach

    @foreach ($users as $listedUser)
        <div id="user-edit-modal-{{ $listedUser->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'edit-'.$listedUser->id && $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="{{ $userModalOverlay }}" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="user-edit-title-{{ $listedUser->id }}" class="{{ $userModalPanel }}">
                    <div class="{{ $userModalHeader }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="{{ $userModalIconTile }}" aria-hidden="true">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            </span>
                            <h2 id="user-edit-title-{{ $listedUser->id }}" class="{{ $userModalTitle }}">Edit Pengguna</h2>
                        </div>
                        <button type="button" data-ui-modal-close class="{{ $userModalClose }}" aria-label="Tutup dialog edit pengguna {{ $listedUser->name }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>
                    @include('admin.users._form', ['action' => route('admin.users.update', $listedUser), 'method' => 'PUT', 'formId' => 'edit-'.$listedUser->id, 'prefix' => 'user-edit-'.$listedUser->id, 'user' => $listedUser, 'isEdit' => true, 'isModal' => true])
                </section>
            </div>
        </div>
    @endforeach

    <div id="password-reset-modal" data-password-reset-modal data-auto-user="{{ old('reset_user_id') }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="{{ $userModalOverlay }}" data-password-reset-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <section role="dialog" aria-modal="true" aria-labelledby="password-reset-title" class="{{ $userModalPanelSm }}">
                <div class="{{ $userModalHeaderStatic }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="{{ $userModalIconTile }}" aria-hidden="true">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 1 1-1.17 2.83L8 17.66V20H5.5v-2.5H3v-2.5h3.34l4.83-4.83A4 4 0 0 1 15.5 8.5Z" /><path stroke-linecap="round" d="M15.5 8.5h.01" /></svg>
                        </span>
                        <h2 id="password-reset-title" data-password-reset-title class="{{ $userModalTitle }}">Ganti password</h2>
                    </div>
                    <button type="button" data-password-reset-close class="{{ $userModalClose }}" aria-label="Tutup dialog ganti password">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
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
                    @error('temporary_password')<x-field-error :message="$message" />@enderror
                    <p class="ui-field-help">Pengguna dapat mengganti password setelah login melalui halaman ganti password.</p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" data-password-reset-close class="ui-btn ui-btn-ghost">Batal</button>
                        <button type="submit" data-password-reset-submit class="ui-btn ui-btn-warning">Simpan password</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
