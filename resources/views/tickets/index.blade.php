@extends('layouts.app')

@php
    $isTeamChair = $isTeamChair ?? false;
    $hasPersonalScope = $canAccessTickets ?? false;
    $ticketListLabel = $isTeamChair && $hasPersonalScope
        ? 'Tiket saya dan tim'
        : ($isTeamChair ? 'Tiket tim' : 'Tiket saya');
@endphp

@section('title', $ticketListLabel.' — '.$branding['application_name'])
@section('header_kicker', 'Tiket')
@section('header_title', $ticketListLabel)

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>{{ ($isTeamChair ?? false) ? 'Pemantauan tim' : 'Pelacakan permintaan' }}</p>
            <h1 class="ui-page-title">{{ $ticketListLabel }}</h1>
            <p class="ui-page-description">{{ $isTeamChair ? 'Pantau metadata, status, SLA, penanggung jawab, balasan publik, dan solusi anggota tim dalam mode baca saja.' : 'Pantau nomor, status, prioritas, dan ringkasan permintaan yang menjadi tanggung jawab Anda.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canViewQueue)
                <a href="{{ route('tickets.queue') }}" class="ui-btn ui-btn-secondary">Antrean Tier 1</a>
            @endif
            @can('create', \App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-primary">Buat tiket <span aria-hidden="true">→</span></a>
            @endcan
        </div>
    </div>

    @if ($tickets->isEmpty())
        <x-empty-state class="mt-8" title="Belum ada tiket pada daftar ini." :description="$isTeamChair ? 'Tiket anggota tim yang dipantau akan muncul di sini setelah tercatat.' : 'Tiket yang Anda buat, ajukan, atau tangani akan muncul di sini setelah tercatat.'" :action="auth()->user()->can('create', \App\Models\Ticket::class) ? route('tickets.create') : null" action-label="Buat tiket pertama" />
    @else
        <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="tickets-list-heading">
            <div class="ui-panel-header flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="tickets-list-heading" class="ui-section-title">Daftar tiket</h2>
                    <p class="ui-section-description">{{ $tickets->total() }} tiket ditemukan.</p>
                </div>
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="ui-table" aria-describedby="tickets-list-heading">
                    <thead>
                        <tr>
                            <th scope="col">Nomor dan layanan</th>
                            <th scope="col">Pemohon</th>
                            <th scope="col">Status</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col">Dibuat</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            @php
                                $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                                $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                                $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?? $ticket->serviceType?->code);
                                $serviceName = $isTeamChair ? $ticket->serviceLabel() : $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name;
                                $requesterName = $isTeamChair ? ($ticket->requesterName ?? 'Belum tercatat') : ($ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat');
                                $submittedAt = $isTeamChair ? $ticket->submittedAt : ($ticket->submitted_at ?? $ticket->created_at);
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="font-extrabold text-[#1d5d72] hover:underline">{{ $ticketNumber }}</a>
                                    <p class="mt-1 max-w-sm text-sm font-bold text-[#35505b]">{{ $ticket->subject }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">{{ $serviceCode ?? 'Layanan belum tersedia' }} &middot; {{ $serviceName }}</p>
                                </td>
                                <td class="text-sm text-[#526f79]">{{ $requesterName }}</td>
                                <td><x-status-badge :status="$ticket->status" /></td>
                                <td><x-priority-badge :priority="$ticket->priority" /></td>
                                <td class="whitespace-nowrap text-xs text-[#78909a]">{{ $submittedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-right"><a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="ui-action-link">Buka detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 p-4 md:hidden">
                @foreach ($tickets as $ticket)
                    @php
                        $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                        $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                        $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?? $ticket->serviceType?->code);
                        $submittedAt = $isTeamChair ? $ticket->submittedAt : ($ticket->submitted_at ?? $ticket->created_at);
                    @endphp
                    <a href="{{ route('tickets.show', $ticketRouteTarget) }}" class="block rounded-xl border border-[#e1eaed] bg-[#fbfdfd] p-4 transition hover:border-[#8bd7ee] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#2bb8aa]">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <span class="text-xs font-extrabold text-[#1d5d72]">{{ $ticketNumber }}</span>
                            <x-status-badge :status="$ticket->status" />
                        </div>
                        <h3 class="mt-3 text-sm font-extrabold leading-5 text-[#35505b]">{{ $ticket->subject }}</h3>
                        <p class="mt-1 text-xs text-[#78909a]">{{ $serviceCode ?? 'Layanan belum tersedia' }}</p>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                            <x-priority-badge :priority="$ticket->priority" />
                            <span class="text-xs text-[#78909a]">{{ $submittedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="mt-5">{{ $tickets->links() }}</div>
    @endif
@endsection
