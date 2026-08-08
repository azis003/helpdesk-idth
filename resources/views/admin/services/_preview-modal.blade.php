<div id="service-preview-modal-{{ $serviceType->id }}" data-ui-modal class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-preview-title-{{ $serviceType->id }}" class="relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-3xl overflow-y-auto overscroll-contain rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 bg-[#147a79] px-5 py-4 text-white sm:px-7">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-teal-100">Preview formulir</p>
                    <h2 id="service-preview-title-{{ $serviceType->id }}" class="mt-1 text-xl font-extrabold">Yang dilihat pemohon</h2>
                    <p class="mt-1 text-xs leading-5 text-teal-100">Preview hanya menampilkan field yang terlihat oleh Pemohon pada tiket baru.</p>
                </div>
                <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup preview formulir {{ $serviceType->name }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6">
                @include('admin.catalog._form-preview', ['serviceType' => $serviceType, 'fieldTypes' => $fieldTypes])
                <div class="mt-4 flex justify-end">
                    <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost !min-h-10">Tutup preview</button>
                </div>
            </div>
        </section>
    </div>
</div>
