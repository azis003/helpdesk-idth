@props([
    'title',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <p class="text-base font-extrabold text-[#35505b]">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[#78909a]">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <a href="{{ $action }}" class="ui-btn ui-btn-primary mt-5">{{ $actionLabel }}</a>
    @endif
</div>
