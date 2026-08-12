@php
    $serviceRequestMatches = (string) request()->query('service') === (string) $serviceType->id;
    $servicePanel = (string) old('_service_edit') === (string) $serviceType->id
        ? old('_service_tab', 'detail')
        : ($serviceRequestMatches ? request()->query('service_tab', 'detail') : 'detail');
    $servicePanel = in_array($servicePanel, ['detail', 'skills', 'formulir'], true) ? $servicePanel : 'detail';
    $autoOpen = (string) $autoOpenService === (string) $serviceType->id && ($errors->any() || request()->query('service') !== null);
    $oldServiceMatches = (string) old('_service_edit') === (string) $serviceType->id;
    $serviceName = $oldServiceMatches ? old('name', $serviceType->name) : $serviceType->name;
    $serviceDescription = $oldServiceMatches ? old('description', $serviceType->description) : $serviceType->description;
    $serviceTicketClass = $oldServiceMatches ? old('ticket_class', $serviceType->ticket_class) : $serviceType->ticket_class;
    $activeSlaPolicy = $serviceType->activeSlaPolicy;
    $defaultUsesSla = (bool) ($activeSlaPolicy?->uses_sla ?? false);
    $serviceUsesSla = $oldServiceMatches && old('uses_sla') !== null
        ? filter_var(old('uses_sla'), FILTER_VALIDATE_BOOLEAN)
        : (bool) $defaultUsesSla;
    $serviceTargetWorkingDays = $oldServiceMatches
        ? old('target_working_days', $activeSlaPolicy?->target_working_days)
        : $activeSlaPolicy?->target_working_days;
    $selectedSkillIds = $oldServiceMatches
        ? collect(old('skill_ids', []))->map(fn ($id): int => (int) $id)->all()
        : $serviceType->skills->pluck('id')->map(fn ($id): int => (int) $id)->all();
    $activeFields = $serviceType->activeFieldDefinitions;
    $nextFieldOrder = ((int) ($activeFields->max('sort_order') ?? 0)) + 1;

    // Presentasional saja - tidak mengubah data, logika, maupun alur formulir.
    $svcPanel = 'relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-6xl overflow-y-auto overscroll-contain rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]';
    $svcHeader = 'sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-7';
    $svcIconTile = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $svcClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]';
    $svcCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)]';
    $svcSummary = 'ui-disclosure-summary flex items-center gap-4 px-4 py-3.5';
    $svcSummaryTitle = 'block text-sm font-extrabold text-[color:var(--tm-text)]';
    $svcSummaryHint = 'mt-0.5 block text-xs text-[color:var(--tm-text-muted)]';
    $svcSummaryMeta = 'text-xs font-bold tabular-nums text-[color:var(--tm-text-faint)]';
    $svcCardBody = 'border-t border-[color:var(--tm-border-subtle)] p-4 sm:p-5';
    $svcMuted = 'text-xs leading-5 text-[color:var(--tm-text-muted)]';
    $svcFooterRow = 'mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[color:var(--tm-border-subtle)] pt-5';
    $svcSunkenPanel = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
    $svcChoiceTile = 'flex min-h-11 cursor-pointer items-start gap-3 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 py-2.5 transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:border-[color:var(--tm-brand-300)] has-[:checked]:border-[color:var(--tm-brand-400)] has-[:checked]:bg-[color:var(--tm-brand-50)]';
    $svcRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $svcAddFieldPanel = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] p-4';
    $svcNumTile = 'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-[var(--tm-r-xs)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] text-xs font-extrabold tabular-nums text-[color:var(--tm-text-secondary)]';
    $svcTagRequired = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2 py-1 text-[0.65rem] font-bold text-[color:var(--tm-warning-700)]';
    $svcTagOptional = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2 py-1 text-[0.65rem] font-bold text-[color:var(--tm-text-muted)]';
    $svcFieldsEmptyTitle = 'Belum ada field formulir';
    $svcFieldsEmptyDescription = 'Tambahkan field pertama di bagian atas. Satu field sebaiknya mewakili satu informasi yang jelas.';
@endphp

