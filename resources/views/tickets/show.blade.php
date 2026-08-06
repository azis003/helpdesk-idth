@extends('layouts.app')

@section('title', ($ticket->ticket_number ?? 'Detail tiket').' — SIHATI')
@section('header_kicker', 'Tiket')
@section('header_title', 'Detail tiket')

@php
    $ticketLabel = $ticket->ticket_number ?? 'Tiket #'.$ticket->id;
    $serviceLabel = $ticket->service_type_name_snapshot ?? $ticket->serviceType?->name ?? 'Layanan belum tersedia';
    $locationLabel = collect([$ticket->building_name_snapshot, $ticket->floor_name_snapshot, $ticket->room_name_snapshot])->filter()->implode(' · ');
    $canCancel = auth()->user()->can('cancel', $ticket);
    [$nextStepTitle, $nextStepDescription] = match ($ticket->status) {
        \App\Enums\TicketStatus::Baru => ['Tiket menunggu diproses', 'Tiket baru masuk ke antrean Tier 1 dan belum memiliki penanggung jawab.'],
        \App\Enums\TicketStatus::Dibatalkan => ['Tiket telah dibatalkan', 'Tiket ini tidak akan masuk ke proses penanganan lebih lanjut.'],
        default => ['Status tiket diperbarui', 'Tim TI akan melanjutkan tiket sesuai status dan kewenangan penanganannya.'],
    };
@endphp

@section('content')
    <div class="ui-page-header">
        <div>
            <a href="{{ route('tickets.index') }}" class="ui-action-link">← Kembali ke tiket saya</a>
            <h1 class="ui-page-title">{{ $ticketLabel }}</h1>
            <p class="ui-page-description">Detail permintaan, identitas pemohon, dan status terbaru tiket.</p>
        </div>
        @if ($canCancel)
            <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" onsubmit="return window.confirm('Batalkan tiket ini? Tiket hanya dapat dibatalkan saat status Baru dan tidak dapat diproses lebih lanjut.');">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger">Batalkan tiket</button>
            </form>
        @endif
    </div>

    <div class="mt-8 grid gap-5 xl:grid-cols-[1.35fr_0.65fr]">
        <div class="space-y-5">
            <section class="ui-panel" aria-labelledby="ticket-summary-heading">
                <div class="ui-panel-header flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#78909a]">Ringkasan permintaan</p>
                        <h2 id="ticket-summary-heading" class="mt-2 text-xl font-extrabold tracking-tight text-[#263a43]">{{ $ticket->subject }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge :status="$ticket->status" />
                        <x-priority-badge :priority="$ticket->priority" />
                    </div>
                </div>
                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Deskripsi</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-[#526f79]">{{ $ticket->description ?: 'Deskripsi belum tersedia.' }}</p>
                    </div>
                    <dl class="grid gap-4 border-t border-[#edf2f4] pt-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Layanan</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->service_type_code_snapshot ?? $ticket->serviceType?->code }} — {{ $serviceLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Dibuat</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ ($ticket->submitted_at ?? $ticket->created_at)?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Lokasi</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $locationLabel !== '' ? $locationLabel : 'Tidak diisi' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Penanggung jawab</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $ticket->assignee?->name ?? 'Belum ada — menunggu antrean Tier 1' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="ui-panel" aria-labelledby="requester-heading">
                <div class="ui-panel-header">
                    <h2 id="requester-heading" class="ui-section-title">Identitas tiket</h2>
                    <p class="ui-section-description">Pemohon dan pembuat tiket disimpan terpisah untuk membedakan tiket mandiri dan pencatatan oleh Agen Tier 1.</p>
                </div>
                <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-lg bg-[#f8fbfc] p-4">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pemohon</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->requester_name_snapshot ?? $ticket->requester?->name ?? 'Belum tercatat' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">NIP: {{ $ticket->requester_nip_snapshot ?? $ticket->requester?->nip ?? 'Tidak tersedia' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">Tim: {{ $ticket->requester_team_snapshot ?: 'Belum memiliki tim' }}</dd>
                    </div>
                    <div class="rounded-lg bg-[#f8fbfc] p-4">
                        <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Pembuat tiket</dt>
                        <dd class="mt-2 text-sm font-extrabold text-[#35505b]">{{ $ticket->creator?->name ?? 'Belum tercatat' }}</dd>
                        <dd class="mt-1 text-xs text-[#78909a]">{{ $ticket->is_self_created ? 'Dibuat mandiri oleh Pemohon' : 'Dicatat atas nama pegawai oleh Agen Tier 1' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ui-panel" aria-labelledby="service-fields-heading">
                <div class="ui-panel-header">
                    <h2 id="service-fields-heading" class="ui-section-title">Informasi layanan</h2>
                    <p class="ui-section-description">Nilai field disimpan bersama label dan versi definisi saat tiket dibuat.</p>
                </div>
                @if ($ticket->fieldValues->isEmpty())
                    <p class="p-5 text-sm text-[#78909a] sm:p-6">Tidak ada field tambahan pada layanan ini.</p>
                @else
                    <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        @foreach ($ticket->fieldValues as $fieldValue)
                            @php
                                $displayValue = is_array($fieldValue->value)
                                    ? implode(', ', $fieldValue->value)
                                    : ($fieldValue->field_type_snapshot === 'boolean' ? ((bool) $fieldValue->value ? 'Ya' : 'Tidak') : $fieldValue->value);
                            @endphp
                            <div class="rounded-lg border border-[#edf2f4] p-4">
                                <dt class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">{{ $fieldValue->label_snapshot }}</dt>
                                <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-[#35505b]">{{ filled($displayValue) ? $displayValue : 'Tidak diisi' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="ui-panel ui-panel--accent p-5 sm:p-6" aria-labelledby="next-step-heading">
                <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Status saat ini</p>
                <h2 id="next-step-heading" class="mt-2 text-lg font-extrabold tracking-tight text-[#263a43]">{{ $nextStepTitle }}</h2>
                <p class="mt-2 text-sm leading-6 text-[#52747b]">{{ $nextStepDescription }}</p>
                <div class="mt-4"><x-status-badge :status="$ticket->status" /></div>
            </section>

            <section class="ui-panel" aria-labelledby="attachments-heading">
                <div class="ui-panel-header">
                    <h2 id="attachments-heading" class="ui-section-title">Lampiran</h2>
                    <p class="ui-section-description">Berkas yang dapat Anda akses pada tiket ini.</p>
                </div>
                @if ($ticket->attachments->isEmpty())
                    <p class="p-5 text-sm leading-6 text-[#78909a] sm:p-6">Tidak ada lampiran pada tiket ini.</p>
                @else
                    <ul class="divide-y divide-[#edf2f4]">
                        @foreach ($ticket->attachments as $attachment)
                            <li class="flex items-start justify-between gap-3 p-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-[#35505b]" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">{{ $attachment->type_label_snapshot }} · {{ number_format($attachment->size_bytes / 1024, 0, ',', '.') }} KB</p>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="ui-action-link shrink-0">Unduh<span class="sr-only"> {{ $attachment->original_name }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
@endsection
