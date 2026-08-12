@props(['priority'])

@php
    $priority = $priority instanceof \App\Enums\Priority
        ? $priority
        : \App\Enums\Priority::tryFrom((string) $priority);
    $tone = match ($priority) {
        \App\Enums\Priority::Kritis => 'ui-priority-badge--critical',
        \App\Enums\Priority::Tinggi => 'ui-priority-badge--high',
        \App\Enums\Priority::Sedang => 'ui-priority-badge--medium',
        \App\Enums\Priority::Rendah => 'ui-priority-badge--low',
        default => 'ui-priority-badge--unset',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge ui-priority-badge inline-flex items-center {$tone}"]) }}>
    {{ $priority?->label() ?? 'Belum ditentukan' }}
</span>
