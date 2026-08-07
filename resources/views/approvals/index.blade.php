@extends('layouts.app')

@section('title', 'Persetujuan — '.$branding['application_name'])
@section('header_kicker', 'Ruang kerja')
@section('header_title', 'Persetujuan')

@section('content')
    <div class="ui-page-header">
        <div>
            <a href="{{ route('dashboard') }}" class="ui-action-link">← Kembali ke dasbor</a>
            <p class="ui-eyebrow mt-4"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Perlu tindakan saya</p>
            <h1 class="ui-page-title">Persetujuan tertunda</h1>
            <p class="ui-page-description">Tinjau konteks tiket, lalu pulihkan state sebelumnya atau berikan keputusan Tidak Setuju dengan catatan.</p>
        </div>
        <span class="rounded-full bg-[#fff4d7] px-3 py-1.5 text-xs font-extrabold text-[#9a6700]">{{ $approvals->count() }} menunggu</span>
    </div>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="approval-list-heading">
        <div class="ui-panel-header">
            <h2 id="approval-list-heading" class="ui-section-title">Daftar persetujuan</h2>
            <p class="ui-section-description">Urutan dimulai dari permintaan yang paling lama menunggu.</p>
        </div>

        @if ($approvals->isEmpty())
            <x-empty-state title="Belum ada persetujuan tertunda." description="Permintaan baru akan muncul setelah agen mengirim tiket untuk keputusan Manajer TI." />
        @else
            <div class="divide-y divide-[#edf2f4]">
                @foreach ($approvals as $approval)
                    <article class="p-5 sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-[#1d5d72]">{{ $approval->ticket?->ticket_number ?? 'Tiket #'.$approval->ticket_id }}</p>
                                <h3 class="mt-2 text-base font-extrabold text-[#35505b]">{{ $approval->ticket?->subject ?? 'Tiket tidak tersedia' }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#526f79]">Diajukan oleh {{ $approval->requestedBy?->name ?? 'petugas' }} untuk tiket {{ $approval->ticket?->requester?->name ?? 'Pemohon' }}.</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <span class="rounded-full bg-[#fff4d7] px-2.5 py-1 text-xs font-extrabold text-[#9a6700]">Menunggu Persetujuan</span>
                                <span class="rounded-full bg-[#f1fbfe] px-2.5 py-1 text-xs font-extrabold text-[#147a79]">{{ $approval->requested_at?->locale('id')->diffForHumans() }}</span>
                            </div>
                        </div>

                        <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">State sebelumnya</dt><dd class="mt-1 text-sm font-extrabold text-[#35505b]">{{ \App\Enums\TicketStatus::tryFrom((string) $approval->previous_status)?->label() ?? 'Tidak tercatat' }}</dd></div>
                            <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Penanggung jawab</dt><dd class="mt-1 text-sm font-extrabold text-[#35505b]">{{ $approval->previousAssignee?->name ?? 'Belum tercatat' }}</dd></div>
                            <div class="rounded-lg bg-[#f8fbfc] p-3"><dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Diajukan</dt><dd class="mt-1 text-sm font-bold text-[#526f79]">{{ $approval->requested_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd></div>
                        </dl>

                        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                            <form method="POST" action="{{ route('approvals.reject', $approval) }}" class="rounded-xl border border-rose-200 bg-[#fff8f9] p-4" data-approval-decision-form>
                                @csrf
                                <label for="approval-note-{{ $approval->id }}" class="ui-field-label !text-[#9f1239]">Catatan bila Tidak Setuju <span class="text-rose-600" aria-hidden="true">*</span></label>
                                <textarea id="approval-note-{{ $approval->id }}" name="decision_note" rows="3" required maxlength="10000" class="ui-textarea mt-2 !border-rose-200 !bg-white" placeholder="Alasan keputusan wajib dicatat."></textarea>
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
