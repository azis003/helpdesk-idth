@props(['field', 'service'])

@php
    $fieldId = 'ticket-field-'.$service->code.'-'.$field->key;
    $fieldName = 'fields['.$field->key.']';
    $oldValue = old('fields.'.$field->key);
    $hasError = $errors->has('fields.'.$field->key);
    $describedBy = $fieldId.'-help'.($hasError ? ' '.$fieldId.'-error' : '');
@endphp

<div data-ticket-field class="rounded-xl border border-[#e5edef] bg-[#fbfdfd] p-4 sm:p-5">
    <label for="{{ $fieldId }}" class="ui-field-label">
        {{ $field->label }}
        @if ($field->is_required)
            <span class="text-rose-600" aria-hidden="true">*</span>
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
        <div class="mt-2 flex min-h-10 items-center rounded-lg border border-[#dfe8ec] bg-white px-3">
            <input type="hidden" name="{{ $fieldName }}" value="0" data-ticket-field-input>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-[#526f79]">
                <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="checkbox" value="1" data-ticket-field-input class="h-4 w-4 rounded border-[#a9bbc2] text-[#147a79] focus:ring-[#2bb8aa]" @checked((bool) $oldValue) @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
                Ya
            </label>
        </div>
    @else
        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="{{ $field->field_type === 'datetime' ? 'datetime-local' : $field->field_type }}" value="{{ is_scalar($oldValue) ? $oldValue : '' }}" data-ticket-field-input class="ui-input mt-2" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
    @endif

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="mt-2 text-sm text-rose-700">{{ $errors->first('fields.'.$field->key) }}</p>
    @endif
</div>
