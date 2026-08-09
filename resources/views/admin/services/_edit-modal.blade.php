@php
    $servicePanel = (string) old('_service_edit') === (string) $serviceType->id
        ? old('_service_tab', 'detail')
        : 'detail';
    $autoOpen = (string) $autoOpenService === (string) $serviceType->id && ($errors->any() || request()->query('service') !== null);
    $oldServiceMatches = (string) old('_service_edit') === (string) $serviceType->id;
    $serviceName = $oldServiceMatches ? old('name', $serviceType->name) : $serviceType->name;
    $serviceDescription = $oldServiceMatches ? old('description', $serviceType->description) : $serviceType->description;
    $serviceTicketClass = $oldServiceMatches ? old('ticket_class', $serviceType->ticket_class) : $serviceType->ticket_class;
    $activeSlaPolicy = $serviceType->activeSlaPolicy;
    $defaultUsesSla = $activeSlaPolicy?->uses_sla ?? $serviceType->code !== 'SVC-07';
    $serviceUsesSla = $oldServiceMatches && old('uses_sla') !== null
        ? filter_var(old('uses_sla'), FILTER_VALIDATE_BOOLEAN)
        : (bool) $defaultUsesSla;
    $serviceUsesSla = $serviceType->code === 'SVC-07' ? false : $serviceUsesSla;
    $serviceTargetWorkingDays = $oldServiceMatches
        ? old('target_working_days', $activeSlaPolicy?->target_working_days)
        : $activeSlaPolicy?->target_working_days;
    $selectedSkillIds = $oldServiceMatches
        ? collect(old('skill_ids', []))->map(fn ($id): int => (int) $id)->all()
        : $serviceType->skills->pluck('id')->map(fn ($id): int => (int) $id)->all();
    $activeFields = $serviceType->activeFieldDefinitions;
    $nextFieldOrder = ((int) ($activeFields->max('sort_order') ?? 0)) + 1;
@endphp

