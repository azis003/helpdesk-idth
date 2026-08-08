@php
    $categoryLabel = $serviceType->ticket_class
        ?: $serviceType->variants->where('is_active', true)->pluck('ticket_class')->unique()->implode(' / ');
    $categoryLabel = $categoryLabel ?: 'Belum diatur';
    $requesterFields = $serviceType->activeFieldDefinitions->filter(fn ($field) => in_array($field->visibility, ['requester', 'both'], true));
    $internalFields = $serviceType->activeFieldDefinitions->filter(fn ($field) => $field->visibility === 'internal');
@endphp

<div id="service-view-modal-{{ $serviceType->id }}" data-ui-modal class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-view-title-{{ $serviceType->id }}" class="relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-3xl overflow-y-auto overscroll-contain rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 bg-[#0b98e5] px-5 py-4 text-white sm:px-7">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-blue-100">Detail layanan</p>
                    <h2 id="service-view-title-{{ $serviceType->id }}" class="mt-1 text-xl font-extrabold">{{ $serviceType->name }}</h2>
                    <p class="mt-1 text-xs text-blue-100">Ringkasan data master dan template formulir yang sedang aktif.</p>
                </div>
                <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup detail layanan {{ $serviceType->name }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                <dl class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-[#dfe8ec] bg-[#f8fbfc] p-4"><dt class="text-[0.65rem] font-extrabold uppercase tracking-wide text-[#78909a]">Kode layanan</dt><dd class="mt-1 text-lg font-extrabold text-[#17313c]">{{ $serviceType->code }}</dd></div>
                    <div class="rounded-xl border border-[#dfe8ec] bg-[#f8fbfc] p-4"><dt class="text-[0.65rem] font-extrabold uppercase tracking-wide text-[#78909a]">Kategori</dt><dd class="mt-1 text-lg font-extrabold text-[#17313c]">{{ $categoryLabel }}</dd></div>
                    <div class="rounded-xl border border-[#dfe8ec] bg-[#f8fbfc] p-4"><dt class="text-[0.65rem] font-extrabold uppercase tracking-wide text-[#78909a]">Status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $serviceType->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></dd></div>
                </dl>

                <section class="rounded-xl border border-[#dfe8ec] bg-white" aria-labelledby="service-view-description-{{ $serviceType->id }}">
                    <div class="border-b border-[#edf2f4] px-4 py-3"><h3 id="service-view-description-{{ $serviceType->id }}" class="text-sm font-extrabold text-[#35505b]">Deskripsi keahlian</h3></div>
                    <p class="px-4 py-4 text-sm leading-6 text-[#607681]">{{ $serviceType->description ?: 'Belum ada deskripsi keahlian untuk layanan ini.' }}</p>
                </section>

                <section class="rounded-xl border border-[#dfe8ec] bg-white" aria-labelledby="service-view-skills-{{ $serviceType->id }}">
                    <div class="border-b border-[#edf2f4] px-4 py-3"><h3 id="service-view-skills-{{ $serviceType->id }}" class="text-sm font-extrabold text-[#35505b]">Syarat keahlian</h3></div>
                    <div class="p-4">
                        @if ($serviceType->skills->isNotEmpty())
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @foreach ($serviceType->skills as $skill)
                                    <li class="rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 py-2.5"><p class="text-xs font-extrabold text-[#35505b]">{{ $skill->name }}</p>@if ($skill->description)<p class="mt-1 text-[0.68rem] leading-4 text-[#78909a]">{{ $skill->description }}</p>@endif</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs leading-5 text-[#78909a]">Belum ada syarat keahlian yang dipetakan.</p>
                        @endif
                    </div>
                </section>

                <section class="rounded-xl border border-[#dfe8ec] bg-white" aria-labelledby="service-view-fields-{{ $serviceType->id }}">
                    <div class="flex items-center justify-between gap-3 border-b border-[#edf2f4] px-4 py-3"><h3 id="service-view-fields-{{ $serviceType->id }}" class="text-sm font-extrabold text-[#35505b]">Template formulir aktif</h3><span class="text-xs font-bold text-[#78909a]">{{ $serviceType->activeFieldDefinitions->count() }} field</span></div>
                    <div class="p-4">
                        @if ($serviceType->activeFieldDefinitions->isNotEmpty())
                            <ol class="space-y-2">
                                @foreach ($serviceType->activeFieldDefinitions as $field)
                                    <li class="flex items-start gap-3 rounded-lg border border-[#e1eaed] bg-[#f8fbfc] px-3 py-3">
                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-white text-xs font-extrabold text-[#526f79]">{{ $loop->iteration }}</span>
                                        <div class="min-w-0 flex-1"><p class="text-xs font-extrabold text-[#35505b]">{{ $field->label }} @if ($field->is_required)<span class="text-rose-600">*</span>@endif</p><p class="mt-1 text-[0.68rem] text-[#78909a]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · {{ $field->visibility === 'requester' ? 'Pemohon' : ($field->visibility === 'internal' ? 'Tim TI' : 'Pemohon dan Tim TI') }}</p></div>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <p class="text-xs leading-5 text-[#78909a]">Belum ada field formulir aktif.</p>
                        @endif
                    </div>
                </section>

                <div class="flex flex-wrap justify-end gap-2 border-t border-[#e3ecef] pt-4">
                    <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost !min-h-10">Tutup</button>
                    <button type="button" data-ui-modal-close data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-primary !min-h-10">Edit layanan</button>
                </div>
            </div>
        </section>
    </div>
</div>
