@extends('layouts.app')

@php
    $isTeamChair = $isTeamChair ?? false;
    $hasPersonalScope = $canAccessTickets ?? false;
    $showFilters = $showFilters ?? ! $isTeamChair;
    $search = $search ?? '';
    $perPage = $perPage ?? 10;
    $requesterActions = $requesterActions ?? [];
    $canCreateTicket = auth()->user()->can('create', \App\Models\Ticket::class);
    $ticketListLabel = $isTeamChair && $hasPersonalScope
        ? 'Tiket saya dan tim'
        : ($isTeamChair ? 'Tiket tim' : 'Tiket saya');
    $pageDescription = $isTeamChair
        ? 'Pantau nomor tiket, layanan, status, dan detail penanganan anggota tim dalam mode baca saja.'
        : 'Lihat nomor tiket, layanan, status, dan tindakan yang perlu Anda selesaikan.';
@endphp

@section('title', $ticketListLabel.' — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', $ticketListLabel)

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>{{ $isTeamChair ? 'Pemantauan tim' : 'Pelacakan permintaan' }}</p>
            <h1 class="ui-page-title">{{ $ticketListLabel }}</h1>
            <p class="ui-page-description">{{ $pageDescription }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canViewQueue)
                <a href="{{ route('tickets.queue') }}" class="ui-btn ui-btn-secondary">Antrean Tier 1</a>
            @endif
            @if ($canCreateTicket)
                <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary">Buat tiket <span aria-hidden="true">→</span></a>
            @endif
        </div>
    </div>

    <section class="mt-8 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="tickets-list-heading">
        <div class="flex flex-col gap-2 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="tickets-list-heading" class="text-xl font-extrabold tracking-tight">Daftar tiket</h2>
            </div>
            @if ($search !== '')
                <span class="inline-flex w-fit items-center rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white">Pencarian aktif</span>
            @endif
        </div>

        @if ($showFilters)
            <div class="border-b border-[#e5eaed] px-5 py-5 sm:px-8">
                <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" role="search" aria-label="Cari tiket">
                    <div class="flex items-center gap-2 text-sm text-[#17212b]">
                        <label for="ticket-per-page" class="font-bold">Tampilkan</label>
                        <select id="ticket-per-page" name="per_page" class="h-10 rounded-lg border border-[#d7e0e4] bg-white px-3 text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15" onchange="this.form.submit()">
                            @foreach ([10, 25, 50] as $pageSize)
                                <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                            @endforeach
                        </select>
                        <span>data</span>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <label for="ticket-search" class="shrink-0 text-sm font-bold text-[#17212b]">Cari:</label>
                        <input id="ticket-search" name="q" type="search" value="{{ $search }}" class="h-10 w-full min-w-0 rounded-lg border border-[#d7e0e4] bg-[#f8fafb] px-3 text-sm text-[#17212b] outline-none placeholder:text-[#9baab0] focus:border-[#0a87c9] focus:bg-white focus:ring-2 focus:ring-[#0a87c9]/15 sm:w-72" placeholder="Nomor tiket atau judul" autocomplete="off">
                        <button type="submit" class="ui-btn ui-btn-secondary h-10 justify-center px-4">Cari</button>
                    </div>
                </form>

                @if ($search !== '')
                    <p class="mt-3 text-xs text-[#718088]">Menampilkan hasil untuk “<span class="font-bold text-[#35505b]">{{ $search }}</span>”. <a href="{{ route('tickets.index', ['per_page' => $perPage]) }}" class="font-extrabold text-[#147a79] underline decoration-[#a8e5dd] underline-offset-2 hover:text-[#0f5f5e]">Hapus pencarian</a></p>
                @endif
            </div>
        @endif

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-[760px] w-full border-collapse text-left text-sm">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, layanan, status, dan aksi</caption>
                <thead class="bg-[#fbfcfd] text-[#34495a]">
                    <tr>
                        <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                        <th scope="col" class="min-w-[21rem] border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">No Tiket</th>
                        <th scope="col" class="min-w-[15rem] border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Layanan</th>
                        <th scope="col" class="min-w-[11rem] border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Status</th>
                        <th scope="col" class="min-w-[17rem] border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
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
                            $requesterAction = $requesterActions[$ticketId] ?? null;
                        @endphp
                        <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                            <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="border-b border-[#e5eaed] px-4 py-5">
                                <span class="block text-xs font-extrabold text-[#1d5d72]">{{ $ticketNumber }}</span>
                                <span class="mt-1 block max-w-[28rem] font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</span>
                            </td>
                            <td class="border-b border-[#e5eaed] px-4 py-5">
                                <span class="block text-xs font-extrabold text-[#1d5d72]">{{ $serviceCode ?: '—' }}</span>
                                <span class="mt-1 block max-w-[18rem] leading-5 text-[#172d45]">{{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                            </td>
                            <td class="border-b border-[#e5eaed] px-4 py-5"><x-status-badge :status="$ticket->status" /></td>
                            <td class="border-b border-[#e5eaed] px-4 py-5 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="ui-btn ui-btn-ghost min-h-9 px-3 py-1.5 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                                    @if ($requesterAction)
                                        <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="ui-btn ui-btn-secondary min-h-9 px-3 py-1.5 text-xs" aria-label="{{ $requesterAction['description'] }}" title="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                                    @endif
                                </div>
                                @if ($requesterAction)
                                    <span class="mt-2 block text-[0.65rem] font-extrabold uppercase tracking-wide text-[#9a6700]">Perlu tindakan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-[#718088]">
                                <p class="text-base font-extrabold text-[#35505b]">{{ $search !== '' ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Belum ada tiket pada daftar ini.' }}</p>
                                <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $search !== '' ? 'Coba gunakan nomor tiket atau judul yang berbeda.' : ($isTeamChair ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.') }}</p>
                                @if ($search !== '')
                                    <a href="{{ route('tickets.index', ['per_page' => $perPage]) }}" class="ui-action-link mt-4 inline-flex">Hapus pencarian</a>
                                @elseif ($canCreateTicket)
                                    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($tickets as $ticket)
                @php
                    $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                    $ticketId = $isTeamChair ? $ticket->id : $ticket->getKey();
                    $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                    $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?: $ticket->serviceType?->code);
                    $serviceName = $isTeamChair ? $ticket->serviceLabel() : ($ticket->service_type_name_snapshot ?: $ticket->serviceType?->name);
                    $requesterAction = $requesterActions[$ticketId] ?? null;
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }}</p>
                            <p class="mt-1 text-xs font-extrabold text-[#1d5d72]">{{ $ticketNumber }}</p>
                            <h3 class="mt-1 line-clamp-2 font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</h3>
                        </div>
                        <x-status-badge :status="$ticket->status" class="shrink-0" />
                    </div>

                    <div class="mt-4 rounded-lg bg-[#f8fafb] p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Layanan</p>
                        <p class="mt-1 text-sm leading-5 text-[#172d45]">{{ $serviceCode ?: '—' }}{{ $serviceName ? ' · '.$serviceName : '' }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="ui-btn ui-btn-ghost min-h-10 px-3 py-2 text-xs" aria-label="Lihat detail {{ $ticketNumber }}">Lihat</a>
                        @if ($requesterAction)
                            <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="ui-btn ui-btn-secondary min-h-10 px-3 py-2 text-xs" aria-label="{{ $requesterAction['description'] }}">{{ $requesterAction['label'] }}</a>
                        @endif
                    </div>
                    @if ($requesterAction)
                        <p class="mt-3 text-xs leading-5 text-[#9a6700]"><span class="font-extrabold">Perlu tindakan:</span> {{ $requesterAction['description'] }}</p>
                    @endif
                </article>
            @empty
                <div class="px-5 py-12 text-center text-[#718088]">
                    <p class="text-base font-extrabold text-[#35505b]">{{ $search !== '' ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Belum ada tiket pada daftar ini.' }}</p>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $search !== '' ? 'Coba gunakan nomor tiket atau judul yang berbeda.' : ($isTeamChair ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.') }}</p>
                    @if ($search !== '')
                        <a href="{{ route('tickets.index', ['per_page' => $perPage]) }}" class="ui-action-link mt-4 inline-flex">Hapus pencarian</a>
                    @elseif ($canCreateTicket)
                        <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[#e5eaed] px-5 py-4 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>
                @if ($tickets->total() > 0)
                    Menampilkan {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket
                @else
                    Tidak ada data tiket
                @endif
            </p>
            @if ($tickets->hasPages())
                <div>{{ $tickets->links() }}</div>
            @endif
        </div>
    </section>
@endsection
