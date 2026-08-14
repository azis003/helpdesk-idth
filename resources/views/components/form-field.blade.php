@props([
    'name',
    'label',
    'type' => 'text',
    'autocomplete' => null,
    'help' => null,
    'value' => null,
    'required' => false,
])

<div class="min-w-0">
    <label for="{{ $name }}" class="ui-field-label">
        {{ $label }}
        @if ($required)
            <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span>
            <span class="sr-only">wajib</span>
        @endif
    </label>
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
    @if ($help)
        <p id="{{ $name }}-help" class="ui-field-help mt-2">{{ $help }}</p>
    @endif
    @error($name)
        <x-field-error id="{{ $name }}-error" :message="$message" />
    @enderror
</div>
