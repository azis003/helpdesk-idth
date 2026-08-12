@extends('layouts.app')

@php
    $autoOpenForm = old('_location_form');
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

    <section class="ui-admin-panel mt-7 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="locations-heading">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="locations-heading" class="text-xl font-extrabold tracking-tight">Daftar Gedung</h2>
                <p class="mt-1 text-xs text-white/75">Setiap gedung memiliki daftar lantai yang dapat dikelola.</p>
            </div>
            <button type="button" data-ui-modal-open="location-building-create-modal" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#7138e8] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#075998]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah gedung
            </button>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <form method="GET" action="{{ route('admin.locations.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-[#17212b]">
                    <label for="location-per-page" class="font-bold">Tampilkan</label>
                    <select id="location-per-page" name="per_page" class="h-10 rounded-lg border border-[#d7e0e4] bg-white px-3 text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15" onchange="this.form.submit()">
                        @foreach ([10, 25, 50] as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                    <span>gedung</span>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <label for="location-search" class="shrink-0 text-sm font-bold text-[#17212b]">Cari:</label>
                    <input id="location-search" name="q" type="search" value="{{ $search }}" class="h-10 w-full min-w-0 rounded-lg border border-[#d7e0e4] bg-[#f8fafb] px-3 text-sm text-[#17212b] outline-none placeholder:text-[#9baab0] focus:border-[#0a87c9] focus:bg-white focus:ring-2 focus:ring-[#0a87c9]/15 sm:w-64" placeholder="Cari gedung atau lantai" aria-label="Cari gedung atau lantai">
                    <button type="submit" class="sr-only">Cari gedung atau lantai</button>
                </div>
            </form>

            <div class="mt-4 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
                <table class="min-w-[980px] w-full border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar gedung beserta lantai, status, dan aksi pengelolaan</caption>
                    <thead class="bg-[#fbfcfd] text-[#34495a]">
                        <tr>
                            <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                            <th scope="col" class="w-64 border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Nama Gedung</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Daftar Lantai</th>
                            <th scope="col" class="w-28 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Status</th>
                            <th scope="col" class="w-56 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ ($buildings->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                    <p class="font-semibold text-[#112b49]">{{ $building->name }}</p>
                                    <p class="mt-1 text-xs text-[#718088]">{{ $building->floors->count() }} lantai terdaftar</p>
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                    @if ($building->floors->isNotEmpty())
                                        <ul class="grid gap-2 sm:grid-cols-2" aria-label="Lantai pada {{ $building->name }}">
                                            @foreach ($building->floors as $floor)
                                                <li class="flex min-w-0 items-center justify-between gap-2 rounded-lg border border-[#e0e8eb] bg-white px-3 py-2">
                                                    <div class="min-w-0">
                                                        <p class="truncate font-semibold text-[#35505b]">{{ $floor->name }}</p>
                                                        <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[0.65rem] font-bold {{ $floor->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                    </div>
                                                    <button type="button" data-ui-modal-open="location-floor-edit-modal-{{ $floor->id }}" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="Edit lantai {{ $floor->name }}" title="Edit lantai">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-[#78909a]">Belum ada lantai.</p>
                                    @endif
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center align-top">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $building->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $building->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @if ($building->is_active)
                                            <button type="button" data-ui-modal-open="location-floor-create-modal-{{ $building->id }}" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-[#7138e8] px-3 text-xs font-bold text-white transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7138e8] focus-visible:ring-offset-2" aria-label="Tambah lantai pada {{ $building->name }}" title="Tambah lantai">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                                <span>Tambah lantai</span>
                                            </button>
                                        @else
                                            <button type="button" disabled class="inline-flex h-9 cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-[#7138e8] px-3 text-xs font-bold text-white opacity-45" aria-label="Aktifkan gedung terlebih dahulu untuk menambah lantai pada {{ $building->name }}" title="Aktifkan gedung terlebih dahulu">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                                <span>Tambah lantai</span>
                                            </button>
                                        @endif
                                        <button type="button" data-ui-modal-open="location-building-edit-modal-{{ $building->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Edit gedung {{ $building->name }}" title="Edit gedung">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.catalog.buildings.status', [$building, $building->is_active ? 'deactivate' : 'activate']) }}" @if ($building->is_active) data-swal-confirm="Nonaktifkan gedung {{ $building->name }}? Pastikan semua lantai sudah nonaktif." @endif>
                                            @csrf
                                            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $building->is_active ? 'bg-[#e94f70] text-white hover:bg-[#d63d5e] focus-visible:ring-[#e94f70]' : 'bg-[#e8faf4] text-[#087f5b] hover:bg-[#cef4e5] focus-visible:ring-[#087f5b]' }}">{{ $building->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-[#718088]">{{ $search !== '' ? 'Tidak ada gedung atau lantai yang cocok dengan pencarian.' : 'Belum ada gedung yang terdaftar.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($buildings->total() > 0)
                        Menampilkan {{ $buildings->firstItem() }}–{{ $buildings->lastItem() }} dari {{ $buildings->total() }} gedung
                    @else
                        Tidak ada data gedung
                    @endif
                </p>
                @if ($buildings->hasPages())
                    <div>{{ $buildings->links() }}</div>
                @endif
            </div>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($buildings as $building)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($buildings->firstItem() ?? 1) + $loop->index }}</p>
                            <h3 class="mt-1 font-bold text-[#112b49]">{{ $building->name }}</h3>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $building->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $building->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    <div class="mt-4 rounded-lg bg-[#f8fafb] p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Daftar lantai</h4>
                            <span class="text-xs font-semibold text-[#526f79]">{{ $building->floors->count() }} lantai</span>
                        </div>
                        @if ($building->floors->isNotEmpty())
                            <ul class="mt-3 space-y-2" aria-label="Lantai pada {{ $building->name }}">
                                @foreach ($building->floors as $floor)
                                    <li class="flex items-center justify-between gap-2 rounded-lg border border-[#e0e8eb] bg-white px-3 py-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-[#35505b]">{{ $floor->name }}</p>
                                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[0.65rem] font-bold {{ $floor->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                        <div class="flex shrink-0 gap-2">
                                            <button type="button" data-ui-modal-open="location-floor-edit-modal-{{ $floor->id }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="Edit lantai {{ $floor->name }}" title="Edit lantai">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                            </button>
                                            <form method="POST" action="{{ route('admin.catalog.floors.status', [$floor, $floor->is_active ? 'deactivate' : 'activate']) }}" @if ($floor->is_active) data-swal-confirm="Nonaktifkan lantai {{ $floor->name }}?" @endif>
                                                @csrf
                                                <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg px-2.5 text-[0.68rem] font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $floor->is_active ? 'bg-[#e94f70] text-white hover:bg-[#d63d5e] focus-visible:ring-[#e94f70]' : 'bg-[#e8faf4] text-[#087f5b] hover:bg-[#cef4e5] focus-visible:ring-[#087f5b]' }}">{{ $floor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-3 text-sm text-[#78909a]">Belum ada lantai pada gedung ini.</p>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        @if ($building->is_active)
                            <button type="button" data-ui-modal-open="location-floor-create-modal-{{ $building->id }}" class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg bg-[#7138e8] px-3 text-xs font-bold text-white transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7138e8] focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                Tambah lantai
                            </button>
                        @endif
                        <button type="button" data-ui-modal-open="location-building-edit-modal-{{ $building->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Edit gedung {{ $building->name }}" title="Edit gedung">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.catalog.buildings.status', [$building, $building->is_active ? 'deactivate' : 'activate']) }}" @if ($building->is_active) data-swal-confirm="Nonaktifkan gedung {{ $building->name }}? Pastikan semua lantai sudah nonaktif." @endif>
                            @csrf
                            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $building->is_active ? 'bg-[#e94f70] text-white hover:bg-[#d63d5e] focus-visible:ring-[#e94f70]' : 'bg-[#e8faf4] text-[#087f5b] hover:bg-[#cef4e5] focus-visible:ring-[#087f5b]' }}">{{ $building->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-[#718088]">{{ $search !== '' ? 'Tidak ada gedung atau lantai yang cocok dengan pencarian.' : 'Belum ada gedung yang terdaftar.' }}</p>
            @endforelse
        </div>
    </section>

    <div id="location-building-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'building-create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="location-building-create-title" class="relative w-full max-w-lg overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                    <h2 id="location-building-create-title" class="text-lg font-extrabold">Tambah Gedung</h2>
                    <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog tambah gedung">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>
                @include('admin.locations._building-form', ['action' => route('admin.catalog.buildings.store'), 'method' => 'POST', 'formId' => 'building-create', 'prefix' => 'location-building-create', 'building' => null, 'isModal' => true])
            </section>
        </div>
    </div>

    @foreach ($buildings as $building)
        <div id="location-building-edit-modal-{{ $building->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'building-edit-'.$building->id && $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="location-building-edit-title-{{ $building->id }}" class="relative w-full max-w-lg overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                        <h2 id="location-building-edit-title-{{ $building->id }}" class="text-lg font-extrabold">Edit Gedung</h2>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog edit gedung {{ $building->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>
                    @include('admin.locations._building-form', ['action' => route('admin.catalog.buildings.update', $building), 'method' => 'PUT', 'formId' => 'building-edit-'.$building->id, 'prefix' => 'location-building-edit-'.$building->id, 'building' => $building, 'isModal' => true])
                </section>
            </div>
        </div>

        @if ($building->is_active)
            <div id="location-floor-create-modal-{{ $building->id }}" data-ui-modal data-auto-open="{{ $autoOpenForm === 'floor-create-'.$building->id && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
                <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                    <section role="dialog" aria-modal="true" aria-labelledby="location-floor-create-title-{{ $building->id }}" class="relative w-full max-w-lg overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                        <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                            <h2 id="location-floor-create-title-{{ $building->id }}" class="text-lg font-extrabold">Tambah Lantai</h2>
                            <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog tambah lantai pada {{ $building->name }}">
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
                <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
                <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                    <section role="dialog" aria-modal="true" aria-labelledby="location-floor-edit-title-{{ $floor->id }}" class="relative w-full max-w-lg overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                        <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                            <h2 id="location-floor-edit-title-{{ $floor->id }}" class="text-lg font-extrabold">Edit Lantai</h2>
                            <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog edit lantai {{ $floor->name }}">
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
