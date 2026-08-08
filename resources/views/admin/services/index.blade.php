@extends('layouts.app')

@php
    $autoOpenService = old('_service_edit', request()->query('service'));
@endphp

@section('title', 'Manajemen Layanan — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Manajemen Layanan')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Data master · Katalog layanan</p>
            <h1 class="ui-page-title">Manajemen Layanan</h1>
            <p class="ui-page-description">Kelola jenis layanan, kategori, syarat keahlian, dan formulir yang akan digunakan pemohon.</p>
        </div>
    </div>

    <section class="mt-7 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="services-heading">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="services-heading" class="text-xl font-extrabold tracking-tight">Daftar layanan</h2>
                <p class="mt-1 text-xs leading-5 text-blue-100">Lihat detail untuk mengubah layanan, atau buka preview formulir pemohon.</p>
            </div>
            <button type="button" data-ui-modal-open="service-create-modal" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#7138e8] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#075998]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat layanan
            </button>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <form method="GET" action="{{ route('admin.services.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-[#17212b]">
                    <label for="service-per-page" class="font-bold">Tampilkan</label>
                    <select id="service-per-page" name="per_page" class="h-10 rounded-lg border border-[#d7e0e4] bg-white px-3 text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15" onchange="this.form.submit()">
                        @foreach ([10, 25, 50] as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                    <span>data</span>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <label for="service-search" class="shrink-0 text-sm font-bold text-[#17212b]">Cari:</label>
                    <input id="service-search" name="q" type="search" value="{{ $search }}" class="h-10 w-full min-w-0 rounded-lg border border-[#d7e0e4] bg-[#f8fafb] px-3 text-sm text-[#17212b] outline-none placeholder:text-[#9baab0] focus:border-[#0a87c9] focus:bg-white focus:ring-2 focus:ring-[#0a87c9]/15 sm:w-64" placeholder="Kode atau jenis layanan" aria-label="Cari layanan">
                    <button type="submit" class="sr-only">Cari layanan</button>
                </div>
            </form>

            <div class="mt-4 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
                <table class="min-w-[860px] w-full border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar layanan dengan kode, jenis, kategori, status, detail, dan preview formulir</caption>
                    <thead class="bg-[#fbfcfd] text-[#34495a]">
                        <tr>
                            <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Kode Layanan</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Jenis Layanan</th>
                            <th scope="col" class="w-32 border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Kategori</th>
                            <th scope="col" class="w-28 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Status</th>
                            <th scope="col" class="w-40 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($serviceTypes as $serviceType)
                            @php
                                $categoryLabel = $serviceType->ticket_class ?: $serviceType->variants->where('is_active', true)->pluck('ticket_class')->unique()->implode(' / ');
                            @endphp
                            <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ ($serviceTypes->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5"><span class="rounded-lg bg-[#eef8fc] px-2.5 py-1 text-xs font-extrabold tracking-[0.08em] text-[#26677b]">{{ $serviceType->code }}</span></td>
                                <td class="border-b border-[#e5eaed] px-4 py-5"><p class="font-bold text-[#112b49]">{{ $serviceType->name }}</p>@if ($serviceType->description)<p class="mt-1 max-w-[28rem] truncate text-xs text-[#78909a]">{{ $serviceType->description }}</p>@endif</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5"><span class="rounded-full bg-[#eef8fc] px-2.5 py-1 text-xs font-bold text-[#26677b]">{{ $categoryLabel ?: 'Belum diatur' }}</span></td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $serviceType->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="service-view-modal-{{ $serviceType->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Lihat layanan {{ $serviceType->name }}" title="Lihat layanan"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z" /><circle cx="12" cy="12" r="2.5" /></svg></button>
                                        <button type="button" data-ui-modal-open="service-preview-modal-{{ $serviceType->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#147a79] text-white transition hover:bg-[#0f6862] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#147a79] focus-visible:ring-offset-2" aria-label="Preview formulir {{ $serviceType->name }}" title="Preview formulir"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-[#718088]">{{ $search !== '' ? 'Tidak ada layanan yang cocok dengan pencarian.' : 'Belum ada layanan. Buat layanan pertama untuk mulai menyusun katalog.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($serviceTypes->total() > 0)
                        Menampilkan {{ $serviceTypes->firstItem() }}–{{ $serviceTypes->lastItem() }} dari {{ $serviceTypes->total() }} layanan
                    @else
                        Tidak ada data layanan
                    @endif
                </p>
                @if ($serviceTypes->hasPages())<div>{{ $serviceTypes->links() }}</div>@endif
            </div>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($serviceTypes as $serviceType)
                @php
                    $categoryLabel = $serviceType->ticket_class ?: $serviceType->variants->where('is_active', true)->pluck('ticket_class')->unique()->implode(' / ');
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($serviceTypes->firstItem() ?? 1) + $loop->index }}</p><p class="mt-1 text-xs font-extrabold tracking-[0.08em] text-[#26677b]">{{ $serviceType->code }}</p><h3 class="mt-1 font-bold text-[#112b49]">{{ $serviceType->name }}</h3></div><span class="shrink-0 rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $serviceType->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                    <dl class="mt-4 grid gap-3 rounded-lg bg-[#f8fafb] p-4 text-sm"><div><dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Jenis layanan</dt><dd class="mt-1 text-[#172d45]">{{ $serviceType->name }}</dd></div><div><dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Kategori</dt><dd class="mt-1 text-[#172d45]">{{ $categoryLabel ?: 'Belum diatur' }}</dd></div></dl>
                    <div class="mt-4 flex flex-wrap justify-end gap-2"><button type="button" data-ui-modal-open="service-view-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Lihat detail</button><button type="button" data-ui-modal-open="service-preview-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-primary !min-h-9 !px-3 !text-xs">Preview formulir</button></div>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-[#718088]">{{ $search !== '' ? 'Tidak ada layanan yang cocok dengan pencarian.' : 'Belum ada layanan. Buat layanan pertama untuk mulai menyusun katalog.' }}</p>
            @endforelse
        </div>
    </section>

    @include('admin.services._create-modal')
    @foreach ($serviceTypes as $serviceType)
        @include('admin.services._view-modal', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
        @include('admin.services._edit-modal', ['serviceType' => $serviceType, 'skills' => $skills, 'ticketClasses' => $ticketClasses, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'autoOpenService' => $autoOpenService])
        @include('admin.services._preview-modal', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
    @endforeach
@endsection
