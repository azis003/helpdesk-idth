@php
    $rowIndex = (string) ($index ?? 0);
    $isTemplate = $rowIndex === '__INDEX__';
    $fieldValue = fn (string $key, mixed $default = null): mixed => $isTemplate ? $default : old("fields.{$rowIndex}.{$key}", $default);
    $fieldTypeValue = $fieldValue('field_type', 'text');
    $visibilityValue = $fieldValue('visibility', 'requester');
    $isChoiceField = in_array($fieldTypeValue, ['select', 'multiselect'], true);
@endphp

<div data-service-field-row class="rounded-xl border border-[#cfe4e8] bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#0f6862]">Field <span data-service-field-number>{{ $isTemplate ? '' : ((int) $rowIndex + 1) }}</span></p>
            <p class="mt-1 text-sm font-extrabold text-[#263a43]">Informasi yang ingin diminta</p>
        </div>
        <button type="button" data-service-field-remove class="inline-flex min-h-9 items-center justify-center rounded-lg border border-[#f2c6cf] px-3 py-2 text-xs font-bold text-[#b42345] transition hover:bg-[#fff1f3] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e94f70]" aria-label="Hapus field ini">
            Hapus field
        </button>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label for="service-field-label-{{ $rowIndex }}" class="ui-field-label">Nama field <span class="text-rose-600">*</span></label>
            <input id="service-field-label-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][label]" value="{{ $fieldValue('label') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Nama aplikasi" data-service-field-label>
            <p class="ui-field-help">Gunakan kalimat singkat yang mudah dipahami pemohon.</p>
            @if (! $isTemplate)
                @error("fields.{$rowIndex}.label")<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
            @endif
        </div>
        <div>
            <label for="service-field-key-{{ $rowIndex }}" class="ui-field-label">Kunci teknis</label>
            <input id="service-field-key-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][key]" value="{{ $fieldValue('key') }}" required readonly class="ui-input mt-2 cursor-not-allowed border-dashed bg-[#f8fbfc] text-[#607681]" placeholder="Dibuat otomatis" data-service-field-key>
            <p class="ui-field-help">Dibuat otomatis dan tidak tampil di formulir pemohon.</p>
            @if (! $isTemplate)
                @error("fields.{$rowIndex}.key")<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
            @endif
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label for="service-field-type-{{ $rowIndex }}" class="ui-field-label">Jenis field <span class="text-rose-600">*</span></label>
            <select id="service-field-type-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][field_type]" required class="ui-select mt-2" data-service-field-type>
                @foreach ($fieldTypes as $type => $typeLabel)
                    <option value="{{ $type }}" @selected($fieldTypeValue === $type)>{{ $typeLabel }}</option>
                @endforeach
            </select>
            <p class="ui-field-help">Pilih cara pemohon mengisi informasi ini.</p>
        </div>
        <div>
            <label for="service-field-visibility-{{ $rowIndex }}" class="ui-field-label">Siapa yang melihat? <span class="text-rose-600">*</span></label>
            <select id="service-field-visibility-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][visibility]" required class="ui-select mt-2">
                @foreach ($visibilities as $visibility => $visibilityLabel)
                    <option value="{{ $visibility }}" @selected($visibilityValue === $visibility)>{{ $visibilityLabel }}</option>
                @endforeach
            </select>
            <p class="ui-field-help">Tentukan apakah jawaban untuk Pemohon, Tim TI, atau keduanya.</p>
        </div>
    </div>

    <div class="mt-4">
        <label for="service-field-help-{{ $rowIndex }}" class="ui-field-label">Petunjuk pengisian</label>
        <input id="service-field-help-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][help_text]" value="{{ $fieldValue('help_text') }}" maxlength="500" class="ui-input mt-2" placeholder="Contoh: Isi nama aplikasi yang terdampak.">
    </div>

    <div class="mt-4 flex min-h-11 items-center rounded-lg border border-[#d8e8e5] bg-[#f8fbfc] px-3">
        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-bold text-[#526f79]">
            <input type="checkbox" name="fields[{{ $rowIndex }}][is_required]" value="1" class="ui-checkbox" @checked((bool) $fieldValue('is_required', false))>
            Wajib diisi oleh pemohon
        </label>
    </div>

    <div class="mt-4 rounded-xl border border-[#e1eaed] bg-[#fbfdfd]">
        <div class="border-b border-[#edf2f4] px-4 py-3">
            <p class="text-xs font-extrabold text-[#35505b]">Pilihan dan aturan tambahan</p>
            <p class="mt-1 text-[0.68rem] leading-5 text-[#78909a]">Pilihan diperlukan untuk jenis field dropdown. Aturan validasi bersifat opsional.</p>
        </div>
        <div class="space-y-4 p-4">
            <div data-service-field-options-panel class="{{ $isChoiceField ? '' : 'hidden' }}">
                <label for="service-field-options-{{ $rowIndex }}" class="ui-field-label">Pilihan dropdown</label>
                <textarea id="service-field-options-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][options_text]" rows="4" class="ui-textarea mt-2" placeholder="repair|Perbaikan&#10;request|Permintaan" data-service-field-options @disabled(! $isChoiceField)>{{ $fieldValue('options_text') }}</textarea>
                <p class="ui-field-help">Satu pilihan per baris dengan format <code class="rounded bg-[#f3f7f8] px-1">nilai|label</code>.</p>
                @if (! $isTemplate)
                    @error("fields.{$rowIndex}.options_text")<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                @endif
            </div>
            <div>
                <label for="service-field-rules-{{ $rowIndex }}" class="ui-field-label">Aturan validasi</label>
                <textarea id="service-field-rules-{{ $rowIndex }}" name="fields[{{ $rowIndex }}][validation_rules_text]" rows="3" class="ui-textarea mt-2" placeholder="string&#10;max:500">{{ $fieldValue('validation_rules_text') }}</textarea>
                <p class="ui-field-help">Satu aturan per baris. Kosongkan jika aturan dasar sudah cukup.</p>
                @if (! $isTemplate)
                    @error("fields.{$rowIndex}.validation_rules_text")<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
                @endif
            </div>
        </div>
    </div>

    <input type="hidden" name="fields[{{ $rowIndex }}][sort_order]" value="{{ $isTemplate ? '' : ($fieldValue('sort_order', (int) $rowIndex + 1)) }}" data-service-field-order>
</div>
