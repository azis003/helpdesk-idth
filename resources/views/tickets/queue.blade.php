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
    $canViewCompleted = $canViewCompleted ?? false;
    $isTechnicianOnly = $isTechnicianOnly ?? false;
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
            'visible' => $canViewCompleted,
            'empty' => $isTechnicianOnly
                ? 'Belum ada tiket yang Anda selesaikan.'
                : 'Belum ada tiket yang ditutup atau dibatalkan.',
        ],
    ];
    $activeTabMeta = $tabs[$activeTab] ?? $tabs['queue'];
@endphp

@section('content')
    <x-page-header
        eyebrow="Ruang kerja"
        title="Monitoring Tiket"
        description="Pantau tiket baru, pekerjaan Anda, penugasan teknisi, dan tiket yang telah selesai."
    />

    <nav class="mt-7 flex flex-wrap items-center gap-2 border-b border-[color:var(--tm-border)] pb-4" aria-label="Filter Monitoring Tiket">
        @foreach ($tabs as $tabKey => $tab)
            @continue(! $tab['visible'])
            @php
                $isActiveTab = $activeTab === $tabKey;
            @endphp
            <a href="{{ route('tickets.queue', ['tab' => $tabKey]) }}" class="inline-flex min-h-9 items-center gap-2 rounded-[var(--tm-r-full)] border px-4 py-1.5 text-xs font-extrabold transition {{ $isActiveTab ? 'border-transparent bg-[color:var(--tm-brand-600)] text-white shadow-[var(--tm-sh-sm)]' : 'border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]' }}" @if ($isActiveTab) aria-current="page" @endif>
                <span>{{ $tab['label'] }}</span>
                <span class="rounded-[var(--tm-r-full)] px-1.5 py-0.5 text-[0.68rem] tabular-nums {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-[color:var(--tm-n-100)] text-[color:var(--tm-text-muted)]' }}">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <section class="ui-panel mt-4 overflow-hidden" aria-labelledby="queue-heading">
        <h2 id="queue-heading" class="sr-only">{{ $activeTabMeta['label'] }}</h2>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table w-full min-w-[54rem]">
                <caption class="sr-only">Daftar tiket dengan nomor tiket, layanan, judul, status, dan prioritas</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-[4rem]">No</th>
                        <th scope="col" class="w-[12rem]">No Tiket</th>
                        <th scope="col" class="w-[14rem]">Layanan</th>
                        <th scope="col" class="min-w-[18rem]">Judul</th>
                        <th scope="col" class="w-[8.5rem]">Status</th>
                        <th scope="col" class="w-[9rem]">Prioritas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        @php
                            $ticketNumber = $ticket->ticket_number ?: 'Tiket #'.$ticket->getKey();
                            $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                            $serviceName = $ticket->service_type_name_snapshot ?: $ticket->serviceType?->name;
                        @endphp
                        <tr>
                            <td class="tabular-nums font-semibold text-[color:var(--tm-text-muted)]">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('tickets.show', ['ticket' => $ticket, 'from' => $activeTab]) }}" class="font-semibold text-[color:var(--tm-brand-700)] hover:underline" aria-label="Lihat detail {{ $ticketNumber }}">{{ $ticketNumber }}</a>
                            </td>
                            <td>
                                <span class="block text-xs font-extrabold text-[color:var(--tm-brand-700)]">{{ $serviceCode ?: '—' }}</span>
                                <span class="mt-1 block max-w-[12rem] leading-5 text-[color:var(--tm-text-muted)]">{{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                            </td>
                            <td><div class="max-w-[24rem] truncate font-bold leading-5 text-[color:var(--tm-text)]">{{ $ticket->subject }}</div></td>
                            <td class="whitespace-nowrap"><x-status-badge :status="$ticket->status" /></td>
                            <td class="whitespace-nowrap"><x-priority-badge :priority="$ticket->priority" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10">
                                <x-empty-state
                                    :title="$activeTabMeta['empty']"
                                    description="Tiket akan muncul di sini begitu tercatat pada tab ini."
                                />
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
                    $serviceCode = $ticket->service_type_code_snapshot ?: $ticket->serviceType?->code;
                @endphp
                <article class="p-5 transition-colors hover:bg-[color:var(--tm-n-25)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-muted)]">No. {{ ($tickets->firstItem() ?? 1) + $loop->index }} · {{ $serviceCode ?: '—' }}</p>
                            <a href="{{ route('tickets.show', ['ticket' => $ticket, 'from' => $activeTab]) }}" class="mt-1 block text-xs font-extrabold text-[color:var(--tm-brand-700)] hover:underline">{{ $ticketNumber }}</a>
                            <h3 class="mt-1 line-clamp-2 font-bold leading-5 text-[color:var(--tm-text)]">{{ $ticket->subject }}</h3>
                        </div>
                        <x-status-badge :status="$ticket->status" />
                    </div>

                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-xs font-semibold text-[color:var(--tm-text-muted)]">Prioritas</span>
                        <x-priority-badge :priority="$ticket->priority" />
                    </div>
                </article>
            @empty
                <div class="p-5">
                    <x-empty-state
                        :title="$activeTabMeta['empty']"
                        description="Tiket akan muncul di sini begitu tercatat pada tab ini."
                    />
                </div>
            @endforelse
        </div>

        @if ($ticketCount > 0)
        <div class="flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] px-4 py-3 text-[0.78rem] text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>
                Menampilkan <span class="font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span> dari <span class="font-extrabold tabular-nums text-[color:var(--tm-text)]">{{ $ticketCount }}</span> tiket
            </p>
            @if ($tickets->hasPages())
                <div>{{ $tickets->onEachSide(1)->links() }}</div>
            @endif
        </div>
        @endif
    </section>
@endsection
