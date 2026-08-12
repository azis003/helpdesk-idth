@props(['status'])

@php
    $status = $status instanceof \App\Enums\TicketStatus
        ? $status
        : \App\Enums\TicketStatus::tryFrom((string) $status);
    $tone = match ($status) {
        \App\Enums\TicketStatus::Baru => 'ui-status-badge--info',
        \App\Enums\TicketStatus::Diproses, \App\Enums\TicketStatus::Dikerjakan => 'ui-status-badge--active',
        \App\Enums\TicketStatus::MenungguPersetujuan, \App\Enums\TicketStatus::MenungguPemohon, \App\Enums\TicketStatus::MenungguPihakKetiga, \App\Enums\TicketStatus::MenungguKonfirmasi => 'ui-status-badge--waiting',
        \App\Enums\TicketStatus::Ditutup => 'ui-status-badge--closed',
        \App\Enums\TicketStatus::Ditolak, \App\Enums\TicketStatus::TidakDisetujui => 'ui-status-badge--danger',
        \App\Enums\TicketStatus::Dibatalkan => 'ui-status-badge--muted',
        default => 'ui-status-badge--muted',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge ui-status-badge inline-flex items-center gap-1.5 {$tone}"]) }}>
    <span class="ui-badge-indicator" aria-hidden="true"></span>
    {{ $status?->label() ?? 'Status tidak diketahui' }}
</span>
