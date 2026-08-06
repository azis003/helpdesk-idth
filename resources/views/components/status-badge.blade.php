@props(['status'])

@php
    $status = $status instanceof \App\Enums\TicketStatus
        ? $status
        : \App\Enums\TicketStatus::tryFrom((string) $status);
    $tone = match ($status) {
        \App\Enums\TicketStatus::Baru => 'bg-[#e9f5ff] text-[#1d5d8f] border-[#b9def7]',
        \App\Enums\TicketStatus::Diproses, \App\Enums\TicketStatus::Dikerjakan => 'bg-[#e8faf4] text-[#087f5b] border-[#b9e9d7]',
        \App\Enums\TicketStatus::MenungguPersetujuan, \App\Enums\TicketStatus::MenungguPemohon, \App\Enums\TicketStatus::MenungguPihakKetiga, \App\Enums\TicketStatus::MenungguKonfirmasi => 'bg-[#fff7df] text-[#9a6700] border-[#f2d996]',
        \App\Enums\TicketStatus::Ditutup => 'bg-[#e8faf4] text-[#087f5b] border-[#b9e9d7]',
        \App\Enums\TicketStatus::Ditolak, \App\Enums\TicketStatus::TidakDisetujui => 'bg-[#fff1f2] text-[#be123c] border-[#fecdd3]',
        \App\Enums\TicketStatus::Dibatalkan => 'bg-[#eef2f4] text-[#657984] border-[#d4e0e5]',
        default => 'bg-[#eef2f4] text-[#657984] border-[#d4e0e5]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-extrabold {$tone}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status?->label() ?? 'Status tidak diketahui' }}
</span>
