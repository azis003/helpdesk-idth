@extends('layouts.app')

@php
    $canCreateTicket = auth()->user()->can('create', \App\Models\Ticket::class);

    $priorityDots = [
        'kritis' => 'bg-[#be123c]',
        'tinggi' => 'bg-[#e11d48]',
        'sedang' => 'bg-[#e4a72c]',
        'rendah' => 'bg-[#2bb8aa]',
    ];

    $tabs = [
        \App\Services\RequesterTicketList::TAB_ALL => ['label' => 'Semua', 'alert' => false],
        \App\Services\RequesterTicketList::TAB_ACTIVE => ['label' => 'Aktif', 'alert' => false],
        \App\Services\RequesterTicketList::TAB_ACTION => ['label' => 'Perlu Tindakan Saya', 'alert' => true],
        \App\Services\RequesterTicketList::TAB_DONE => ['label' => 'Selesai', 'alert' => false],
    ];

    $filterQuery = array_filter([
        'q' => $search,
        'class' => $serviceClass,
        'status' => $statusFilter,
        'from' => $dateFrom,
        'to' => $dateTo,
        'per_page' => $perPage === 10 ? null : $perPage,
    ], static fn ($value): bool => $value !== null && $value !== '');

    $controlClass = 'h-10 w-full rounded-lg border border-[#d7e0e4] bg-white text-sm text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15';
@endphp

