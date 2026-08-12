@extends('layouts.app')

@php
    $activeSection = $activeSection ?? 'services';
    $activeServices = $serviceTypes->where('is_active', true)->count();
    $activeFields = $serviceTypes->sum(fn ($service) => $service->activeFieldDefinitions->where('is_active', true)->count());
    $activeBuildings = $buildings->where('is_active', true)->count();
    $activeFloors = $buildings->flatMap(fn ($building) => $building->floors)->where('is_active', true)->count();
    $activeRooms = $buildings->flatMap(fn ($building) => $building->floors)->flatMap(fn ($floor) => $floor->rooms)->where('is_active', true)->count();
    $activePolicies = $attachmentPolicies->where('is_active', true)->count();
    $formatOptions = function ($field): string {
        return $field->options->map(fn ($option) => $option->value.'|'.$option->label)->implode("\n");
    };
    $formatRules = function ($field): string {
        return collect($field->validation_rules ?? [])->implode("\n");
    };

    // Presentasional saja - tidak mengubah data, logika, maupun alur halaman.
    $catPageTitle = $activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan';
    $catPageDescription = $activeSection === 'attachments'
        ? 'Atur aturan lampiran per layanan.'
        : 'Kelola katalog layanan dan formulir dinamis. Perubahan template berlaku untuk tiket baru, sementara histori tiket lama tetap utuh.';
    $catTabBase = 'whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-bold transition-[color,border-color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)]';
    $catTabActive = $catTabBase.' border-[color:var(--tm-brand-600)] text-[color:var(--tm-brand-700)]';
    $catTabIdle = $catTabBase.' border-transparent text-[color:var(--tm-text-muted)] hover:border-[color:var(--tm-border-strong)] hover:text-[color:var(--tm-text)]';
    $catTabCount = 'ml-1 text-xs font-semibold tabular-nums text-[color:var(--tm-text-faint)]';
    $catStatCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-4 py-3.5 shadow-[var(--tm-sh-xs)]';
    $catStatLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $catStatValue = 'mt-1.5 text-2xl font-extrabold tracking-tight tabular-nums text-[color:var(--tm-text)]';
    $catCodeText = 'mt-1 text-xs font-extrabold tracking-[0.08em] tabular-nums text-[color:var(--tm-brand-700)]';
    $catSkillChip = 'ui-chip !border-[color:var(--tm-border)] !bg-[color:var(--tm-sunken)] !text-[color:var(--tm-text-muted)]';
    $catMobileCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-4';
    $catMetaLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $catServicesEmptyTitle = 'Belum ada layanan';
    $catServicesEmptyDescription = 'Jalankan seeder katalog terlebih dahulu sebelum mengatur formulir layanan.';
@endphp

