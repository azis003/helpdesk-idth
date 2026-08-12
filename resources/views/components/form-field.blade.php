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
        <p id="{{ $name }}-help" class="mt-2 text-xs leading-5 text-[color:var(--tm-text-muted)]">{{ $help }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 flex items-start gap-1.5 text-sm font-medium text-[color:var(--tm-danger-700)]">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" d="M12 7.5v5m0 3.5h.01" />
            </svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
