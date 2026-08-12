@php
    // Presentasional saja - tidak mengubah data maupun logika preview.
    $servicePreviewPanel = 'relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-3xl overflow-y-auto overscroll-contain rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]';
    $servicePreviewHeader = 'sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-7';
    $servicePreviewIconTile = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $servicePreviewClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]';
@endphp

<div id="service-preview-modal-{{ $serviceType->id }}" data-ui-modal class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-preview-title-{{ $serviceType->id }}" class="{{ $servicePreviewPanel }}">
            <div class="{{ $servicePreviewHeader }}">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="{{ $servicePreviewIconTile }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14v13H5zM8 9h8M8 12h5M8 15h7" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-[color:var(--tm-brand-700)]">Preview formulir</p>
                        <h2 id="service-preview-title-{{ $serviceType->id }}" class="mt-1 text-lg font-extrabold tracking-tight text-[color:var(--tm-text)]">Yang dilihat pemohon</h2>
                        <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Preview hanya menampilkan field yang terlihat oleh Pemohon pada tiket baru.</p>
                    </div>
                </div>
                <button type="button" data-ui-modal-close class="{{ $servicePreviewClose }}" aria-label="Tutup preview formulir {{ $serviceType->name }}">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6">
                @include('admin.catalog._form-preview', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
                <div class="mt-4 flex justify-end border-t border-[color:var(--tm-border-subtle)] pt-5">
                    <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost !min-h-10">Tutup preview</button>
                </div>
            </div>
        </section>
    </div>
</div>
