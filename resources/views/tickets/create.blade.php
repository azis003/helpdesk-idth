@extends('layouts.app')

@section('title', 'Buat tiket — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', 'Buat tiket')

@php
    $selectedServiceId = old('service_type_id');
    $selectedRoomId = old('room_id');
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Katalog layanan TI</p>
            <h1 class="ui-page-title">Buat tiket</h1>
            <p class="ui-page-description">Pilih layanan yang sesuai, jelaskan kebutuhan Anda, lalu kirim tiket untuk dicatat oleh Tim TI.</p>
        </div>
        <a href="{{ route('tickets.index') }}" class="ui-btn ui-btn-ghost">Lihat tiket saya</a>
    </div>

    <section class="mt-7" aria-labelledby="service-catalog-heading">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Pilih kebutuhan Anda</p>
                <h2 id="service-catalog-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Katalog layanan</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6a8089]">Mulai dari layanan yang paling mendekati kebutuhan Anda. Detail formulir akan menyesuaikan pilihan ini.</p>
            </div>
            <div class="w-full lg:max-w-xs">
                <label for="service-catalog-search" class="sr-only">Cari layanan</label>
                <div class="relative">
                    <input id="service-catalog-search" type="search" class="ui-input !pr-10" placeholder="Cari layanan" autocomplete="off" data-ticket-service-search>
                    <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#78909a]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3" /><path stroke-linecap="round" d="m16 16 4.2 4.2" /></svg>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-ticket-service-catalog>
            @foreach ($serviceTypes as $serviceType)
                @php
                    $serviceSearchText = strtolower(trim($serviceType->code.' '.$serviceType->name.' '.($serviceType->description ?? '')));
                    $serviceIsSelected = (string) $selectedServiceId === (string) $serviceType->id;
                @endphp
                <button
                    type="button"
                    class="ui-catalog-card group text-left"
                    data-ticket-service-card
                    data-service-id="{{ $serviceType->id }}"
                    data-service-search="{{ $serviceSearchText }}"
                    aria-pressed="{{ $serviceIsSelected ? 'true' : 'false' }}"
                >
                    <span class="ui-catalog-icon" aria-hidden="true">
                        @switch($serviceType->code)
                            @case('SVC-01')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M5 12h3l2-6 4 12 2-6h3" /></svg>
                                @break
                            @case('SVC-02')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h9l3 3v12H6zM14 4.5v3h4M9 12h6M9 15.5h4" /></svg>
                                @break
                            @case('SVC-03')
                            @case('SVC-04')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7.5h14v9H5zM8 4.5h8M8 11h8M8 14h4" /></svg>
                                @break
                            @case('SVC-05')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="6.5" width="15" height="11" rx="1.5" /><path stroke-linecap="round" d="M8 10h8M8 13.5h5" /></svg>
                                @break
                            @case('SVC-06')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg>
                                @break
                            @default
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h12v15H6zM9 8h6M9 11.5h6M9 15h4" /></svg>
                        @endswitch
                    </span>
                    <span class="mt-4 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">{{ $serviceType->code }} · {{ $serviceType->ticket_class ?? 'Layanan' }}</span>
                    <span class="mt-2 text-sm font-extrabold leading-5 text-[#35505b]">{{ $serviceType->name }}</span>
                    <span class="mt-2 line-clamp-2 text-xs leading-5 text-[#78909a]">{{ $serviceType->description ?: 'Pilih layanan ini untuk melihat formulir yang sesuai.' }}</span>
                    <span class="mt-auto pt-4 text-xs font-extrabold text-[#147a79]">Pilih layanan <span aria-hidden="true">→</span></span>
                </button>
            @endforeach
        </div>
        <p class="ui-empty mt-4 hidden !p-6" data-ticket-service-catalog-empty role="status">Layanan yang Anda cari belum ditemukan. Coba kata kunci lain.</p>
    </section>

    @if ($announcements->isNotEmpty())
        <section class="mt-7" aria-labelledby="ticket-announcements-heading">
            <div>
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Perhatian sebelum mengirim</p>
                <h2 id="ticket-announcements-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">Pengumuman layanan</h2>
            </div>
            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                @foreach ($announcements as $announcement)
                    <article class="ui-panel border-l-4 border-l-[#e4a72c] p-5" aria-labelledby="announcement-{{ $announcement->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <h3 id="announcement-{{ $announcement->id }}" class="text-sm font-extrabold text-[#263a43]">{{ $announcement->title }}</h3>
                            <time class="shrink-0 text-xs font-bold text-[#86979e]" datetime="{{ $announcement->starts_at?->toIso8601String() }}">{{ $announcement->starts_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                        </div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-[#526f79]">{{ $announcement->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" data-ticket-form class="mt-8 space-y-5">
        @csrf

        <section class="ui-panel" aria-labelledby="ticket-context-heading">
            <div class="ui-panel-header">
                <h2 id="ticket-context-heading" class="ui-section-title">Konteks tiket</h2>
                <p class="ui-section-description">Identitas pemohon dan layanan menentukan aturan formulir serta nomor tiket.</p>
            </div>
            <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-2">
                @if ($canCreateForOthers)
                    <div>
                        <label for="requester_id" class="ui-field-label">Pemohon <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                        <p id="requester_id-help" class="ui-field-help">Pilih pegawai yang menyampaikan permintaan. Anda tercatat sebagai pembuat tiket.</p>
                        <select id="requester_id" name="requester_id" required class="ui-select mt-2" @error('requester_id') aria-invalid="true" aria-describedby="requester_id-error" @else aria-describedby="requester_id-help" @enderror>
                            <option value="">Pilih pemohon</option>
                            @foreach ($requesters as $requester)
                                <option value="{{ $requester->id }}" @selected((string) old('requester_id') === (string) $requester->id)>{{ $requester->name }}{{ $requester->nip ? ' — NIP '.$requester->nip : '' }}</option>
                            @endforeach
                        </select>
                        @error('requester_id')<p id="requester_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                @else
                    <div>
                        <span class="ui-field-label">Pemohon</span>
                        <p class="ui-field-help">Identitas pemohon diambil dari akun yang sedang digunakan.</p>
                        <div class="mt-2 rounded-lg border border-[#dfe8ec] bg-[#f8fbfc] px-3 py-2.5 text-sm font-bold text-[#35505b]">{{ $actor->name }}{{ $actor->nip ? ' — NIP '.$actor->nip : '' }}</div>
                        <input type="hidden" name="requester_id" value="{{ $actor->id }}">
                    </div>
                @endif

                <div>
                    <label for="service_type_id" class="ui-field-label">Layanan <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <p id="service_type_id-help" class="ui-field-help">Pilihan dari katalog akan mengatur formulir di bawah. Anda dapat mengubahnya kapan saja.</p>
                    <select id="service_type_id" name="service_type_id" required data-ticket-service-select class="ui-select mt-2" @error('service_type_id') aria-invalid="true" aria-describedby="service_type_id-error" @else aria-describedby="service_type_id-help" @enderror>
                        <option value="" data-service-code="">Pilih layanan</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}" data-service-code="{{ $serviceType->code }}" @selected((string) $selectedServiceId === (string) $serviceType->id)>{{ $serviceType->code }} — {{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                    @error('service_type_id')<p id="service_type_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="ui-panel" aria-labelledby="ticket-request-heading">
            <div class="ui-panel-header">
                <h2 id="ticket-request-heading" class="ui-section-title">Detail permintaan</h2>
                <p class="ui-section-description">Tuliskan ringkasan dan penjelasan yang membantu Tim TI memahami kebutuhan Anda.</p>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <div>
                    <label for="subject" class="ui-field-label">Ringkasan permintaan <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <p id="subject-help" class="ui-field-help">Gunakan judul singkat yang mudah dikenali, misalnya “Tidak dapat terhubung ke Wi-Fi lantai 2”.</p>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="150" class="ui-input mt-2" @error('subject') aria-invalid="true" aria-describedby="subject-error" @else aria-describedby="subject-help" @enderror>
                    @error('subject')<p id="subject-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="ui-field-label">Deskripsi permintaan <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <p id="description-help" class="ui-field-help">Jelaskan kondisi, dampak, dan hasil yang diharapkan.</p>
                    <textarea id="description" name="description" rows="6" required maxlength="10000" class="ui-textarea mt-2" @error('description') aria-invalid="true" aria-describedby="description-error" @else aria-describedby="description-help" @enderror>{{ old('description') }}</textarea>
                    @error('description')<p id="description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="priority" class="ui-field-label">Prioritas usulan <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                        <p id="priority-help" class="ui-field-help">Prioritas dapat disesuaikan oleh Agen Tier 1 saat triase.</p>
                        <select id="priority" name="priority" required class="ui-select mt-2" @error('priority') aria-invalid="true" aria-describedby="priority-error" @else aria-describedby="priority-help" @enderror>
                            <option value="">Pilih prioritas</option>
                            @foreach (\App\Enums\Priority::labels() as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority')<p id="priority-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="room_id" class="ui-field-label">Lokasi <span data-ticket-location-required class="text-rose-600" aria-hidden="true">*</span><span data-ticket-location-required class="sr-only">wajib untuk layanan tertentu</span></label>
                        <p id="room_id-help" data-ticket-location-help class="ui-field-help">Wajib untuk SVC-01 dan SVC-05. Layanan lain dapat menyimpan lokasi kosong.</p>
                        <select id="room_id" name="room_id" data-ticket-location-select class="ui-select mt-2" @error('room_id') aria-invalid="true" aria-describedby="room_id-error" @else aria-describedby="room_id-help" @enderror>
                            <option value="">Pilih lokasi bila diperlukan</option>
                            @foreach ($buildings as $building)
                                @foreach ($building->floors as $floor)
                                    <optgroup label="{{ $building->name }} — {{ $floor->name }}">
                                        @foreach ($floor->rooms as $room)
                                            <option value="{{ $room->id }}" @selected((string) $selectedRoomId === (string) $room->id)>{{ $room->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endforeach
                        </select>
                        @error('room_id')<p id="room_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-panel" aria-labelledby="dynamic-fields-heading">
            <div class="ui-panel-header">
                <h2 id="dynamic-fields-heading" class="ui-section-title">Informasi layanan</h2>
                <p class="ui-section-description">Pilih layanan untuk menampilkan pertanyaan yang sesuai. Field bertanda bintang wajib diisi.</p>
            </div>
            <div class="space-y-4 p-5 sm:p-6">
                <p data-ticket-service-empty class="ui-empty !p-6">Pilih layanan terlebih dahulu agar formulir yang sesuai dapat ditampilkan.</p>
                @foreach ($serviceTypes as $serviceType)
                    <fieldset data-ticket-service-panel data-service-id="{{ $serviceType->id }}" data-service-code="{{ $serviceType->code }}" class="hidden space-y-4" @if ((string) $selectedServiceId !== (string) $serviceType->id) disabled @endif>
                        <legend class="sr-only">Field layanan {{ $serviceType->code }}</legend>
                        @if ($serviceType->code === 'SVC-07')
                            <div class="rounded-xl border border-[#b9e5f2] bg-[#f1fbfe] p-4 sm:p-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#d9f6ff] text-sm font-black text-[#1d5d72]" aria-hidden="true">P</span>
                                    <div>
                                        <p class="text-sm font-extrabold text-[#1d5d72]">Bagian Pemohon</p>
                                        <p class="mt-1 text-xs leading-5 text-[#52747b]">Lengkapi kebutuhan dan konteks usulan di bawah ini. Setelah tiket dibuat, Tim TI akan mengisi bagian internalnya pada detail tiket.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @foreach ($serviceType->activeFieldDefinitions->whereIn('visibility', ['requester', 'both']) as $field)
                            <x-tickets.dynamic-field :field="$field" :service="$serviceType" />
                        @endforeach
                    </fieldset>
                @endforeach
            </div>
        </section>

        <section class="ui-panel" aria-labelledby="attachments-heading">
            <div class="ui-panel-header">
                <h2 id="attachments-heading" class="ui-section-title">Lampiran pendukung</h2>
                <p class="ui-section-description">Unggah berkas sesuai kebijakan layanan. Lampiran disimpan pada penyimpanan privat dan tidak menjadi URL publik.</p>
            </div>
            <div class="space-y-4 p-5 sm:p-6">
                <p data-ticket-attachment-empty class="rounded-lg border border-[#e5edef] bg-[#f8fbfc] p-4 text-sm leading-6 text-[#6a8089]">Pilih layanan untuk melihat tipe lampiran yang tersedia.</p>
                @forelse ($attachmentPolicies as $policy)
                    @php
                        $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                        $policyServiceId = $policy->service_type_id ? (string) $policy->service_type_id : '';
                    @endphp
                    <div data-ticket-attachment data-service-id="{{ $policyServiceId }}" class="hidden rounded-xl border border-[#e5edef] bg-[#fbfdfd] p-4 sm:p-5">
                        <label for="attachment-policy-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                        <p id="attachment-policy-{{ $policy->id }}-help" class="ui-field-help">Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas{{ $policy->allowed_extensions ? ' · '.implode(', ', $policy->allowed_extensions) : '' }}.</p>
                        <input id="attachment-policy-{{ $policy->id }}" name="attachments[{{ $policy->id }}][]" type="file" data-ticket-attachment-input class="ui-input mt-2 file:mr-3 file:rounded-md file:border-0 file:bg-[#e8f7fb] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#1d5d72]" multiple @if ($accept !== '') accept="{{ $accept }}" @endif aria-describedby="attachment-policy-{{ $policy->id }}-help">
                    </div>
                @empty
                    <p class="ui-empty !p-6">Belum ada kebijakan lampiran yang tersedia. Tiket tetap dapat dikirim tanpa lampiran.</p>
                @endforelse
            </div>
        </section>

        <div class="ui-sticky-actions -mx-1 rounded-t-xl sm:mx-0 sm:rounded-xl sm:border">
            <p class="mr-auto hidden text-xs leading-5 text-[#78909a] sm:block">Setelah dikirim, tiket mendapat nomor dan status Baru.</p>
            <a href="{{ route('tickets.index') }}" class="ui-btn ui-btn-ghost">Batal</a>
            <button type="submit" data-ticket-submit class="ui-btn ui-btn-primary min-w-32">
                <span data-ticket-submit-label>Kirim tiket</span>
                <span data-ticket-submit-loading class="hidden" aria-hidden="true">Mengirim…</span>
            </button>
        </div>
    </form>
@endsection
