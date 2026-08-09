@php
    $autoOpen = old('_service_create') === '1';
    $createUsesSla = old('uses_sla') === null
        ? true
        : filter_var(old('uses_sla'), FILTER_VALIDATE_BOOLEAN);
    $selectedSkillIds = collect(old('skill_ids', []))->map(fn ($id): int => (int) $id)->all();
@endphp

<div id="service-create-modal" data-ui-modal data-auto-open="{{ $autoOpen ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-create-title" class="relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-5xl overflow-y-auto overscroll-contain rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 bg-[#075998] px-5 py-4 text-white sm:px-7">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-blue-100">Layanan baru</p>
                    <h2 id="service-create-title" class="mt-1 text-xl font-extrabold">Buat layanan</h2>
                    <p class="mt-1 text-xs leading-5 text-blue-100">Isi data layanan dan susun formulir pemohon dari satu tempat.</p>
                </div>
                <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup buat layanan">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.services.store') }}" data-submit-feedback data-service-field-builder data-service-sla-form>
                @csrf
                <input type="hidden" name="_service_create" value="1">

                @if ($errors->any())
                    <div class="mx-5 mt-5 rounded-xl border border-[#fecdd3] bg-[#fff1f2] px-4 py-3 text-sm text-[#9f1239] sm:mx-7" role="alert">
                        <p class="font-extrabold">Layanan belum disimpan.</p>
                        <p class="mt-1 text-xs leading-5">Periksa keterangan di bawah field yang bermasalah, lalu coba lagi.</p>
                    </div>
                @endif

                <div class="space-y-5 p-5 sm:p-7">
                    <section class="rounded-xl border border-[#dfe8ec] bg-white" aria-labelledby="new-service-data-heading">
                        <div class="border-b border-[#edf2f4] bg-[#f8fbfc] px-4 py-4 sm:px-5">
                            <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Langkah 1</p>
                            <h3 id="new-service-data-heading" class="mt-1 text-base font-extrabold text-[#263a43]">Data layanan</h3>
                            <p class="mt-1 text-xs leading-5 text-[#78909a]">Kode menjadi identitas sistem, sedangkan jenis layanan tampil di katalog pemohon.</p>
                        </div>
                        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                            <div>
                                <label for="new-service-code" class="ui-field-label">Kode layanan <span class="text-rose-600">*</span></label>
                                <input id="new-service-code" name="code" value="{{ old('code') }}" required maxlength="20" class="ui-input mt-2 uppercase" placeholder="Contoh: SVC-08" data-service-sla-code>
                                <p class="ui-field-help">Gunakan kode singkat dan unik, misalnya SVC-08.</p>
                                @error('code')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="new-service-name" class="ui-field-label">Jenis layanan <span class="text-rose-600">*</span></label>
                                <input id="new-service-name" name="name" value="{{ old('name') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Permintaan akses aplikasi">
                                <p class="ui-field-help">Pilih kata yang mudah dipahami pemohon.</p>
                                @error('name')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="new-service-category" class="ui-field-label">Kategori layanan <span class="text-rose-600">*</span></label>
                                <select id="new-service-category" name="ticket_class" required class="ui-select mt-2">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($ticketClasses as $ticketClass)
                                        <option value="{{ $ticketClass }}" @selected(old('ticket_class') === $ticketClass)>{{ $ticketClass }}</option>
                                    @endforeach
                                </select>
                                <p class="ui-field-help">INC untuk gangguan, REQ untuk permintaan, CHG untuk perubahan.</p>
                                @error('ticket_class')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div class="rounded-xl border border-[#cfe4e8] bg-[#f8fbfc] p-4 sm:col-span-2" data-service-sla-panel>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <input type="hidden" name="uses_sla" value="0">
                                        <input id="new-service-uses-sla" name="uses_sla" value="1" type="checkbox" class="ui-checkbox mt-0.5" @checked($createUsesSla) data-service-sla-toggle>
                                        <div>
                                            <label for="new-service-uses-sla" class="ui-field-label">Gunakan target SLA</label>
                                            <p class="mt-1 text-xs leading-5 text-[#78909a]" data-service-sla-status>Target penyelesaian dihitung dalam hari kerja dan tersimpan sebagai versi kebijakan.</p>
                                        </div>
                                    </div>
                                    <div class="w-full sm:max-w-xs" data-service-sla-target>
                                        <label for="new-service-target-sla" class="ui-field-label">Target SLA (hari kerja)</label>
                                        <input id="new-service-target-sla" name="target_working_days" type="number" min="1" max="365" value="{{ old('target_working_days') }}" class="ui-input mt-2" data-service-sla-target-input @disabled(! $createUsesSla)>
                                        <p class="ui-field-help">Minimal 1 dan maksimal 365 hari kerja.</p>
                                        @error('target_working_days')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="new-service-description" class="ui-field-label">Deskripsi keahlian</label>
                                <textarea id="new-service-description" name="description" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Jelaskan konteks layanan dan kemampuan yang biasanya dibutuhkan untuk menanganinya.">{{ old('description') }}</textarea>
                                <p class="ui-field-help">Deskripsi ini membantu pemohon dan Tim TI memahami cakupan layanan.</p>
                                @error('description')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-[#dfe8ec] bg-white" aria-labelledby="new-service-skills-heading">
                        <div class="border-b border-[#edf2f4] bg-[#f8fbfc] px-4 py-4 sm:px-5">
                            <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Langkah 2</p>
                            <h3 id="new-service-skills-heading" class="mt-1 text-base font-extrabold text-[#263a43]">Syarat keahlian</h3>
                            <p class="mt-1 text-xs leading-5 text-[#78909a]">Pilih keahlian dari master Keahlian. Boleh memilih lebih dari satu.</p>
                        </div>
                        <div class="p-4 sm:p-5">
                            <details class="rounded-xl border border-[#dfe8ec] bg-white" data-skill-picker>
                                <summary class="ui-disclosure-summary flex min-h-12 items-center gap-3 px-4 py-3">
                                    <span class="min-w-0 flex-1"><span class="block text-xs font-extrabold text-[#35505b]">Pilih keahlian</span><span class="mt-0.5 block truncate text-[0.68rem] text-[#78909a]" data-skill-picker-summary>Belum ada keahlian dipilih</span></span>
                                    <span class="flex shrink-0 items-center text-xs font-bold text-[#78909a]" aria-hidden="true">Buka</span>
                                </summary>
                                <fieldset class="grid gap-2 border-t border-[#edf2f4] p-3 sm:grid-cols-2">
                                    <legend class="sr-only">Daftar syarat keahlian</legend>
                                    @forelse ($skills as $skill)
                                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-[#e1eaed] bg-white px-3 py-2.5 transition hover:border-[#8bd7ee] has-[:checked]:border-[#75d5f3] has-[:checked]:bg-[#f1fbfe]">
                                            <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="ui-checkbox mt-0.5" @checked(in_array($skill->id, $selectedSkillIds, true))>
                                            <span class="min-w-0"><span class="block text-xs font-extrabold text-[#35505b]">{{ $skill->name }}</span>@if ($skill->description)<span class="mt-0.5 block text-[0.68rem] leading-4 text-[#78909a]">{{ $skill->description }}</span>@endif</span>
                                        </label>
                                    @empty
                                        <p class="text-xs leading-5 text-[#78909a] sm:col-span-2">Belum ada keahlian aktif. Tambahkan master keahlian terlebih dahulu.</p>
                                    @endforelse
                                </fieldset>
                            </details>
                            @error('skill_ids')<p class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="rounded-xl border border-[#b9e8e1] bg-[#ecfbf8]" aria-labelledby="new-service-template-heading">
                        <div class="flex flex-col gap-3 border-b border-[#b9e8e1] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div>
                                <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#0f6862]">Langkah 3</p>
                                <h3 id="new-service-template-heading" class="mt-1 text-base font-extrabold text-[#17313c]">Template formulir</h3>
                                <p class="mt-1 text-xs leading-5 text-[#52747b]">Tambah field yang perlu diisi pemohon. Field dapat berupa teks, dropdown, angka, tanggal, atau Ya/Tidak.</p>
                            </div>
                            <button type="button" data-service-field-add class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#147a79] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0f6862] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#147a79] focus-visible:ring-offset-2 focus-visible:ring-offset-[#ecfbf8]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                Tambah field
                            </button>
                        </div>
                        <div class="space-y-3 p-4 sm:p-5" data-service-field-list>
                            @include('admin.services._create-field-row', ['index' => 0, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities])
                        </div>
                        <template data-service-field-template>
                            @include('admin.services._create-field-row', ['index' => '__INDEX__', 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities])
                        </template>
                    </section>
                </div>

                <div class="sticky bottom-0 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] bg-white px-5 py-4 sm:px-7">
                    <p class="max-w-xl text-xs leading-5 text-[#78909a]">Setelah disimpan, layanan dan field aktif akan langsung tersedia untuk tiket baru.</p>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost !min-h-10">Batal</button>
                        <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button><span data-submit-label>Simpan layanan</span><span class="hidden" data-submit-loading>Menyimpan layanan...</span></button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>
