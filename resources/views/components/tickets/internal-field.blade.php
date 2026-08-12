@props(['field', 'value' => null])

@php
    $fieldId = 'ticket-internal-field-'.$field->key;
    $fieldName = 'internal_fields['.$field->key.']';
    $versionName = 'internal_field_versions['.$field->key.']';
    $errorKey = 'internal_fields.'.$field->key;
    $oldValue = old($errorKey, $value?->value);
    $hasError = $errors->has($errorKey);
    $describedBy = $fieldId.'-help'.($hasError ? ' '.$fieldId.'-error' : '');
    $checked = filter_var($oldValue, FILTER_VALIDATE_BOOLEAN);
@endphp

<div data-ticket-internal-field class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-4 sm:p-5">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <label for="{{ $fieldId }}" class="ui-field-label">
            {{ $field->label }}
            @if ($field->is_required)
                <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span>
                <span class="sr-only">wajib</span>
            @endif
        </label>
        <span class="inline-flex items-center gap-1 rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-100)] px-2 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.08em] text-[color:var(--tm-warning-700)]">
            <svg class="h-3 w-3 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4.75" y="8.75" width="10.5" height="7" rx="1.5" /><path d="M7.25 8.75V6.5a2.75 2.75 0 0 1 5.5 0v2.25" /></svg>
            Definisi v{{ $field->version }}
        </span>
    </div>
    @if ($field->help_text)
        <p id="{{ $fieldId }}-help" class="ui-field-help">{{ $field->help_text }}</p>
    @else
        <p id="{{ $fieldId }}-help" class="ui-field-help">Hanya terlihat oleh petugas berwenang Tim TI.</p>
    @endif

    @if ($field->field_type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="4" data-ticket-internal-field-input class="ui-textarea mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">{{ is_scalar($oldValue) ? $oldValue : '' }}</textarea>
    @elseif ($field->field_type === 'select')
        <select id="{{ $fieldId }}" name="{{ $fieldName }}" data-ticket-internal-field-input class="ui-select mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            <option value="">Pilih {{ strtolower($field->label) }}</option>
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected((string) $oldValue === (string) $option->value)>{{ $option->label }}</option>
            @endforeach
        </select>
    @elseif ($field->field_type === 'multiselect')
        @php($selectedValues = is_array($oldValue) ? $oldValue : [])
        <select id="{{ $fieldId }}" name="{{ $fieldName }}[]" data-ticket-internal-field-input class="ui-select mt-2 min-h-28" multiple @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected(in_array($option->value, $selectedValues, true))>{{ $option->label }}</option>
            @endforeach
        </select>
        <p class="ui-field-help">Gunakan Ctrl atau Command untuk memilih lebih dari satu pilihan.</p>
    @elseif ($field->field_type === 'boolean')
        <div class="mt-2 flex min-h-10 items-center rounded-[var(--tm-r-md)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-3">
            <input type="hidden" name="{{ $fieldName }}" value="0" data-ticket-internal-field-input>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-[color:var(--tm-text-secondary)]">
                <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="checkbox" value="1" data-ticket-internal-field-input class="ui-checkbox" @checked($checked) @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
                Ya
            </label>
        </div>
    @else
        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="{{ $field->field_type === 'datetime' ? 'datetime-local' : $field->field_type }}" value="{{ is_scalar($oldValue) ? $oldValue : '' }}" data-ticket-internal-field-input class="ui-input mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
    @endif

    <input type="hidden" name="{{ $versionName }}" value="{{ $field->version }}">

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="mt-2 flex items-center gap-1.5 text-sm font-medium text-[color:var(--tm-danger-600)]">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
            {{ $errors->first($errorKey) }}
        </p>
    @endif
</div>
