@extends('layouts.app')

@section('title', 'Monitoring Tiket — '.$branding['application_name'])
@section('header_kicker', 'Ruang kerja tiket')
@section('header_title', 'Monitoring Tiket')

@php
    $activeTab = $activeTab ?? 'queue';
    $ticketCount = $tickets->total();
    $queueCount = $queueCount ?? ($activeTab === 'queue' ? $ticketCount : 0);
    $mineCount = $mineCount ?? ($activeTab === 'mine' ? $ticketCount : 0);
    $assignedCount = $assignedCount ?? ($activeTab === 'assigned' ? $ticketCount : 0);
    $completedCount = $completedCount ?? ($activeTab === 'completed' ? $ticketCount : 0);
    $canViewQueue = $canViewQueue ?? false;
    $canViewAssigned = $canViewAssigned ?? false;
    $tabs = [
        'queue' => [
            'label' => 'Antrian Tiket',
            'count' => $queueCount,
            'visible' => $canViewQueue,
            'empty' => 'Belum ada tiket baru dalam antrian.',
        ],
        'mine' => [
            'label' => 'Tiket Saya',
            'count' => $mineCount,
            'visible' => $canViewAssigned,
            'empty' => 'Belum ada tiket yang sedang Anda kerjakan.',
        ],
        'assigned' => [
            'label' => 'Tiket Assign',
            'count' => $assignedCount,
            'visible' => $canViewQueue,
            'empty' => 'Belum ada tiket yang di-assign kepada teknisi.',
        ],
        'completed' => [
            'label' => 'Tiket Selesai',
            'count' => $completedCount,
            'visible' => $canViewQueue,
            'empty' => 'Belum ada tiket yang ditutup atau dibatalkan.',
        ],
    ];
    $activeTabMeta = $tabs[$activeTab] ?? $tabs['queue'];
    $priorityDots = [
        'kritis' => 'bg-[#be123c]',
        'tinggi' => 'bg-[#e11d48]',
        'sedang' => 'bg-[#e4a72c]',
        'rendah' => 'bg-[#2bb8aa]',
    ];
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff0b9]" aria-hidden="true"></span>Ruang kerja</p>
            <h1 class="ui-page-title">Monitoring Tiket</h1>
            <p class="ui-page-description">Pantau tiket baru, pekerjaan Anda, penugasan teknisi, dan tiket yang telah selesai.</p>
        </div>
    </div>

    <nav class="mt-7 flex flex-wrap items-center gap-2 border-b border-[#dfe8ec] pb-4" aria-label="Filter Monitoring Tiket">
        @foreach ($tabs as $tabKey => $tab)
            @continue(! $tab['visible'])
            @php
                $isActiveTab = $activeTab === $tabKey;
            @endphp
            <a href="{{ route('tickets.queue', ['tab' => $tabKey]) }}" class="inline-flex min-h-9 items-center gap-1.5 rounded-full border px-4 py-1.5 text-xs font-extrabold transition focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#2bb8aa] {{ $isActiveTab ? 'border-transparent bg-[#1d5d72] text-white shadow-sm' : 'border-[#dfe8ec] bg-white text-[#5b7683] hover:border-[#b9e5f2] hover:bg-[#f1fbfe] hover:text-[#1d5d72]' }}" @if ($isActiveTab) aria-current="page" @endif>
                <span>{{ $tab['label'] }}</span>
                <span class="opacity-70">({{ $tab['count'] }})</span>
            </a>
        @endforeach
    </nav>

    <section class="mt-4 overflow-hidden rounded-xl border border-[#dfe8ec] bg-white shadow-[0_1px_3px_rgba(33,57,67,0.08)]" aria-labelledby="queue-heading">
        <h2 id="queue-heading" class="sr-only">{{ $activeTabMeta['label'] }}</h2>

        <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[54rem] border-collapse text-left">
                    <caption class="sr-only">Daftar tiket dengan nomor tiket, layanan, judul, status, dan prioritas</caption>
                    <thead>
                        <tr class="border-b border-[#dfe8ec] bg-[#f1f5f9] text-xs font-semibold uppercase leading-4 tracking-[0.04em] text-[#5b7683]">
                            <th scope="col" class="w-[4rem] px-4 py-3">No</th>
                            <th scope="col" class="w-[12rem] px-4 py-3">No Tiket</th>
                            <th scope="col" class="w-[14rem] px-4 py-3">Layanan</th>
                            <th scope="col" class="min-w-[18rem] px-4 py-3">Judul</th>
                            <th scope="col" class="w-[8.5rem] px-4 py-3">Status</th>
                            <th scope="col" class="w-[9rem] px-4 py-3">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="text-[0.8125rem] leading-[1.125rem] text-[#17303c]">
                        @forelse ($tickets as $ticket)
                            @php
                                $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                                $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                                $serviceName = $ticket->service_type_name_snapshot ?: $ticket->serviceType?->name;
                            @endphp
                            <tr class="border-b border-[#eaf0f2] transition-colors hover:bg-[#fbfdfd]">
                                <td class="px-4 py-2.5 font-semibold text-[#5b7683]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-2.5">
                                    <a href="{{ route('tickets.show', ['ticket' => $ticket, 'from' => $activeTab]) }}" class="text-[0.8125rem] font-semibold leading-[1.125rem] text-[#1d5d72] hover:text-[#0a87c9] hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#2bb8aa]" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="block text-xs font-extrabold text-[#1d5d72]">{{ $serviceCode ?: '—' }}</span>
                                    <span class="mt-1 block max-w-[12rem] leading-5 text-[#51707c]">{{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                                </td>
                                <td class="px-4 py-2.5"><div class="max-w-[24rem] truncate font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</div></td>
                                <td class="whitespace-nowrap px-4 py-2.5"><x-status-badge :status="$ticket->status" /></td>
                                <td class="whitespace-nowrap px-4 py-2.5">
                                    <span class="ui-badge inline-flex items-center gap-1.5 text-[#35505b]">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $priorityDots[$ticket->priority?->value] ?? 'bg-[#c3ced3]' }}" aria-hidden="true"></span>
                                        {{ $ticket->priority?->label() ?? 'Belum ditentukan' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center text-[#718088]">
                                    <p class="text-base font-extrabold text-[#35505b]">{{ $activeTabMeta['empty'] }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-[#eaf0f2] md:hidden">
                @forelse ($tickets as $ticket)
                    @php
                        $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                        $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                    @endphp
                    <article class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $serviceCode ?: '—' }}</p>
                                <a href="{{ route('tickets.show', ['ticket' => $ticket, 'from' => $activeTab]) }}" class="mt-1 block text-xs font-extrabold text-[#1d5d72] hover:underline focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#2bb8aa]">{{ $ticketNumber }}</a>
                                <h3 class="mt-1 line-clamp-2 font-bold leading-5 text-[#112b49]">{{ $ticket->subject }}</h3>
                            </div>
                            <x-status-badge :status="$ticket->status" />
                        </div>

                        <div class="ui-badge mt-3 inline-flex items-center gap-1.5 text-[#35505b]">
                            <span class="h-1.5 w-1.5 rounded-full {{ $priorityDots[$ticket->priority?->value] ?? 'bg-[#c3ced3]' }}" aria-hidden="true"></span>
                            Prioritas {{ $ticket->priority?->label() ?? 'belum ditentukan' }}
                        </div>

                    </article>
                @empty
                    <div class="px-5 py-12 text-center text-[#718088]">
                        <p class="text-base font-extrabold text-[#35505b]">{{ $activeTabMeta['empty'] }}</p>
                    </div>
                @endforelse
            </div>

        @if ($ticketCount > 0)
        <div class="flex flex-col gap-3 border-t border-[#e5eaed] px-4 py-3 text-[0.78rem] text-[#6b818a] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>
                Menampilkan <span class="font-extrabold text-[#35505b]">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span> dari <span class="font-extrabold text-[#35505b]">{{ $ticketCount }}</span> tiket
            </p>
            @if ($tickets->hasPages())
                <div>{{ $tickets->onEachSide(1)->links() }}</div>
            @endif
        </div>
        @endif
    </section>
@endsection
