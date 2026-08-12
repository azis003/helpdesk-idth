@php($isEditingService = (string) old('_service_edit') === (string) $serviceType->id)
@php($servicePanel = $isEditingService ? old('_service_tab', 'formulir') : 'formulir')
@php
    // Presentasional saja - tidak mengubah data, logika, maupun alur formulir.
    $catModalPanel = 'relative max-h-[calc(100vh-2rem)] w-full max-w-6xl overflow-y-auto rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]';
    $catModalHeader = 'sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6';
    $catIconTile = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $catClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]';
    $catCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)]';
    $catSummary = 'ui-disclosure-summary flex items-center justify-between gap-4 px-4 py-3.5';
    $catSummaryTitle = 'block text-sm font-extrabold text-[color:var(--tm-text)]';
    $catSummaryHint = 'mt-0.5 block text-xs text-[color:var(--tm-text-muted)]';
    $catSummaryMeta = 'text-xs font-bold tabular-nums text-[color:var(--tm-text-faint)]';
    $catCardBody = 'border-t border-[color:var(--tm-border-subtle)]';
    $catMuted = 'text-xs leading-5 text-[color:var(--tm-text-muted)]';
    $catSectionTitle = 'text-sm font-extrabold text-[color:var(--tm-text)]';
    $catSunkenNote = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-4 py-3';
    $catRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $catChoiceTile = 'flex min-h-11 cursor-pointer items-start gap-3 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 py-2.5 transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-400)] has-[:checked]:bg-[color:var(--tm-brand-50)]';
    $catAddPanel = 'mt-4 rounded-[var(--tm-r-md)] border border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] p-4 sm:p-5';
    $catCheckTile = 'inline-flex min-h-10 items-center gap-2 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 text-xs font-bold text-[color:var(--tm-text-secondary)]';
    $catTagRequired = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2 py-1 text-[0.68rem] font-bold text-[color:var(--tm-warning-700)]';
    $catTagOptional = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2 py-1 text-[0.68rem] font-bold text-[color:var(--tm-text-muted)]';
    $catFooterRow = 'mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-[color:var(--tm-border-subtle)] pt-4';
    $catFieldEmptyTitle = 'Belum ada field aktif';
    $catFieldEmptyDescription = 'Belum ada field aktif untuk layanan ini. Tambahkan field pertama di bagian atas.';
    $catSkillEmptyTitle = 'Belum ada keahlian aktif';
    $catSkillEmptyDescription = 'Tambahkan master keahlian terlebih dahulu agar dapat dipetakan ke layanan ini.';
@endphp

