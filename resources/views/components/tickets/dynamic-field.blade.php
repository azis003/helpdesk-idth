@props(['field', 'service'])

@php
    $fieldId = 'ticket-field-'.$service->code.'-'.$field->key;
    $fieldName = 'fields['.$field->key.']';
    $oldValue = old('fields.'.$field->key);
    $hasError = $errors->has('fields.'.$field->key);
    $describedBy = collect([
        $field->help_text ? $fieldId.'-help' : null,
        $hasError ? $fieldId.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div data-ticket-field class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-4 sm:p-5">
    <label for="{{ $fieldId }}" class="ui-field-label">
        {{ $field->label }}
        @if ($field->is_required)
            <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span>
            <span class="sr-only">wajib</span>
        @endif
    </label>
    @if ($field->help_text)
        <p id="{{ $fieldId }}-help" class="ui-field-help">{{ $field->help_text }}</p>
    @endif

    @if ($field->field_type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="4" data-ticket-field-input class="ui-textarea mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">{{ is_scalar($oldValue) ? $oldValue : '' }}</textarea>
    @elseif ($field->field_type === 'select')
        <select id="{{ $fieldId }}" name="{{ $fieldName }}" data-ticket-field-input class="ui-select mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            <option value="">Pilih {{ strtolower($field->label) }}</option>
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected((string) $oldValue === (string) $option->value)>{{ $option->label }}</option>
            @endforeach
        </select>
    @elseif ($field->field_type === 'multiselect')
        @php($selectedValues = is_array($oldValue) ? $oldValue : [])
        <select id="{{ $fieldId }}" name="{{ $fieldName }}[]" data-ticket-field-input class="ui-select mt-2 min-h-28" multiple @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected(in_array($option->value, $selectedValues, true))>{{ $option->label }}</option>
            @endforeach
        </select>
        <p class="ui-field-help">Gunakan Ctrl atau Command untuk memilih lebih dari satu pilihan.</p>
    @elseif ($field->field_type === 'boolean')
        <div class="mt-2 flex min-h-10 items-center rounded-[var(--tm-r-md)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-3">
            <input type="hidden" name="{{ $fieldName }}" value="0" data-ticket-field-input>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-[color:var(--tm-text-secondary)]">
                <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="checkbox" value="1" data-ticket-field-input class="ui-checkbox" @checked((bool) $oldValue) @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
                Ya
            </label>
        </div>
    @else
        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="{{ $field->field_type === 'datetime' ? 'datetime-local' : $field->field_type }}" value="{{ is_scalar($oldValue) ? $oldValue : '' }}" data-ticket-field-input class="ui-input mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
    @endif

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="mt-2 flex items-center gap-1.5 text-sm font-medium text-[color:var(--tm-danger-600)]">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
            {{ $errors->first('fields.'.$field->key) }}
        </p>
    @endif
</div>
