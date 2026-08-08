@php
    $isVersion = ($mode ?? 'create') === 'version';
    $fieldTypeValue = old('field_type', $field?->field_type ?? 'text');
    $visibilityValue = old('visibility', $field?->visibility ?? 'requester');
    $optionsText = old('options_text', $field ? $field->options->map(fn ($option) => $option->value.'|'.$option->label)->implode("\n") : '');
    $rulesText = old('validation_rules_text', $field ? collect($field->validation_rules ?? [])->implode("\n") : '');
    $isChoiceField = in_array($fieldTypeValue, ['select', 'multiselect'], true);
@endphp

<form method="POST" action="{{ $isVersion ? route('admin.catalog.fields.versions.store', $field) : route('admin.catalog.fields.store', $serviceType) }}" data-submit-feedback data-field-builder class="space-y-4">
    @csrf
    @if (! empty($keepOpen))
        <input type="hidden" name="_service_edit" value="{{ $serviceType->id }}">
        <input type="hidden" name="_service_tab" value="formulir">
    @endif
    @if (! $isVersion)
        <div>
            <label for="new-field-label-{{ $serviceType->id }}" class="ui-field-label">Nama field <span class="text-rose-600">*</span></label>
            <input id="new-field-label-{{ $serviceType->id }}" name="label" value="{{ old('label') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Nama aplikasi" data-field-label-input>
            <p class="ui-field-help">Tulis pertanyaan yang akan dibaca pemohon. Singkat lebih mudah diisi.</p>
            @error('label')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
        </div>
    @else
        <div>
            <label for="field-label-{{ $field->id }}" class="ui-field-label">Nama field <span class="text-rose-600">*</span></label>
            <input id="field-label-{{ $field->id }}" name="label" value="{{ old('label', $field->label) }}" required maxlength="150" class="ui-input mt-2" data-field-label-input>
            <p class="ui-field-help">Perubahan akan membuat versi baru. Tiket lama tetap memakai versi sebelumnya.</p>
            @error('label')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
        </div>
    @endif

    @if ($isVersion)
        <input type="hidden" name="key" value="{{ $field->key }}">
        <div class="rounded-lg border border-dashed border-[#cbd9df] bg-[#f8fbfc] px-3 py-2.5">
            <p class="text-[0.68rem] font-bold text-[#78909a]">Kunci teknis</p>
            <code class="mt-1 block break-all text-xs font-bold text-[#35505b]">{{ $field->key }}</code>
            <p class="mt-1 text-[0.68rem] leading-5 text-[#8aa0a8]">Tetap sama agar data versi lama dapat ditelusuri.</p>
        </div>
    @else
        <div>
            <label for="new-field-key-{{ $serviceType->id }}" class="ui-field-label">Kunci teknis</label>
            <input id="new-field-key-{{ $serviceType->id }}" name="key" value="{{ old('key') }}" required readonly class="ui-input mt-2 cursor-not-allowed border-dashed bg-[#f8fbfc] text-[#607681]" placeholder="Dibuat otomatis dari nama field" data-field-key-input>
            <p class="ui-field-help">Dibuat otomatis dari nama field. Tidak tampil di formulir pemohon.</p>
            @error('key')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $isVersion ? 'field-type-'.$field->id : 'new-field-type-'.$serviceType->id }}" class="ui-field-label">Jenis jawaban <span class="text-rose-600">*</span></label>
            <select id="{{ $isVersion ? 'field-type-'.$field->id : 'new-field-type-'.$serviceType->id }}" name="field_type" required class="ui-select mt-2" data-field-type-select>
                @foreach ($fieldTypes as $type => $typeLabel)
                    <option value="{{ $type }}" @selected($fieldTypeValue === $type)>{{ $typeLabel }}</option>
                @endforeach
            </select>
            <p class="ui-field-help">Pilih sesuai jawaban yang diharapkan.</p>
            @error('field_type')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="{{ $isVersion ? 'field-visibility-'.$field->id : 'new-field-visibility-'.$serviceType->id }}" class="ui-field-label">Siapa yang melihat? <span class="text-rose-600">*</span></label>
            <select id="{{ $isVersion ? 'field-visibility-'.$field->id : 'new-field-visibility-'.$serviceType->id }}" name="visibility" required class="ui-select mt-2">
                @foreach ($visibilities as $visibility => $visibilityLabel)
                    <option value="{{ $visibility }}" @selected($visibilityValue === $visibility)>{{ $visibilityLabel }}</option>
                @endforeach
            </select>
            <p class="ui-field-help">Tentukan apakah jawaban untuk Pemohon, Tim TI, atau keduanya.</p>
            @error('visibility')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_10rem] sm:items-start sm:gap-x-6">
        <div>
            <label for="{{ $isVersion ? 'field-help-'.$field->id : 'new-field-help-'.$serviceType->id }}" class="ui-field-label">Petunjuk pengisian</label>
            <input id="{{ $isVersion ? 'field-help-'.$field->id : 'new-field-help-'.$serviceType->id }}" name="help_text" value="{{ old('help_text', $field?->help_text) }}" maxlength="500" class="ui-input mt-2" placeholder="Contoh: Isi nama aplikasi yang terdampak.">
            <p class="ui-field-help">Muncul di bawah label untuk membantu pemohon menjawab.</p>
        </div>
        <div>
            <label for="{{ $isVersion ? 'field-order-'.$field->id : 'new-field-order-'.$serviceType->id }}" class="ui-field-label">Urutan</label>
            <input id="{{ $isVersion ? 'field-order-'.$field->id : 'new-field-order-'.$serviceType->id }}" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $field?->sort_order ?? $nextFieldOrder) }}" class="ui-input mt-2 w-full">
            <p class="ui-field-help">Urutan tampil di formulir.</p>
        </div>
    </div>

    <div class="flex min-h-11 items-center rounded-lg border border-[#d8e8e5] bg-[#f8fbfc] px-3">
        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-bold text-[#526f79]">
            <input type="checkbox" name="is_required" value="1" class="ui-checkbox" @checked((bool) old('is_required', $field?->is_required ?? false))>
            Wajib diisi oleh pemohon
        </label>
    </div>

    <div class="rounded-xl border border-[#e1eaed] bg-white">
        <div class="border-b border-[#edf2f4] px-4 py-3">
            <p class="text-xs font-extrabold text-[#35505b]">Pilihan dan validasi</p>
            <p class="mt-1 text-[0.68rem] leading-5 text-[#78909a]">Bagian ini opsional. Gunakan bila field membutuhkan opsi atau aturan khusus.</p>
        </div>
        <div class="space-y-4 p-4">
            <div data-field-options-panel class="{{ $isChoiceField ? '' : 'hidden' }}">
                <label for="{{ $isVersion ? 'field-options-'.$field->id : 'new-field-options-'.$serviceType->id }}" class="ui-field-label">Pilihan jawaban</label>
                <textarea id="{{ $isVersion ? 'field-options-'.$field->id : 'new-field-options-'.$serviceType->id }}" name="options_text" rows="4" class="ui-textarea mt-2" placeholder="repair|Perbaikan&#10;request|Permintaan" data-field-options-input @disabled(! $isChoiceField)>{{ $optionsText }}</textarea>
                <p class="ui-field-help">Satu pilihan per baris dengan format <code class="rounded bg-[#f3f7f8] px-1">nilai|label</code>.</p>
                @error('options_text')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="{{ $isVersion ? 'field-rules-'.$field->id : 'new-field-rules-'.$serviceType->id }}" class="ui-field-label">Aturan validasi</label>
                <textarea id="{{ $isVersion ? 'field-rules-'.$field->id : 'new-field-rules-'.$serviceType->id }}" name="validation_rules_text" rows="3" class="ui-textarea mt-2" placeholder="string&#10;max:500">{{ $rulesText }}</textarea>
                <p class="ui-field-help">Satu aturan per baris. Kosongkan jika aturan dasar sudah cukup.</p>
                @error('validation_rules_text')<p class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#e3ecef] pt-4">
        <p class="max-w-md text-xs leading-5 text-[#78909a]">{{ $isVersion ? 'Versi baru hanya digunakan untuk tiket yang dibuat setelah disimpan.' : 'Field baru langsung menjadi bagian dari formulir aktif setelah disimpan.' }}</p>
        <button type="submit" class="ui-btn ui-btn-primary !min-h-10" data-submit-button>
            <span data-submit-label>{{ $isVersion ? 'Simpan versi baru' : 'Tambah field' }}</span>
            <span class="hidden" data-submit-loading>{{ $isVersion ? 'Menyimpan versi...' : 'Menambahkan field...' }}</span>
        </button>
    </div>
</form>
