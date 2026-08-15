@php
    $isModal = $isModal ?? true;
    $formId = $formId ?? 'building-create';
    $prefix = $prefix ?? 'building';
    $building = $building ?? null;
    $useOld = old('_location_form') === $formId;
    $nameValue = $useOld ? old('name') : ($building?->name ?? '');

    // Presentasional saja - tidak mengubah logika maupun data.
    $locFormRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $locFormFooter = 'mt-5 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-5 sm:flex-row sm:justify-end';
@endphp

<form method="POST" action="{{ $action }}" data-ui-modal-form class="p-5 sm:p-6 space-y-4">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_location_form" value="{{ $formId }}">

    <div>
        <label for="{{ $prefix }}-name" class="ui-field-label block font-semibold text-sm text-[color:var(--tm-text)]">Nama Gedung <span class="{{ $locFormRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
        <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" required maxlength="150" data-ui-modal-focus class="ui-input mt-1.5 w-full" placeholder="Contoh: Gedung Utama" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
        <p class="mt-1.5 text-xs text-[color:var(--tm-text-muted)]">Gunakan nama yang mudah dikenali oleh pengguna, misalnya Gedung Rektorat atau Lab Komputer.</p>
        @error('name')<x-field-error id="{{ $prefix }}-name-error" data-ui-validation-error :message="$message" />@enderror
    </div>

    <div class="{{ $locFormFooter }} pt-4 border-t border-[color:var(--tm-border-subtle)] mt-6">
        @if ($isModal)
            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
        @endif
        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
            <span data-ui-modal-label>{{ $building ? 'Simpan perubahan' : 'Simpan gedung' }}</span>
            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
        </button>
    </div>
</form>
