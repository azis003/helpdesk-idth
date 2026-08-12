@props([
    'title',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-[var(--tm-r-full)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-500)] ring-1 ring-[color:var(--tm-brand-100)]" aria-hidden="true">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3h6.6a1.5 1.5 0 0 1 1.06.44l4.4 4.4A1.5 1.5 0 0 1 20 8.9V19.5A1.5 1.5 0 0 1 18.5 21h-11A1.5 1.5 0 0 1 6 19.5v-15A1.5 1.5 0 0 1 7.5 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.2V8a1 1 0 0 0 1 1h4.8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 13.5h5m-5 3.5h3" />
        </svg>
    </span>
    <p class="text-base font-extrabold tracking-[-0.01em] text-[color:var(--tm-text)]">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[color:var(--tm-text-muted)]">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="ui-btn ui-btn-primary mt-5">{{ $actionLabel }}</a>
    @endif
</div>
