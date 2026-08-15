@php
    $isModal = $isModal ?? true;
    $formId = $formId ?? 'floor-create';
    $prefix = $prefix ?? 'floor';
    $floor = $floor ?? null;
    $building = $building ?? $floor?->building;
    $useOld = old('_location_form') === $formId;
    $nameValue = $useOld ? old('name') : ($floor?->name ?? '');
    $sortOrderValue = $useOld ? old('sort_order', 0) : ($floor?->sort_order ?? 0);

    // Presentasional saja - tidak mengubah logika maupun data.
    $locFormRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $locFormNote = 'mb-4 flex items-center gap-2 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5 text-sm text-[color:var(--tm-text-muted)]';
    $locFormFooter = 'mt-5 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-5 sm:flex-row sm:justify-end';
@endphp

<form method="POST" action="{{ $action }}" data-ui-modal-form class="p-5 sm:p-6 space-y-4">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_location_form" value="{{ $formId }}">

    @if ($building)
        <div class="flex items-center gap-2.5 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-3 py-2.5 text-xs text-[color:var(--tm-text-secondary)]">
            <svg class="h-4 w-4 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20V5.5A1.5 1.5 0 0 1 6.5 4h7A1.5 1.5 0 0 1 15 5.5V20M15 10h2.5A1.5 1.5 0 0 1 19 11.5V20M3.5 20h17" /><path stroke-linecap="round" d="M8 8h4M8 12h4M8 16h4" /></svg>
            <span class="min-w-0">Lokasi Gedung: <span class="font-bold text-[color:var(--tm-text)]">{{ $building->name }}</span></span>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_8rem]">
        <div>
            <label for="{{ $prefix }}-name" class="ui-field-label block font-semibold text-sm text-[color:var(--tm-text)]">Nama Lantai <span class="{{ $locFormRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" required maxlength="100" data-ui-modal-focus class="ui-input mt-1.5 w-full" placeholder="Contoh: Lantai 1" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
            @error('name')<x-field-error id="{{ $prefix }}-name-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-order" class="ui-field-label block font-semibold text-sm text-[color:var(--tm-text)]">Urutan</label>
            <input id="{{ $prefix }}-order" name="sort_order" type="number" min="0" max="999" value="{{ $sortOrderValue }}" class="ui-input mt-1.5 w-full tabular-nums" @error('sort_order') aria-invalid="true" aria-describedby="{{ $prefix }}-order-error" @enderror>
            @error('sort_order')<x-field-error id="{{ $prefix }}-order-error" data-ui-validation-error :message="$message" />@enderror
        </div>
    </div>

    <p class="text-xs text-[color:var(--tm-text-muted)] mt-1">Urutan menentukan posisi tampil lantai pada menu (angka lebih kecil akan ditampilkan lebih dahulu).</p>

    <div class="{{ $locFormFooter }} pt-4 border-t border-[color:var(--tm-border-subtle)] mt-6">
        @if ($isModal)
            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
        @endif
        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
            <span data-ui-modal-label>{{ $floor ? 'Simpan perubahan' : 'Simpan lantai' }}</span>
            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
        </button>
    </div>
</form>
