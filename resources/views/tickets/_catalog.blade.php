@php
    // Presentasional saja - tidak mengubah data maupun logika katalog layanan.
    $catalogMeta = 'mt-4 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]';
    $catalogTitle = 'mt-2 text-sm font-extrabold leading-5 text-[color:var(--tm-text)]';
    $catalogDescription = 'mt-2 line-clamp-2 text-xs leading-5 text-[color:var(--tm-text-muted)]';
    $catalogCta = 'mt-auto pt-4 text-xs font-extrabold text-[color:var(--tm-brand-700)]';
    $catalogEmptyTitle = 'Belum ada layanan';
    $catalogEmptyDescription = 'Belum ada layanan yang dapat dipilih. Hubungi Tim TI bila layanan yang Anda butuhkan belum tersedia.';
@endphp

<x-page-header eyebrow="Tiket baru" title="Pilih layanan">
    <a href="{{ route('tickets.index') }}" class="ui-btn ui-btn-ghost">Lihat tiket saya</a>
</x-page-header>

<section class="mt-6" aria-labelledby="service-catalog-heading">
    <h2 id="service-catalog-heading" class="sr-only">Katalog layanan</h2>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($serviceTypes as $serviceType)
            <a
                href="{{ route('tickets.create', ['service_type_id' => $serviceType->getKey()]) }}"
                class="ui-catalog-card group text-left"
                aria-label="Pilih layanan {{ $serviceType->name }}"
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
                <span class="{{ $catalogMeta }}">{{ $serviceType->code }} · {{ $serviceType->ticket_class ?? 'Layanan' }}</span>
                <span class="{{ $catalogTitle }}">{{ $serviceType->name }}</span>
                <span class="{{ $catalogDescription }}">{{ $serviceType->description ?: 'Pilih layanan ini untuk melanjutkan.' }}</span>
                <span class="{{ $catalogCta }}">Pilih layanan <span aria-hidden="true">→</span></span>
            </a>
        @empty
            <div class="sm:col-span-2 xl:col-span-4">
                <x-empty-state :title="$catalogEmptyTitle" :description="$catalogEmptyDescription" />
            </div>
        @endforelse
    </div>
</section>