@section('title', 'Tiket saya — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', 'Tiket saya')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Pelacakan permintaan</p>
            <h1 class="ui-page-title">Tiket Saya</h1>
            <p class="ui-page-description">Semua tiket yang Anda ajukan atau diajukan atas nama Anda.</p>
        </div>
        @if ($canCreateTicket)
            <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Buat Tiket Baru
            </a>
        @endif
    </div>

    <nav class="mt-7 flex flex-wrap items-center gap-2 border-b border-[#dfe8ec] pb-4" aria-label="Saringan cepat tiket saya">
        @foreach ($tabs as $tabKey => $tab)
            @php
                $isActiveTab = $activeTab === $tabKey;
                $tabCount = $tabCounts[$tabKey] ?? 0;
            @endphp
            <a href="{{ route('tickets.index', array_merge($filterQuery, ['tab' => $tabKey])) }}"
                @class([
                    'inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-xs font-extrabold transition',
                    'border-transparent bg-[#1d5d72] text-white shadow-sm' => $isActiveTab,
                    'border-[#dfe8ec] bg-white text-[#5b7683] hover:border-[#b9e5f2] hover:bg-[#f1fbfe] hover:text-[#1d5d72]' => ! $isActiveTab,
                ])
                @if ($isActiveTab) aria-current="page" @endif>
                <span>{{ $tab['label'] }}</span>
                @if ($tab['alert'] && $tabCount > 0)
                    <span class="h-2 w-2 rounded-full bg-[#e11d48]" aria-hidden="true"></span>
                @endif
                <span class="opacity-70">({{ $tabCount }})</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('tickets.index') }}" class="mt-6 flex flex-wrap items-center gap-3 rounded-xl border border-[#dfe8ec] bg-white p-4 shadow-[0_1px_3px_rgba(33,57,67,0.08)]" role="search" aria-label="Saring tiket saya">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">

        <div class="relative min-w-[15rem] flex-1">
            <button type="submit" class="absolute left-2.5 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded text-[#9aaeb6] transition hover:text-[#1d5d72]" aria-label="Cari tiket">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 17a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13ZM20 20l-4.3-4.3" /></svg>
            </button>
            <label for="ticket-search" class="sr-only">Cari nomor tiket atau kata kunci</label>
            <input id="ticket-search" name="q" type="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nomor tiket atau kata kunci" class="{{ $controlClass }} pl-9 pr-3 placeholder:text-[#9baab0]">
        </div>

        <div class="min-w-[11rem]">
            <label for="ticket-class" class="sr-only">Jenis layanan</label>
            <select id="ticket-class" name="class" onchange="this.form.submit()" class="{{ $controlClass }} px-3">
                <option value="">Semua Jenis Layanan</option>
                @foreach ($ticketClassOptions as $classKey => $classOption)
                    <option value="{{ $classKey }}" @selected($serviceClass === $classKey)>{{ $classOption }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[10rem]">
            <label for="ticket-status" class="sr-only">Status tiket</label>
            <select id="ticket-status" name="status" onchange="this.form.submit()" class="{{ $controlClass }} px-3">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $statusKey => $statusLabel)
                    <option value="{{ $statusKey }}" @selected($statusFilter === $statusKey)>{{ $statusLabel }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex h-10 min-w-[17.5rem] items-center gap-2 rounded-lg border border-[#d7e0e4] bg-white px-3 focus-within:border-[#0a87c9] focus-within:ring-2 focus-within:ring-[#0a87c9]/15">
            <svg class="h-4 w-4 shrink-0 text-[#9aaeb6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6.5h14v13H5zM8.5 4v4M15.5 4v4M5 10.5h14" /></svg>
            <label for="ticket-date-from" class="sr-only">Tanggal pengajuan mulai</label>
            <input id="ticket-date-from" name="from" type="date" value="{{ $dateFrom }}" onchange="this.form.submit()" class="w-full min-w-0 border-0 bg-transparent p-0 text-sm text-[#35505b] outline-none focus:ring-0">
            <span class="text-[#9aaeb6]" aria-hidden="true">–</span>
            <label for="ticket-date-to" class="sr-only">Tanggal pengajuan akhir</label>
            <input id="ticket-date-to" name="to" type="date" value="{{ $dateTo }}" onchange="this.form.submit()" class="w-full min-w-0 border-0 bg-transparent p-0 text-sm text-[#35505b] outline-none focus:ring-0">
        </div>

        @if ($hasFilters)
            <a href="{{ route('tickets.index', ['tab' => $activeTab]) }}" class="ui-action-link px-1">Atur Ulang</a>
        @else
            <span class="px-1 text-[0.78rem] font-extrabold text-[#a9b8be]">Atur Ulang</span>
        @endif
    </form>

    <section class="mt-6 overflow-hidden rounded-xl border border-[#dfe8ec] bg-white shadow-[0_1px_3px_rgba(33,57,67,0.08)]" aria-labelledby="requester-tickets-heading">
        <h2 id="requester-tickets-heading" class="sr-only">Daftar tiket saya</h2>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-[54rem] border-collapse text-left">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, jenis layanan, deskripsi, status, dan prioritas</caption>
                <thead>
                    <tr class="border-b border-[#dfe8ec] bg-[#f1f5f9] text-[0.66rem] font-extrabold uppercase tracking-wide text-[#5b7683]">
                        <th scope="col" class="w-[4rem] px-4 py-3">No</th>
                        <th scope="col" class="w-[11rem] px-4 py-3">No Tiket</th>
                        <th scope="col" class="w-[8.5rem] px-4 py-3">Layanan</th>
                        <th scope="col" class="min-w-[16rem] px-4 py-3">Deskripsi</th>
                        <th scope="col" class="w-[12rem] px-4 py-3">Status</th>
                        <th scope="col" class="w-[9rem] px-4 py-3">Prioritas</th>
                    </tr>
                </thead>
                <tbody class="text-[0.82rem] text-[#17303c]">
                    @forelse ($tickets as $ticket)
                        @php
                            $status = $ticket->status;
                            $isClosed = $status?->isClosed() ?? false;
                            $needsAction = $status?->needsRequesterAction() ?? false;
                            $action = $requesterActions[$ticket->getKey()] ?? null;
                            $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                            $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                            $serviceName = $ticket->service_type_name_snapshot ?: $ticket->serviceType?->name;
                            $serviceTooltip = trim(($serviceCode ?: '').' · '.($serviceName ?: ''), " ·\t\n");
                            $serviceTooltip = $serviceTooltip !== '' ? $serviceTooltip : 'Layanan belum tersedia';
                            $rowHint = $needsAction && $action ? $action['description'] : $serviceTooltip;
                        @endphp
                        <tr @class([
                                'border-b border-[#eaf0f2] transition-colors',
                                'border-l-[3px] border-l-[#0a87c9] bg-[#f1fbfe] hover:bg-[#e4f4fc]' => $needsAction,
                                'hover:bg-[#fbfdfd]' => ! $needsAction,
                                'opacity-80' => $isClosed,
                            ]) title="{{ $rowHint }}">
                            <td class="px-4 py-2.5 font-semibold text-[#5b7683]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-[0.78rem] font-extrabold text-[#1d5d72] hover:text-[#0a87c9] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-[#51707c]">{{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <div @class([
                                        'max-w-[26rem] truncate',
                                        'font-bold text-[#112b49]' => $needsAction,
                                        'text-[#78909a] line-through' => $isClosed,
                                    ])>{{ $ticket->subject }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2.5"><x-status-badge :status="$ticket->status" /></td>
                            <td class="whitespace-nowrap px-4 py-2.5">
                                <span class="inline-flex items-center gap-1.5 font-semibold text-[#35505b]">
                                    <span class="h-2 w-2 rounded-full {{ $priorityDots[$ticket->priority?->value] ?? 'bg-[#c3ced3]' }}" aria-hidden="true"></span>
                                    {{ $ticket->priority?->label() ?? 'Belum ditentukan' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-[#718088]">
                                <p class="text-base font-extrabold text-[#35505b]">{{ $hasFilters ? 'Tidak ada tiket yang cocok dengan saringan.' : 'Belum ada tiket pada daftar ini.' }}</p>
                                <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $hasFilters ? 'Coba ubah kata kunci, jenis layanan, status, atau rentang tanggal.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.' }}</p>
                                @if ($hasFilters)
                                    <a href="{{ route('tickets.index', ['tab' => $activeTab]) }}" class="ui-action-link mt-4 inline-flex">Atur ulang saringan</a>
                                @elseif ($canCreateTicket)
                                    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#eaf0f2] md:hidden">
            @forelse ($tickets as $ticket)
                @php
                    $status = $ticket->status;
                    $isClosed = $status?->isClosed() ?? false;
                    $needsAction = $status?->needsRequesterAction() ?? false;
                    $action = $requesterActions[$ticket->getKey()] ?? null;
                    $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                @endphp
                <article @class(['p-5', 'border-l-[3px] border-l-[#0a87c9] bg-[#f1fbfe]' => $needsAction])>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $ticketClassLabels[$ticket->ticket_class ?? ''] ?? '—' }}</p>
                            <a href="{{ route('tickets.show', $ticket) }}" class="mt-1 block text-xs font-extrabold text-[#1d5d72] hover:underline">{{ $ticketNumber }}</a>
                            <h3 @class(['mt-1 line-clamp-2 font-bold leading-5 text-[#112b49]', 'text-[#78909a] line-through' => $isClosed])>{{ $ticket->subject }}</h3>
                        </div>
                        <x-status-badge :status="$ticket->status" />
                    </div>

                    <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-[#35505b]">
                        <span class="h-2 w-2 rounded-full {{ $priorityDots[$ticket->priority?->value] ?? 'bg-[#c3ced3]' }}" aria-hidden="true"></span>
                        Prioritas {{ $ticket->priority?->label() ?? 'belum ditentukan' }}
                    </div>

                    @if ($needsAction && $action)
                        <a href="{{ route('tickets.show', $ticket) }}" class="ui-btn ui-btn-secondary mt-4 px-3 py-2 text-xs">{{ $action['label'] }}</a>
                        <p class="mt-2 text-xs leading-5 text-[#9a6700]">{{ $action['description'] }}</p>
                    @endif
                </article>
            @empty
                <div class="px-5 py-12 text-center text-[#718088]">
                    <p class="text-base font-extrabold text-[#35505b]">{{ $hasFilters ? 'Tidak ada tiket yang cocok dengan saringan.' : 'Belum ada tiket pada daftar ini.' }}</p>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6">{{ $hasFilters ? 'Coba ubah kata kunci, jenis layanan, status, atau rentang tanggal.' : 'Tiket yang Anda ajukan akan muncul di sini setelah dikirim.' }}</p>
                    @if ($hasFilters)
                        <a href="{{ route('tickets.index', ['tab' => $activeTab]) }}" class="ui-action-link mt-4 inline-flex">Atur ulang saringan</a>
                    @elseif ($canCreateTicket)
                        <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary mt-5">Buat tiket pertama</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="flex flex-col gap-3 border-t border-[#e5eaed] px-4 py-3 text-[0.78rem] text-[#6b818a] sm:flex-row sm:items-center sm:justify-between">
            <p>
                @if ($tickets->total() > 0)
                    Menampilkan <span class="font-extrabold text-[#35505b]">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span> dari <span class="font-extrabold text-[#35505b]">{{ $tickets->total() }}</span> tiket
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
                    <select id="ticket-per-page" name="per_page" onchange="this.form.submit()" class="h-8 rounded-lg border border-[#d7e0e4] bg-white px-2 text-[0.78rem] text-[#35505b] outline-none focus:border-[#0a87c9] focus:ring-2 focus:ring-[#0a87c9]/15">
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
