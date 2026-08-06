@props(['priority'])

@php
    $priority = $priority instanceof \App\Enums\Priority
        ? $priority
        : \App\Enums\Priority::tryFrom((string) $priority);
    $tone = match ($priority) {
        \App\Enums\Priority::Kritis => 'bg-[#fff1f2] text-[#be123c] border-[#fecdd3]',
        \App\Enums\Priority::Tinggi => 'bg-[#fff7ed] text-[#c2410c] border-[#fed7aa]',
        \App\Enums\Priority::Sedang => 'bg-[#fff7df] text-[#9a6700] border-[#f2d996]',
        \App\Enums\Priority::Rendah => 'bg-[#eef2f4] text-[#657984] border-[#d4e0e5]',
        default => 'bg-[#eef2f4] text-[#657984] border-[#d4e0e5]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold {$tone}"]) }}>
    {{ $priority?->label() ?? 'Belum ditentukan' }}
</span>
