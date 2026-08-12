@extends('layouts.app')

@php
    $canCreateTicket = auth()->user()->can('create', \App\Models\Ticket::class);
    $hasFilters = $search !== '';

    $emptyTitle = $hasFilters
        ? 'Tidak ada tiket yang cocok dengan pencarian.'
        : 'Belum ada tiket pada daftar ini.';
    $emptyDescription = $hasFilters
        ? 'Coba ubah kata kunci atau kosongkan pencarian.'
        : 'Tiket yang dibuat akan muncul di sini setelah tercatat.';
    $emptyAction = $hasFilters
        ? route('tickets.all')
        : ($canCreateTicket ? route('tickets.create') : null);
    $emptyActionLabel = $hasFilters
        ? 'Hapus pencarian'
        : ($canCreateTicket ? 'Buat tiket pertama' : null);
@endphp

@section('title', 'Semua Tiket — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', 'Semua Tiket')

@section('content')
    <x-page-header
        eyebrow="Pengelolaan tiket"
        title="Semua Tiket"
        description="Daftar seluruh tiket yang pernah dibuat."
    >
        @if ($canCreateTicket)
            <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat Tiket Baru
            </a>
        @endif
    </x-page-header>

    <div class="mt-6 flex justify-end">
        <form method="GET" action="{{ route('tickets.all') }}" class="flex items-center gap-2" role="search" aria-label="Cari semua tiket">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <label for="ticket-search" class="sr-only">Cari tiket</label>
            <div class="relative w-full sm:w-60">
                <button type="submit" class="absolute left-2.5 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-[var(--tm-r-xs)] text-[color:var(--tm-text-faint)] transition-colors hover:text-[color:var(--tm-brand-700)]" aria-label="Cari tiket">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 17a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13ZM20 20l-4.3-4.3" /></svg>
                </button>
                <input id="ticket-search" name="q" type="search" value="{{ $search }}" autocomplete="off" placeholder="Nomor tiket/kata kunci" class="h-9 w-full rounded-[var(--tm-r-md)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] pl-9 pr-3 text-xs text-[color:var(--tm-text)] outline-none transition placeholder:text-[color:var(--tm-text-faint)] focus:border-[color:var(--tm-brand-400)] focus:shadow-[var(--tm-ring)]">
            </div>
        </form>
    </div>

    <section class="ui-panel mt-4 overflow-hidden" aria-labelledby="all-tickets-heading">
        <h2 id="all-tickets-heading" class="sr-only">Daftar seluruh tiket</h2>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full min-w-[54rem]">
                <caption class="sr-only">Daftar seluruh tiket dengan nomor tiket, jenis layanan, judul, status, dan prioritas</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-[4rem]">No</th>
                        <th scope="col" class="w-[11rem]">No Tiket</th>
                        <th scope="col" class="w-[8.5rem]">Layanan</th>
                        <th scope="col" class="min-w-[16rem]">Judul</th>
                        <th scope="col" class="w-[12rem]">Status</th>
                        <th scope="col" class="w-[9rem]">Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        @php
                            $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                            $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                            $serviceName = $ticket->service_type_name_snapshot ?: $ticket->serviceType?->name;
                            $serviceTooltip = trim(($serviceCode ?: '').' · '.($serviceName ?: ''), " ·\t\n");
                            $serviceTooltip = $serviceTooltip !== '' ? $serviceTooltip : 'Layanan belum tersedia';
                            $ticketDetailUrl = route('tickets.show', ['ticket' => $ticket, 'from' => 'all']);
                        @endphp
                        <tr title="{{ $serviceTooltip }}">
                            <td class="font-semibold tabular-nums text-[color:var(--tm-text-faint)]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ $ticketDetailUrl }}" class="font-semibold tabular-nums text-[color:var(--tm-brand-700)] transition-colors hover:text-[color:var(--tm-brand-800)] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td class="whitespace-nowrap text-[color:var(--tm-text-secondary)]">{{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</td>
                            <td>
                                <div class="max-w-[26rem] truncate text-[color:var(--tm-text)]">{{ $ticket->subject }}</div>
                            </td>
                            <td class="whitespace-nowrap"><x-status-badge :status="$ticket->status" /></td>
                            <td class="whitespace-nowrap"><x-priority-badge :priority="$ticket->priority" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-empty-state :title="$emptyTitle" :description="$emptyDescription" :action="$emptyAction" :actionLabel="$emptyActionLabel" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[color:var(--tm-border-subtle)] md:hidden">
            @forelse ($tickets as $ticket)
                @php
                    $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                    $ticketDetailUrl = route('tickets.show', ['ticket' => $ticket, 'from' => 'all']);
                @endphp
                <article class="px-5 py-4 transition-colors hover:bg-[color:var(--tm-surface-sunken)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</p>
                            <a href="{{ $ticketDetailUrl }}" class="mt-1 block text-xs font-semibold tabular-nums text-[color:var(--tm-brand-700)] hover:underline">{{ $ticketNumber }}</a>
                            <h3 class="mt-1 line-clamp-2 font-semibold leading-5 text-[color:var(--tm-text)]">{{ $ticket->subject }}</h3>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-status-badge :status="$ticket->status" />
                            <x-priority-badge :priority="$ticket->priority" />
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state :title="$emptyTitle" :description="$emptyDescription" :action="$emptyAction" :actionLabel="$emptyActionLabel" />
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-5 py-3 text-xs text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="tabular-nums">
                @if ($tickets->total() > 0)
                    Menampilkan <span class="font-semibold text-[color:var(--tm-text)]">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span> dari <span class="font-semibold text-[color:var(--tm-text)]">{{ $tickets->total() }}</span> tiket
                @else
                    Tidak ada data tiket
                @endif
            </p>

            <div class="flex flex-wrap items-center gap-4">
                <form method="GET" action="{{ route('tickets.all') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $search }}">
                    <label for="ticket-per-page" class="whitespace-nowrap">Baris per halaman:</label>
                    <select id="ticket-per-page" name="per_page" onchange="this.form.submit()" class="ui-select h-9 w-auto min-w-[4.5rem] text-xs tabular-nums">
                        @foreach ($perPageOptions as $pageSize)
                            <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                </form>

                @if ($tickets->hasPages())
                    <div>{{ $tickets->onEachSide(1)->links() }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
