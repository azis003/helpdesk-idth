@props(['status'])

@php
    $status = $status instanceof \App\Enums\TicketStatus
        ? $status
        : \App\Enums\TicketStatus::tryFrom((string) $status);
    $state = match ($status) {
        \App\Enums\TicketStatus::Baru => 'baru',
        \App\Enums\TicketStatus::Diproses => 'diproses',
        \App\Enums\TicketStatus::Dikerjakan => 'dikerjakan',
        \App\Enums\TicketStatus::MenungguPersetujuan => 'menunggu-persetujuan',
        \App\Enums\TicketStatus::MenungguPemohon => 'menunggu-pemohon',
        \App\Enums\TicketStatus::MenungguPihakKetiga => 'menunggu-pihak-ketiga',
        \App\Enums\TicketStatus::MenungguKonfirmasi => 'menunggu-konfirmasi',
        \App\Enums\TicketStatus::Ditutup => 'ditutup',
        \App\Enums\TicketStatus::Ditolak => 'ditolak',
        \App\Enums\TicketStatus::TidakDisetujui => 'tidak-disetujui',
        \App\Enums\TicketStatus::Dibatalkan => 'dibatalkan',
        default => 'unknown',
    };
@endphp

<span {{ $attributes->merge(['class' => "ui-badge ui-status-badge ui-status-badge--{$state}"]) }}>
    <svg class="ui-status-badge__cue" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
        @switch($status)
            @case(\App\Enums\TicketStatus::Baru)
                <circle cx="10" cy="10" r="6.25" />
                @break
            @case(\App\Enums\TicketStatus::Diproses)
                <circle cx="10" cy="10" r="6.25" />
                <path d="M10 3.75a6.25 6.25 0 0 1 0 12.5Z" fill="currentColor" stroke="none" />
                @break
            @case(\App\Enums\TicketStatus::Dikerjakan)
                <circle cx="10" cy="10" r="6.25" fill="currentColor" stroke="none" />
                @break
            @case(\App\Enums\TicketStatus::MenungguPersetujuan)
                <circle cx="8.5" cy="11" r="5.5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 7.75V11l2.25 1.5M15.5 2.75l1.75 1.75-1.75 1.75-1.75-1.75 1.75-1.75Z" />
                @break
            @case(\App\Enums\TicketStatus::MenungguPemohon)
                <circle cx="8.5" cy="11" r="5.5" />
                <path stroke-linecap="round" d="M8.5 7.75V11l2.25 1.5" />
                <circle cx="15.5" cy="3.75" r="1.5" />
                <path stroke-linecap="round" d="M13.5 7c.4-1.15 1.05-1.7 2-1.7s1.6.55 2 1.7" />
                @break
            @case(\App\Enums\TicketStatus::MenungguPihakKetiga)
                <circle cx="8.5" cy="11" r="5.5" />
                <path stroke-linecap="round" d="M8.5 7.75V11l2.25 1.5M13.25 3.5h3.25v3.25M16.35 3.65l-3.5 3.5" />
                @break
            @case(\App\Enums\TicketStatus::MenungguKonfirmasi)
                <circle cx="8.5" cy="11" r="5.5" />
                <path stroke-linecap="round" d="M8.5 7.75V11l2.25 1.5M13.5 4.5l1.25 1.25 2.5-2.5" />
                @break
            @case(\App\Enums\TicketStatus::Ditutup)
                <circle cx="10" cy="10" r="6.25" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m6.75 10 2.1 2.1 4.5-4.5" />
                @break
            @case(\App\Enums\TicketStatus::Ditolak)
                <path stroke-linejoin="round" d="m7 3.75 6 0 3.25 3.25v6L13 16.25H7L3.75 13V7L7 3.75Z" />
                <path stroke-linecap="round" d="M7 10h6" />
                @break
            @case(\App\Enums\TicketStatus::TidakDisetujui)
                <path stroke-linejoin="round" d="m10 3 6.5 7-6.5 7-6.5-7L10 3Z" />
                <path stroke-linecap="round" d="m7.5 7.5 5 5m0-5-5 5" />
                @break
            @case(\App\Enums\TicketStatus::Dibatalkan)
                <circle cx="10" cy="10" r="6.25" />
                <path stroke-linecap="round" d="m5.5 14.5 9-9" />
                @break
            @default
                <circle cx="10" cy="10" r="6.25" />
                <path stroke-linecap="round" d="M10 7v3.5m0 2.5h.01" />
        @endswitch
    </svg>
    <span>{{ $status?->label() ?? 'Status tidak diketahui' }}</span>
</span>