<div id="service-edit-modal-{{ $serviceType->id }}" data-ui-modal data-auto-open="{{ $isEditingService ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <button type="button" data-ui-modal-close class="absolute inset-0 cursor-default bg-slate-950/50 backdrop-blur-[3px]" tabindex="-1" aria-label="Tutup dialog"></button>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <section role="dialog" aria-modal="true" aria-labelledby="service-edit-title-{{ $serviceType->id }}" class="{{ $catModalPanel }}">
            <div class="{{ $catModalHeader }}">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="{{ $catIconTile }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h9l3 3v12H6zM9 9h6M9 12.5h6M9 16h4" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-[color:var(--tm-brand-700)]">Konfigurasi layanan</p>
                        <h2 id="service-edit-title-{{ $serviceType->id }}" class="mt-1 text-base font-extrabold tracking-tight text-[color:var(--tm-text)]">{{ $serviceType->code }} · {{ $serviceType->name }}</h2>
                        <p class="mt-1 text-xs text-[color:var(--tm-text-muted)]">Perubahan pada field dibuat sebagai versi baru agar histori tiket tetap aman.</p>
                    </div>
                </div>
                <button type="button" data-ui-modal-close class="{{ $catClose }}" aria-label="Tutup dialog konfigurasi {{ $serviceType->name }}">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.85fr)]">
                <div class="space-y-3">
                    <div class="{{ $catSunkenNote }}">
                        <p class="text-xs font-extrabold text-[color:var(--tm-text)]">Perubahan aman untuk histori</p>
                        <p class="mt-1 {{ $catMuted }}">Versi baru hanya dipakai tiket yang dibuat setelah perubahan diterbitkan. Tiket lama tetap menggunakan snapshot sebelumnya.</p>
                    </div>

                    <details class="{{ $catCard }}" @if ($servicePanel === 'detail') open @endif>
                        <summary class="{{ $catSummary }}">
                            <span><span class="{{ $catSummaryTitle }}">Detail layanan</span><span class="{{ $catSummaryHint }}">Nama, deskripsi, dan kelas tiket</span></span>
                            <span class="{{ $catSummaryMeta }}">Data dasar</span>
                        </summary>
                        <div class="{{ $catCardBody }} p-4 sm:p-5">
                            <form method="POST" action="{{ route('admin.catalog.services.update', $serviceType) }}" data-ui-modal-form class="p-0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                <input type="hidden" name="_service_tab" value="detail">
                                <div class="flex items-start justify-between gap-4">
                                    <div><h3 class="{{ $catSectionTitle }}">Detail layanan</h3><p class="mt-1 {{ $catMuted }}">Kode layanan bersifat canonical dan tidak dapat diubah.</p></div>
                                    <span class="inline-flex items-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-brand-100)] bg-[color:var(--tm-brand-50)] px-2.5 py-1 text-xs font-extrabold tabular-nums text-[color:var(--tm-brand-800)]">{{ $serviceType->code }}</span>
                                </div>
                                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                    <div><label for="service-name-{{ $serviceType->id }}" class="ui-field-label">Nama layanan <span class="{{ $catRequiredMark }}">*</span></label><input id="service-name-{{ $serviceType->id }}" name="name" value="{{ $serviceType->name }}" required class="ui-input mt-2"></div>
                                    <div><label for="service-class-{{ $serviceType->id }}" class="ui-field-label">Kelas nomor <span class="{{ $catRequiredMark }}">*</span></label><select id="service-class-{{ $serviceType->id }}" name="ticket_class" required class="ui-select mt-2"><option value="">Pilih kelas</option>@foreach ($ticketClasses as $ticketClass)<option value="{{ $ticketClass }}" @selected($serviceType->ticket_class === $ticketClass)>{{ $ticketClass }}</option>@endforeach</select></div>
                                </div>
                                <div class="mt-4"><label for="service-description-{{ $serviceType->id }}" class="ui-field-label">Deskripsi</label><textarea id="service-description-{{ $serviceType->id }}" name="description" rows="2" class="ui-textarea mt-2">{{ $serviceType->description }}</textarea></div>
                                <div class="{{ $catFooterRow }}">
                                    <p class="{{ $catMuted }}">Layanan nonaktif tidak tampil pada katalog pengguna.</p>
                                    <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Simpan detail layanan</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                                </div>
                            </form>
                        </div>
                    </details>

                    <details class="{{ $catCard }}" @if ($servicePanel === 'skills') open @endif>
                        <summary class="{{ $catSummary }}">
                            <span><span class="{{ $catSummaryTitle }}">Keahlian penanganan</span><span class="{{ $catSummaryHint }}">Pemetaan untuk saran teknisi Tier 2</span></span>
                            <span class="{{ $catSummaryMeta }}">{{ $serviceType->skills->where('is_active', true)->count() }} aktif</span>
                        </summary>
                        <div class="{{ $catCardBody }}">
                            <section aria-labelledby="service-skills-heading-{{ $serviceType->id }}" class="p-4 sm:p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 id="service-skills-heading-{{ $serviceType->id }}" class="{{ $catSectionTitle }}">Keahlian penanganan</h3>
                                        <p class="mt-1 max-w-2xl {{ $catMuted }}">Pilih keahlian yang relevan untuk layanan ini. Pemetaan ini menjadi dasar saran teknisi Tier 2 saat triase.</p>
                                    </div>
                                    <span class="ui-count tabular-nums">{{ $serviceType->skills->where('is_active', true)->count() }} aktif</span>
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
                                                    <label class="{{ $catChoiceTile }}">
                                                        <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="ui-checkbox mt-0.5" @checked($serviceType->skills->contains('id', $skill->id))>
                                                        <span class="min-w-0"><span class="block text-xs font-extrabold text-[color:var(--tm-text)]">{{ $skill->name }}</span>@if ($skill->description)<span class="mt-0.5 block truncate text-[0.68rem] text-[color:var(--tm-text-muted)]">{{ $skill->description }}</span>@endif</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    @else
                                        <x-empty-state :title="$catSkillEmptyTitle" :description="$catSkillEmptyDescription" />
                                    @endif
                                    <div class="{{ $catFooterRow }}">
                                        <p class="max-w-xl {{ $catMuted }}">Keahlian yang dinonaktifkan tidak digunakan untuk saran baru, tetapi histori tiket dan pemetaan lama tetap tersimpan.</p>
                                        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Simpan keahlian</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                                    </div>
                                </form>
                            </section>
                        </div>
                    </details>

                    <details class="{{ $catCard }}" @if ($servicePanel === 'formulir') open @endif>
                        <summary class="{{ $catSummary }}">
                            <span><span class="{{ $catSummaryTitle }}">Template formulir</span><span class="{{ $catSummaryHint }}">Field aktif dan versi yang sedang digunakan</span></span>
                            <span class="{{ $catSummaryMeta }}">{{ $serviceType->activeFieldDefinitions->count() }} field</span>
                        </summary>
                        <div class="{{ $catCardBody }}">
                            <section aria-labelledby="fields-heading-{{ $serviceType->id }}" class="p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-4"><div><h3 id="fields-heading-{{ $serviceType->id }}" class="{{ $catSectionTitle }}">Field formulir aktif</h3><p class="mt-1 max-w-2xl {{ $catMuted }}">Field aktif dipakai tiket baru. Jika ada perubahan, buat versi baru agar data lama tetap dapat ditelusuri.</p></div><span class="ui-count tabular-nums">{{ $serviceType->activeFieldDefinitions->count() }}</span></div>

                                <form method="POST" action="{{ route('admin.catalog.fields.store', $serviceType) }}" data-ui-modal-form class="{{ $catAddPanel }}">
                                    @csrf
                                    <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                    <input type="hidden" name="_service_tab" value="formulir">
                                    <div><p class="text-sm font-extrabold text-[color:var(--tm-brand-800)]">Tambah field baru</p><p class="mt-1 text-xs leading-5 text-[color:var(--tm-brand-700)]">Gunakan kunci stabil dalam bahasa Inggris; label yang tampil tetap menggunakan Bahasa Indonesia.</p></div>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        <div><label for="new-field-key-{{ $serviceType->id }}" class="ui-field-label">Kunci field <span class="{{ $catRequiredMark }}">*</span></label><input id="new-field-key-{{ $serviceType->id }}" name="key" required class="ui-input mt-2" placeholder="contoh: application_name"></div>
                                        <div><label for="new-field-label-{{ $serviceType->id }}" class="ui-field-label">Label <span class="{{ $catRequiredMark }}">*</span></label><input id="new-field-label-{{ $serviceType->id }}" name="label" required class="ui-input mt-2" placeholder="Nama aplikasi"></div>
                                        <div><label for="new-field-type-{{ $serviceType->id }}" class="ui-field-label">Tipe <span class="{{ $catRequiredMark }}">*</span></label><select id="new-field-type-{{ $serviceType->id }}" name="field_type" required class="ui-select mt-2"><option value="">Pilih tipe</option>@foreach ($fieldTypes as $type => $typeLabel)<option value="{{ $type }}">{{ $typeLabel }}</option>@endforeach</select></div>
                                        <div><label for="new-field-visibility-{{ $serviceType->id }}" class="ui-field-label">Visibilitas <span class="{{ $catRequiredMark }}">*</span></label><select id="new-field-visibility-{{ $serviceType->id }}" name="visibility" required class="ui-select mt-2">@foreach ($visibilities as $visibility => $visibilityLabel)<option value="{{ $visibility }}">{{ $visibilityLabel }}</option>@endforeach</select></div>
                                    </div>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end">
                                        <div><label for="new-field-help-{{ $serviceType->id }}" class="ui-field-label">Petunjuk pengisian</label><input id="new-field-help-{{ $serviceType->id }}" name="help_text" class="ui-input mt-2" placeholder="Petunjuk singkat untuk pemohon"></div>
                                        <label class="{{ $catCheckTile }}"><input type="checkbox" name="is_required" value="1" class="ui-checkbox"> Wajib</label>
                                        <div><label for="new-field-order-{{ $serviceType->id }}" class="ui-field-label">Urutan</label><input id="new-field-order-{{ $serviceType->id }}" name="sort_order" type="number" min="0" value="0" class="ui-input mt-2 w-24 tabular-nums"></div>
                                    </div>
                                    <div class="mt-3 grid gap-3 lg:grid-cols-2"><div><label for="new-field-options-{{ $serviceType->id }}" class="ui-field-label">Pilihan <span class="font-normal text-[color:var(--tm-text-faint)]">(untuk select; satu per baris: nilai|label)</span></label><textarea id="new-field-options-{{ $serviceType->id }}" name="options_text" rows="3" class="ui-textarea mt-2" placeholder="repair|Perbaikan&#10;request|Permintaan"></textarea></div><div><label for="new-field-rules-{{ $serviceType->id }}" class="ui-field-label">Aturan validasi <span class="font-normal text-[color:var(--tm-text-faint)]">(satu per baris)</span></label><textarea id="new-field-rules-{{ $serviceType->id }}" name="validation_rules_text" rows="3" class="ui-textarea mt-2" placeholder="string&#10;max:500"></textarea></div></div>
                                    <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary mt-3 !min-h-9 !text-xs"><span data-ui-modal-label>Tambah field</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button>
                                </form>

                                <div class="mt-4 space-y-2">
                                    @forelse ($serviceType->activeFieldDefinitions as $field)
                                        <details class="{{ $catCard }}">
                                            <summary class="ui-disclosure-summary flex items-center justify-between gap-3 p-4"><span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[color:var(--tm-text)]">{{ $field->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[color:var(--tm-text-muted)]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · versi {{ $field->version }}</span></span><span class="flex shrink-0 items-center gap-2"><span class="ui-chip hidden sm:inline-flex">{{ $visibilities[$field->visibility] ?? $field->visibility }}</span>@if ($field->is_required)<span class="{{ $catTagRequired }}">Wajib</span>@else<span class="{{ $catTagOptional }}">Opsional</span>@endif</span></summary>
                                            <div class="{{ $catCardBody }} bg-[color:var(--tm-sunken)] p-4">
                                                <form method="POST" action="{{ route('admin.catalog.fields.versions.store', $field) }}" data-ui-modal-form class="space-y-3">
                                                    @csrf
                                                    <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                                                    <input type="hidden" name="_service_tab" value="formulir">
                                                    <div class="rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 py-2.5 text-xs leading-5 text-[color:var(--tm-text-secondary)]">Versi aktif saat ini: <span class="font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $field->version }}</span>. Setelah disimpan, versi baru dipakai tiket berikutnya.</div>
                                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><div><label for="field-label-{{ $field->id }}" class="ui-field-label">Label <span class="{{ $catRequiredMark }}">*</span></label><input id="field-label-{{ $field->id }}" name="label" value="{{ $field->label }}" required class="ui-input mt-2"></div><div><label for="field-type-{{ $field->id }}" class="ui-field-label">Tipe <span class="{{ $catRequiredMark }}">*</span></label><select id="field-type-{{ $field->id }}" name="field_type" required class="ui-select mt-2">@foreach ($fieldTypes as $type => $typeLabel)<option value="{{ $type }}" @selected($field->field_type === $type)>{{ $typeLabel }}</option>@endforeach</select></div><div><label for="field-visibility-{{ $field->id }}" class="ui-field-label">Visibilitas <span class="{{ $catRequiredMark }}">*</span></label><select id="field-visibility-{{ $field->id }}" name="visibility" required class="ui-select mt-2">@foreach ($visibilities as $visibility => $visibilityLabel)<option value="{{ $visibility }}" @selected($field->visibility === $visibility)>{{ $visibilityLabel }}</option>@endforeach</select></div><div><label for="field-order-{{ $field->id }}" class="ui-field-label">Urutan</label><input id="field-order-{{ $field->id }}" name="sort_order" type="number" min="0" value="{{ $field->sort_order }}" class="ui-input mt-2 tabular-nums"></div></div>
                                                    <div><label for="field-help-{{ $field->id }}" class="ui-field-label">Petunjuk pengisian</label><input id="field-help-{{ $field->id }}" name="help_text" value="{{ $field->help_text }}" class="ui-input mt-2"></div>
                                                    <div class="grid gap-3 lg:grid-cols-2"><div><label for="field-options-{{ $field->id }}" class="ui-field-label">Pilihan <span class="font-normal text-[color:var(--tm-text-faint)]">(satu per baris: nilai|label)</span></label><textarea id="field-options-{{ $field->id }}" name="options_text" rows="3" class="ui-textarea mt-2">{{ $formatOptions($field) }}</textarea></div><div><label for="field-rules-{{ $field->id }}" class="ui-field-label">Aturan validasi <span class="font-normal text-[color:var(--tm-text-faint)]">(satu per baris)</span></label><textarea id="field-rules-{{ $field->id }}" name="validation_rules_text" rows="3" class="ui-textarea mt-2">{{ $formatRules($field) }}</textarea></div></div>
                                                    <label class="{{ $catCheckTile }}"><input type="checkbox" name="is_required" value="1" class="ui-checkbox" @checked($field->is_required)> Field wajib diisi</label>
                                                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[color:var(--tm-border-subtle)] pt-3"><button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary !min-h-9 !text-xs"><span data-ui-modal-label>Buat versi baru</span><span data-ui-modal-loading class="hidden">Menyimpan...</span></button><div class="flex flex-wrap gap-2">@if ($field->is_active)<button type="submit" form="deactivate-field-{{ $field->id }}" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>@else<button type="submit" form="activate-field-{{ $field->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>@endif</div></div>
                                                </form>
                                                <form id="activate-field-{{ $field->id }}" method="POST" action="{{ route('admin.catalog.fields.status', [$field, 'activate']) }}" class="hidden">@csrf<input type="hidden" name="_service_edit" value="{{ $serviceType->id }}"><input type="hidden" name="_service_tab" value="formulir"></form>
                                                <form id="deactivate-field-{{ $field->id }}" method="POST" action="{{ route('admin.catalog.fields.status', [$field, 'deactivate']) }}" class="hidden" data-swal-confirm="Nonaktifkan field ini? Definisi versi lama tetap tersedia.">@csrf<input type="hidden" name="_service_edit" value="{{ $serviceType->id }}"><input type="hidden" name="_service_tab" value="formulir"></form>
                                            </div>
                                        </details>
                                    @empty
                                        <x-empty-state :title="$catFieldEmptyTitle" :description="$catFieldEmptyDescription" />
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
