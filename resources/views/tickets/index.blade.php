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
@endphp

@section('title', $ticketListLabel.' — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', $ticketListLabel)

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>{{ $isAllTickets ? 'Pengelolaan tiket' : ($isTeamChair ? 'Pemantauan tim' : 'Pelacakan permintaan') }}</p>
            <h1 class="ui-page-title">{{ $ticketListLabel }}</h1>
            <p class="ui-page-description">{{ $pageDescription }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canViewQueue)
                <a href="{{ route('tickets.queue') }}" class="ui-btn ui-btn-secondary">Monitoring Tiket</a>
            @endif
            @if ($canCreateTicket)
                <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary">Buat tiket <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </div>

    <section class="ui-panel ui-list-panel mt-6 overflow-hidden" aria-labelledby="tickets-list-heading">
        <h2 id="tickets-list-heading" class="sr-only">{{ $isAllTickets ? 'Daftar seluruh tiket' : 'Daftar tiket' }}</h2>

        @if ($showFilters)
            <div class="ui-filter-bar">
                <form method="GET" action="{{ $listRoute }}" class="ui-filter-bar-form flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" role="search" aria-label="Cari tiket">
                    <div class="flex items-center gap-2 text-sm text-[#17212b]">
                        <label for="ticket-per-page" class="font-bold">Tampilkan</label>
                        <select id="ticket-per-page" name="per_page" class="ui-select ui-filter-control" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $pageSize)
                                <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                            @endforeach
                        </select>
                        <span>data</span>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <label for="ticket-search" class="shrink-0 text-sm font-bold text-[#17212b]">Cari:</label>
                        <input id="ticket-search" name="q" type="search" value="{{ $search }}" class="ui-input ui-filter-search w-full min-w-0 sm:w-72" placeholder="Nomor tiket atau judul" autocomplete="off">
                        <button type="submit" class="ui-btn ui-btn-secondary h-10 justify-center px-4">Cari</button>
                    </div>
                </form>

                @if ($search !== '')
                    <p class="mt-3 text-xs text-[#718088]">Menampilkan hasil untuk “<span class="font-bold text-[#35505b]">{{ $search }}</span>”. <a href="{{ $listRoute.'?per_page='.$perPage }}" class="font-extrabold text-[#147a79] underline decoration-[#a8e5dd] underline-offset-2 hover:text-[#0f5f5e]">Hapus pencarian</a></p>
                @endif
            </div>
        @endif

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-data-table w-full min-w-[76rem] border-collapse text-left">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, layanan, judul, status, prioritas, dan aksi</caption>
                <thead>
                    <tr class="border-b border-[#dfe8ec] bg-[#f1f5f9] text-xs font-semibold uppercase leading-4 tracking-[0.04em] text-[#5b7683]">
                        <th scope="col" class="w-[4rem] px-4 py-3">No</th>
                        <th scope="col" class="min-w-[21rem] px-4 py-3">No Tiket</th>
                        <th scope="col" class="min-w-[15rem] px-4 py-3">Layanan</th>
                        <th scope="col" class="min-w-[18rem] px-4 py-3">Judul</th>
                        @if ($isAllTickets)
                            <th scope="col" class="min-w-[13rem] px-4 py-3">Pemohon</th>
                        @endif
                        <th scope="col" class="min-w-[11rem] px-4 py-3">Status</th>
                        @if ($isAllTickets)
                            <th scope="col" class="min-w-[13rem] px-4 py-3">Penanggung jawab</th>
                        @endif
                        <th scope="col" class="min-w-[9rem] px-4 py-3">Prioritas</th>
                        <th scope="col" class="min-w-[13rem] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-[0.8125rem] leading-[1.125rem] text-[#17303c]">
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
                        <tr class="ui-data-table-row border-b border-[#eaf0f2] transition-colors hover:bg-[#fbfdfd]">
                            <td class="px-4 py-2.5 font-semibold text-[#5b7683]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5">
                                <a href="{{ $ticketDetailUrl }}" class="text-[0.8125rem] font-semibold leading-[1.125rem] text-[#1d5d72] hover:text-[#0a87c9] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2.5">
                                <span class="block text-xs font-extrabold text-[#1d5d72]">{{ $serviceCode ?: '—' }}</span>
                                <span class="mt-1 block max-w-[18rem] leading-5 text-[#51707c]">{{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                            </td>
                            <td class="px-4 py-2.5"><div class="max-w-[28rem] truncate font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</div></td>
                            @if ($isAllTickets)
                                <td class="px-4 py-2.5 text-[#51707c]">{{ $requesterName }}</td>
                            @endif
                            <td class="px-4 py-2.5"><x-status-badge :status="$ticket->status" /></td>
                            @if ($isAllTickets)
                                <td class="px-4 py-2.5 text-[#51707c]">{{ $assigneeName }}</td>
                            @endif
                            <td class="px-4 py-2.5"><x-priority-badge :priority="$ticket->priority" /></td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-ghost min-h-9 px-3 py-1.5 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                                    @if ($requesterAction)
                                        <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-secondary min-h-9 px-3 py-1.5 text-xs" aria-label="{{ $requesterAction['description'] }}" title="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                                    @endif
                                </div>
                                @if ($requesterAction)
                                    <span class="mt-2 block text-[0.65rem] font-extrabold uppercase tracking-wide text-[#9a6700]">Perlu tindakan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAllTickets ? 9 : 7 }}" class="px-4 py-14 text-center text-[#718088]">
                                <p class="text-base font-extrabold text-[#35505b]">{{ $search !== '' ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Belum ada tiket pada daftar ini.' }}</p>
                                <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $search !== '' ? 'Coba gunakan nomor tiket atau judul yang berbeda.' : ($isAllTickets ? 'Tiket yang dibuat akan muncul di sini setelah tercatat.' : ($isTeamChair ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.')) }}</p>
                                @if ($search !== '')
                                    <a href="{{ $listRoute.'?per_page='.$perPage }}" class="ui-action-link mt-4 inline-flex">Hapus pencarian</a>
                                @elseif ($canCreateTicket)
                                    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ui-mobile-ticket-list divide-y divide-[#eaf0f2] md:hidden">
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
                <article class="ui-mobile-ticket-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $serviceCode ?: '—' }}</p>
                            <a href="{{ $ticketDetailUrl }}" class="mt-1 block text-xs font-extrabold text-[#1d5d72] hover:underline">{{ $ticketNumber }}</a>
                            <h3 class="mt-1 line-clamp-2 font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</h3>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-status-badge :status="$ticket->status" />
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Layanan</p>
                        <p class="mt-1 text-sm leading-5 text-[#172d45]">{{ $serviceCode ?: '—' }}{{ $serviceName ? ' · '.$serviceName : '' }}</p>
                    </div>

                    @if ($isAllTickets)
                        <div class="ui-mobile-ticket-facts mt-3 grid gap-3 rounded-lg border border-[#e5eaed] p-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Pemohon</p>
                                <p class="mt-1 text-sm leading-5 text-[#35505b]">{{ $requesterName }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Penanggung jawab</p>
                                <p class="mt-1 text-sm leading-5 text-[#35505b]">{{ $assigneeName }}</p>
                            </div>
                        </div>
                    @endif

                    <x-priority-badge :priority="$ticket->priority" class="mt-3" />

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-ghost min-h-10 px-3 py-2 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                        @if ($requesterAction)
                            <a href="{{ $ticketDetailUrl }}" class="ui-btn ui-btn-secondary min-h-10 px-3 py-2 text-xs" aria-label="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                        @endif
                    </div>
                    @if ($requesterAction)
                        <p class="mt-3 text-xs leading-5 text-[#9a6700]"><span class="font-extrabold">Perlu tindakan:</span> {{ $requesterAction['description'] }}</p>
                    @endif
                </article>
            @empty
                <div class="px-5 py-12 text-center text-[#718088]">
                    <p class="text-base font-extrabold text-[#35505b]">{{ $search !== '' ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Belum ada tiket pada daftar ini.' }}</p>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $search !== '' ? 'Coba gunakan nomor tiket atau judul yang berbeda.' : ($isAllTickets ? 'Tiket yang dibuat akan muncul di sini setelah tercatat.' : ($isTeamChair ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.')) }}</p>
                    @if ($search !== '')
                        <a href="{{ $listRoute.'?per_page='.$perPage }}" class="ui-action-link mt-4 inline-flex">Hapus pencarian</a>
                    @elseif ($canCreateTicket)
                        <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="ui-list-footer flex flex-col gap-3 border-t border-[#e5eaed] px-4 py-3 text-[0.78rem] text-[#6b818a] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>
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
