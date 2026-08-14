@props([
    'title',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <span class="ui-empty__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3h6.6a1.5 1.5 0 0 1 1.06.44l4.4 4.4A1.5 1.5 0 0 1 20 8.9V19.5A1.5 1.5 0 0 1 18.5 21h-11A1.5 1.5 0 0 1 6 19.5v-15A1.5 1.5 0 0 1 7.5 3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.2V8a1 1 0 0 0 1 1h4.8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 13.5h5m-5 3.5h3" />
        </svg>
    </span>
    <p class="ui-empty__title">{{ $title }}</p>
    @if ($description)
        <p class="ui-empty__description">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="ui-btn ui-btn-primary mt-5">{{ $actionLabel }}</a>
    @endif
</div>
