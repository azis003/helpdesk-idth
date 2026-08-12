@extends('layouts.app')

@php
    $autoOpenService = old('_service_edit', request()->query('service'));

    // Presentasional saja - tidak mengubah data, logika, maupun alur halaman.
    $serviceActionBase = 'inline-flex h-9 w-9 items-center justify-center rounded-[var(--tm-r-sm)] border transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)]';
    $serviceActionNeutral = $serviceActionBase.' border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]';
    $serviceCodeChip = 'inline-flex items-center whitespace-nowrap rounded-[var(--tm-r-sm)] border border-[color:var(--tm-brand-100)] bg-[color:var(--tm-brand-50)] px-2.5 py-1 text-xs font-extrabold tracking-[0.08em] text-[color:var(--tm-brand-800)] tabular-nums';
    $serviceSlaWarning = 'inline-flex items-center whitespace-nowrap rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2.5 py-1 text-xs font-extrabold text-[color:var(--tm-warning-700)] tabular-nums';
    $serviceSlaNeutral = 'inline-flex items-center whitespace-nowrap rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2.5 py-1 text-xs font-bold text-[color:var(--tm-text-muted)]';
    $serviceMetaLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $servicePanelSoft = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
    $serviceEmptyTitle = $search !== '' ? 'Layanan tidak ditemukan' : 'Belum ada layanan';
    $serviceEmptyDescription = $search !== ''
        ? 'Tidak ada layanan yang cocok dengan pencarian. Coba kata kunci lain atau kosongkan kolom pencarian.'
        : 'Buat layanan pertama untuk mulai menyusun katalog.';
@endphp

@section('title', 'Manajemen Layanan — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Manajemen Layanan')

