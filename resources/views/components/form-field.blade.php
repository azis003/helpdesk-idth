@props([
    'name',
    'label',
    'type' => 'text',
    'autocomplete' => null,
    'help' => null,
    'value' => null,
    'required' => false,
])

<div>
    <label for="{{ $name }}" class="ui-field-label">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600" aria-hidden="true">*</span>
            <span class="sr-only">wajib</span>
        @endif
    </label>
    @if ($help)
        <p id="{{ $name }}-help" class="ui-field-help">{{ $help }}</p>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @elseif($help) aria-describedby="{{ $name }}-help" @enderror
        {{ $attributes->merge(['class' => 'ui-input mt-2']) }}
    >
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
    @enderror
</div>
