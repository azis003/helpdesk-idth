@extends('layouts.app')

@php
    $autoOpenForm = old('_location_form');
@endphp

@php
    // Presentasional saja: kelas tombol dipakai di tabel desktop dan kartu mobile.
    $locIconBtn = 'inline-flex shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]';
    $locAddFloorBtn = 'inline-flex h-9 items-center justify-center gap-1.5 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] px-3 text-xs font-bold text-[color:var(--tm-brand-700)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-100)]';
    $locAddFloorDisabled = 'inline-flex h-9 cursor-not-allowed items-center justify-center gap-1.5 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 text-xs font-bold text-[color:var(--tm-text-faint)]';
    $locToggleDanger = 'inline-flex h-9 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] px-3 text-xs font-bold text-[color:var(--tm-danger-700)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-danger-100)]';
    $locToggleSuccess = 'inline-flex h-9 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] px-3 text-xs font-bold text-[color:var(--tm-success-700)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-success-100)]';
    $locToggleDangerSm = 'inline-flex h-8 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] px-2.5 text-[0.68rem] font-bold text-[color:var(--tm-danger-700)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-danger-100)]';
    $locToggleSuccessSm = 'inline-flex h-8 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] px-2.5 text-[0.68rem] font-bold text-[color:var(--tm-success-700)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-success-100)]';
    $locFloorCard = 'flex min-w-0 items-center justify-between gap-2 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 py-2 transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-200)]';
    $locModalOverlay = 'absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]';
    $locModalPanel = 'relative w-full max-w-lg overflow-y-auto rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]';
    $locModalHeader = 'sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6';
    $locModalTitle = 'truncate text-base font-bold text-[color:var(--tm-text)]';
    $locModalIconTile = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)]';
    $locModalClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] text-[color:var(--tm-text-muted)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]';
    $locEmptyTitle = $search !== '' ? 'Tidak ada gedung atau lantai yang cocok' : 'Belum ada gedung';
    $locEmptyDescription = $search !== '' ? 'Coba kata kunci lain atau kosongkan kolom pencarian.' : 'Tambahkan gedung terlebih dahulu, lalu lengkapi daftar lantainya.';
@endphp

@section('title', 'Manajemen Lokasi — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Lokasi')
@section('header_title', 'Lokasi')

