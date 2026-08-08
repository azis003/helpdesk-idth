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
@endphp

@section('title', ($activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan').' — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', $activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Master data</p>
            <h1 class="ui-page-title">{{ $activeSection === 'attachments' ? 'Kebijakan lampiran' : 'Manajemen Layanan' }}</h1>
            <p class="ui-page-description">{{ $activeSection === 'attachments' ? 'Atur aturan lampiran per layanan.' : 'Kelola katalog layanan dan formulir dinamis. Perubahan template berlaku untuk tiket baru, sementara histori tiket lama tetap utuh.' }}</p>
        </div>
    </div>

    <nav class="mt-7 overflow-x-auto" aria-label="Bagian master data">
        <div class="inline-flex min-w-full gap-1 border-b border-[#dfe8ec] sm:min-w-0">
            <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-bold transition {{ $activeSection === 'services' ? 'border-[#17313c] text-[#17313c]' : 'border-transparent text-[#78909a] hover:border-[#b9cbd1] hover:text-[#35505b]' }}" @if ($activeSection === 'services') aria-current="page" @endif>Manajemen Layanan <span class="ml-1 text-xs font-semibold text-[#8aa0a8]">{{ $activeServices }}</span></a>
            <a href="{{ route('admin.locations.index') }}" class="whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-bold transition {{ $activeSection === 'locations' ? 'border-[#17313c] text-[#17313c]' : 'border-transparent text-[#78909a] hover:border-[#b9cbd1] hover:text-[#35505b]' }}">Lokasi <span class="ml-1 text-xs font-semibold text-[#8aa0a8]">{{ $activeBuildings }}</span></a>
            <a href="{{ route('admin.catalog.index', ['section' => 'attachments']) }}" class="whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-bold transition {{ $activeSection === 'attachments' ? 'border-[#17313c] text-[#17313c]' : 'border-transparent text-[#78909a] hover:border-[#b9cbd1] hover:text-[#35505b]' }}" @if ($activeSection === 'attachments') aria-current="page" @endif>Kebijakan lampiran <span class="ml-1 text-xs font-semibold text-[#8aa0a8]">{{ $activePolicies }}</span></a>
        </div>
    </nav>

    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan {{ $activeSection === 'services' ? 'layanan dan formulir' : ($activeSection === 'locations' ? 'lokasi' : 'kebijakan lampiran') }}">
        @if ($activeSection === 'services')
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Layanan aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activeServices }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Field aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activeFields }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Layanan dikelola</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $serviceTypes->count() }}</p></article>
        @elseif ($activeSection === 'locations')
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Gedung aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activeBuildings }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Lantai aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activeFloors }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Ruangan aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activeRooms }}</p></article>
        @else
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Kebijakan aktif</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $activePolicies }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Total kebijakan</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $attachmentPolicies->count() }}</p></article>
            <article class="rounded-2xl border border-[#dfe8ec] bg-white px-4 py-3.5"><p class="text-xs font-bold text-[#78909a]">Layanan tercakup</p><p class="mt-1 text-2xl font-extrabold tracking-tight text-[#17313c]">{{ $attachmentPolicies->pluck('service_type_id')->filter()->unique()->count() }}</p></article>
        @endif
    </section>

    @if ($activeSection === 'services')
    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="services-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Definisi layanan</p>
                <h2 id="services-heading" class="mt-2 ui-section-title">Daftar layanan</h2>
                <p class="ui-section-description">Pilih layanan untuk mengatur field, visibilitas, keahlian penanganan, dan versi formulir yang akan dipakai pada tiket baru.</p>
            </div>
            <span class="ui-chip shrink-0">{{ $serviceTypes->count() }} layanan canonical</span>
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
                                    <p class="font-bold text-[#17313c]">{{ $serviceType->name }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#26677b]">{{ $serviceType->code }}</p>
                                    @if ($serviceType->description)
                                        <p class="mt-1 max-w-[22rem] truncate text-xs text-[#78909a]">{{ $serviceType->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex min-w-[14rem] flex-wrap gap-1.5">
                                    <span class="ui-chip">{{ $serviceType->activeFieldDefinitions->count() }} field aktif</span>
                                    <span class="ui-chip !border-[#dfe8ec] !bg-[#f7fafb] !text-[#607681]">{{ $serviceType->skills->where('is_active', true)->count() }} keahlian aktif</span>
                                </div>
                            </td>
                            <td>
                                @if ($serviceType->code === 'SVC-05')
                                    <div class="flex min-w-[10rem] flex-wrap gap-1.5">
                                        @foreach ($serviceType->variants as $variant)
                                            <span class="ui-chip !border-[#dfe8ec] !bg-[#f7fafb] !text-[#607681]">{{ $variant->ticket_class }} · {{ $variant->label }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="ui-chip">{{ $serviceType->ticket_class ?? 'Belum diatur' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="ui-status {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex min-w-[15rem] flex-wrap justify-end gap-2">
                                    <button type="button" data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs" aria-label="Buka editor formulir {{ $serviceType->name }}">Buka editor</button>
                                    <form method="POST" action="{{ route('admin.catalog.services.status', [$serviceType, $serviceType->is_active ? 'deactivate' : 'activate']) }}" @if ($serviceType->is_active) data-swal-confirm="Nonaktifkan layanan ini? Layanan tidak tampil bagi pemohon." @endif>
                                        @csrf
                                        <button type="submit" class="ui-btn {{ $serviceType->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !px-3 !text-xs">{{ $serviceType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="ui-empty m-4">Belum ada layanan. Jalankan seeder katalog sebelum mengatur formulir.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 lg:hidden">
            @forelse ($serviceTypes as $serviceType)
                <article class="rounded-xl border border-[#dfe8ec] bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold tracking-[0.08em] text-[#26677b]">{{ $serviceType->code }}</p>
                            <h3 class="mt-1 text-sm font-extrabold leading-5 text-[#17313c]">{{ $serviceType->name }}</h3>
                        </div>
                        <span class="ui-status shrink-0 {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    @if ($serviceType->description)
                        <p class="mt-3 text-xs leading-5 text-[#78909a]">{{ $serviceType->description }}</p>
                    @endif
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-[#edf2f4] pt-3 text-xs">
                        <div><dt class="text-[#8aa0a8]">Field aktif</dt><dd class="mt-1 font-extrabold text-[#35505b]">{{ $serviceType->activeFieldDefinitions->count() }}</dd></div>
                        <div><dt class="text-[#8aa0a8]">Keahlian aktif</dt><dd class="mt-1 font-extrabold text-[#35505b]">{{ $serviceType->skills->where('is_active', true)->count() }}</dd></div>
                        <div><dt class="text-[#8aa0a8]">Kelas tiket</dt><dd class="mt-1 font-extrabold text-[#35505b]">{{ $serviceType->ticket_class ?? ($serviceType->code === 'SVC-05' ? 'INC / REQ' : 'Belum diatur') }}</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-[#edf2f4] pt-3">
                        <button type="button" data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs" aria-label="Buka editor formulir {{ $serviceType->name }}">Buka editor</button>
                        <form method="POST" action="{{ route('admin.catalog.services.status', [$serviceType, $serviceType->is_active ? 'deactivate' : 'activate']) }}" @if ($serviceType->is_active) data-swal-confirm="Nonaktifkan layanan ini? Layanan tidak tampil bagi pemohon." @endif>
                            @csrf
                            <button type="submit" class="ui-btn {{ $serviceType->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !px-3 !text-xs">{{ $serviceType->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="ui-empty">Belum ada layanan. Jalankan seeder katalog sebelum mengatur formulir.</div>
            @endforelse
        </div>
    </section>

    @foreach ($serviceTypes as $serviceType)
        @php($isEditingService = (string) old('_service_edit') === (string) $serviceType->id)
        @php($servicePanel = $isEditingService ? old('_service_tab', 'formulir') : 'formulir')
        <div id="service-edit-modal-{{ $serviceType->id }}" data-ui-modal data-auto-open="{{ $isEditingService ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <button type="button" data-ui-modal-close class="absolute inset-0 cursor-default bg-slate-950/40" tabindex="-1" aria-label="Tutup dialog"></button>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <section role="dialog" aria-modal="true" aria-labelledby="service-edit-title-{{ $serviceType->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-6xl overflow-y-auto rounded-2xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[#e7eef1] bg-white px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#7a929a]">Konfigurasi layanan</p>
                            <h2 id="service-edit-title-{{ $serviceType->id }}" class="mt-1 text-base font-extrabold text-[#17313c]">{{ $serviceType->code }} · {{ $serviceType->name }}</h2>
                            <p class="mt-1 text-xs text-[#78909a]">Perubahan pada field dibuat sebagai versi baru agar histori tiket tetap aman.</p>
                        </div>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#78909a] transition hover:bg-[#f4f8f9] hover:text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa]" aria-label="Tutup dialog konfigurasi {{ $serviceType->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.85fr)]">
                        <div class="space-y-3">
                        <div class="rounded-xl border border-[#d9e8ec] bg-[#f7fbfc] px-4 py-3">
                            <p class="text-xs font-extrabold text-[#35505b]">Perubahan aman untuk histori</p>
                            <p class="mt-1 text-xs leading-5 text-[#78909a]">Versi baru hanya dipakai tiket yang dibuat setelah perubahan diterbitkan. Tiket lama tetap menggunakan snapshot sebelumnya.</p>
                        </div>

                        <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'detail') open @endif>
                            <summary class="ui-disclosure-summary flex items-center justify-between gap-4 px-4 py-3.5">
                                <span><span class="block text-sm font-extrabold text-[#263a43]">Detail layanan</span><span class="mt-0.5 block text-xs text-[#78909a]">Nama, deskripsi, dan kelas tiket</span></span>
                                <span class="text-xs font-bold text-[#8aa0a8]">Data dasar</span>
                            </summary>
                            <div class="border-t border-[#edf2f4] p-4 sm:p-5">
                        <form method="POST" action="{{ route('admin.catalog.services.update', $serviceType) }}" data-ui-modal-form class="p-0">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                            <input type="hidden" name="_service_tab" value="detail">
                            <div class="flex items-start justify-between gap-4">
                                <div><h3 class="text-sm font-extrabold text-[#263a43]">Detail layanan</h3><p class="mt-1 text-xs leading-5 text-[#78909a]">Kode layanan bersifat canonical dan tidak dapat diubah.</p></div>
                                <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-extrabold text-[#346478]">{{ $serviceType->code }}</span>
                            </div>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div><label for="service-name-{{ $serviceType->id }}" class="ui-field-label">Nama layanan <span class="text-rose-600">*</span></label><input id="service-name-{{ $serviceType->id }}" name="name" value="{{ $serviceType->name }}" required class="ui-input mt-2"></div>
                                @if ($serviceType->code !== 'SVC-05')
                                    <div><label for="service-class-{{ $serviceType->id }}" class="ui-field-label">Kelas nomor <span class="text-rose-600">*</span></label><select id="service-class-{{ $serviceType->id }}" name="ticket_class" required class="ui-select mt-2"><option value="">Pilih kelas</option>@foreach ($ticketClasses as $ticketClass)<option value="{{ $ticketClass }}" @selected($serviceType->ticket_class === $ticketClass)>{{ $ticketClass }}</option>@endforeach</select></div>
                                @else
                                    <div><p class="ui-field-label">Mapping SVC-05</p><p class="mt-2 rounded-lg border border-[#f2dfab] bg-[#fffaf0] p-3 text-xs leading-5 text-[#7a5a12]">Perbaikan selalu menggunakan INC dan Permintaan selalu menggunakan REQ.</p></div>
                                @endif
                            </div>
                            <div class="mt-4"><label for="service-description-{{ $serviceType->id }}" class="ui-field-label">Deskripsi</label><textarea id="service-description-{{ $serviceType->id }}" name="description" rows="2" class="ui-textarea mt-2">{{ $serviceType->description }}</textarea></div>
                            @if ($serviceType->code === 'SVC-05')
                                <fieldset class="mt-4 rounded-lg border border-[#e2ebee] bg-white p-3"><legend class="px-1 text-xs font-extrabold text-[#526f79]">Subjenis dan kelas nomor</legend><div class="grid gap-3 sm:grid-cols-2">@foreach ($serviceType->variants as $variant)<div><label for="variant-label-{{ $serviceType->id }}-{{ $variant->code }}" class="ui-field-label">{{ $variant->code === 'repair' ? 'Perbaikan' : 'Permintaan' }}</label><input type="hidden" name="variants[{{ $loop->index }}][code]" value="{{ $variant->code }}"><input id="variant-label-{{ $serviceType->id }}-{{ $variant->code }}" name="variants[{{ $loop->index }}][label]" value="{{ $variant->label }}" required class="ui-input mt-2"><p class="mt-1 text-[0.68rem] text-[#78909a]">Kelas: {{ $variant->ticket_class }}</p><input type="hidden" name="variants[{{ $loop->index }}][sort_order]" value="{{ $variant->sort_order }}"></div>@endforeach</div></fieldset>
                            @endif
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] pt-4">
                                <p class="text-xs leading-5 text-[#78909a]">Layanan nonaktif tidak tampil pada katalog pengguna.</p>
                                <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Simpan detail layanan</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                            </div>
                        </form>
                            </div>
                        </details>

                        <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'skills') open @endif>
                            <summary class="ui-disclosure-summary flex items-center justify-between gap-4 px-4 py-3.5">
                                <span><span class="block text-sm font-extrabold text-[#263a43]">Keahlian penanganan</span><span class="mt-0.5 block text-xs text-[#78909a]">Pemetaan untuk saran teknisi Tier 2</span></span>
                                <span class="text-xs font-bold text-[#8aa0a8]">{{ $serviceType->skills->where('is_active', true)->count() }} aktif</span>
                            </summary>
                            <div class="border-t border-[#edf2f4]">
                        <section aria-labelledby="service-skills-heading-{{ $serviceType->id }}" class="p-4 sm:p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 id="service-skills-heading-{{ $serviceType->id }}" class="text-sm font-extrabold text-[#263a43]">Keahlian penanganan</h3>
                                    <p class="mt-1 max-w-2xl text-xs leading-5 text-[#78909a]">Pilih keahlian yang relevan untuk layanan ini. Pemetaan ini menjadi dasar saran teknisi Tier 2 saat triase.</p>
                                </div>
                                <span class="rounded-full bg-[#e8faf4] px-2.5 py-1 text-xs font-extrabold text-[#087f5b]">{{ $serviceType->skills->where('is_active', true)->count() }} aktif</span>
                            </div>
                            <form method="POST" action="{{ route('admin.catalog.services.skills.update', $serviceType) }}" data-ui-modal-form class="mt-4">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                <input type="hidden" name="_service_tab" value="skills">
                                @if ($skills->isNotEmpty())
                                    <fieldset>
                                        <legend class="ui-field-label">Keahlian yang dipetakan</legend>
                                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($skills as $skill)
                                                <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-[#e1eaed] bg-white px-3 py-2.5 transition hover:border-[#8bd7ee] has-[:checked]:border-[#75d5f3] has-[:checked]:bg-[#f1fbfe]">
                                                    <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="ui-checkbox mt-0.5" @checked($serviceType->skills->contains('id', $skill->id))>
                                                    <span class="min-w-0"><span class="block text-xs font-extrabold text-[#35505b]">{{ $skill->name }}</span>@if ($skill->description)<span class="mt-0.5 block truncate text-[0.68rem] text-[#78909a]">{{ $skill->description }}</span>@endif</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @else
                                    <div class="ui-empty !p-3">Belum ada keahlian aktif. Tambahkan master keahlian terlebih dahulu.</div>
                                @endif
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] pt-4">
                                    <p class="max-w-xl text-xs leading-5 text-[#78909a]">Keahlian yang dinonaktifkan tidak digunakan untuk saran baru, tetapi histori tiket dan pemetaan lama tetap tersimpan.</p>
                                    <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Simpan keahlian</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                                </div>
                            </form>
                        </section>
                            </div>
                        </details>

                        <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'formulir') open @endif>
                            <summary class="ui-disclosure-summary flex items-center justify-between gap-4 px-4 py-3.5">
                                <span><span class="block text-sm font-extrabold text-[#263a43]">Template formulir</span><span class="mt-0.5 block text-xs text-[#78909a]">Field aktif dan versi yang sedang digunakan</span></span>
                                <span class="text-xs font-bold text-[#8aa0a8]">{{ $serviceType->activeFieldDefinitions->count() }} field</span>
                            </summary>
                            <div class="border-t border-[#edf2f4]">
                        <section aria-labelledby="fields-heading-{{ $serviceType->id }}" class="p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-4"><div><h3 id="fields-heading-{{ $serviceType->id }}" class="text-sm font-extrabold text-[#263a43]">Field formulir aktif</h3><p class="mt-1 max-w-2xl text-xs leading-5 text-[#78909a]">Field aktif dipakai tiket baru. Jika ada perubahan, buat versi baru agar data lama tetap dapat ditelusuri.</p></div><span class="rounded-full bg-[#eef3ff] px-2.5 py-1 text-xs font-extrabold text-[#4f63a6]">{{ $serviceType->activeFieldDefinitions->count() }}</span></div>

                            <form method="POST" action="{{ route('admin.catalog.fields.store', $serviceType) }}" data-ui-modal-form class="mt-4 rounded-xl border border-[#b9e8e1] bg-[#ecfbf8] p-4 sm:p-5">
                                @csrf
                                <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                <input type="hidden" name="_service_tab" value="formulir">
                                <div><p class="text-sm font-extrabold text-[#17313c]">Tambah field baru</p><p class="mt-1 text-xs leading-5 text-[#52747b]">Gunakan kunci stabil dalam bahasa Inggris; label yang tampil tetap menggunakan Bahasa Indonesia.</p></div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div><label for="new-field-key-{{ $serviceType->id }}" class="ui-field-label">Kunci field <span class="text-rose-600">*</span></label><input id="new-field-key-{{ $serviceType->id }}" name="key" required class="ui-input mt-2" placeholder="contoh: application_name"></div>
                                    <div><label for="new-field-label-{{ $serviceType->id }}" class="ui-field-label">Label <span class="text-rose-600">*</span></label><input id="new-field-label-{{ $serviceType->id }}" name="label" required class="ui-input mt-2" placeholder="Nama aplikasi"></div>
                                    <div><label for="new-field-type-{{ $serviceType->id }}" class="ui-field-label">Tipe <span class="text-rose-600">*</span></label><select id="new-field-type-{{ $serviceType->id }}" name="field_type" required class="ui-select mt-2"><option value="">Pilih tipe</option>@foreach ($fieldTypes as $type => $typeLabel)<option value="{{ $type }}">{{ $typeLabel }}</option>@endforeach</select></div>
                                    <div><label for="new-field-visibility-{{ $serviceType->id }}" class="ui-field-label">Visibilitas <span class="text-rose-600">*</span></label><select id="new-field-visibility-{{ $serviceType->id }}" name="visibility" required class="ui-select mt-2">@foreach ($visibilities as $visibility => $visibilityLabel)<option value="{{ $visibility }}">{{ $visibilityLabel }}</option>@endforeach</select></div>
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end">
                                    <div><label for="new-field-help-{{ $serviceType->id }}" class="ui-field-label">Petunjuk pengisian</label><input id="new-field-help-{{ $serviceType->id }}" name="help_text" class="ui-input mt-2" placeholder="Petunjuk singkat untuk pemohon"></div>
                                    <label class="flex min-h-10 items-center gap-2 rounded-lg border border-[#d8e8e5] bg-white px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_required" value="1" class="ui-checkbox"> Wajib</label>
                                    <div><label for="new-field-order-{{ $serviceType->id }}" class="ui-field-label">Urutan</label><input id="new-field-order-{{ $serviceType->id }}" name="sort_order" type="number" min="0" value="0" class="ui-input mt-2 w-24"></div>
                                </div>
                                <div class="mt-3 grid gap-3 lg:grid-cols-2"><div><label for="new-field-options-{{ $serviceType->id }}" class="ui-field-label">Pilihan <span class="font-normal text-[#78909a]">(untuk select; satu per baris: nilai|label)</span></label><textarea id="new-field-options-{{ $serviceType->id }}" name="options_text" rows="3" class="ui-textarea mt-2" placeholder="repair|Perbaikan&#10;request|Permintaan"></textarea></div><div><label for="new-field-rules-{{ $serviceType->id }}" class="ui-field-label">Aturan validasi <span class="font-normal text-[#78909a]">(satu per baris)</span></label><textarea id="new-field-rules-{{ $serviceType->id }}" name="validation_rules_text" rows="3" class="ui-textarea mt-2" placeholder="string&#10;max:500"></textarea></div></div>
                                <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary mt-3 !min-h-9 !text-xs"><span data-ui-modal-label>Tambah field</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                            </form>

                            <div class="mt-4 space-y-2">
                                @forelse ($serviceType->activeFieldDefinitions as $field)
                                    <details class="rounded-xl border border-[#e1eaed] bg-white">
                                        <summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4"><span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $field->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · versi {{ $field->version }}</span></span><span class="flex shrink-0 items-center gap-2"><span class="hidden rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.68rem] font-bold text-[#607681] sm:inline-flex">{{ $visibilities[$field->visibility] ?? $field->visibility }}</span>@if ($field->is_required)<span class="rounded-full bg-[#fff6df] px-2 py-1 text-[0.68rem] font-bold text-[#956b16]">Wajib</span>@else<span class="rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.68rem] font-bold text-[#78909a]">Opsional</span>@endif</span></summary>
                                        <div class="border-t border-[#edf2f4] p-4">
                                             <form method="POST" action="{{ route('admin.catalog.fields.versions.store', $field) }}" data-ui-modal-form class="space-y-3">
                                                 @csrf
                                                 <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                                 <input type="hidden" name="_service_tab" value="formulir">
                                                 <div class="rounded-lg bg-[#f8fbfc] px-3 py-2.5 text-xs leading-5 text-[#607681]">Versi aktif saat ini: <span class="font-extrabold text-[#35505b]">{{ $field->version }}</span>. Setelah disimpan, versi baru dipakai tiket berikutnya.</div>
                                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><label for="field-label-{{ $field->id }}" class="ui-field-label">Label <span class="text-rose-600">*</span></label><input id="field-label-{{ $field->id }}" name="label" value="{{ $field->label }}" required class="ui-input mt-2"></div><div><label for="field-type-{{ $field->id }}" class="ui-field-label">Tipe <span class="text-rose-600">*</span></label><select id="field-type-{{ $field->id }}" name="field_type" required class="ui-select mt-2">@foreach ($fieldTypes as $type => $typeLabel)<option value="{{ $type }}" @selected($field->field_type === $type)>{{ $typeLabel }}</option>@endforeach</select></div><div><label for="field-visibility-{{ $field->id }}" class="ui-field-label">Visibilitas <span class="text-rose-600">*</span></label><select id="field-visibility-{{ $field->id }}" name="visibility" required class="ui-select mt-2">@foreach ($visibilities as $visibility => $visibilityLabel)<option value="{{ $visibility }}" @selected($field->visibility === $visibility)>{{ $visibilityLabel }}</option>@endforeach</select></div><div><label for="field-order-{{ $field->id }}" class="ui-field-label">Urutan</label><input id="field-order-{{ $field->id }}" name="sort_order" type="number" min="0" value="{{ $field->sort_order }}" class="ui-input mt-2"></div></div>
                                                <div><label for="field-help-{{ $field->id }}" class="ui-field-label">Petunjuk pengisian</label><input id="field-help-{{ $field->id }}" name="help_text" value="{{ $field->help_text }}" class="ui-input mt-2"></div>
                                                <div class="grid gap-3 lg:grid-cols-2"><div><label for="field-options-{{ $field->id }}" class="ui-field-label">Pilihan <span class="font-normal text-[#78909a]">(satu per baris: nilai|label)</span></label><textarea id="field-options-{{ $field->id }}" name="options_text" rows="3" class="ui-textarea mt-2">{{ $formatOptions($field) }}</textarea></div><div><label for="field-rules-{{ $field->id }}" class="ui-field-label">Aturan validasi <span class="font-normal text-[#78909a]">(satu per baris)</span></label><textarea id="field-rules-{{ $field->id }}" name="validation_rules_text" rows="3" class="ui-textarea mt-2">{{ $formatRules($field) }}</textarea></div></div>
                                                <label class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_required" value="1" class="ui-checkbox" @checked($field->is_required)> Field wajib diisi</label>
                                                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#edf2f4] pt-3"><button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Buat versi baru</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button><div class="flex flex-wrap gap-2">@if ($field->is_active)<button type="submit" form="deactivate-field-{{ $field->id }}" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>@else<button type="submit" form="activate-field-{{ $field->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>@endif</div></div>
                                            </form>
                                            <form id="activate-field-{{ $field->id }}" method="POST" action="{{ route('admin.catalog.fields.status', [$field, 'activate']) }}" class="hidden">@csrf<input type="hidden" name="_service_edit" value="{{ $serviceType->id }}"><input type="hidden" name="_service_tab" value="formulir"></form>
                                            <form id="deactivate-field-{{ $field->id }}" method="POST" action="{{ route('admin.catalog.fields.status', [$field, 'deactivate']) }}" class="hidden" data-swal-confirm="Nonaktifkan field ini? Definisi versi lama tetap tersedia.">@csrf<input type="hidden" name="_service_edit" value="{{ $serviceType->id }}"><input type="hidden" name="_service_tab" value="formulir"></form>
                                        </div>
                                    </details>
                                @empty
                                    <div class="ui-empty">Belum ada field aktif untuk layanan ini. Tambahkan field pertama di atas.</div>
                                @endforelse
                            </div>
                        </section>
                            </div>
                        </details>
                        </div>
                        @include('admin.catalog._form-preview', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
                    </div>
                </section>
            </div>
        </div>
    @endforeach
    @endif

    @if ($activeSection === 'locations')
    <section class="mt-8" aria-labelledby="locations-heading">
        <div class="mb-4"><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Master lokasi</p><h2 id="locations-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Lokasi</h2><p class="mt-2 text-sm leading-6 text-[#6a8089]">Atur gedung, lantai, dan ruangan yang tersedia untuk layanan. Nonaktifkan bagian paling dalam sebelum induknya.</p></div>
        <div class="ui-panel overflow-hidden">
            <form method="POST" action="{{ route('admin.catalog.buildings.store') }}" class="border-b border-[#e5edef] bg-[#f8fbfc] p-4 sm:p-5"><div class="flex flex-wrap items-end gap-3"><div class="min-w-[15rem] flex-1"><label for="new-building-name" class="ui-field-label">Nama gedung <span class="text-rose-600">*</span></label><input id="new-building-name" name="name" required class="ui-input mt-2" placeholder="Contoh: Gedung Utama"></div><button type="submit" class="ui-btn ui-btn-primary !min-h-10">Tambah gedung</button></div></form>
            <div class="space-y-3 p-4 sm:p-5">
                @forelse ($buildings as $building)
                    <details class="rounded-xl border border-[#e1eaed] bg-white" @if ($loop->first) open @endif><summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4"><span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $building->name }}</span><span class="mt-1 block text-xs text-[#78909a]">{{ $building->floors->count() }} lantai · {{ $building->floors->sum(fn ($floor) => $floor->rooms->count()) }} ruangan</span></span><span class="ui-status {{ $building->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $building->is_active ? 'Aktif' : 'Nonaktif' }}</span></summary><div class="space-y-4 border-t border-[#edf2f4] p-4">
                        <form method="POST" action="{{ route('admin.catalog.buildings.update', $building) }}" class="flex flex-wrap items-end gap-2">@csrf @method('PUT')<div class="min-w-[15rem] flex-1"><label for="building-name-{{ $building->id }}" class="ui-field-label">Nama gedung</label><input id="building-name-{{ $building->id }}" name="name" value="{{ $building->name }}" required class="ui-input mt-2"></div><button type="submit" class="ui-btn ui-btn-secondary !min-h-10 !text-xs">Simpan</button></form>
                        <div class="rounded-lg border border-[#e4edef] bg-[#f8fbfc] p-3"><p class="text-xs font-extrabold text-[#526f79]">Tambah lantai</p><form method="POST" action="{{ route('admin.catalog.floors.store', $building) }}" class="mt-3 flex flex-wrap items-end gap-2">@csrf<div class="min-w-[12rem] flex-1"><label for="new-floor-name-{{ $building->id }}" class="ui-field-label">Nama lantai <span class="text-rose-600">*</span></label><input id="new-floor-name-{{ $building->id }}" name="name" required class="ui-input mt-2" placeholder="Contoh: Lantai 1"></div><div><label for="new-floor-order-{{ $building->id }}" class="ui-field-label">Urutan</label><input id="new-floor-order-{{ $building->id }}" name="sort_order" type="number" min="0" value="0" class="ui-input mt-2 w-24"></div><button type="submit" class="ui-btn ui-btn-primary !min-h-10 !text-xs">Tambah lantai</button></form></div>
                        <div class="space-y-2">@forelse ($building->floors as $floor)<details class="rounded-lg border border-[#e6edef] bg-white"><summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-3"><span class="min-w-0"><span class="block truncate text-sm font-bold text-[#526f79]">{{ $floor->name }}</span><span class="mt-1 block text-[0.68rem] text-[#78909a]">{{ $floor->rooms->count() }} ruangan</span></span><span class="ui-status {{ $floor->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</span></summary><div class="space-y-3 border-t border-[#edf2f4] p-3"><form method="POST" action="{{ route('admin.catalog.floors.update', $floor) }}" class="flex flex-wrap items-end gap-2">@csrf @method('PUT')<div class="min-w-[12rem] flex-1"><label for="floor-name-{{ $floor->id }}" class="ui-field-label">Nama lantai</label><input id="floor-name-{{ $floor->id }}" name="name" value="{{ $floor->name }}" required class="ui-input mt-2"></div><div><label for="floor-order-{{ $floor->id }}" class="ui-field-label">Urutan</label><input id="floor-order-{{ $floor->id }}" name="sort_order" type="number" min="0" value="{{ $floor->sort_order }}" class="ui-input mt-2 w-24"></div><button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !text-xs">Simpan lantai</button></form><div class="rounded-lg bg-[#f8fbfc] p-3"><p class="text-xs font-extrabold text-[#526f79]">Tambah ruangan</p><form method="POST" action="{{ route('admin.catalog.rooms.store', $floor) }}" class="mt-3 flex flex-wrap items-end gap-2">@csrf<div class="min-w-[12rem] flex-1"><label for="new-room-name-{{ $floor->id }}" class="ui-field-label">Nama ruangan <span class="text-rose-600">*</span></label><input id="new-room-name-{{ $floor->id }}" name="name" required class="ui-input mt-2" placeholder="Contoh: Ruang TI"></div><button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Tambah ruangan</button></form></div><div class="space-y-2">@forelse ($floor->rooms as $room)<div class="flex flex-wrap items-end gap-2 rounded-lg border border-[#edf2f4] p-3"><form method="POST" action="{{ route('admin.catalog.rooms.update', $room) }}" class="flex min-w-[14rem] flex-1 items-end gap-2">@csrf @method('PUT')<div class="min-w-0 flex-1"><label for="room-name-{{ $room->id }}" class="ui-field-label">Nama ruangan</label><input id="room-name-{{ $room->id }}" name="name" value="{{ $room->name }}" required class="ui-input mt-2"></div><button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !text-xs">Simpan</button></form><form method="POST" action="{{ route('admin.catalog.rooms.status', [$room, $room->is_active ? 'deactivate' : 'activate']) }}" @if ($room->is_active) data-swal-confirm="Nonaktifkan ruangan ini?" @endif>@csrf<button type="submit" class="ui-btn {{ $room->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !text-xs">{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div>@empty<div class="ui-empty !p-3">Belum ada ruangan pada lantai ini.</div>@endforelse</div></div></details>@empty<div class="ui-empty !p-3">Belum ada lantai pada gedung ini.</div>@endforelse</div>
                        <div class="flex justify-end border-t border-[#edf2f4] pt-3"><form method="POST" action="{{ route('admin.catalog.buildings.status', [$building, $building->is_active ? 'deactivate' : 'activate']) }}" @if ($building->is_active) data-swal-confirm="Nonaktifkan gedung ini? Pastikan semua lantai sudah nonaktif." @endif>@csrf<button type="submit" class="ui-btn {{ $building->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !text-xs">{{ $building->is_active ? 'Nonaktifkan gedung' : 'Aktifkan gedung' }}</button></form></div>
                    </div></details>
                @empty
                    <div class="ui-empty">Belum ada gedung. Tambahkan gedung pertama di atas.</div>
                @endforelse
            </div>
        </div>
    </section>
    @endif

    @if ($activeSection === 'attachments')
    <section class="mt-8" aria-labelledby="attachments-heading">
        <div class="mb-4"><p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Konfigurasi berkas</p><h2 id="attachments-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Kebijakan lampiran</h2><p class="mt-2 text-sm leading-6 text-[#6a8089]">Tentukan batas ukuran, jumlah, tipe berkas, dan siapa yang dapat melihatnya.</p></div>
        <div class="ui-panel overflow-hidden">
            <form method="POST" action="{{ route('admin.catalog.attachment-policies.store') }}" class="border-b border-[#e5edef] bg-[#f8fbfc] p-4 sm:p-5">@csrf<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><label for="policy-service" class="ui-field-label">Cakupan layanan</label><select id="policy-service" name="service_type_id" class="ui-select mt-2"><option value="">Global</option>@foreach ($serviceTypes as $serviceType)<option value="{{ $serviceType->id }}">{{ $serviceType->code }} — {{ $serviceType->name }}</option>@endforeach</select></div><div><label for="policy-key" class="ui-field-label">Kunci tipe <span class="text-rose-600">*</span></label><input id="policy-key" name="type_key" required class="ui-input mt-2" placeholder="supporting"></div><div><label for="policy-label" class="ui-field-label">Label <span class="text-rose-600">*</span></label><input id="policy-label" name="label" required class="ui-input mt-2" placeholder="Dokumen pendukung"></div><div><label for="policy-visibility" class="ui-field-label">Visibilitas <span class="text-rose-600">*</span></label><select id="policy-visibility" name="visibility" required class="ui-select mt-2"><option value="both">Pemohon dan Tim TI</option><option value="requester">Pemohon</option><option value="internal">Tim TI</option></select></div></div><div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><label for="policy-size" class="ui-field-label">Maksimal ukuran (KB) <span class="text-rose-600">*</span></label><input id="policy-size" name="max_file_size_kb" type="number" min="1" value="10240" required class="ui-input mt-2"></div><div><label for="policy-count" class="ui-field-label">Maksimal jumlah <span class="text-rose-600">*</span></label><input id="policy-count" name="max_file_count" type="number" min="1" value="5" required class="ui-input mt-2"></div><div><label for="policy-mimes" class="ui-field-label">MIME yang diizinkan</label><input id="policy-mimes" name="allowed_mimes_text" class="ui-input mt-2" placeholder="image/png, application/pdf"></div><div><label for="policy-extensions" class="ui-field-label">Ekstensi yang diizinkan</label><input id="policy-extensions" name="allowed_extensions_text" class="ui-input mt-2" placeholder="png, pdf, docx"></div></div><label class="mt-3 inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#e1eaed] bg-white px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_active" value="1" checked class="ui-checkbox"> Aktifkan kebijakan</label><div class="mt-3"><button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Tambah kebijakan</button></div></form>
            <div class="space-y-2 p-4 sm:p-5">@forelse ($attachmentPolicies as $policy)<details class="rounded-xl border border-[#e1eaed] bg-white"><summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4"><span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $policy->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $policy->type_key }} · {{ $policy->serviceType?->code ?? 'Global' }} · {{ $policy->max_file_size_kb }} KB · maksimal {{ $policy->max_file_count }} berkas</span></span><span class="ui-status {{ $policy->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $policy->is_active ? 'Aktif' : 'Nonaktif' }}</span></summary><div class="border-t border-[#edf2f4] p-4"><form method="POST" action="{{ route('admin.catalog.attachment-policies.update', $policy) }}" class="space-y-3">@csrf @method('PUT')<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><label for="edit-policy-key-{{ $policy->id }}" class="ui-field-label">Kunci tipe</label><input id="edit-policy-key-{{ $policy->id }}" name="type_key" value="{{ $policy->type_key }}" required class="ui-input mt-2"></div><div><label for="edit-policy-label-{{ $policy->id }}" class="ui-field-label">Label</label><input id="edit-policy-label-{{ $policy->id }}" name="label" value="{{ $policy->label }}" required class="ui-input mt-2"></div><div><label for="edit-policy-size-{{ $policy->id }}" class="ui-field-label">Maksimal ukuran (KB)</label><input id="edit-policy-size-{{ $policy->id }}" name="max_file_size_kb" type="number" min="1" value="{{ $policy->max_file_size_kb }}" required class="ui-input mt-2"></div><div><label for="edit-policy-count-{{ $policy->id }}" class="ui-field-label">Maksimal jumlah</label><input id="edit-policy-count-{{ $policy->id }}" name="max_file_count" type="number" min="1" value="{{ $policy->max_file_count }}" required class="ui-input mt-2"></div></div><div class="grid gap-3 sm:grid-cols-2"><div><label for="edit-policy-mimes-{{ $policy->id }}" class="ui-field-label">MIME yang diizinkan</label><input id="edit-policy-mimes-{{ $policy->id }}" name="allowed_mimes_text" value="{{ implode(', ', $policy->allowed_mimes ?? []) }}" class="ui-input mt-2"></div><div><label for="edit-policy-extensions-{{ $policy->id }}" class="ui-field-label">Ekstensi yang diizinkan</label><input id="edit-policy-extensions-{{ $policy->id }}" name="allowed_extensions_text" value="{{ implode(', ', $policy->allowed_extensions ?? []) }}" class="ui-input mt-2"></div></div><input type="hidden" name="service_type_id" value="{{ $policy->service_type_id }}"><div><label for="edit-policy-visibility-{{ $policy->id }}" class="ui-field-label">Visibilitas</label><select id="edit-policy-visibility-{{ $policy->id }}" name="visibility" class="ui-select mt-2"><option value="both" @selected($policy->visibility === 'both')>Pemohon dan Tim TI</option><option value="requester" @selected($policy->visibility === 'requester')>Pemohon</option><option value="internal" @selected($policy->visibility === 'internal')>Tim TI</option></select></div><label class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 text-xs font-bold text-[#526f79]"><input type="checkbox" name="is_active" value="1" class="ui-checkbox" @checked($policy->is_active)> Kebijakan aktif</label><div class="flex flex-wrap gap-2"><button type="submit" class="ui-btn ui-btn-primary !min-h-9 !text-xs">Simpan perubahan</button></div></form><div class="mt-3 flex justify-end"><form method="POST" action="{{ route('admin.catalog.attachment-policies.status', [$policy, $policy->is_active ? 'deactivate' : 'activate']) }}" @if ($policy->is_active) data-swal-confirm="Nonaktifkan kebijakan ini?" @endif>@csrf<button type="submit" class="ui-btn {{ $policy->is_active ? 'ui-btn-warning' : 'ui-btn-secondary' }} !min-h-9 !text-xs">{{ $policy->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div></div></details>@empty<div class="ui-empty">Belum ada kebijakan lampiran.</div>@endforelse</div>
        </div>
    </section>
    @endif

    @if ($activeSection === 'attachments')
        <p class="mt-8 text-xs leading-5 text-[#78909a]">Kebijakan ini mengatur batas dan tipe berkas. Penyimpanan privat, akses, dan kontrol khusus layanan tetap mengikuti alur tiket.</p>
    @endif
@endsection
