@props([
    'approvalRequest',
    'ticket',
    'canDecide' => false,
])

@php
    $isPending = $approvalRequest?->isPending() === true;
    $isApproved = $approvalRequest?->isApproved() === true;
@endphp

<section class="ui-panel border-l-4 {{ $isPending ? 'border-l-[#e4a72c]' : ($isApproved ? 'border-l-[#2bb8aa]' : 'border-l-[#d06478]') }}" aria-labelledby="approval-heading-{{ $approvalRequest->id }}">
    <div class="ui-panel-header">
        <h2 id="approval-heading-{{ $approvalRequest->id }}" class="ui-section-title">
            {{ $isPending ? 'Menunggu keputusan Manajer TI' : ($isApproved ? 'Persetujuan disetujui' : 'Persetujuan tidak disetujui') }}
        </h2>
    </div>

    <dl class="grid gap-3 px-5 pb-5 text-sm sm:grid-cols-2 sm:px-6">
        <div class="rounded-lg bg-[#f8fbfc] p-3">
            <dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Approver aktif</dt>
            <dd class="mt-1 font-extrabold text-[#35505b]">{{ $approvalRequest->approver?->name ?? 'Belum tersedia' }}</dd>
        </div>
        <div class="rounded-lg bg-[#f8fbfc] p-3">
            <dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Diajukan oleh</dt>
            <dd class="mt-1 font-extrabold text-[#35505b]">{{ $approvalRequest->requestedBy?->name ?? 'Sistem' }}</dd>
        </div>
        <div class="rounded-lg bg-[#f8fbfc] p-3">
            <dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Status sebelumnya</dt>
            <dd class="mt-1 font-extrabold text-[#35505b]">{{ \App\Enums\TicketStatus::tryFrom((string) $approvalRequest->previous_status)?->label() ?? 'Tidak tercatat' }}</dd>
        </div>
        <div class="rounded-lg bg-[#f8fbfc] p-3">
            <dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Waktu pengajuan</dt>
            <dd class="mt-1 font-bold text-[#526f79]">{{ $approvalRequest->requested_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd>
        </div>
    </dl>

    @if ($approvalRequest->decision_note)
        <div class="mx-5 mb-5 rounded-xl border {{ $isApproved ? 'border-[#c8eee4] bg-[#f3fcf8]' : 'border-rose-200 bg-[#fff6f7]' }} p-4 sm:mx-6">
            <p class="text-xs font-extrabold uppercase tracking-[0.1em] {{ $isApproved ? 'text-[#087f5b]' : 'text-[#be123c]' }}">Catatan keputusan</p>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 {{ $isApproved ? 'text-[#35675a]' : 'text-[#7f1d1d]' }}">{{ $approvalRequest->decision_note }}</p>
        </div>
    @endif

    @if ($isPending && $canDecide)
        <div class="grid gap-4 border-t border-[#edf2f4] bg-[#fbfdfd] p-5 sm:p-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('approvals.approve', $approvalRequest) }}" class="rounded-xl border border-[#c8eee4] bg-[#f5fcf9] p-4" data-approval-decision-form>
                @csrf
                <p class="text-sm font-extrabold text-[#35505b]">Setujui permintaan</p>
                <button type="submit" class="ui-btn ui-btn-primary mt-4 w-full" data-approval-submit>Setuju</button>
            </form>
            <form method="POST" action="{{ route('approvals.reject', $approvalRequest) }}" class="rounded-xl border border-rose-200 bg-[#fff8f9] p-4" data-approval-decision-form>
                @csrf
                <label for="approval-decision-note-{{ $approvalRequest->id }}" class="ui-field-label !text-[#9f1239]">Catatan Tidak Setuju <span class="text-rose-600" aria-hidden="true">*</span></label>
                <textarea id="approval-decision-note-{{ $approvalRequest->id }}" name="decision_note" rows="4" required maxlength="10000" class="ui-textarea mt-2 !border-rose-200 !bg-white" placeholder="Jelaskan alasan keputusan kepada pihak yang berwenang."></textarea>
                @error('decision_note')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                <button type="submit" class="ui-btn ui-btn-danger mt-4 w-full" data-approval-submit>Tidak Setuju</button>
            </form>
        </div>
    @elseif ($isPending)
        <p class="border-t border-[#edf2f4] bg-[#fbfdfd] px-5 py-4 text-sm leading-6 text-[#526f79] sm:px-6">Permintaan ini sedang menunggu keputusan approver aktif. Anda akan menerima notifikasi setelah keputusan dicatat.</p>
    @endif
</section>