<div id="service-edit-modal-{{ $serviceType->id }}" data-ui-modal data-auto-open="{{ $autoOpen ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-edit-title-{{ $serviceType->id }}" class="{{ $svcPanel }}">
            <div class="{{ $svcHeader }}">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="{{ $svcIconTile }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h4l9-9a2.12 2.12 0 0 0-3-3l-9 9v3Z" /><path stroke-linecap="round" d="M14 6.5l3 3" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-bold uppercase tracking-[0.14em] text-[color:var(--tm-brand-700)]">Edit layanan</p>
                        <h2 id="service-edit-title-{{ $serviceType->id }}" class="mt-1 text-lg font-extrabold tracking-tight text-[color:var(--tm-text)]">{{ $serviceType->code }} · {{ $serviceType->name }}</h2>
                        <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">Kelola data layanan dan template formulir dari satu tempat.</p>
                    </div>
                </div>
                <button type="button" data-ui-modal-close class="{{ $svcClose }}" aria-label="Tutup edit layanan {{ $serviceType->name }}">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="space-y-4 p-5 sm:p-7">
                <details class="{{ $svcCard }}" @if ($servicePanel === 'detail') open @endif>
                    <summary class="{{ $svcSummary }}">
                        <span class="min-w-0 flex-1"><span class="{{ $svcSummaryTitle }}">Data layanan</span><span class="{{ $svcSummaryHint }}">Kode, jenis, kategori, dan deskripsi</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="ui-status {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></span>
                    </summary>
                    <div class="{{ $svcCardBody }}">
                        <form method="POST" action="{{ route('admin.catalog.services.update', $serviceType) }}" data-submit-feedback data-service-sla-form>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                            <input type="hidden" name="_service_tab" value="detail">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="edit-service-code-{{ $serviceType->id }}" class="ui-field-label">Kode layanan</label>
                                    <input id="edit-service-code-{{ $serviceType->id }}" value="{{ $serviceType->code }}" readonly class="ui-input mt-2 cursor-not-allowed border-dashed bg-[color:var(--tm-sunken)] tabular-nums text-[color:var(--tm-text-muted)]">
                                    <p class="ui-field-help">Kode adalah identitas sistem dan tidak diubah setelah layanan digunakan.</p>
                                </div>
                                <div>
                                    <label for="edit-service-name-{{ $serviceType->id }}" class="ui-field-label">Jenis layanan <span class="{{ $svcRequiredMark }}">*</span></label>
                                    <input id="edit-service-name-{{ $serviceType->id }}" name="name" value="{{ $serviceName }}" required maxlength="150" class="ui-input mt-2">
                                    @error('name')<x-field-error :message="$message" />@enderror
                                </div>
                                <div>
                                    <label for="edit-service-category-{{ $serviceType->id }}" class="ui-field-label">Kategori layanan <span class="{{ $svcRequiredMark }}">*</span></label>
                                    <select id="edit-service-category-{{ $serviceType->id }}" name="ticket_class" required class="ui-select mt-2">
                                        @foreach ($ticketClasses as $ticketClass)
                                            <option value="{{ $ticketClass }}" @selected($serviceTicketClass === $ticketClass)>{{ $ticketClass }}</option>
                                        @endforeach
                                    </select>
                                    @error('ticket_class')<x-field-error :message="$message" />@enderror
                                </div>
                                <div class="{{ $svcSunkenPanel }} sm:col-span-2" data-service-sla-panel>
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <input type="hidden" name="uses_sla" value="0">
                                            <input id="edit-service-uses-sla-{{ $serviceType->id }}" name="uses_sla" value="1" type="checkbox" class="ui-checkbox mt-0.5" @checked($serviceUsesSla) data-service-sla-toggle>
                                            <div>
                                                <label for="edit-service-uses-sla-{{ $serviceType->id }}" class="ui-field-label">Gunakan target SLA</label>
                                                <p class="mt-1 {{ $svcMuted }}" data-service-sla-status>Target penyelesaian dihitung dalam hari kerja dan tersimpan sebagai versi kebijakan.</p>
                                            </div>
                                        </div>
                                        <div class="w-full sm:max-w-xs" data-service-sla-target>
                                            <label for="edit-service-target-sla-{{ $serviceType->id }}" class="ui-field-label">Target SLA (hari kerja)</label>
                                            <input id="edit-service-target-sla-{{ $serviceType->id }}" name="target_working_days" type="number" min="1" max="365" value="{{ $serviceTargetWorkingDays }}" class="ui-input mt-2 tabular-nums" data-service-sla-target-input @disabled(! $serviceUsesSla)>
                                            <p class="ui-field-help">Minimal 1 dan maksimal 365 hari kerja.</p>
                                            @error('target_working_days')<x-field-error :message="$message" />@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="edit-service-description-{{ $serviceType->id }}" class="ui-field-label">Deskripsi layanan</label>
                                    <textarea id="edit-service-description-{{ $serviceType->id }}" name="description" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Jelaskan layanan, tujuan, dan cakupan permintaan yang dapat diajukan pemohon.">{{ $serviceDescription }}</textarea>
                                    <p class="ui-field-help">Deskripsi ini tampil sebagai penjelasan layanan di katalog pemohon dan membantu Tim TI memahami cakupannya.</p>
                                </div>
                            </div>
                            <div class="{{ $svcFooterRow }}">
                                <p class="max-w-xl {{ $svcMuted }}">Perubahan data layanan hanya berlaku untuk katalog dan tiket baru.</p>
                                <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button><span data-submit-label>Simpan data layanan</span><span class="hidden" data-submit-loading>Menyimpan...</span></button>
                            </div>
                        </form>
                    </div>
                </details>

                <details class="{{ $svcCard }}" @if ($servicePanel === 'skills') open @endif>
                    <summary class="{{ $svcSummary }}">
                        <span class="min-w-0 flex-1"><span class="{{ $svcSummaryTitle }}">Syarat keahlian</span><span class="{{ $svcSummaryHint }}">Dipakai sebagai dasar saran teknisi</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="ui-count tabular-nums">{{ $serviceType->skills->count() }} dipilih</span></span>
                    </summary>
                    <div class="{{ $svcCardBody }}">
                        <form method="POST" action="{{ route('admin.catalog.services.skills.update', $serviceType) }}" data-submit-feedback>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
                            <input type="hidden" name="_service_tab" value="skills">
                            <p class="{{ $svcMuted }}">Pilih satu atau lebih keahlian dari master Keahlian. Pemetaan ini membantu sistem memberi saran, bukan menetapkan teknisi secara otomatis.</p>
                            <details class="mt-3 {{ $svcCard }}" data-skill-picker>
                                <summary class="ui-disclosure-summary flex min-h-12 items-center gap-3 px-4 py-3">
                                    <span class="min-w-0 flex-1"><span class="block text-xs font-extrabold text-[color:var(--tm-text)]">Pilih keahlian</span><span class="mt-0.5 block truncate text-[0.68rem] text-[color:var(--tm-text-muted)]" data-skill-picker-summary>{{ $selectedSkillIds === [] ? 'Belum ada keahlian dipilih' : count($selectedSkillIds).' keahlian dipilih' }}</span></span>
                                    <span class="flex shrink-0 items-center text-xs font-bold text-[color:var(--tm-brand-700)]" aria-hidden="true">Buka</span>
                                </summary>
                                <fieldset class="grid gap-2 border-t border-[color:var(--tm-border-subtle)] p-3 sm:grid-cols-2">
                                    <legend class="sr-only">Daftar syarat keahlian</legend>
                                    @forelse ($skills as $skill)
                                        <label class="{{ $svcChoiceTile }}">
                                            <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="ui-checkbox mt-0.5" @checked(in_array($skill->id, $selectedSkillIds, true))>
                                            <span class="min-w-0"><span class="block text-xs font-extrabold text-[color:var(--tm-text)]">{{ $skill->name }}</span>@if ($skill->description)<span class="mt-0.5 block text-[0.68rem] leading-4 text-[color:var(--tm-text-muted)]">{{ $skill->description }}</span>@endif</span>
                                        </label>
                                    @empty
                                        <p class="{{ $svcMuted }} sm:col-span-2">Belum ada keahlian aktif. Tambahkan master keahlian terlebih dahulu.</p>
                                    @endforelse
                                </fieldset>
                            </details>
                            @error('skill_ids')<x-field-error :message="$message" />@enderror
                            <div class="{{ $svcFooterRow }}">
                                <p class="max-w-xl {{ $svcMuted }}">Perubahan pemetaan tidak mengubah histori tiket.</p>
                                <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button><span data-submit-label>Simpan syarat keahlian</span><span class="hidden" data-submit-loading>Menyimpan...</span></button>
                            </div>
                        </form>
                    </div>
                </details>

                <details class="{{ $svcCard }}" @if ($servicePanel === 'formulir') open @endif>
                    <summary class="{{ $svcSummary }}">
                        <span class="min-w-0 flex-1"><span class="{{ $svcSummaryTitle }}">Template formulir</span><span class="{{ $svcSummaryHint }}">Tambah, ubah versi, atau hapus field dari formulir</span></span>
                        <span class="flex shrink-0 items-center gap-3"><span class="ui-count tabular-nums">{{ $activeFields->count() }} field aktif</span></span>
                    </summary>
                    <div class="{{ $svcCardBody }}">
                        <div class="{{ $svcAddFieldPanel }}">
                            <p class="text-sm font-extrabold text-[color:var(--tm-brand-800)]">Tambah field baru</p>
                            <p class="mt-1 text-xs leading-5 text-[color:var(--tm-brand-700)]">Field baru langsung dipakai pada formulir tiket baru setelah disimpan.</p>
                            <div class="mt-4">
                                @include('admin.forms._field-form', ['mode' => 'create', 'field' => null, 'serviceType' => $serviceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder, 'keepOpen' => true])
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-extrabold text-[color:var(--tm-text)]">Field formulir aktif</h3>
                                <p class="mt-1 {{ $svcMuted }}">Buka satu field untuk mengubah detailnya. Hapus field jika sudah tidak diperlukan.</p>
                            </div>
                            <span class="ui-count tabular-nums">{{ $activeFields->count() }}</span>
                        </div>

                        <div class="mt-3 space-y-2">
                            @forelse ($activeFields as $field)
                                <details class="{{ $svcCard }}" @if ($loop->first && $errors->any()) open @endif>
                                    <summary class="ui-disclosure-summary flex items-start gap-3 p-4">
                                        <span class="flex min-w-0 flex-1 items-start gap-3">
                                            <span class="{{ $svcNumTile }}">{{ $loop->iteration }}</span>
                                            <span class="min-w-0"><span class="block truncate text-sm font-extrabold text-[color:var(--tm-text)]">{{ $field->label }}</span><span class="mt-1 block truncate text-[0.68rem] text-[color:var(--tm-text-muted)]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · versi {{ $field->version }}</span></span>
                                        </span>
                                        <span class="flex shrink-0 flex-wrap justify-end gap-1.5">
                                            <span class="ui-chip hidden sm:inline-flex">{{ $visibilities[$field->visibility] ?? $field->visibility }}</span>
                                            <span class="{{ $field->is_required ? $svcTagRequired : $svcTagOptional }}">{{ $field->is_required ? 'Wajib' : 'Opsional' }}</span>
                                        </span>
                                    </summary>
                                    <div class="border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4 sm:p-5">
                                        <div class="mb-4 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-3 py-2.5 text-xs leading-5 text-[color:var(--tm-text-secondary)]">Simpan perubahan untuk membuat versi berikutnya. Versi lama tetap tersedia untuk histori tiket.</div>
                                        @include('admin.forms._field-form', ['mode' => 'version', 'field' => $field, 'serviceType' => $serviceType, 'fieldTypes' => $fieldTypes, 'visibilities' => $visibilities, 'nextFieldOrder' => $nextFieldOrder, 'keepOpen' => true])
                                        <div class="mt-4 flex justify-end border-t border-[color:var(--tm-border-subtle)] pt-4">
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
                                <x-empty-state :title="$svcFieldsEmptyTitle" :description="$svcFieldsEmptyDescription" />
                            @endforelse
                        </div>
                    </div>
                </details>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-4 py-3.5">
                    <div>
                        <p class="text-xs font-extrabold text-[color:var(--tm-text)]">Status layanan</p>
                        <p class="mt-1 text-[0.68rem] leading-5 text-[color:var(--tm-text-muted)]">Layanan nonaktif tidak muncul di katalog pemohon.</p>
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
