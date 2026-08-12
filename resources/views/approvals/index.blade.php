@extends('layouts.app')

@php
    // Presentasional saja - tidak mengubah data maupun logika persetujuan.
    $apprDot = '!bg-[color:var(--tm-warning-600)] !shadow-[0_0_0_4px_var(--tm-warning-100)]';
    $apprCountChip = 'inline-flex shrink-0 items-center rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-3 py-1.5 text-xs font-extrabold tabular-nums text-[color:var(--tm-warning-700)]';
    $apprWaitChip = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-2.5 py-1 text-xs font-extrabold text-[color:var(--tm-warning-700)]';
    $apprAgeChip = 'rounded-[var(--tm-r-full)] border border-[color:var(--tm-brand-100)] bg-[color:var(--tm-brand-50)] px-2.5 py-1 text-xs font-extrabold text-[color:var(--tm-brand-700)]';
    $apprTile = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-3.5';
    $apprTileLabel = 'text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]';
    $apprTileValue = 'mt-1 text-sm font-extrabold text-[color:var(--tm-text)]';
    $apprRejectPanel = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] p-4';
    $apprRejectLabel = 'ui-field-label !text-[color:var(--tm-danger-700)]';
    $apprRejectTextarea = 'ui-textarea mt-2 !border-[color:var(--tm-danger-200)] !bg-[color:var(--tm-surface)]';
    $apprRequiredMark = 'text-[color:var(--tm-danger-600)]';
@endphp

@section('title', 'Persetujuan — '.$branding['application_name'])
@section('header_kicker', 'Ruang kerja')
@section('header_title', 'Persetujuan')

@section('content')
    <x-page-header
        eyebrow="Perlu tindakan saya"
        title="Persetujuan tertunda"
        description="Tinjau konteks tiket, lalu pulihkan state sebelumnya atau berikan keputusan Tidak Setuju dengan catatan."
        :backUrl="route('dashboard')"
        backLabel="Kembali ke dasbor"
        :dotClass="$apprDot"
    >
        <span class="{{ $apprCountChip }}">{{ $approvals->count() }} menunggu</span>
    </x-page-header>

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="approval-list-heading">
        <div class="ui-panel-header">
            <h2 id="approval-list-heading" class="ui-section-title">Daftar persetujuan</h2>
            <p class="ui-section-description">Urutan dimulai dari permintaan yang paling lama menunggu.</p>
        </div>

        @if ($approvals->isEmpty())
            <x-empty-state title="Belum ada persetujuan tertunda." description="Permintaan baru akan muncul setelah agen mengirim tiket untuk keputusan Manajer TI." />
        @else
            <div class="divide-y divide-[color:var(--tm-border-subtle)]">
                @foreach ($approvals as $approval)
                    <article class="p-5 sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold tabular-nums text-[color:var(--tm-brand-700)]">{{ $approval->ticket?->ticket_number ?? 'Tiket #'.$approval->ticket_id }}</p>
                                <h3 class="mt-2 text-base font-extrabold text-[color:var(--tm-text)]">{{ $approval->ticket?->subject ?? 'Tiket tidak tersedia' }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[color:var(--tm-text-secondary)]">Diajukan oleh {{ $approval->requestedBy?->name ?? 'petugas' }} untuk tiket {{ $approval->ticket?->requester?->name ?? 'Pemohon' }}.</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <span class="{{ $apprWaitChip }}">Menunggu Persetujuan</span>
                                <span class="{{ $apprAgeChip }}">{{ $approval->requested_at?->locale('id')->diffForHumans() }}</span>
                            </div>
                        </div>

                        <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="{{ $apprTile }}"><dt class="{{ $apprTileLabel }}">State sebelumnya</dt><dd class="{{ $apprTileValue }}">{{ \App\Enums\TicketStatus::tryFrom((string) $approval->previous_status)?->label() ?? 'Tidak tercatat' }}</dd></div>
                            <div class="{{ $apprTile }}"><dt class="{{ $apprTileLabel }}">Penanggung jawab</dt><dd class="{{ $apprTileValue }}">{{ $approval->previousAssignee?->name ?? 'Belum tercatat' }}</dd></div>
                            <div class="{{ $apprTile }}"><dt class="{{ $apprTileLabel }}">Diajukan</dt><dd class="mt-1 text-sm font-bold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $approval->requested_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd></div>
                        </dl>

                        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                            <form method="POST" action="{{ route('approvals.reject', $approval) }}" class="{{ $apprRejectPanel }}" data-approval-decision-form>
                                @csrf
                                <label for="approval-note-{{ $approval->id }}" class="{{ $apprRejectLabel }}">Catatan bila Tidak Setuju <span class="{{ $apprRequiredMark }}" aria-hidden="true">*</span></label>
                                <textarea id="approval-note-{{ $approval->id }}" name="decision_note" rows="3" required maxlength="10000" class="{{ $apprRejectTextarea }}" placeholder="Alasan keputusan wajib dicatat."></textarea>
                                <button type="submit" class="ui-btn ui-btn-danger mt-3" data-approval-submit>Tidak Setuju</button>
                            </form>
                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <a href="{{ route('tickets.show', $approval->ticket_id) }}" class="ui-btn ui-btn-secondary">Buka tiket</a>
                                <form method="POST" action="{{ route('approvals.approve', $approval) }}" data-approval-decision-form>
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-primary" data-approval-submit>Setuju</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
