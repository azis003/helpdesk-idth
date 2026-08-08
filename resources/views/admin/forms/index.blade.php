@extends('layouts.app')

@php
    $activeFields = $selectedServiceType?->activeFieldDefinitions ?? collect();
    $requesterFields = $activeFields->filter(fn ($field) => in_array($field->visibility, ['requester', 'both'], true))->count();
    $internalFields = $activeFields->filter(fn ($field) => $field->visibility === 'internal')->count();
    $nextFieldOrder = ((int) ($activeFields->max('sort_order') ?? 0)) + 1;
    $formatOptions = function ($field): string {
        return $field->options->map(fn ($option) => $option->value.'|'.$option->label)->implode("\n");
    };
    $formatRules = function ($field): string {
        return collect($field->validation_rules ?? [])->implode("\n");
    };
@endphp

@section('title', 'Manajemen Formulir — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Manajemen Formulir')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Data master</p>
            <h1 class="ui-page-title">Manajemen Formulir</h1>
            <p class="ui-page-description">Pilih satu layanan, lalu susun pertanyaan yang perlu diisi pemohon. Setiap perubahan disimpan sebagai versi baru agar tiket lama tetap aman.</p>
        </div>
    </div>

    <nav class="mt-7 overflow-x-auto" aria-label="Manajemen layanan dan formulir">
        <div class="inline-flex min-w-full gap-1 border-b border-[#dfe8ec] sm:min-w-0">
            <a href="{{ route('admin.catalog.index', ['section' => 'services']) }}" class="whitespace-nowrap border-b-2 border-transparent px-3 pb-3 text-sm font-bold text-[#78909a] transition hover:border-[#b9cbd1] hover:text-[#35505b]">Manajemen Layanan</a>
            <a href="{{ route('admin.forms.index') }}" class="whitespace-nowrap border-b-2 border-[#17313c] px-3 pb-3 text-sm font-bold text-[#17313c]" aria-current="page">Manajemen Formulir</a>
        </div>
    </nav>

    <section class="mt-6 rounded-xl border border-[#d9e8ec] bg-[#f7fbfc] px-4 py-4 sm:px-5" aria-labelledby="form-flow-heading">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="form-flow-heading" class="text-sm font-extrabold text-[#35505b]">Cara mengatur formulir</h2>
                <p class="mt-1 text-xs leading-5 text-[#78909a]">Ikuti urutan sederhana ini agar konfigurasi tetap mudah dipahami.</p>
            </div>
            <ol class="grid gap-2 text-xs text-[#526f79] sm:grid-cols-3 lg:min-w-[42rem]">
                <li class="rounded-lg border border-[#dfe8ec] bg-white px-3 py-2"><span class="font-extrabold text-[#147a79]">1.</span> Pilih layanan</li>
                <li class="rounded-lg border border-[#dfe8ec] bg-white px-3 py-2"><span class="font-extrabold text-[#147a79]">2.</span> Tambah field</li>
                <li class="rounded-lg border border-[#dfe8ec] bg-white px-3 py-2"><span class="font-extrabold text-[#147a79]">3.</span> Simpan versi</li>
            </ol>
        </div>
    </section>

    <div class="mt-6 grid gap-5 xl:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="self-start overflow-hidden rounded-xl border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)] xl:sticky xl:top-24" aria-labelledby="service-picker-heading">
            <div class="border-b border-[#e5eaed] bg-[#075998] px-5 py-4 text-white">
                <h2 id="service-picker-heading" class="text-base font-extrabold">Pilih layanan</h2>
                <p class="mt-1 text-xs leading-5 text-blue-100">Formulir mengikuti layanan yang dipilih.</p>
            </div>
            <nav class="max-h-[32rem] overflow-y-auto p-2" aria-label="Daftar layanan untuk formulir">
                @forelse ($serviceTypes as $serviceType)
                    @php($isSelectedService = $selectedServiceType?->is($serviceType))
                    <a href="{{ route('admin.forms.index', ['service' => $serviceType->id]) }}" class="mb-1 block rounded-lg border px-3 py-3 transition last:mb-0 {{ $isSelectedService ? 'border-[#75d5f3] bg-[#effaff] shadow-sm' : 'border-transparent hover:border-[#d9e8ec] hover:bg-[#f8fbfc]' }}" @if ($isSelectedService) aria-current="page" @endif>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[0.65rem] font-extrabold tracking-[0.08em] text-[#26677b]">{{ $serviceType->code }}</p>
                                <p class="mt-1 text-sm font-extrabold leading-5 text-[#263a43]">{{ $serviceType->name }}</p>
                            </div>
                            <span class="mt-0.5 shrink-0 rounded-full px-2 py-1 text-[0.6rem] font-bold {{ $serviceType->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-3 text-[0.68rem] text-[#78909a]">
                            <span>{{ $serviceType->activeFieldDefinitions->count() }} field aktif</span>
                            <span aria-hidden="true">→</span>
                        </div>
                    </a>
                @empty
                    <p class="p-5 text-center text-xs leading-5 text-[#78909a]">Belum ada layanan untuk dikonfigurasi.</p>
                @endforelse
            </nav>
        </aside>

        <main class="min-w-0">
            @if ($selectedServiceType)
                <section class="overflow-hidden rounded-xl border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="selected-form-heading">
                    <div class="flex flex-col gap-4 border-b border-[#e5eaed] px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-7">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Formulir layanan</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <h2 id="selected-form-heading" class="text-xl font-extrabold tracking-tight text-[#18252b]">{{ $selectedServiceType->name }}</h2>
                                <span class="rounded-lg bg-[#eef8fc] px-2.5 py-1 text-xs font-extrabold text-[#26677b]">{{ $selectedServiceType->code }}</span>
                            </div>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#718088]">{{ $selectedServiceType->description ?: 'Belum ada deskripsi layanan. Tambahkan dari Manajemen Layanan agar pemohon lebih mudah memilih.' }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $selectedServiceType->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $selectedServiceType->is_active ? 'Layanan aktif' : 'Layanan nonaktif' }}</span>
                            <a href="{{ route('admin.services.index', ['service' => $selectedServiceType->id]) }}" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs">Edit layanan</a>
                        </div>
                    </div>

                    <div class="grid gap-3 border-b border-[#e5eaed] bg-[#fbfcfd] px-5 py-4 sm:grid-cols-3 sm:px-7">
                        <div><p class="text-[0.68rem] font-bold uppercase tracking-wide text-[#78909a]">Field aktif</p><p class="mt-1 text-lg font-extrabold text-[#17313c]">{{ $activeFields->count() }}</p></div>
                        <div><p class="text-[0.68rem] font-bold uppercase tracking-wide text-[#78909a]">Terlihat pemohon</p><p class="mt-1 text-lg font-extrabold text-[#17313c]">{{ $requesterFields }}</p></div>
                        <div><p class="text-[0.68rem] font-bold uppercase tracking-wide text-[#78909a]">Internal Tim TI</p><p class="mt-1 text-lg font-extrabold text-[#17313c]">{{ $internalFields }}</p></div>
                    </div>
                </section>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-[#fecdd3] bg-[#fff1f2] px-4 py-3 text-sm text-[#9f1239]" role="alert">
                        <p class="font-extrabold">Perubahan belum disimpan.</p>
                        <p class="mt-1 text-xs leading-5">Periksa keterangan di bawah field yang bermasalah, lalu coba lagi.</p>
                    </div>
                @endif

                <div class="mt-5 grid gap-5 2xl:grid-cols-[minmax(0,1fr)_20rem]">
                    <section class="min-w-0" aria-labelledby="field-builder-heading">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 id="field-builder-heading" class="text-lg font-extrabold tracking-tight text-[#263a43]">Susunan field</h2>
                                <p class="mt-1 text-sm leading-6 text-[#718088]">Field aktif muncul saat pemohon membuat tiket baru untuk layanan ini.</p>
                            </div>
                        </div>

                        <section class="mt-4 overflow-hidden rounded-xl border border-[#b9e8e1] bg-[#ecfbf8]" aria-labelledby="new-field-heading">
                            <div class="border-b border-[#b9e8e1] px-4 py-4 sm:px-5">
                                <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#0f6862]">Langkah 2</p>
                                <h3 id="new-field-heading" class="mt-1 text-base font-extrabold text-[#17313c]">Tambahkan field</h3>
                                <p class="mt-1 text-xs leading-5 text-[#52747b]">Mulai dari nama field. Kunci teknis akan dibuat otomatis untuk Anda.</p>
                            </div>
                            <div class="p-4 sm:p-5">
                                @include('admin.forms._field-form', ['mode' => 'create', 'field' => null, 'serviceType' => $selectedServiceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder])
                            </div>
                        </section>

                        <section class="mt-5" aria-labelledby="active-fields-heading">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 id="active-fields-heading" class="text-base font-extrabold text-[#263a43]">Field formulir aktif</h3>
                                    <p class="mt-1 text-xs leading-5 text-[#78909a]">Buka satu field untuk mengubah detailnya. Hapus field dari formulir jika sudah tidak diperlukan.</p>
                                </div>
                                <span class="rounded-full bg-[#eef3ff] px-2.5 py-1 text-xs font-extrabold text-[#4f63a6]">{{ $activeFields->count() }} field</span>
                            </div>

                            <div class="mt-3 space-y-2">
                                @forelse ($activeFields as $field)
                                    <details class="rounded-xl border border-[#dfe8ec] bg-white" @if ($loop->first && $errors->any()) open @endif>
                                        <summary class="ui-disclosure-summary flex items-start justify-between gap-3 p-4">
                                            <span class="flex min-w-0 items-start gap-3">
                                                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#f1f7f9] text-xs font-extrabold text-[#526f79]">{{ $loop->iteration }}</span>
                                                <span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $field->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · versi {{ $field->version }}</span></span>
                                            </span>
                                            <span class="flex shrink-0 flex-wrap justify-end gap-1.5">
                                                <span class="hidden rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.65rem] font-bold text-[#607681] sm:inline-flex">{{ $visibilities[$field->visibility] ?? $field->visibility }}</span>
                                                <span class="rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $field->is_required ? 'bg-[#fff6df] text-[#956b16]' : 'bg-[#f3f7f8] text-[#78909a]' }}">{{ $field->is_required ? 'Wajib' : 'Opsional' }}</span>
                                            </span>
                                        </summary>
                                        <div class="border-t border-[#edf2f4] bg-[#fcfdfd] p-4 sm:p-5">
                                            <div class="mb-4 rounded-lg bg-[#f8fbfc] px-3 py-2.5 text-xs leading-5 text-[#607681]">Anda sedang mengedit <span class="font-extrabold text-[#35505b]">versi {{ $field->version }}</span>. Simpan untuk membuat versi berikutnya; definisi lama tetap tersedia untuk histori tiket.</div>
                                            @include('admin.forms._field-form', ['mode' => 'version', 'field' => $field, 'serviceType' => $selectedServiceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder])
                                            <div class="mt-4 flex justify-end border-t border-[#e3ecef] pt-4">
                                                <form method="POST" action="{{ route('admin.catalog.fields.destroy', $field) }}" data-swal-confirm="Hapus {{ $field->label }} dari formulir? Field tidak akan tampil pada tiket baru, tetapi versi lamanya tetap tersimpan untuk histori tiket." data-submit-feedback>
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="_service_edit" value="{{ $selectedServiceType->id }}">
                                                    <input type="hidden" name="_service_tab" value="formulir">
                                                    <button type="submit" class="ui-btn ui-btn-warning !min-h-9 !text-xs" data-submit-button><span data-submit-label>Hapus dari formulir</span><span class="hidden" data-submit-loading>Menghapus...</span></button>
                                                </form>
                                            </div>
                                        </div>
                                    </details>
                                @empty
                                    <div class="ui-empty">
                                        <p class="font-extrabold text-[#526f79]">Belum ada field formulir</p>
                                        <p class="mt-1 text-xs leading-5">Mulai dengan menambahkan field di panel atas. Satu field sebaiknya mewakili satu informasi yang jelas.</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    </section>

                    @include('admin.catalog._form-preview', ['serviceType' => $selectedServiceType, 'fieldTypes' => $fieldTypes])
                </div>
            @else
                <div class="ui-empty">
                    <p class="font-extrabold text-[#526f79]">Pilih layanan untuk mulai</p>
                    <p class="mt-1 text-xs leading-5">Daftar layanan ada di panel sebelah kiri. Setelah dipilih, field formulirnya akan muncul di sini.</p>
                </div>
            @endif
        </main>
    </div>
@endsection