<div id="service-edit-modal-{{ $serviceType->id }}" data-ui-modal data-auto-open="{{ $autoOpen ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-edit-title-{{ $serviceType->id }}" class="relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-6xl overflow-y-auto overscroll-contain rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-7">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-blue-100">Edit layanan</p>
                    <h2 id="service-edit-title-{{ $serviceType->id }}" class="mt-1 text-xl font-extrabold">{{ $serviceType->code }} · {{ $serviceType->name }}</h2>
                    <p class="mt-1 text-xs leading-5 text-blue-100">Kelola data layanan dan template formulir dari satu tempat.</p>
                </div>
                <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup edit layanan {{ $serviceType->name }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="space-y-4 p-5 sm:p-7">
                <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'detail') open @endif>
                    <summary class="ui-disclosure-summary flex items-center gap-4 px-4 py-3.5">
                        <span class="min-w-0 flex-1"><span class="block text-sm font-extrabold text-[#263a43]">Data layanan</span><span class="mt-0.5 block text-xs text-[#78909a]">Kode, jenis, kategori, dan deskripsi</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="text-xs font-bold text-[#8aa0a8]">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></span>
                    </summary>
                    <div class="border-t border-[#edf2f4] p-4 sm:p-5">
                        <form method="POST" action="{{ route('admin.catalog.services.update', $serviceType) }}" data-submit-feedback data-service-sla-form>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                            <input type="hidden" name="_service_tab" value="detail">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="edit-service-code-{{ $serviceType->id }}" class="ui-field-label">Kode layanan</label>
                                    <input id="edit-service-code-{{ $serviceType->id }}" value="{{ $serviceType->code }}" readonly class="ui-input mt-2 cursor-not-allowed border-dashed bg-[#f8fbfc] text-[#607681]" data-service-sla-code>
                                    <p class="ui-field-help">Kode adalah identitas sistem dan tidak diubah setelah layanan digunakan.</p>
                                </div>
                                <div>
                                    <label for="edit-service-name-{{ $serviceType->id }}" class="ui-field-label">Jenis layanan <span class="text-rose-600">*</span></label>
                                    <input id="edit-service-name-{{ $serviceType->id }}" name="name" value="{{ $serviceName }}" required maxlength="150" class="ui-input mt-2">
                                    @error('name')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    @if ($serviceType->code === 'SVC-05')
                                        <p class="ui-field-label">Kategori layanan</p>
                                        <p class="mt-2 rounded-lg border border-[#f2dfab] bg-[#fffaf0] p-3 text-xs leading-5 text-[#7a5a12]">INC untuk Perbaikan dan REQ untuk Permintaan hardware.</p>
                                    @else
                                        <label for="edit-service-category-{{ $serviceType->id }}" class="ui-field-label">Kategori layanan <span class="text-rose-600">*</span></label>
                                        <select id="edit-service-category-{{ $serviceType->id }}" name="ticket_class" required class="ui-select mt-2">
                                            @foreach ($ticketClasses as $ticketClass)
                                                <option value="{{ $ticketClass }}" @selected($serviceTicketClass === $ticketClass)>{{ $ticketClass }}</option>
                                            @endforeach
                                        </select>
                                        @error('ticket_class')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                                    @endif
                                </div>
                                <div class="rounded-xl border border-[#cfe4e8] bg-[#f8fbfc] p-4 sm:col-span-2" data-service-sla-panel>
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <input type="hidden" name="uses_sla" value="0">
                                            <input id="edit-service-uses-sla-{{ $serviceType->id }}" name="uses_sla" value="1" type="checkbox" class="ui-checkbox mt-0.5" @checked($serviceUsesSla) data-service-sla-toggle @disabled($serviceType->code === 'SVC-07')>
                                            <div>
                                                <label for="edit-service-uses-sla-{{ $serviceType->id }}" class="ui-field-label">Gunakan target SLA</label>
                                                <p class="mt-1 text-xs leading-5 text-[#78909a]" data-service-sla-status>{{ $serviceType->code === 'SVC-07' ? 'Layanan ini menggunakan mekanisme usulan dan tidak memiliki target SLA tetap.' : 'Target penyelesaian dihitung dalam hari kerja dan tersimpan sebagai versi kebijakan.' }}</p>
                                            </div>
                                        </div>
                                        <div class="w-full sm:max-w-xs" data-service-sla-target>
                                            <label for="edit-service-target-sla-{{ $serviceType->id }}" class="ui-field-label">Target SLA (hari kerja)</label>
                                            <input id="edit-service-target-sla-{{ $serviceType->id }}" name="target_working_days" type="number" min="1" max="365" value="{{ $serviceTargetWorkingDays }}" class="ui-input mt-2" data-service-sla-target-input @disabled(! $serviceUsesSla || $serviceType->code === 'SVC-07')>
                                            <p class="ui-field-help">Minimal 1 dan maksimal 365 hari kerja.</p>
                                            @error('target_working_days')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="edit-service-description-{{ $serviceType->id }}" class="ui-field-label">Deskripsi layanan</label>
                                    <textarea id="edit-service-description-{{ $serviceType->id }}" name="description" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Jelaskan layanan, tujuan, dan cakupan permintaan yang dapat diajukan pemohon.">{{ $serviceDescription }}</textarea>
                                    <p class="ui-field-help">Deskripsi ini tampil sebagai penjelasan layanan di katalog pemohon dan membantu Tim TI memahami cakupannya.</p>
                                </div>
                            </div>
                            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] pt-4">
                                <p class="max-w-xl text-xs leading-5 text-[#78909a]">Perubahan data layanan hanya berlaku untuk katalog dan tiket baru.</p>
                                <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button><span data-submit-label>Simpan data layanan</span><span class="hidden" data-submit-loading>Menyimpan...</span></button>
                            </div>
                        </form>
                    </div>
                </details>

                <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'skills') open @endif>
                    <summary class="ui-disclosure-summary flex items-center gap-4 px-4 py-3.5">
                        <span class="min-w-0 flex-1"><span class="block text-sm font-extrabold text-[#263a43]">Syarat keahlian</span><span class="mt-0.5 block text-xs text-[#78909a]">Dipakai sebagai dasar saran teknisi</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="text-xs font-bold text-[#8aa0a8]">{{ $serviceType->skills->count() }} dipilih</span></span>
                    </summary>
                    <div class="border-t border-[#edf2f4] p-4 sm:p-5">
                        <form method="POST" action="{{ route('admin.catalog.services.skills.update', $serviceType) }}" data-submit-feedback>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                            <input type="hidden" name="_service_tab" value="skills">
                            <p class="text-xs leading-5 text-[#78909a]">Pilih satu atau lebih keahlian dari master Keahlian. Pemetaan ini membantu sistem memberi saran, bukan menetapkan teknisi secara otomatis.</p>
                            <details class="mt-3 rounded-xl border border-[#dfe8ec] bg-white" data-skill-picker>
                                <summary class="ui-disclosure-summary flex min-h-12 items-center gap-3 px-4 py-3">
                                    <span class="min-w-0 flex-1"><span class="block text-xs font-extrabold text-[#35505b]">Pilih keahlian</span><span class="mt-0.5 block truncate text-[0.68rem] text-[#78909a]" data-skill-picker-summary>{{ $selectedSkillIds === [] ? 'Belum ada keahlian dipilih' : count($selectedSkillIds).' keahlian dipilih' }}</span></span>
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
                            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] pt-4">
                                <p class="max-w-xl text-xs leading-5 text-[#78909a]">Perubahan pemetaan tidak mengubah histori tiket.</p>
                                <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button><span data-submit-label>Simpan syarat keahlian</span><span class="hidden" data-submit-loading>Menyimpan...</span></button>
                            </div>
                        </form>
                    </div>
                </details>

                <details class="rounded-xl border border-[#dce9ed] bg-white" @if ($servicePanel === 'formulir') open @endif>
                    <summary class="ui-disclosure-summary flex items-center gap-4 px-4 py-3.5">
                        <span class="min-w-0 flex-1"><span class="block text-sm font-extrabold text-[#263a43]">Template formulir</span><span class="mt-0.5 block text-xs text-[#78909a]">Tambah, ubah versi, atau hapus field dari formulir</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="text-xs font-bold text-[#8aa0a8]">{{ $activeFields->count() }} field aktif</span></span>
                    </summary>
                    <div class="border-t border-[#edf2f4] p-4 sm:p-5">
                        <div class="rounded-xl border border-[#b9e8e1] bg-[#ecfbf8] p-4">
                            <p class="text-sm font-extrabold text-[#17313c]">Tambah field baru</p>
                            <p class="mt-1 text-xs leading-5 text-[#52747b]">Field baru langsung dipakai pada formulir tiket baru setelah disimpan.</p>
                            <div class="mt-4">
                                @include('admin.forms._field-form', ['mode' => 'create', 'field' => null, 'serviceType' => $serviceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder, 'keepOpen' => true])
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-extrabold text-[#263a43]">Field formulir aktif</h3>
                                <p class="mt-1 text-xs leading-5 text-[#78909a]">Buka satu field untuk mengubah detailnya. Hapus field jika sudah tidak diperlukan.</p>
                            </div>
                            <span class="rounded-full bg-[#eef3ff] px-2.5 py-1 text-xs font-extrabold text-[#4f63a6]">{{ $activeFields->count() }}</span>
                        </div>

                        <div class="mt-3 space-y-2">
                            @forelse ($activeFields as $field)
                                <details class="rounded-xl border border-[#dfe8ec] bg-white" @if ($loop->first && $errors->any()) open @endif>
                                    <summary class="ui-disclosure-summary flex items-start gap-3 p-4">
                                        <span class="flex min-w-0 flex-1 items-start gap-3">
                                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#f1f7f9] text-xs font-extrabold text-[#526f79]">{{ $loop->iteration }}</span>
                                            <span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[#35505b]">{{ $field->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[#78909a]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · versi {{ $field->version }}</span></span>
                                        </span>
                                        <span class="flex shrink-0 flex-wrap justify-end gap-1.5">
                                            <span class="hidden rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.65rem] font-bold text-[#607681] sm:inline-flex">{{ $visibilities[$field->visibility] ?? $field->visibility }}</span>
                                            <span class="rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $field->is_required ? 'bg-[#fff6df] text-[#956b16]' : 'bg-[#f3f7f8] text-[#78909a]' }}">{{ $field->is_required ? 'Wajib' : 'Opsional' }}</span>
                                        </span>
                                    </summary>
                                    <div class="border-t border-[#edf2f4] bg-[#fcfdfd] p-4 sm:p-5">
                                        <div class="mb-4 rounded-lg bg-[#f8fbfc] px-3 py-2.5 text-xs leading-5 text-[#607681]">Simpan perubahan untuk membuat versi berikutnya. Versi lama tetap tersedia untuk histori tiket.</div>
                                        @include('admin.forms._field-form', ['mode' => 'version', 'field' => $field, 'serviceType' => $serviceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder, 'keepOpen' => true])
                                        <div class="mt-4 flex justify-end border-t border-[#e3ecef] pt-4">
                                            <form method="POST" action="{{ route('admin.catalog.fields.destroy', $field) }}" data-swal-confirm="Hapus {{ $field->label }} dari formulir? Field tidak akan tampil pada tiket baru, tetapi versi lamanya tetap tersimpan untuk histori tiket." data-submit-feedback>
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                                <input type="hidden" name="_service_tab" value="formulir">
                                                <button type="submit" class="ui-btn ui-btn-warning !min-h-9 !text-xs" data-submit-button><span data-submit-label>Hapus dari formulir</span><span class="hidden" data-submit-loading>Menghapus...</span></button>
                                            </form>
                                        </div>
                                    </div>
                                </details>
                            @empty
                                <div class="ui-empty">
                                    <p class="font-extrabold text-[#526f79]">Belum ada field formulir</p>
                                    <p class="mt-1 text-xs leading-5">Tambahkan field pertama di bagian atas. Satu field sebaiknya mewakili satu informasi yang jelas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </details>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#dfe8ec] bg-[#f8fbfc] px-4 py-3">
                    <div>
                        <p class="text-xs font-extrabold text-[#526f79]">Status layanan</p>
                        <p class="mt-1 text-[0.68rem] leading-5 text-[#78909a]">Layanan nonaktif tidak muncul di katalog pemohon.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.catalog.services.status', [$serviceType, $serviceType->is_active ? 'deactivate' : 'activate']) }}" @if ($serviceType->is_active) data-swal-confirm="Nonaktifkan layanan {{ $serviceType->name }}? Layanan tidak akan muncul bagi pemohon, tetapi histori tiket tetap tersedia." @endif data-submit-feedback>
                        @csrf
                        <button type="submit" class="ui-btn {{ $serviceType->is_active ? 'ui-btn-warning' : 'ui-btn-primary' }} !min-h-9 !text-xs" data-submit-button><span data-submit-label>{{ $serviceType->is_active ? 'Nonaktifkan layanan' : 'Aktifkan layanan' }}</span><span class="hidden" data-submit-loading>{{ $serviceType->is_active ? 'Menonaktifkan...' : 'Mengaktifkan...' }}</span></button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
