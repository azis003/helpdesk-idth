@props(['priority'])

@php
    $priority = $priority instanceof \App\Enums\Priority
        ? $priority
        : \App\Enums\Priority::tryFrom((string) $priority);
    $tone = match ($priority) {
        \App\Enums\Priority::Kritis => 'border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-700)]',
        \App\Enums\Priority::Tinggi => 'border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] text-[color:var(--tm-warning-700)]',
        \App\Enums\Priority::Sedang => 'border-[color:var(--tm-info-200)] bg-[color:var(--tm-info-50)] text-[color:var(--tm-info-700)]',
        \App\Enums\Priority::Rendah => 'border-[color:var(--tm-n-200)] bg-[color:var(--tm-n-100)] text-[color:var(--tm-n-600)]',
        default => 'border-[color:var(--tm-n-200)] bg-[color:var(--tm-n-100)] text-[color:var(--tm-n-600)]',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 {$tone}"]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    {{ $priority?->label() ?? 'Belum ditentukan' }}
</span>
