@extends('layouts.app')

@section('title', 'Antrean Tier 1 — SIHATI')
@section('header_kicker', 'Operasional')
@section('header_title', 'Antrean Tier 1')

@php
    $priorityEdge = static fn ($priority): string => match (is_object($priority) ? $priority->value : (string) $priority) {
        'kritis' => 'border-l-[#eb3349]',
        'tinggi' => 'border-l-[#e4a72c]',
        'sedang' => 'border-l-[#75d5f3]',
        default => 'border-l-[#b9cbd2]',
    };
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#ffd44f] !shadow-[0_0_0_4px_#fff0b9]" aria-hidden="true"></span>Ruang kerja Agen Tier 1</p>
            <h1 class="ui-page-title">Antrean Tier 1</h1>
            <p class="ui-page-description">Ambil tiket Baru yang paling mendesak, lalu lanjutkan ke triase awal dengan jejak penanganan yang jelas.</p>
        </div>
        <a href="{{ route('tickets.index') }}" class="ui-btn ui-btn-ghost">Tiket saya <span aria-hidden="true">→</span></a>
    </div>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="queue-heading">
        <div class="ui-panel-header flex flex-wrap items-end justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 id="queue-heading" class="ui-section-title">Tiket menunggu klaim</h2>
                    <span class="ui-chip !border-[#f2d996] !bg-[#fff7df] !text-[#956b16]">{{ $tickets->total() }} tiket</span>
                </div>
                <p class="ui-section-description">Urutan: Kritis, Tinggi, Sedang, Rendah, lalu tiket yang dibuat paling lama.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-bold text-[#78909a]" aria-label="Keterangan prioritas">
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#eb3349]" aria-hidden="true"></span>Kritis</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#e4a72c]" aria-hidden="true"></span>Tinggi</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#75d5f3]" aria-hidden="true"></span>Normal</span>
            </div>
        </div>

        @if ($tickets->isEmpty())
            <div class="p-5 sm:p-6">
                <x-empty-state title="Belum ada tiket pada antrean ini." description="Tiket baru akan muncul setelah Pemohon mengirim permintaan. Anda dapat memeriksa Tiket saya untuk pekerjaan yang sudah ditangani." :action="route('tickets.index')" action-label="Buka tiket saya" />
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="ui-table" aria-describedby="queue-heading">
                    <thead>
                        <tr>
                            <th scope="col">Tiket dan permintaan</th>
                            <th scope="col">Pemohon</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col">Masuk</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td class="border-l-4 {{ $priorityEdge($ticket->priority) }}">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-extrabold text-[#1d5d72] hover:underline">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</a>
                                    <p class="mt-1 max-w-sm text-sm font-bold text-[#35505b]">{{ $ticket->subject }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">{{ $ticket->service_type_code_snapshot ?? $ticket->serviceType?->code ?? 'Layanan belum tersedia' }} · {{ $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name }}</p>
                                </td>
                                <td class="text-sm text-[#526f79]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat' }}</td>
                                <td class="text-sm text-[#526f79]">{{ $ticket->problemCategory?->name ?? 'Belum dikategorikan' }}</td>
                                <td><x-priority-badge :priority="$ticket->priority" /></td>
                                <td class="whitespace-nowrap text-xs text-[#78909a]">{{ ($ticket->submitted_at ?? $ticket->created_at)?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('tickets.claim', $ticket) }}" data-queue-claim>
                                        @csrf
                                        <button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs" data-queue-claim-button>
                                            <span data-queue-claim-label>Ambil tiket</span>
                                            <span class="hidden" data-queue-claim-loading aria-hidden="true">Mengambil…</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 p-4 md:hidden">
                @foreach ($tickets as $ticket)
                    <article class="rounded-xl border border-[#dfe8ec] border-l-4 {{ $priorityEdge($ticket->priority) }} bg-[#fbfdfd] p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-extrabold text-[#1d5d72] hover:underline">{{ $ticket->ticket_number ?? 'Tiket #'.$ticket->id }}</a>
                            <x-priority-badge :priority="$ticket->priority" />
                        </div>
                        <h3 class="mt-3 text-sm font-extrabold leading-5 text-[#35505b]">{{ $ticket->subject }}</h3>
                        <p class="mt-1 text-xs text-[#78909a]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat' }} · {{ $ticket->problemCategory?->name ?? 'Belum dikategorikan' }}</p>
                        <p class="mt-1 text-xs text-[#78909a]">{{ ($ticket->submitted_at ?? $ticket->created_at)?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</p>
                        <form method="POST" action="{{ route('tickets.claim', $ticket) }}" class="mt-4" data-queue-claim>
                            @csrf
                            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-queue-claim-button>
                                <span data-queue-claim-label>Ambil tiket</span>
                                <span class="hidden" data-queue-claim-loading aria-hidden="true">Mengambil…</span>
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if ($tickets->hasPages())
        <div class="mt-5">{{ $tickets->links() }}</div>
    @endif
@endsection
