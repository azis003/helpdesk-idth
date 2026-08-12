@props(['status'])

@php
    $status = $status instanceof \App\Enums\TicketStatus
        ? $status
        : \App\Enums\TicketStatus::tryFrom((string) $status);
    $tone = match ($status) {
        \App\Enums\TicketStatus::Baru => 'border-[color:var(--tm-info-200)] bg-[color:var(--tm-info-50)] text-[color:var(--tm-info-700)]',
        \App\Enums\TicketStatus::Diproses, \App\Enums\TicketStatus::Dikerjakan => 'border-[color:var(--tm-brand-200)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]',
        \App\Enums\TicketStatus::MenungguPersetujuan, \App\Enums\TicketStatus::MenungguPemohon, \App\Enums\TicketStatus::MenungguPihakKetiga, \App\Enums\TicketStatus::MenungguKonfirmasi => 'border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] text-[color:var(--tm-warning-700)]',
        \App\Enums\TicketStatus::Ditutup => 'border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] text-[color:var(--tm-success-700)]',
        \App\Enums\TicketStatus::Ditolak, \App\Enums\TicketStatus::TidakDisetujui => 'border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-700)]',
        \App\Enums\TicketStatus::Dibatalkan => 'border-[color:var(--tm-n-200)] bg-[color:var(--tm-n-100)] text-[color:var(--tm-n-600)]',
        default => 'border-[color:var(--tm-n-200)] bg-[color:var(--tm-n-100)] text-[color:var(--tm-n-600)]',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 {$tone}"]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status?->label() ?? 'Status tidak diketahui' }}
</span>
