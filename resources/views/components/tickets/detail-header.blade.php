@props([
    'backUrl',
    'backLabel',
    'ticketLabel',
    'status',
    'priority',
    'submittedAt',
    'showActions' => false,
])

<nav class="mb-5" aria-label="Navigasi detail tiket">
    <a href="{{ $backUrl }}" class="ui-action-link inline-flex items-center gap-2">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
        </svg>
        {{ $backLabel }}
    </a>
</nav>

<header class="ticket-reference-page-header">
    <div class="min-w-0 w-full">
        <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="ticket-reference-number">{{ $ticketLabel }}</h1>
            <x-status-badge :status="$status" class="!border-[#b9c7ff] !bg-[#e7ebff] !text-[#0037b0]" />
            <x-priority-badge :priority="$priority" class="!border-[#d6dce8] !bg-[#f1f4f8] !text-[#25344c]" />
        </div>

        <div class="ticket-reference-header-meta">
            <p class="ticket-reference-subtitle">Dibuat pada {{ $submittedAt }} WIB</p>

            @if ($showActions && isset($actions))
                <details class="ticket-reference-action-menu" data-ticket-action-menu>
                    <summary class="ticket-reference-action-trigger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="5" cy="12" r="1.2" fill="currentColor" stroke="none" /><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none" /><circle cx="19" cy="12" r="1.2" fill="currentColor" stroke="none" /></svg>
                        <span>Tindakan</span>
                        <svg class="ticket-reference-action-trigger-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8 10 4 4 4-4" /></svg>
                    </summary>
                    <div class="ticket-reference-action-menu-panel" aria-label="Daftar tindakan tiket">
                        {{ $actions }}
                    </div>
                </details>
            @endif
        </div>
    </div>
</header>