@section('content')
    <x-page-header
        eyebrow="Data master · Katalog layanan"
        title="Manajemen Layanan"
        description="Kelola jenis layanan, kategori, target SLA, syarat keahlian, dan formulir yang akan digunakan pemohon."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="services-heading">
        <div class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <h2 id="services-heading" class="text-lg font-extrabold tracking-tight text-[color:var(--tm-text)]">Daftar layanan</h2>
                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Lihat detail untuk mengubah layanan, atau buka preview formulir pemohon.</p>
            </div>
            <button type="button" data-ui-modal-open="service-create-modal" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat layanan
            </button>
        </div>

        <form method="GET" action="{{ route('admin.services.index') }}" class="flex flex-col gap-3 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-2 text-sm text-[color:var(--tm-text-secondary)]">
                <label for="service-per-page" class="font-bold">Tampilkan</label>
                <select id="service-per-page" name="per_page" class="ui-select w-auto tabular-nums" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto">
                <label for="service-search" class="shrink-0 text-sm font-bold text-[color:var(--tm-text-secondary)]">Cari:</label>
                <input id="service-search" name="q" type="search" value="{{ $search }}" class="ui-input w-full min-w-0 sm:w-64" placeholder="Kode atau jenis layanan" aria-label="Cari layanan">
                <button type="submit" class="ui-btn ui-btn-secondary shrink-0">Cari</button>
            </div>
        </form>

        <div class="hidden px-5 py-5 sm:px-6 md:block">
            <div class="overflow-x-auto rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)]">
                <table class="ui-table w-full min-w-[65rem] table-fixed text-left">
                    <caption class="sr-only">Daftar layanan dengan kode, jenis, kategori, target SLA, status, detail, dan preview formulir</caption>
                    <colgroup>
                        <col class="w-[4.25rem]">
                        <col class="w-[8.5rem]">
                        <col>
                        <col class="w-[8rem]">
                        <col class="w-[9rem]">
                        <col class="w-[7rem]">
                        <col class="w-[8.5rem]">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Kode Layanan</th>
                            <th scope="col">Jenis Layanan</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Target SLA</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($serviceTypes as $serviceType)
                            @php
                                $categoryLabel = $serviceType->ticket_class ?: 'Belum diatur';
                            @endphp
                            <tr>
                                <td class="align-middle font-semibold tabular-nums text-[color:var(--tm-text-muted)]">{{ ($serviceTypes->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="whitespace-nowrap align-middle"><span class="{{ $serviceCodeChip }}">{{ $serviceType->code }}</span></td>
                                <td class="max-w-0 align-middle"><p class="truncate font-bold text-[color:var(--tm-text)]" title="{{ $serviceType->name }}">{{ $serviceType->name }}</p>@if ($serviceType->description)<p class="mt-1 max-w-[28rem] truncate text-xs text-[color:var(--tm-text-muted)]" title="{{ $serviceType->description }}">{{ $serviceType->description }}</p>@endif</td>
                                <td class="whitespace-nowrap align-middle"><span class="ui-chip">{{ $categoryLabel ?: 'Belum diatur' }}</span></td>
                                <td class="whitespace-nowrap align-middle">
                                    @if ($serviceType->activeSlaPolicy?->uses_sla && $serviceType->activeSlaPolicy->target_working_days)
                                        <span class="{{ $serviceSlaWarning }}">{{ $serviceType->activeSlaPolicy->target_working_days }} hari kerja</span>
                                    @elseif ($serviceType->activeSlaPolicy)
                                        <span class="{{ $serviceSlaNeutral }}">Tidak digunakan</span>
                                    @else
                                        <span class="whitespace-nowrap text-xs font-semibold text-[color:var(--tm-text-faint)]">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap align-middle"><span class="ui-status {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="align-middle">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="service-view-modal-{{ $serviceType->id }}" class="{{ $serviceActionNeutral }}" aria-label="Lihat layanan {{ $serviceType->name }}" title="Lihat layanan"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z" /><circle cx="12" cy="12" r="2.5" /></svg></button>
                                        <button type="button" data-ui-modal-open="service-preview-modal-{{ $serviceType->id }}" class="{{ $serviceActionNeutral }}" aria-label="Preview formulir {{ $serviceType->name }}" title="Preview formulir"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-0"><x-empty-state :title="$serviceEmptyTitle" :description="$serviceEmptyDescription" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="divide-y divide-[color:var(--tm-border-subtle)] md:hidden">
            @forelse ($serviceTypes as $serviceType)
                @php
                    $categoryLabel = $serviceType->ticket_class ?: 'Belum diatur';
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="{{ $serviceMetaLabel }} tabular-nums">No. {{ ($serviceTypes->firstItem() ?? 1) + $loop->index }}</p>
                            <p class="mt-1.5"><span class="{{ $serviceCodeChip }}">{{ $serviceType->code }}</span></p>
                            <h3 class="mt-2 font-bold text-[color:var(--tm-text)]">{{ $serviceType->name }}</h3>
                        </div>
                        <span class="ui-status shrink-0 {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm {{ $servicePanelSoft }}">
                        <div><dt class="{{ $serviceMetaLabel }}">Jenis layanan</dt><dd class="mt-1 font-semibold text-[color:var(--tm-text)]">{{ $serviceType->name }}</dd></div>
                        <div><dt class="{{ $serviceMetaLabel }}">Kategori</dt><dd class="mt-1 font-semibold text-[color:var(--tm-text)]">{{ $categoryLabel ?: 'Belum diatur' }}</dd></div>
                        <div><dt class="{{ $serviceMetaLabel }}">Target SLA</dt><dd class="mt-1 font-semibold tabular-nums text-[color:var(--tm-text)]">@if ($serviceType->activeSlaPolicy?->uses_sla && $serviceType->activeSlaPolicy->target_working_days){{ $serviceType->activeSlaPolicy->target_working_days }} hari kerja @elseif ($serviceType->activeSlaPolicy)Tidak digunakan @else Belum diatur @endif</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap justify-end gap-2"><button type="button" data-ui-modal-open="service-view-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Lihat detail</button><button type="button" data-ui-modal-open="service-preview-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-primary !min-h-9 !px-3 !text-xs">Preview formulir</button></div>
                </article>
            @empty
                <div class="p-5"><x-empty-state :title="$serviceEmptyTitle" :description="$serviceEmptyDescription" /></div>
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="tabular-nums">
                @if ($serviceTypes->total() > 0)
                    Menampilkan {{ $serviceTypes->firstItem() }}–{{ $serviceTypes->lastItem() }} dari {{ $serviceTypes->total() }} layanan
                @else
                    Tidak ada data layanan
                @endif
            </p>
            @if ($serviceTypes->hasPages())<div>{{ $serviceTypes->links() }}</div>@endif
        </div>
    </section>

    @include('admin.services._create-modal')
    @foreach ($serviceTypes as $serviceType)
        @include('admin.services._view-modal', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
        @include('admin.services._edit-modal', ['serviceType' => $serviceType, 'skills' => $skills, 'ticketClasses' => $ticketClasses, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'autoOpenService' => $autoOpenService])
        @include('admin.services._preview-modal', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
    @endforeach
@endsection
