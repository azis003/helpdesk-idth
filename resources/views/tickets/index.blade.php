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

            <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-2">
                @foreach ($tickets as $ticket)
                    @php
                        $ticketRouteTarget = $isTeamChair ? $ticket->id : $ticket;
                        $ticketNumber = $isTeamChair ? $ticket->ticketLabel() : ($ticket->ticket_number ?? 'Tiket #'.$ticket->id);
                        $serviceCode = $isTeamChair ? $ticket->serviceCode : ($ticket->service_type_code_snapshot ?? $ticket->serviceType?->code);
                        $serviceName = $isTeamChair ? $ticket->serviceLabel() : $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name;
                        $requesterName = $isTeamChair ? ($ticket->requesterName ?? 'Belum tercatat') : ($ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat');
                        $submittedAt = $isTeamChair ? $ticket->submittedAt : ($ticket->submitted_at ?? $ticket->created_at);
                    @endphp
                    <a href="{{ route('tickets.show', $ticketRouteTarget) }}" aria-label="Buka detail {{ $ticketNumber }}: {{ $ticket->subject }}" class="group flex min-h-40 gap-4 rounded-xl border border-[#e1eaed] bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#8bd7ee] hover:shadow-[0_12px_24px_-20px_rgba(48,134,165,0.75)] focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#2bb8aa] sm:p-5">
                        <span class="ui-catalog-icon mt-0.5 shrink-0" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7.5h14v11H5zM8 7.5V5h8v2.5M8.5 11h7M8.5 14.5h4" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block text-xs font-extrabold text-[#1d5d72]">{{ $ticketNumber }}</span>
                                    <span class="mt-2 block line-clamp-2 text-sm font-extrabold leading-5 text-[#35505b]">{{ $ticket->subject }}</span>
                                </span>
                                <x-status-badge :status="$ticket->status" class="shrink-0" />
                            </span>
                            <span class="mt-2 block text-xs leading-5 text-[#78909a]">{{ $serviceCode ?? 'Layanan belum tersedia' }} &middot; {{ $serviceName ?: 'Layanan belum tersedia' }}</span>
                            @if ($isTeamChair)
                                <span class="mt-1 block truncate text-xs text-[#78909a]">Pemohon: {{ $requesterName }}</span>
                            @endif
                            <span class="mt-4 flex flex-wrap items-center gap-2">
                                <x-priority-badge :priority="$ticket->priority" />
                                <span class="text-xs text-[#78909a]">{{ $submittedAt?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</span>
                                <span class="ml-auto text-xs font-extrabold text-[#147a79] group-hover:underline">Buka detail <span aria-hidden="true">→</span></span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="mt-5">{{ $tickets->links() }}</div>
    @endif
@endsection
