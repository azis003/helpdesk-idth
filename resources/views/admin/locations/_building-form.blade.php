@php
    $isModal = $isModal ?? true;
    $formId = $formId ?? 'building-create';
    $prefix = $prefix ?? 'building';
    $building = $building ?? null;
    $useOld = old('_location_form') === $formId;
    $nameValue = $useOld ? old('name') : ($building?->name ?? '');
@endphp

<form method="POST" action="{{ $action }}" data-ui-modal-form class="p-5 sm:p-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_location_form" value="{{ $formId }}">

    <div>
        <label for="{{ $prefix }}-name" class="ui-field-label">Nama gedung <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
        <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" required maxlength="150" data-ui-modal-focus class="ui-input mt-2" placeholder="Contoh: Gedung Utama" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
        @error('name')<p id="{{ $prefix }}-name-error" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
        @if ($isModal)
            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
        @endif
        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
            <span data-ui-modal-label>{{ $building ? 'Simpan perubahan' : 'Simpan gedung' }}</span>
            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
        </button>
    </div>
</form>
