@php
    $isModal = $isModal ?? true;
    $formId = $formId ?? 'floor-create';
    $prefix = $prefix ?? 'floor';
    $floor = $floor ?? null;
    $building = $building ?? $floor?->building;
    $useOld = old('_location_form') === $formId;
    $nameValue = $useOld ? old('name') : ($floor?->name ?? '');
    $sortOrderValue = $useOld ? old('sort_order', 0) : ($floor?->sort_order ?? 0);
@endphp

<form method="POST" action="{{ $action }}" data-ui-modal-form class="p-5 sm:p-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_location_form" value="{{ $formId }}">

    @if ($building)
        <p class="mb-4 rounded-lg bg-[#f8fafb] px-3 py-2 text-sm text-[#607681]">Gedung: <span class="font-bold text-[#17313c]">{{ $building->name }}</span></p>
    @endif

    <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_8rem]">
        <div>
            <label for="{{ $prefix }}-name" class="ui-field-label">Nama lantai <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" required maxlength="100" data-ui-modal-focus class="ui-input mt-2" placeholder="Contoh: Lantai 1" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
            @error('name')<p id="{{ $prefix }}-name-error" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-order" class="ui-field-label">Urutan</label>
            <input id="{{ $prefix }}-order" name="sort_order" type="number" min="0" max="999" value="{{ $sortOrderValue }}" class="ui-input mt-2" @error('sort_order') aria-invalid="true" aria-describedby="{{ $prefix }}-order-error" @enderror>
            @error('sort_order')<p id="{{ $prefix }}-order-error" data-ui-validation-error class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
        @if ($isModal)
            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
        @endif
        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
            <span data-ui-modal-label>{{ $floor ? 'Simpan perubahan' : 'Simpan lantai' }}</span>
            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
        </button>
    </div>
</form>