@section('content')
    <x-page-header
        eyebrow="Data Master · Struktur lokasi"
        title="Manajemen Lokasi"
        description="Kelola gedung dan lantai dalam satu daftar agar lokasi mudah dipilih saat membuat tiket."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="locations-heading">
        <div class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <h2 id="locations-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Daftar Gedung</h2>
                <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-muted)]">Setiap gedung memiliki daftar lantai yang dapat dikelola.</p>
            </div>
            <button type="button" data-ui-modal-open="location-building-create-modal" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah gedung
            </button>
        </div>

        <form method="GET" action="{{ route('admin.locations.index') }}" class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-2 text-sm text-[color:var(--tm-text-secondary)]">
                <label for="location-per-page" class="font-bold">Tampilkan</label>
                <select id="location-per-page" name="per_page" class="ui-select w-auto tabular-nums" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
                <span>gedung</span>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto">
                <label for="location-search" class="shrink-0 text-sm font-bold text-[color:var(--tm-text-secondary)]">Cari:</label>
                <input id="location-search" name="q" type="search" value="{{ $search }}" class="ui-input min-w-0 sm:w-64" placeholder="Cari gedung atau lantai" aria-label="Cari gedung atau lantai">
                <button type="submit" class="ui-btn ui-btn-secondary shrink-0">Cari</button>
            </div>
        </form>

        <div class="px-5 py-5 sm:px-6">
            <div class="hidden overflow-x-auto rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] md:block">
                <table class="ui-table w-full min-w-[62rem]">
                    <caption class="sr-only">Daftar gedung beserta lantai, status, dan aksi pengelolaan</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="w-14 text-center">No</th>
                            <th scope="col" class="w-56">Nama Gedung</th>
                            <th scope="col">Daftar Lantai</th>
                            <th scope="col" class="w-28 text-center">Status</th>
                            <th scope="col" class="w-56 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr>
                                <td class="text-center align-top font-semibold tabular-nums text-[color:var(--tm-text-muted)]">{{ ($buildings->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="align-top">
                                    <p class="font-semibold text-[color:var(--tm-text)]">{{ $building->name }}</p>
                                    <p class="mt-1 text-xs tabular-nums text-[color:var(--tm-text-faint)]">{{ $building->floors->count() }} lantai terdaftar</p>
                                </td>
                                <td class="align-top">
                                    @if ($building->floors->isNotEmpty())
                                        <ul class="grid gap-2 sm:grid-cols-2" aria-label="Lantai pada {{ $building->name }}">
                                            @foreach ($building->floors as $floor)
                                                <li class="{{ $locFloorCard }}">
                                                    <div class="min-w-0">
                                                        <p class="truncate font-semibold text-[color:var(--tm-text)]">{{ $floor->name }}</p>
                                                        <span class="ui-status {{ $floor->is_active ? 'ui-status-active' : 'ui-status-inactive' }} mt-1">{{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                    </div>
                                                    <button type="button" data-ui-modal-open="location-floor-edit-modal-{{ $floor->id }}" class="{{ $locIconBtn }} h-8 w-8" aria-label="Edit lantai {{ $floor->name }}" title="Edit lantai">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-[color:var(--tm-text-faint)]">Belum ada lantai.</p>
                                    @endif
                                </td>
                                <td class="text-center align-top">
                                    <span class="ui-status {{ $building->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $building->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td class="align-top">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @if ($building->is_active)
                                            <button type="button" data-ui-modal-open="location-floor-create-modal-{{ $building->id }}" class="{{ $locAddFloorBtn }}" aria-label="Tambah lantai pada {{ $building->name }}" title="Tambah lantai">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                                <span>Tambah lantai</span>
                                            </button>
                                        @else
                                            <button type="button" disabled class="{{ $locAddFloorDisabled }}" aria-label="Aktifkan gedung terlebih dahulu untuk menambah lantai pada {{ $building->name }}" title="Aktifkan gedung terlebih dahulu">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                                <span>Tambah lantai</span>
                                            </button>
                                        @endif
                                        <button type="button" data-ui-modal-open="location-building-edit-modal-{{ $building->id }}" class="{{ $locIconBtn }} h-9 w-9" aria-label="Edit gedung {{ $building->name }}" title="Edit gedung">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.catalog.buildings.status', [$building, $building->is_active ? 'deactivate' : 'activate']) }}" @if ($building->is_active) data-swal-confirm="Nonaktifkan gedung {{ $building->name }}? Pastikan semua lantai sudah nonaktif." @endif>
                                            @csrf
                                            <button type="submit" class="{{ $building->is_active ? $locToggleDanger : $locToggleSuccess }}">{{ $building->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <x-empty-state :title="$locEmptyTitle" :description="$locEmptyDescription" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($buildings->total() > 0)
                        Menampilkan <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $buildings->firstItem() }}–{{ $buildings->lastItem() }}</span> dari <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $buildings->total() }}</span> gedung
                    @else
                        Tidak ada data gedung
                    @endif
                </p>
                @if ($buildings->hasPages())
                    <div>{{ $buildings->links() }}</div>
                @endif
            </div>
        </div>

        <div class="divide-y divide-[color:var(--tm-border-subtle)] border-t border-[color:var(--tm-border-subtle)] md:hidden">
            @forelse ($buildings as $building)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide tabular-nums text-[color:var(--tm-text-faint)]">No. {{ ($buildings->firstItem() ?? 1) + $loop->index }}</p>
                            <h3 class="mt-1 font-bold text-[color:var(--tm-text)]">{{ $building->name }}</h3>
                        </div>
                        <span class="ui-status {{ $building->is_active ? 'ui-status-active' : 'ui-status-inactive' }} shrink-0">{{ $building->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    <div class="mt-4 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-[color:var(--tm-text-faint)]">Daftar lantai</h4>
                            <span class="text-xs font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $building->floors->count() }} lantai</span>
                        </div>
                        @if ($building->floors->isNotEmpty())
                            <ul class="mt-3 space-y-2" aria-label="Lantai pada {{ $building->name }}">
                                @foreach ($building->floors as $floor)
                                    <li class="{{ $locFloorCard }}">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-[color:var(--tm-text)]">{{ $floor->name }}</p>
                                            <span class="ui-status {{ $floor->is_active ? 'ui-status-active' : 'ui-status-inactive' }} mt-1">{{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <button type="button" data-ui-modal-open="location-floor-edit-modal-{{ $floor->id }}" class="{{ $locIconBtn }} h-8 w-8" aria-label="Edit lantai {{ $floor->name }}" title="Edit lantai">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                            </button>
                                            <form method="POST" action="{{ route('admin.catalog.floors.status', [$floor, $floor->is_active ? 'deactivate' : 'activate']) }}" @if ($floor->is_active) data-swal-confirm="Nonaktifkan lantai {{ $floor->name }}?" @endif>
                                                @csrf
                                                <button type="submit" class="{{ $floor->is_active ? $locToggleDangerSm : $locToggleSuccessSm }}">{{ $floor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-3 text-sm text-[color:var(--tm-text-faint)]">Belum ada lantai pada gedung ini.</p>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        @if ($building->is_active)
                            <button type="button" data-ui-modal-open="location-floor-create-modal-{{ $building->id }}" class="{{ $locAddFloorBtn }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                Tambah lantai
                            </button>
                        @endif
                        <button type="button" data-ui-modal-open="location-building-edit-modal-{{ $building->id }}" class="{{ $locIconBtn }} h-9 w-9" aria-label="Edit gedung {{ $building->name }}" title="Edit gedung">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.catalog.buildings.status', [$building, $building->is_active ? 'deactivate' : 'activate']) }}" @if ($building->is_active) data-swal-confirm="Nonaktifkan gedung {{ $building->name }}? Pastikan semua lantai sudah nonaktif." @endif>
                            @csrf
                            <button type="submit" class="{{ $building->is_active ? $locToggleDanger : $locToggleSuccess }}">{{ $building->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="p-5">
                    <x-empty-state :title="$locEmptyTitle" :description="$locEmptyDescription" />
                </div>
            @endforelse
        </div>
    </section>

    <div id="location-building-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'building-create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="{{ $locModalOverlay }}" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="location-building-create-title" class="{{ $locModalPanel }}">
                <div class="{{ $locModalHeader }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="{{ $locModalIconTile }}" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v15M15 20V9h3a1 1 0 0 1 1 1v10M3 20h18M8 8h3M8 12h3M8 16h3" /></svg>
                        </span>
                        <h2 id="location-building-create-title" class="{{ $locModalTitle }}">Tambah Gedung</h2>
                    </div>
                    <button type="button" data-ui-modal-close class="{{ $locModalClose }}" aria-label="Tutup dialog tambah gedung">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>
                @include('admin.locations._building-form', ['action' => route('admin.catalog.buildings.store'), 'method' => 'POST', 'formId' => 'building-create', 'prefix' => 'location-building-create', 'building' => null, 'isModal' => true])
            </section>
        </div>
    </div>

    @foreach ($buildings as $building)
        <div id="location-building-edit-modal-{{ $building->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'building-edit-'.$building->id && $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="{{ $locModalOverlay }}" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="location-building-edit-title-{{ $building->id }}" class="{{ $locModalPanel }}">
                    <div class="{{ $locModalHeader }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="{{ $locModalIconTile }}" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            </span>
                            <h2 id="location-building-edit-title-{{ $building->id }}" class="{{ $locModalTitle }}">Edit Gedung</h2>
                        </div>
                        <button type="button" data-ui-modal-close class="{{ $locModalClose }}" aria-label="Tutup dialog edit gedung {{ $building->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>
                    @include('admin.locations._building-form', ['action' => route('admin.catalog.buildings.update', $building), 'method' => 'PUT', 'formId' => 'building-edit-'.$building->id, 'prefix' => 'location-building-edit-'.$building->id, 'building' => $building, 'isModal' => true])
                </section>
            </div>
        </div>

        @if ($building->is_active)
            <div id="location-floor-create-modal-{{ $building->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'floor-create-'.$building->id && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="{{ $locModalOverlay }}" data-ui-modal-close></div>
                <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                    <section role="dialog" aria-modal="true" aria-labelledby="location-floor-create-title-{{ $building->id }}" class="{{ $locModalPanel }}">
                        <div class="{{ $locModalHeader }}">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="{{ $locModalIconTile }}" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 5-9 5-9-5 9-5ZM3 13l9 5 9-5" /></svg>
                                </span>
                                <h2 id="location-floor-create-title-{{ $building->id }}" class="{{ $locModalTitle }}">Tambah Lantai</h2>
                            </div>
                            <button type="button" data-ui-modal-close class="{{ $locModalClose }}" aria-label="Tutup dialog tambah lantai pada {{ $building->name }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                            </button>
                        </div>
                        @include('admin.locations._floor-form', ['action' => route('admin.catalog.floors.store', $building), 'method' => 'POST', 'formId' => 'floor-create-'.$building->id, 'prefix' => 'location-floor-create-'.$building->id, 'building' => $building, 'floor' => null, 'isModal' => true])
                    </section>
                </div>
            </div>
        @endif

        @foreach ($building->floors as $floor)
            <div id="location-floor-edit-modal-{{ $floor->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'floor-edit-'.$floor->id && $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="{{ $locModalOverlay }}" data-ui-modal-close></div>
                <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                    <section role="dialog" aria-modal="true" aria-labelledby="location-floor-edit-title-{{ $floor->id }}" class="{{ $locModalPanel }}">
                        <div class="{{ $locModalHeader }}">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="{{ $locModalIconTile }}" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                </span>
                                <h2 id="location-floor-edit-title-{{ $floor->id }}" class="{{ $locModalTitle }}">Edit Lantai</h2>
                            </div>
                            <button type="button" data-ui-modal-close class="{{ $locModalClose }}" aria-label="Tutup dialog edit lantai {{ $floor->name }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                            </button>
                        </div>
                        @include('admin.locations._floor-form', ['action' => route('admin.catalog.floors.update', $floor), 'method' => 'PUT', 'formId' => 'floor-edit-'.$floor->id, 'prefix' => 'location-floor-edit-'.$floor->id, 'building' => $building, 'floor' => $floor, 'isModal' => true])
                    </section>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection
