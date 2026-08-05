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
    <label for="{{ $name }}" class="block text-sm font-semibold text-slate-800">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600" aria-hidden="true">*</span>
            <span class="sr-only">wajib</span>
        @endif
    </label>
    @if ($help)
        <p id="{{ $name }}-help" class="mt-1 text-xs leading-5 text-slate-500">{{ $help }}</p>
    @endif
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @elseif($help) aria-describedby="{{ $name }}-help" @enderror
        {{ $attributes->merge(['class' => 'mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200']) }}
    >
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
    @enderror
</div>
