@extends('layouts.app')

@php
    $canCreateTicket = auth()->user()->can('create', \App\Models\Ticket::class);

    $tabs = [
        \App\Services\RequesterTicketList::TAB_ALL => ['label' => 'Semua', 'alert' => false],
        \App\Services\RequesterTicketList::TAB_ACTIVE => ['label' => 'Aktif', 'alert' => false],
        \App\Services\RequesterTicketList::TAB_ACTION => ['label' => 'Perlu Konfirmasi', 'alert' => true],
        \App\Services\RequesterTicketList::TAB_DONE => ['label' => 'Selesai', 'alert' => false],
    ];

    $filterQuery = array_filter([
        'q' => $search,
        'per_page' => $perPage === 10 ? null : $perPage,
    ], static fn ($value): bool => $value !== null && $value !== '');

    $emptyTitle = $hasFilters
        ? 'Tidak ada tiket yang cocok dengan pencarian.'
        : 'Belum ada tiket pada daftar ini.';
    $emptyDescription = $hasFilters
        ? 'Coba ubah kata kunci atau kosongkan pencarian.'
        : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.';
    $emptyAction = $hasFilters
        ? route('tickets.index', ['tab' => $activeTab])
        : ($canCreateTicket ? route('tickets.create') : null);
    $emptyActionLabel = $hasFilters
        ? 'Hapus pencarian'
        : ($canCreateTicket ? 'Buat tiket pertama' : null);
@endphp

@section('title', 'Tiket saya — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', 'Tiket saya')

@section('content')
    <x-page-header
        eyebrow="Pelacakan permintaan"
        title="Tiket Saya"
        description="Semua tiket yang Anda ajukan atau diajukan atas nama Anda."
    >
        @if ($canCreateTicket)
            <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat Tiket Baru
            </a>
        @endif
    </x-page-header>

    <div class="mt-6 flex flex-col gap-3 border-b border-[color:var(--tm-border-subtle)] pb-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap items-center gap-2" aria-label="Status tiket saya">
            @foreach ($tabs as $tabKey => $tab)
                @php
                    $isActiveTab = $activeTab === $tabKey;
                    $tabCount = $tabCounts[$tabKey] ?? 0;
                @endphp
                <a href="{{ route('tickets.index', array_merge($filterQuery, ['tab' => $tabKey])) }}"
                    @class([
                        'inline-flex items-center gap-1.5 rounded-[var(--tm-r-full)] border px-3.5 py-1.5 text-xs font-semibold transition',
                        'border-transparent bg-[color:var(--tm-brand-600)] text-white shadow-[var(--tm-sh-sm)]' => $isActiveTab,
                        'border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]' => ! $isActiveTab,
                    ])
                    @if ($isActiveTab) aria-current="page" @endif>
                    @if ($tab['alert'] && $tabCount > 0)
                        <span @class([
                            'h-1.5 w-1.5 rounded-full',
                            'bg-white' => $isActiveTab,
                            'bg-[color:var(--tm-danger-600)]' => ! $isActiveTab,
                        ]) aria-hidden="true"></span>
                    @endif
                    <span>{{ $tab['label'] }}</span>
                    <span @class([
                        'rounded-[var(--tm-r-full)] px-1.5 py-0.5 text-[0.68rem] font-semibold tabular-nums',
                        'bg-white/20 text-white' => $isActiveTab,
                        'bg-[color:var(--tm-n-100)] text-[color:var(--tm-text-muted)]' => ! $isActiveTab,
                    ])>{{ $tabCount }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('tickets.index') }}" class="flex items-center gap-2" role="search" aria-label="Cari tiket saya">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
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

    <section class="ui-panel mt-4 overflow-hidden" aria-labelledby="requester-tickets-heading">
        <h2 id="requester-tickets-heading" class="sr-only">Daftar tiket saya</h2>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full min-w-[54rem]">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, jenis layanan, judul, status, dan prioritas</caption>
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
                            $status = $ticket->status;
                            $needsAction = $status?->needsRequesterAction() ?? false;
                            $action = $requesterActions[$ticket->getKey()] ?? null;
                            $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                            $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                            $serviceName = $ticket->service_type_name_snapshot ?: $ticket->serviceType?->name;
                            $serviceTooltip = trim(($serviceCode ?: '').' · '.($serviceName ?: ''), " ·\t\n");
                            $serviceTooltip = $serviceTooltip !== '' ? $serviceTooltip : 'Layanan belum tersedia';
                            $rowHint = $needsAction && $action ? $action['description'] : $serviceTooltip;
                        @endphp
                        <tr title="{{ $rowHint }}">
                            <td @class([
                                    'font-semibold tabular-nums text-[color:var(--tm-text-faint)]',
                                    'border-l-[3px] border-l-[color:var(--tm-brand-500)]' => $needsAction,
                                ])>{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold tabular-nums text-[color:var(--tm-brand-700)] transition-colors hover:text-[color:var(--tm-brand-800)] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td class="whitespace-nowrap text-[color:var(--tm-text-secondary)]">{{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</td>
                            <td>
                                <div @class([
                                        'max-w-[26rem] truncate',
                                        'font-semibold text-[color:var(--tm-text)]' => $needsAction,
                                        'text-[color:var(--tm-text-secondary)]' => ! $needsAction,
                                    ])>{{ $ticket->subject }}</div>
                                @if ($needsAction && $action)
                                    <span class="mt-1 inline-flex items-center gap-1 rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-[0.05em] text-[color:var(--tm-warning-700)]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--tm-warning-600)]" aria-hidden="true"></span>
                                        {{ $action['label'] }}
                                    </span>
                                @endif
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
                    $status = $ticket->status;
                    $needsAction = $status?->needsRequesterAction() ?? false;
                    $action = $requesterActions[$ticket->getKey()] ?? null;
                    $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                @endphp
                <article @class([
                    'px-5 py-4 transition-colors',
                    'border-l-[3px] border-l-[color:var(--tm-brand-500)] bg-[color:var(--tm-brand-50)]' => $needsAction,
                    'hover:bg-[color:var(--tm-surface-sunken)]' => ! $needsAction,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</p>
                            <a href="{{ route('tickets.show', $ticket) }}" class="mt-1 block text-xs font-semibold tabular-nums text-[color:var(--tm-brand-700)] hover:underline">{{ $ticketNumber }}</a>
                            <h4 class="mt-1 line-clamp-2 font-semibold leading-5 text-[color:var(--tm-text)]">{{ $ticket->subject }}</h4>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-status-badge :status="$ticket->status" />
                            <x-priority-badge :priority="$ticket->priority" />
                        </div>
                    </div>

                    @if ($needsAction && $action)
                        <div class="mt-4 rounded-[var(--tm-r-md)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-3">
                            <p class="text-xs leading-5 text-[color:var(--tm-warning-700)]">{{ $action['description'] }}</p>
                            <a href="{{ route('tickets.show', $ticket) }}" class="ui-btn ui-btn-secondary mt-3 px-3 py-2 text-xs">{{ $action['label'] }}</a>
                        </div>
                    @endif
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
                <form method="GET" action="{{ route('tickets.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <input type="hidden" name="q" value="{{ $search }}">
                    <input type="hidden" name="class" value="{{ $serviceClass }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <input type="hidden" name="from" value="{{ $dateFrom }}">
                    <input type="hidden" name="to" value="{{ $dateTo }}">
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