@section('title', ($activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan').' — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', $activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan')

@section('content')
    <x-page-header eyebrow="Master data" :title="$catPageTitle" :description="$catPageDescription" />

    <nav class="mt-6 overflow-x-auto" aria-label="Bagian master data">
        <div class="inline-flex min-w-full gap-1 border-b border-[color:var(--tm-border-subtle)] sm:min-w-0">
            <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="{{ $activeSection === 'services' ? $catTabActive : $catTabIdle }}" @if ($activeSection === 'services') aria-current="page" @endif>Manajemen Layanan <span class="{{ $catTabCount }}">{{ $activeServices }}</span></a>
            <a href="{{ route('admin.locations.index') }}" class="{{ $activeSection === 'locations' ? $catTabActive : $catTabIdle }}">Lokasi <span class="{{ $catTabCount }}">{{ $activeBuildings }}</span></a>
            <a href="{{ route('admin.catalog.index', ['section' => 'attachments']) }}" class="{{ $activeSection === 'attachments' ? $catTabActive : $catTabIdle }}" @if ($activeSection === 'attachments') aria-current="page" @endif>Kebijakan lampiran <span class="{{ $catTabCount }}">{{ $activePolicies }}</span></a>
        </div>
    </nav>

    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan {{ $activeSection === 'services' ? 'layanan dan formulir' : ($activeSection === 'locations' ? 'lokasi' : 'kebijakan lampiran') }}">
        @if ($activeSection === 'services')
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Layanan aktif</p><p class="{{ $catStatValue }}">{{ $activeServices }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Field aktif</p><p class="{{ $catStatValue }}">{{ $activeFields }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Layanan dikelola</p><p class="{{ $catStatValue }}">{{ $serviceTypes->count() }}</p></article>
        @elseif ($activeSection === 'locations')
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Gedung aktif</p><p class="{{ $catStatValue }}">{{ $activeBuildings }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Lantai aktif</p><p class="{{ $catStatValue }}">{{ $activeFloors }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Ruangan aktif</p><p class="{{ $catStatValue }}">{{ $activeRooms }}</p></article>
        @else
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Kebijakan aktif</p><p class="{{ $catStatValue }}">{{ $activePolicies }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Total kebijakan</p><p class="{{ $catStatValue }}">{{ $attachmentPolicies->count() }}</p></article>
            <article class="{{ $catStatCard }}"><p class="{{ $catStatLabel }}">Layanan tercakup</p><p class="{{ $catStatValue }}">{{ $attachmentPolicies->pluck('service_type_id')->filter()->unique()->count() }}</p></article>
        @endif
    </section>

    @if ($activeSection === 'services')
    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="services-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Definisi layanan</p>
                <h2 id="services-heading" class="mt-2 ui-section-title">Daftar layanan</h2>
                <p class="ui-section-description">Pilih layanan untuk mengatur field, visibilitas, keahlian penanganan, dan versi formulir yang akan dipakai pada tiket baru.</p>
            </div>
            <span class="ui-count shrink-0 tabular-nums">{{ $serviceTypes->count() }} layanan canonical</span>
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="ui-table min-w-[64rem]">
                <caption class="sr-only">Daftar layanan, formulir, pemetaan keahlian, kelas tiket, status, dan aksi</caption>
                <thead>
                    <tr>
                        <th scope="col">Layanan</th>
                        <th scope="col">Formulir dan pemetaan</th>
                        <th scope="col">Kelas tiket</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceTypes as $serviceType)
                        <tr>
                            <td>
                                <div class="min-w-[16rem]">
                                    <p class="font-bold text-[color:var(--tm-text)]">{{ $serviceType->name }}</p>
                                    <p class="{{ $catCodeText }}">{{ $serviceType->code }}</p>
                                    @if ($serviceType->description)
                                        <p class="mt-1 max-w-[22rem] truncate text-xs text-[color:var(--tm-text-muted)]">{{ $serviceType->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex min-w-[14rem] flex-wrap gap-1.5">
                                    <span class="ui-chip">{{ $serviceType->activeFieldDefinitions->count() }} field aktif</span>
                                    <span class="{{ $catSkillChip }}">{{ $serviceType->skills->where('is_active', true)->count() }} keahlian aktif</span>
                                </div>
                            </td>
                            <td>
                                <span class="ui-chip">{{ $serviceType->ticket_class ?? 'Belum diatur' }}</span>
                            </td>
                            <td>
                                <span class="ui-status {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex min-w-[15rem] flex-wrap justify-end gap-2">
                                    <button type="button" data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs" aria-label="Buka editor formulir {{ $serviceType->name }}">Buka editor</button>
                                    <form method="POST" action="{{ route('admin.catalog.services.status', [$serviceType, $serviceType->is_active ? 'deactivate' : 'activate']) }}" @if ($serviceType->is_active) data-swal-confirm="Nonaktifkan layanan ini? Layanan tidak tampil bagi pemohon." @endif>
                                        @csrf
                                        <button type="submit" class="ui-btn {{ $serviceType->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !px-3 !text-xs">{{ $serviceType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-0"><x-empty-state :title="$catServicesEmptyTitle" :description="$catServicesEmptyDescription" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 lg:hidden">
            @forelse ($serviceTypes as $serviceType)
                <article class="{{ $catMobileCard }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold tracking-[0.08em] tabular-nums text-[color:var(--tm-brand-700)]">{{ $serviceType->code }}</p>
                            <h4 class="mt-1 text-sm font-extrabold leading-5 text-[color:var(--tm-text)]">{{ $serviceType->name }}</h4>
                        </div>
                        <span class="ui-status shrink-0 {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    @if ($serviceType->description)
                        <p class="mt-3 text-xs leading-5 text-[color:var(--tm-text-muted)]">{{ $serviceType->description }}</p>
                    @endif
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-[color:var(--tm-border-subtle)] pt-3 text-xs">
                        <div><dt class="{{ $catMetaLabel }}">Field aktif</dt><dd class="mt-1 font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $serviceType->activeFieldDefinitions->count() }}</dd></div>
                        <div><dt class="{{ $catMetaLabel }}">Keahlian aktif</dt><dd class="mt-1 font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $serviceType->skills->where('is_active', true)->count() }}</dd></div>
                        <div><dt class="{{ $catMetaLabel }}">Kelas tiket</dt><dd class="mt-1 font-extrabold text-[color:var(--tm-text)]">{{ $serviceType->ticket_class ?? 'Belum diatur' }}</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-[color:var(--tm-border-subtle)] pt-3">
                        <button type="button" data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs" aria-label="Buka editor formulir {{ $serviceType->name }}">Buka editor</button>
                        <form method="POST" action="{{ route('admin.catalog.services.status', [$serviceType, $serviceType->is_active ? 'deactivate' : 'activate']) }}" @if ($serviceType->is_active) data-swal-confirm="Nonaktifkan layanan ini? Layanan tidak tampil bagi pemohon." @endif>
                            @csrf
                            <button type="submit" class="ui-btn {{ $serviceType->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !px-3 !text-xs">{{ $serviceType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <x-empty-state :title="$catServicesEmptyTitle" :description="$catServicesEmptyDescription" />
            @endforelse
        </div>
    </section>

    @foreach ($serviceTypes as $serviceType)
        @include('admin.catalog._service-edit-modal', ['serviceType' => $serviceType, 'ticketClasses' => $ticketClasses, 'skills' => $skills, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'formatOptions' => $formatOptions, 'formatRules' => $formatRules])
    @endforeach
    @endif

    @if ($activeSection === 'locations')
        @include('admin.catalog._locations-section', ['buildings' => $buildings])
    @endif

    @if ($activeSection === 'attachments')
        @include('admin.catalog._attachments-section', ['attachmentPolicies' => $attachmentPolicies, 'serviceTypes' => $serviceTypes])
        <p class="mt-8 text-xs leading-5 text-[color:var(--tm-text-muted)]">Kebijakan ini mengatur batas dan tipe berkas. Penyimpanan privat, akses, dan kontrol khusus layanan tetap mengikuti alur tiket.</p>
    @endif
@endsection
