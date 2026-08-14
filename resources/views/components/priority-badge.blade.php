@props(['priority'])

@php
    $priority = $priority instanceof \App\Enums\Priority
        ? $priority
        : \App\Enums\Priority::tryFrom((string) $priority);
    [$state, $level] = match ($priority) {
        \App\Enums\Priority::Kritis => ['kritis', 4],
        \App\Enums\Priority::Tinggi => ['tinggi', 3],
        \App\Enums\Priority::Sedang => ['sedang', 2],
        \App\Enums\Priority::Rendah => ['rendah', 1],
        default => ['unknown', 0],
    };
    $label = $priority?->label() ?? 'Belum ditentukan';
    $accessibleLabel = $priority
        ? "Prioritas {$label}, tingkat {$level} dari 4"
        : 'Prioritas belum ditentukan';
@endphp

<span
    {{ $attributes->merge(['class' => "ui-badge ui-priority-badge ui-priority-badge--{$state}"]) }}
    aria-label="{{ $accessibleLabel }}"
>
    <span class="ui-priority-badge__cue" aria-hidden="true">
        @foreach (range(1, 4) as $segment)
            <span class="ui-priority-badge__segment {{ $segment <= $level ? 'is-filled' : '' }}"></span>
        @endforeach
    </span>
    <span>{{ $label }}</span>
</span>
