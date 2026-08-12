@extends('layouts.app')

@php
    $isTeamChair = $isTeamChair ?? false;
    $hasPersonalScope = $canAccessTickets ?? false;
    $showFilters = $showFilters ?? ! $isTeamChair;
    $search = $search ?? '';
    $perPage = $perPage ?? 10;
    $requesterActions = $requesterActions ?? [];
    $isAllTickets = $isAllTickets ?? false;
    $canCreateTicket = auth()->user()->can('create', \App\Models\Ticket::class);
    $ticketListLabel = $isAllTickets
        ? 'Semua Tiket'
        : ($isTeamChair && $hasPersonalScope
        ? 'Tiket saya dan tim'
        : ($isTeamChair ? 'Tiket tim' : 'Tiket saya'));
    $pageDescription = $isAllTickets
        ? 'Lihat seluruh tiket yang pernah dibuat, termasuk status, prioritas, dan penanggung jawabnya.'
        : ($isTeamChair
        ? 'Pantau nomor tiket, layanan, status, dan detail penanganan anggota tim dalam mode baca saja.'
        : 'Lihat nomor tiket, layanan, status, dan tindakan yang perlu Anda selesaikan.');
    $listRoute = $isAllTickets ? route('tickets.all') : route('tickets.index');
    $emptyTitle = $search !== ''
        ? 'Tidak ada tiket yang cocok dengan pencarian.'
        : 'Belum ada tiket pada daftar ini.';
    $emptyDescription = $search !== ''
        ? 'Coba gunakan nomor tiket atau judul yang berbeda.'
        : ($isAllTickets
        ? 'Tiket yang dibuat akan muncul di sini setelah tercatat.'
        : ($isTeamChair
        ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.'
        : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.'));
    $emptyAction = $search !== ''
        ? $listRoute.'?per_page='.$perPage
        : ($canCreateTicket ? route('tickets.create') : null);
    $emptyActionLabel = $search !== ''
        ? 'Hapus pencarian'
        : ($canCreateTicket ? 'Buat tiket pertama' : null);
@endphp

@section('title', $ticketListLabel.' — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', $ticketListLabel)

@section('content')
    <x-page-header
        :eyebrow="$isAllTickets ? 'Pengelolaan tiket' : ($isTeamChair ? 'Pemantauan tim' : 'Pelacakan permintaan')"
        :title="$ticketListLabel"
        :description="$pageDescription"
    >
        @if ($canViewQueue)
            <a href="{{ route('tickets.queue') }}" class="ui-btn ui-btn-secondary">Monitoring Tiket</a>
        @endif
        @if ($canCreateTicket)
            <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary">
                Buat tiket
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12" /><path d="m11 5 5 5-5 5" /></svg>
            </a>
        @endif
    </x-page-header>

    <section class="ui-panel mt-4 overflow-hidden" aria-labelledby="tickets-list-heading">
        <h2 id="tickets-list-heading" class="sr-only">{{ $isAllTickets ? 'Daftar seluruh tiket' : 'Daftar tiket' }}</h2>

        @if ($showFilters)
            <div class="border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                <form method="GET" action="{{ $listRoute }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" role="search" aria-label="Cari tiket">
                    <div class="flex items-center gap-2 text-sm text-[color:var(--tm-text-secondary)]">
                        <label for="ticket-per-page" class="font-semibold text-[color:var(--tm-text)]">Tampilkan</label>
                        <select id="ticket-per-page" name="per_page" class="ui-select h-10 w-auto min-w-[4.75rem] tabular-nums" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $pageSize)
                                <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                            @endforeach
                        </select>
                        <span>data</span>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <label for="ticket-search" class="shrink-0 text-sm font-semibold text-[color:var(--tm-text)]">Cari:</label>
                        <input id="ticket-search" name="q" type="search" value="{{ $search }}" class="ui-input h-10 w-full min-w-0 sm:w-72" placeholder="Nomor tiket atau judul" autocomplete="off">
                        <button type="submit" class="ui-btn ui-btn-secondary h-10 justify-center px-4">Cari</button>
                    </div>
                </form>

                @if ($search !== '')
                    <p class="mt-3 text-xs text-[color:var(--tm-text-muted)]">Menampilkan hasil untuk “<span class="font-semibold text-[color:var(--tm-text)]">{{ $search }}</span>”. <a href="{{ $listRoute.'?per_page='.$perPage }}" class="font-semibold text-[color:var(--tm-brand-700)] underline decoration-[color:var(--tm-brand-200)] underline-offset-2 hover:text-[color:var(--tm-brand-800)]">Hapus pencarian</a></p>
                @endif
            </div>
        @endif

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full min-w-[76rem]">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, layanan, judul, status, prioritas, dan aksi</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-[4rem]">No</th>
                        <th scope="col" class="min-w-[14rem]">No Tiket</th>
                        <th scope="col" class="min-w-[15rem]">Layanan</th>
                        <th scope="col" class="min-w-[18rem]">Judul</th>
                        @if ($isAllTickets)
                            <th scope="col" class="min-w-[13rem]">Pemohon</th>
                        @endif
                        <th scope="col" class="min-w-[11rem]">Status</th>
                        @if ($isAllTickets)
                            <th scope="col" class="min-w-[13rem]">Penanggung jawab</th>
                        @endif
                        <th scope="col" class="min-w-[9rem]">Prioritas</th>
                        <th scope="col" class="min-w-[13rem] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        @php
                            $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                            $ticketId = $isTeamChair ? $ticket->id : $ticket->getKey();
                            $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                            $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?: $ticket->serviceType?->code);
                            $serviceName = $isTeamChair ? $ticket->serviceLabel() : ($ticket->service_type_name_snapshot ?: $ticket->serviceType?->name);
                            $requesterName = $isAllTickets ? ($ticket->requester_name_snapshot ?: $ticket->requester?->name ?: 'Pemohon belum tercatat') : null;
                            $assigneeName = $isAllTickets ? ($ticket->assignee?->name ?: 'Belum ditugaskan') : null;
                            $requesterAction = $requesterActions[$ticketId] ?? null;
                            $ticketDetailUrl = $isAllTickets
                                ? route('tickets.show', ['ticket' => $ticket, 'from' => 'all'])
                                : route('tickets.show', $ticketRouteTarget);
                        @endphp
                        <tr>
                            <td class="font-semibold tabular-nums text-[color:var(--tm-text-faint)]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ $ticketDetailUrl }}" class="font-semibold tabular-nums text-[color:var(--tm-brand-700)] transition-colors hover:text-[color:var(--tm-brand-800)] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td>
                                <span class="block text-xs font-semibold uppercase tracking-[0.05em] text-[color:var(--tm-text-faint)]">{{ $serviceCode ?: '—' }}</span>
                                <span class="mt-0.5 block max-w-[18rem] text-[color:var(--tm-text-secondary)]">{{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                            </td>
                            <td><div class="max-w-[28rem] truncate font-semibold text-[color:var(--tm-text)]">{{ $ticket->subject }}</div></td>
                            @if ($isAllTickets)
                                <td class="text-[color:var(--tm-text-secondary)]">{{ $requesterName }}</td>
                            @endif
                            <td><x-status-badge :status="$ticket->status" /></td>
                            @if ($isAllTickets)
                                <td class="text-[color:var(--tm-text-secondary)]">{{ $assigneeName }}</td>
                            @endif
                            <td><x-priority-badge :priority="$ticket->priority" /></td>
                            <td class="text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-ghost min-h-9 px-3 py-1.5 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                                    @if ($requesterAction)
                                        <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-secondary min-h-9 px-3 py-1.5 text-xs" aria-label="{{ $requesterAction['description'] }}" title="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                                    @endif
                                </div>
                                @if ($requesterAction)
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-[0.05em] text-[color:var(--tm-warning-700)]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[color:var(--tm-warning-600)]" aria-hidden="true"></span>
                                        Perlu tindakan
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAllTickets ? 9 : 7 }}" class="p-0">
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
                    $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                    $ticketId = $isTeamChair ? $ticket->id : $ticket->getKey();
                    $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                    $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?: $ticket->serviceType?->code);
                    $serviceName = $isTeamChair ? $ticket->serviceLabel() : ($ticket->service_type_name_snapshot ?: $ticket->serviceType?->name);
                    $requesterName = $isAllTickets ? ($ticket->requester_name_snapshot ?: $ticket->requester?->name ?: 'Pemohon belum tercatat') : null;
                    $assigneeName = $isAllTickets ? ($ticket->assignee?->name ?: 'Belum ditugaskan') : null;
                    $requesterAction = $requesterActions[$ticketId] ?? null;
                    $ticketDetailUrl = $isAllTickets
                        ? route('tickets.show', ['ticket' => $ticket, 'from' => 'all'])
                        : route('tickets.show', $ticketRouteTarget);
                @endphp
                <article class="px-5 py-4 transition-colors hover:bg-[color:var(--tm-brand-50)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $serviceCode ?: '—' }}</p>
                            <a href="{{ $ticketDetailUrl }}" class="mt-1 block text-xs font-semibold tabular-nums text-[color:var(--tm-brand-700)] hover:underline">{{ $ticketNumber }}</a>
                            <h3 class="mt-1 line-clamp-2 font-semibold leading-5 text-[color:var(--tm-text)]">{{ $ticket->subject }}</h3>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-status-badge :status="$ticket->status" />
                            <x-priority-badge :priority="$ticket->priority" />
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">Layanan</p>
                        <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-secondary)]">{{ $serviceCode ?: '—' }}{{ $serviceName ? ' · '.$serviceName : '' }}</p>
                    </div>

                    @if ($isAllTickets)
                        <div class="mt-3 grid gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-4 sm:grid-cols-2">
                            <div>
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">Pemohon</p>
                                <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-secondary)]">{{ $requesterName }}</p>
                            </div>
                            <div>
                                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.06em] text-[color:var(--tm-text-faint)]">Penanggung jawab</p>
                                <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-secondary)]">{{ $assigneeName }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-ghost min-h-10 px-3 py-2 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                        @if ($requesterAction)
                            <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-secondary min-h-10 px-3 py-2 text-xs" aria-label="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                        @endif
                    </div>
                    @if ($requesterAction)
                        <p class="mt-3 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-3 py-2 text-xs leading-5 text-[color:var(--tm-warning-700)]"><span class="font-semibold">Perlu tindakan:</span> {{ $requesterAction['description'] }}</p>
                    @endif
                </article>
            @empty
                <x-empty-state :title="$emptyTitle" :description="$emptyDescription" :action="$emptyAction" :actionLabel="$emptyActionLabel" />
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-5 py-3 text-xs text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="tabular-nums">
                @if ($tickets->total() > 0)
                    Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket
                @else
                    Tidak ada data tiket
                @endif
            </p>
            @if ($tickets->hasPages())
                <div>{{ $tickets->onEachSide(1)->links() }}</div>
            @endif
        </div>
    </section>
@endsection
