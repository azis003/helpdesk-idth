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

<div data-ticket-internal-field class="rounded-xl border border-[#f0d28c] bg-[#fffdf7] p-4 sm:p-5">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <label for="{{ $fieldId }}" class="ui-field-label !text-[#6f5314]">
            {{ $field->label }}
            @if ($field->is_required)
                <span class="text-rose-600" aria-hidden="true">*</span>
                <span class="sr-only">wajib</span>
            @endif
        </label>
        <span class="rounded-full bg-[#fff2c9] px-2 py-1 text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-[#8a6514]">Definisi v{{ $field->version }}</span>
    </div>
    @if ($field->help_text)
        <p id="{{ $fieldId }}-help" class="ui-field-help !text-[#8a7440]">{{ $field->help_text }}</p>
    @else
        <p id="{{ $fieldId }}-help" class="ui-field-help !text-[#8a7440]">Hanya terlihat oleh petugas berwenang Tim TI.</p>
    @endif

    @if ($field->field_type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="4" data-ticket-internal-field-input class="ui-textarea mt-2 !border-[#ecd89f] !bg-white" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">{{ is_scalar($oldValue) ? $oldValue : '' }}</textarea>
    @elseif ($field->field_type === 'select')
        <select id="{{ $fieldId }}" name="{{ $fieldName }}" data-ticket-internal-field-input class="ui-select mt-2 !border-[#ecd89f] !bg-white" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            <option value="">Pilih {{ strtolower($field->label) }}</option>
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected((string) $oldValue === (string) $option->value)>{{ $option->label }}</option>
            @endforeach
        </select>
    @elseif ($field->field_type === 'multiselect')
        @php($selectedValues = is_array($oldValue) ? $oldValue : [])
        <select id="{{ $fieldId }}" name="{{ $fieldName }}[]" data-ticket-internal-field-input class="ui-select mt-2 min-h-28 !border-[#ecd89f] !bg-white" multiple @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
            @foreach ($field->options->where('is_active', true) as $option)
                <option value="{{ $option->value }}" @selected(in_array($option->value, $selectedValues, true))>{{ $option->label }}</option>
            @endforeach
        </select>
        <p class="ui-field-help !text-[#8a7440]">Gunakan Ctrl atau Command untuk memilih lebih dari satu pilihan.</p>
    @elseif ($field->field_type === 'boolean')
        <div class="mt-2 flex min-h-10 items-center rounded-lg border border-[#ecd89f] bg-white px-3">
            <input type="hidden" name="{{ $fieldName }}" value="0" data-ticket-internal-field-input>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-[#6f5314]">
                <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="checkbox" value="1" data-ticket-internal-field-input class="h-4 w-4 rounded border-[#c6a84e] text-[#9b741a] focus:ring-[#d7ad48]" @checked($checked) @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
                Ya
            </label>
        </div>
    @else
        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="{{ $field->field_type === 'datetime' ? 'datetime-local' : $field->field_type }}" value="{{ is_scalar($oldValue) ? $oldValue : '' }}" data-ticket-internal-field-input class="ui-input mt-2 !border-[#ecd89f] !bg-white" @if ($field->is_required) required @endif @if ($hasError) aria-invalid="true" @endif aria-describedby="{{ $describedBy }}">
    @endif

    <input type="hidden" name="{{ $versionName }}" value="{{ $field->version }}">

    @if ($hasError)
        <p id="{{ $fieldId }}-error" class="mt-2 text-sm text-rose-700">{{ $errors->first($errorKey) }}</p>
    @endif
</div>
