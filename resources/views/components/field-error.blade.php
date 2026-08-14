@props(['message'])

<p {{ $attributes->merge(['class' => 'ui-field-error']) }}>
    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
        <circle cx="10" cy="10" r="7" />
        <path stroke-linecap="round" d="M10 6.25v4.5m0 2.75h.01" />
    </svg>
    <span class="min-w-0">{{ $message }}</span>
</p>
