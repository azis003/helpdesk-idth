@props([
    'approvalRequest',
    'ticket',
    'canDecide' => false,
])

@php
    $isPending = $approvalRequest?->isPending() === true;
    $isApproved = $approvalRequest?->isApproved() === true;
@endphp

<section class="ui-panel border-l-4 {{ $isPending ? 'border-l-[color:var(--tm-warning-600)]' : ($isApproved ? 'border-l-[color:var(--tm-success-600)]' : 'border-l-[color:var(--tm-danger-600)]') }}" aria-labelledby="approval-heading-{{ $approvalRequest->id }}">
    <div class="ui-panel-header">
        <h2 id="approval-heading-{{ $approvalRequest->id }}" class="ui-section-title flex items-center gap-2">
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-[var(--tm-r-full)] {{ $isPending ? 'bg-[color:var(--tm-warning-100)] text-[color:var(--tm-warning-700)]' : ($isApproved ? 'bg-[color:var(--tm-success-100)] text-[color:var(--tm-success-700)]' : 'bg-[color:var(--tm-danger-100)] text-[color:var(--tm-danger-700)]') }}" aria-hidden="true">
                @if ($isPending)
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7.25" /><path d="M10 6v4.25l2.5 1.5" /></svg>
                @elseif ($isApproved)
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7.25" /><path d="m6.75 10 2.1 2.1 4.4-4.4" /></svg>
                @else
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7.25" /><path d="m7.5 7.5 5 5m0-5-5 5" /></svg>
                @endif
            </span>
            {{ $isPending ? 'Menunggu keputusan Manajer TI' : ($isApproved ? 'Persetujuan disetujui' : 'Persetujuan tidak disetujui') }}
        </h2>
    </div>

    <dl class="grid gap-3 px-5 pb-5 text-sm sm:grid-cols-2 sm:px-6">
        <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-3">
            <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]">Approver aktif</dt>
            <dd class="mt-1 font-semibold text-[color:var(--tm-text)]">{{ $approvalRequest->approver?->name ?? 'Belum tersedia' }}</dd>
        </div>
        <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-3">
            <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]">Diajukan oleh</dt>
            <dd class="mt-1 font-semibold text-[color:var(--tm-text)]">{{ $approvalRequest->requestedBy?->name ?? 'Sistem' }}</dd>
        </div>
        <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-3">
            <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]">Status sebelumnya</dt>
            <dd class="mt-1 font-semibold text-[color:var(--tm-text)]">{{ \App\Enums\TicketStatus::tryFrom((string) $approvalRequest->previous_status)?->label() ?? 'Tidak tercatat' }}</dd>
        </div>
        <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-3">
            <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]">Waktu pengajuan</dt>
            <dd class="mt-1 font-medium tabular-nums text-[color:var(--tm-text-secondary)]">{{ $approvalRequest->requested_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</dd>
        </div>
    </dl>

    @if ($approvalRequest->decision_note)
        <div class="mx-5 mb-5 rounded-[var(--tm-r-lg)] border p-4 sm:mx-6 {{ $isApproved ? 'border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)]' : 'border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)]' }}">
            <p class="text-xs font-semibold uppercase tracking-[0.1em] {{ $isApproved ? 'text-[color:var(--tm-success-700)]' : 'text-[color:var(--tm-danger-700)]' }}">Catatan keputusan</p>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 {{ $isApproved ? 'text-[color:var(--tm-success-700)]' : 'text-[color:var(--tm-danger-700)]' }}">{{ $approvalRequest->decision_note }}</p>
        </div>
    @endif

    @if ($isPending && $canDecide)
        <div class="grid gap-4 border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] p-5 sm:p-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('approvals.approve', $approvalRequest) }}" class="flex flex-col rounded-[var(--tm-r-lg)] border border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] p-4" data-approval-decision-form>
                @csrf
                <p class="text-sm font-semibold text-[color:var(--tm-success-700)]">Setujui permintaan</p>
                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-secondary)]">Tiket akan dilanjutkan ke tahap berikutnya.</p>
                <button type="submit" class="ui-btn ui-btn-primary mt-4 w-full justify-center" data-approval-submit>Setuju</button>
            </form>
            <form method="POST" action="{{ route('approvals.reject', $approvalRequest) }}" class="flex flex-col rounded-[var(--tm-r-lg)] border border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] p-4" data-approval-decision-form>
                @csrf
                <label for="approval-decision-note-{{ $approvalRequest->id }}" class="ui-field-label">Catatan Tidak Setuju <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="approval-decision-note-{{ $approvalRequest->id }}" name="decision_note" rows="4" required maxlength="10000" class="ui-textarea mt-2" placeholder="Jelaskan alasan keputusan kepada pihak yang berwenang."></textarea>
                @error('decision_note')
                    <p class="mt-2 flex items-center gap-1.5 text-sm font-medium text-[color:var(--tm-danger-600)]">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="10" cy="10" r="7.25" /><path d="M10 6.5v4" /><path d="M10 13.25h.01" /></svg>
                        {{ $message }}
                    </p>
                @enderror
                <button type="submit" class="ui-btn ui-btn-danger mt-4 w-full justify-center" data-approval-submit>Tidak Setuju</button>
            </form>
        </div>
    @elseif ($isPending)
        <p class="border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-5 py-4 text-sm leading-6 text-[color:var(--tm-text-secondary)] sm:px-6">Permintaan ini sedang menunggu keputusan approver aktif. Anda akan menerima notifikasi setelah keputusan dicatat.</p>
    @endif
</section>
