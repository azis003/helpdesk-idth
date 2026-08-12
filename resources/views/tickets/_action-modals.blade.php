@if ($showActionPanel)
<div class="contents">

@if ($canChangePriority)
    <x-ui.modal-panel id="ticket-priority-modal" labelledby="priority-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-priority-modal'">
    <section class="ui-panel" aria-labelledby="priority-heading">
        <div class="ui-panel-header">
            <div>
                <h2 id="priority-heading" class="ui-section-title">Ubah Prioritas</h2>
                <p class="ui-section-description">Pilih prioritas yang sesuai dengan dampak dan urgensi tiket.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('tickets.priority.update', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-priority-form>
            @csrf
            @method('PUT')
            <input type="hidden" name="_action_modal" value="ticket-priority-modal">
            <div>
                <label for="ticket-priority" class="ui-field-label">Prioritas <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <select id="ticket-priority" name="priority" required class="ui-select mt-2">
                    @foreach ($priorityOptions as $value => $label)
                        <option value="{{ $value }}" @selected($currentPriority === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority')<x-field-error :message="$message" />@enderror
            </div>
            <div>
                <label for="priority-change-reason" class="ui-field-label">Alasan perubahan <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="priority-change-reason" name="reason" rows="3" required maxlength="1000" class="ui-textarea mt-2" placeholder="Jelaskan mengapa prioritas perlu disesuaikan.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-priority-submit>Simpan Prioritas</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canReject)
    <x-ui.modal-panel id="ticket-reject-modal" labelledby="reject-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-reject-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-danger-600)]" aria-labelledby="reject-heading">
        <div class="ui-panel-header">
            <div>
                <h2 id="reject-heading" class="ui-section-title">Tolak Tiket</h2>
                <p class="ui-section-description">Tiket yang ditolak tidak lagi berada di antrean Helpdesk.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('tickets.reject', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-reject-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-reject-modal">
            <div>
                <label for="ticket-reject-reason" class="ui-field-label">Alasan penolakan <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="ticket-reject-reason" name="reason" rows="4" required maxlength="2000" class="ui-textarea mt-2" placeholder="Jelaskan alasan yang perlu diketahui Pelapor.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-danger w-full" data-ticket-reject-submit>Tolak Tiket</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canCommentInternal)
    <x-ui.modal-panel id="ticket-internal-comment-modal" labelledby="internal-comment-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-internal-comment-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="internal-comment-heading">
        <div class="ui-panel-header">
            <div>
                <h2 id="internal-comment-heading" class="ui-section-title">Catatan Internal</h2>
                <p class="ui-section-description">Hanya dapat dibaca oleh Tim TI.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('tickets.comments.internal', $ticket) }}" enctype="multipart/form-data" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-internal-comment-modal">
            <div>
                <label for="internal-comment-body" class="ui-field-label">Isi catatan internal <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="internal-comment-body" name="body" rows="5" required class="ui-textarea mt-2" placeholder="Tulis konteks untuk Tim TI.">{{ old('body') }}</textarea>
                @error('body')<x-field-error :message="$message" />@enderror
            </div>

            @if ($commentInternalPolicies->isNotEmpty())
                <fieldset class="space-y-3">
                    <legend class="ui-field-label">Lampiran internal</legend>
                    @foreach ($commentInternalPolicies as $policy)
                        @php
                            $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                        @endphp
                        <div>
                            <label for="internal-comment-attachment-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                            <input id="internal-comment-attachment-{{ $policy->id }}" type="file" name="attachments[{{ $policy->id }}][]" multiple class="ui-file-input mt-2" @if ($accept !== '') accept="{{ $accept }}" @endif>
                        </div>
                    @endforeach
                </fieldset>
            @endif

            <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-communication-submit>Simpan catatan internal</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canRequestInformation)
    <x-ui.modal-panel id="ticket-request-information-modal" labelledby="request-information-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-request-information-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-brand-600)]" aria-labelledby="request-information-heading">
        <div class="ui-panel-header">
            <div>
                <h2 id="request-information-heading" class="ui-section-title">Kembalikan ke Pelapor</h2>
                <p class="ui-section-description">Tiket akan menunggu balasan Pemohon maksimal 3 hari kerja.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('tickets.request-information', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-request-information-modal">
            <div>
                <label for="request-information-body" class="ui-field-label">Pertanyaan untuk Pemohon <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="request-information-body" name="body" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau bukti yang perlu dilengkapi Pemohon.">{{ old('body') }}</textarea>
                @error('body')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-communication-submit>Kirim ke Pelapor dan tunggu balasan</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canSeeInternal && ($specialControlReadiness['kind'] ?? null) === 'database_change')
    @php
        $changeControl = $specialControlReadiness['control'] ?? null;
        $controlLabels = [
            'change_script' => 'Skrip perubahan',
            'rollback_script' => 'Skrip pemulihan',
            'backup_evidence' => 'Bukti backup',
        ];
    @endphp
    <x-ui.modal-panel id="ticket-database-change-modal" labelledby="database-change-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-database-change-modal'" max-width="max-w-3xl">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="database-change-heading">
        <div class="ui-panel-header">
            <h2 id="database-change-heading" class="ui-section-title">Kontrol SVC-03</h2>
        </div>
        <div class="space-y-4 p-5 sm:p-6">
            <ul class="space-y-2" aria-label="Persyaratan bukti SVC-03">
                @foreach (($specialControlReadiness['requirements'] ?? []) as $type => $requirement)
                    <li class="flex items-start justify-between gap-3 rounded-[var(--tm-r-md)] border px-3.5 py-3 text-sm {{ $requirement['valid'] ? 'border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)]' : 'border-[color:var(--tm-border)] bg-[color:var(--tm-sunken)]' }}">
                        <span class="min-w-0">
                            <span class="block font-bold text-[color:var(--tm-text)]">{{ $controlLabels[$type] ?? $type }}</span>
                            @if ($requirement['attachment'])
                                <span class="mt-1 block truncate text-xs text-[color:var(--tm-text-muted)]">{{ $requirement['attachment']->original_name }}</span>
                            @endif
                        </span>
                        <span class="flex shrink-0 items-center gap-1.5 text-xs font-bold {{ $requirement['valid'] ? 'text-[color:var(--tm-success-700)]' : 'text-[color:var(--tm-danger-700)]' }}">
                            @if ($requirement['valid'])
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" /></svg>
                                Tersedia dan valid
                            @else
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 8v4.5m0 3h.01" /></svg>
                                Belum tersedia/valid
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>

            @if ($changeControl?->executionStarted())
                <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-info-200)] bg-[color:var(--tm-info-50)] p-3.5 text-sm text-[color:var(--tm-text-secondary)]">
                    <p class="font-bold text-[color:var(--tm-info-700)]">Eksekusi sudah dimulai</p>
                    <p class="mt-1 text-xs leading-5">Oleh {{ $changeControl->executionStartedBy?->name ?? 'Pengguna yang tercatat' }} pada <span class="tabular-nums">{{ $changeControl->execution_started_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</span>.</p>
                </div>
            @elseif ($canStartDatabaseChange)
                <form method="POST" action="{{ route('tickets.database-change.execute', $ticket) }}" class="space-y-3" data-ticket-database-change-form data-swal-confirm="Mulai Eksekusi SVC-03 setelah tiga bukti diverifikasi?">
                    @csrf
                    <input type="hidden" name="_action_modal" value="ticket-database-change-modal">
                    <p class="text-xs leading-5 text-[color:var(--tm-text-muted)]">Tiga bukti harus valid sebelum eksekusi.</p>
                    <button type="submit" class="ui-btn ui-btn-warning w-full">Mulai Eksekusi</button>
                    @error('database_change')<x-field-error :message="$message" />@enderror
                </form>
            @elseif (! ($specialControlReadiness['evidence_ready'] ?? false))
                <p class="flex items-start gap-2 rounded-[var(--tm-r-md)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-3.5 text-xs leading-5 text-[color:var(--tm-warning-700)]">
                    <svg class="mt-px h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 8v4.5m0 3h.01" /></svg>
                    <span>Lengkapi dan unggah tiga bukti berbeda untuk mengaktifkan Mulai Eksekusi.</span>
                </p>
            @endif

            @if ($canVerifyDatabaseChange && $changeControl?->executionStarted() && ! $changeControl?->verified())
                <form method="POST" action="{{ route('tickets.database-change.verify', $ticket) }}" class="space-y-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-sunken)] p-4" data-ticket-database-change-form>
                    @csrf
                    <input type="hidden" name="_action_modal" value="ticket-database-change-modal">
                    <div>
                        <label for="database-change-result" class="ui-field-label">Hasil verifikasi <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                        <textarea id="database-change-result" name="verification_result" rows="3" required maxlength="20000" class="ui-textarea mt-2" placeholder="Jelaskan hasil pemeriksaan setelah perubahan dijalankan.">{{ old('verification_result') }}</textarea>
                        @error('verification_result')<x-field-error :message="$message" />@enderror
                    </div>
                    <div>
                        <label for="database-change-notes" class="ui-field-label">Catatan verifikasi <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                        <textarea id="database-change-notes" name="verification_notes" rows="3" required maxlength="20000" class="ui-textarea mt-2" placeholder="Catat bukti pemeriksaan, dampak, atau tindak lanjut.">{{ old('verification_notes') }}</textarea>
                        @error('verification_notes')<x-field-error :message="$message" />@enderror
                    </div>
                    <button type="submit" class="ui-btn ui-btn-primary w-full">Simpan verifikasi hasil</button>
                </form>
            @elseif ($changeControl?->verified())
                <div class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] p-3.5 text-sm text-[color:var(--tm-text-secondary)]">
                    <p class="font-bold text-[color:var(--tm-success-700)]">Verifikasi hasil selesai</p>
                    <p class="mt-1 whitespace-pre-line text-xs leading-5">{{ $changeControl->verification_result }}</p>
                    <p class="mt-2 text-xs leading-5">Oleh {{ $changeControl->verifier?->name ?? 'Pengguna yang tercatat' }} pada <span class="tabular-nums">{{ $changeControl->verified_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</span>.</p>
                    <p class="mt-2 whitespace-pre-line rounded-[var(--tm-r-sm)] bg-[color:var(--tm-n-0)] px-3 py-2 text-xs leading-5">Catatan: {{ $changeControl->verification_notes }}</p>
                </div>
            @endif
        </div>
    </section>
    </x-ui.modal-panel>
@elseif ($canSeeInternal && ($specialControlReadiness['kind'] ?? null) === 'data_export')
    {{-- Status hasil tarik data ditampilkan di dalam dialog penyelesaian. --}}
@endif

@if ($canComplete)
    <x-ui.modal-panel id="ticket-complete-modal" labelledby="complete-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-complete-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-brand-500)]" aria-labelledby="complete-heading">
        @php
            $isDataExport = ($specialControlReadiness['kind'] ?? null) === 'data_export';
        @endphp
        <div class="ui-panel-header">
            <h2 id="complete-heading" class="ui-section-title">{{ $isDataExport ? 'Simpan solusi dan hasil tarik data' : 'Simpan solusi' }}</h2>
        </div>
        <form method="POST" action="{{ route('tickets.complete', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-resolution-form @if ($isDataExport) enctype="multipart/form-data" @endif>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-complete-modal">
            <div>
                <label for="ticket-solution" class="ui-field-label">Solusi <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="ticket-solution" name="solution" rows="6" required maxlength="20000" class="ui-textarea mt-2" placeholder="Jelaskan tindakan dan hasil penyelesaian tiket.">{{ old('solution') }}</textarea>
                @error('solution')<x-field-error :message="$message" />@enderror
            </div>
            @if ($isDataExport)
                <div class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-info-200)] bg-[color:var(--tm-info-50)] p-4">
                    <label for="data-export-result" class="ui-field-label">Hasil tarik data <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                    <p id="data-export-result-help" class="ui-field-help">Unggah satu berkas hasil yang dapat diakses Pemohon. Ukuran maksimal 10 MB.</p>
                    <input id="data-export-result" name="data_export_result" type="file" required class="ui-file-input mt-2" aria-describedby="data-export-result-help">
                    @error('data_export_result')<x-field-error :message="$message" />@enderror
                </div>
            @endif
            <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-resolution-submit>{{ $isDataExport ? 'Simpan solusi dan unggah hasil' : 'Simpan solusi dan minta konfirmasi' }}</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canConfirm || $canNotSatisfied)
    <x-ui.modal-panel id="ticket-confirmation-modal" labelledby="confirmation-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-confirmation-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-brand-500)]" aria-labelledby="confirmation-heading">
        <div class="ui-panel-header">
            <h2 id="confirmation-heading" class="ui-section-title">Apakah hasilnya sudah sesuai?</h2>
        </div>
        <div class="grid gap-3 p-5 sm:p-6">
            @if ($canConfirm)
                <form method="POST" action="{{ route('tickets.confirm', $ticket) }}" data-swal-confirm="Konfirmasi hasil ini dan tutup tiket?">
                    @csrf
                    <input type="hidden" name="_action_modal" value="ticket-confirmation-modal">
                    <button type="submit" class="ui-btn ui-btn-primary w-full">Hasil sudah sesuai dan tutup tiket</button>
                </form>
            @endif
            @if ($canNotSatisfied)
                <form method="POST" action="{{ route('tickets.not-satisfied', $ticket) }}" class="space-y-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-4" data-ticket-resolution-form>
                    @csrf
                    <input type="hidden" name="_action_modal" value="ticket-confirmation-modal">
                    <div>
                        <label for="not-satisfied-reason" class="ui-field-label">Alasan hasil belum sesuai <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                        <textarea id="not-satisfied-reason" name="reason" rows="4" required maxlength="5000" class="ui-textarea mt-2" placeholder="Jelaskan bagian hasil yang masih perlu diperbaiki.">{{ old('reason') }}</textarea>
                        @error('reason')<x-field-error :message="$message" />@enderror
                    </div>
                    <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-resolution-submit>Hasil belum sesuai</button>
                </form>
            @endif
        </div>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canReopen)
    <x-ui.modal-panel id="ticket-reopen-modal" labelledby="reopen-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-reopen-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="reopen-heading">
        <div class="ui-panel-header">
            <h2 id="reopen-heading" class="ui-section-title">Buka kembali tiket</h2>
        </div>
        <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-resolution-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-reopen-modal">
            <div>
                <label for="reopen-reason" class="ui-field-label">Alasan buka kembali</label>
                <textarea id="reopen-reason" name="reason" rows="3" maxlength="5000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan tindak lanjut yang masih diperlukan.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-resolution-submit>Buka kembali tiket</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($approvalRequest)
    <x-ui.modal-panel id="ticket-approval-decision-modal" labelledby="approval-heading-{{ $approvalRequest->id }}" :auto-open="$errors->any() && old('_action_modal') === 'ticket-approval-decision-modal'">
    <x-approval-panel :approval-request="$approvalRequest" :ticket="$ticket" :can-decide="$canDecideApproval" />
    </x-ui.modal-panel>
@endif

@if ($canRequestApproval)
    <x-ui.modal-panel id="ticket-request-approval-modal" labelledby="request-approval-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-request-approval-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="request-approval-heading">
        <div class="ui-panel-header">
            <h2 id="request-approval-heading" class="ui-section-title">Butuh Persetujuan</h2>
        </div>
        <form method="POST" action="{{ route('tickets.request-approval', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-approval-request-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-request-approval-modal">
            <div>
                <label for="approval-request-reason" class="ui-field-label">Catatan permintaan</label>
                <textarea id="approval-request-reason" name="reason" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan konteks yang perlu diputuskan Manajer TI.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-warning w-full" data-approval-request-submit>Butuh Persetujuan</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canStartThirdParty || $canResumeThirdParty)
    <x-ui.modal-panel id="ticket-third-party-modal" labelledby="third-party-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-third-party-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-brand-500)]" aria-labelledby="third-party-heading">
        <div class="ui-panel-header">
            <h2 id="third-party-heading" class="ui-section-title">{{ $canResumeThirdParty ? 'Pending tiket' : 'Pending tiket' }}</h2>
        </div>
        @if ($canResumeThirdParty && $activeWait)
            <div class="space-y-3 px-5 pb-1 sm:px-6">
                <dl class="grid gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4 text-sm">
                    <div><dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-muted)]">Alasan pending</dt><dd class="mt-1 font-bold text-[color:var(--tm-text)]">{{ $activeWait->third_party_name }}</dd></div>
                    <div><dt class="text-[0.68rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-muted)]">Mulai menunggu</dt><dd class="mt-1 font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $activeWait->started_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}@if ($activeWait->follow_up_date) · Follow-up {{ $activeWait->follow_up_date->translatedFormat('d M Y') }}@endif</dd></div>
                </dl>
                <form method="POST" action="{{ route('tickets.resume-third-party', $ticket) }}" class="space-y-3" data-ticket-communication-form>
                    @csrf
                    <input type="hidden" name="_action_modal" value="ticket-third-party-modal">
                    <div>
                        <label for="third-party-resume-reason" class="ui-field-label">Catatan pelanjutan</label>
                        <textarea id="third-party-resume-reason" name="reason" rows="3" class="ui-textarea mt-2" placeholder="Opsional: tulis alasan tiket dapat dilanjutkan.">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-communication-submit>Lanjutkan pengerjaan</button>
                </form>
            </div>
        @elseif ($canStartThirdParty)
            <form method="POST" action="{{ route('tickets.wait-third-party', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-communication-form>
                @csrf
                <input type="hidden" name="_action_modal" value="ticket-third-party-modal">
                <div>
                    <label for="third-party-name" class="ui-field-label">Alasan pending <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                    <input id="third-party-name" name="third_party_name" value="{{ old('third_party_name') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Menunggu vendor atau jadwal perubahan">
                </div>
                <div>
                    <label for="third-party-follow-up" class="ui-field-label">Tanggal follow-up</label>
                    <input id="third-party-follow-up" type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="ui-input mt-2">
                </div>
                <div>
                    <label for="third-party-note" class="ui-field-label">Catatan pending</label>
                    <textarea id="third-party-note" name="note" rows="3" maxlength="1000" class="ui-textarea mt-2" placeholder="Opsional: jelaskan detail atau tindak lanjut yang dibutuhkan.">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-communication-submit>Simpan Pending</button>
            </form>
        @endif
    </section>
    </x-ui.modal-panel>
@endif

@if ($canTriage)
    <x-ui.modal-panel id="ticket-triage-modal" labelledby="triage-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-triage-modal'" max-width="max-w-3xl">
    <section class="ui-panel overflow-hidden" aria-labelledby="triage-heading">
        <div class="ui-panel-header">
            <h2 id="triage-heading" class="ui-section-title">Triase tiket</h2>
        </div>
        <form method="POST" action="{{ route('tickets.triage', $ticket) }}" class="space-y-5 p-5 sm:p-6" data-ticket-triage-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-triage-modal">
            <fieldset>
                <legend class="ui-field-label">Hasil triase <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></legend>
                <div class="mt-2 grid gap-2">
                    @foreach ([['self', 'Ambil dan kerjakan sendiri'], ['tier_2', 'Tugaskan ke Agen Tier 2']] as [$value, $label])
                        <label class="flex cursor-pointer gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] p-3.5 transition-[border-color,background-color,box-shadow] duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] has-[:checked]:border-[color:var(--tm-brand-500)] has-[:checked]:bg-[color:var(--tm-brand-50)] has-[:checked]:shadow-[var(--tm-sh-xs)]">
                            <input type="radio" name="outcome" value="{{ $value }}" class="mt-0.5 h-4 w-4 shrink-0 border-[color:var(--tm-border-strong)] text-[color:var(--tm-brand-600)]" @checked($currentOutcome === $value) data-ticket-triage-outcome>
                            <span class="block text-sm font-bold text-[color:var(--tm-text)]">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('outcome')<x-field-error :message="$message" />@enderror
            </fieldset>

            <div class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4">
                <p class="ui-field-label">Layanan tiket</p>
                <p class="mt-2 text-sm font-bold text-[color:var(--tm-text)]">{{ $serviceLabel }}</p>
            </div>

            <div data-ticket-triage-panel="tier_2" class="space-y-4 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-sunken)] p-4" @if ($currentOutcome !== 'tier_2') hidden @endif>
                <div>
                    <label for="tier-two-assignee" class="ui-field-label">Teknisi Tier 2</label>
                    <select id="tier-two-assignee" name="assigned_to_id" class="ui-select mt-2" data-ticket-assignee-select aria-describedby="tier-two-help">
                        <option value="">Pilih teknisi</option>
                        @foreach ($tierTwoUsers as $tierTwoUser)
                            <option value="{{ $tierTwoUser->id }}" @selected((string) old('assigned_to_id') === (string) $tierTwoUser->id)>{{ $tierTwoUser->name }}</option>
                        @endforeach
                    </select>
                    <p id="tier-two-help" class="ui-field-help">Saran berdasarkan keahlian layanan.</p>
                    @error('assigned_to_id')<x-field-error :message="$message" />@enderror
                </div>
                <div aria-live="polite">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[0.68rem] font-bold uppercase tracking-[0.1em] text-[color:var(--tm-text-muted)]">Saran teknisi</p>
                    </div>
                    <ul class="mt-2 space-y-2" data-ticket-suggestion-list>
                        @foreach ($ticketSuggestions as $suggestion)
                            <li class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-n-0)] px-3 py-2.5">
                                <p class="flex flex-wrap items-center gap-1.5 text-xs font-bold text-[color:var(--tm-text)]">{{ $suggestion['user_name'] }} <span class="rounded-[var(--tm-r-full)] bg-[color:var(--tm-success-50)] px-2 py-0.5 text-[0.62rem] font-bold tabular-nums text-[color:var(--tm-success-700)]">{{ $suggestion['match_count'] }} keahlian</span></p>
                                <p class="mt-1 text-[0.68rem] leading-5 text-[color:var(--tm-text-muted)]">{{ implode(', ', $suggestion['matching_skill_names']) }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs leading-5 text-[color:var(--tm-text-muted)] {{ $ticketSuggestions->isNotEmpty() ? 'hidden' : '' }}" data-ticket-suggestion-empty>Belum ada teknisi Tier 2 dengan keahlian yang sesuai layanan ini.</p>
                </div>
            </div>

            <button type="submit" class="ui-btn ui-btn-primary w-full" data-ticket-triage-submit>
                <span data-ticket-triage-submit-label>Simpan triase</span>
                <span class="hidden" data-ticket-triage-submit-loading aria-hidden="true">Menyimpan…</span>
            </button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canAssignTierTwo)
    <x-ui.modal-panel id="ticket-assign-tier-two-modal" labelledby="assign-tier-two-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-assign-tier-two-modal'">
    <section class="ui-panel" aria-labelledby="assign-tier-two-heading">
        <div class="ui-panel-header">
            <h2 id="assign-tier-two-heading" class="ui-section-title">Tugaskan ke Tier 2</h2>
        </div>
        <form method="POST" action="{{ route('tickets.assign-tier-2', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-assignment-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-assign-tier-two-modal">
            <div>
                <label for="assign-tier-two-user" class="ui-field-label">Teknisi Tier 2 <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <select id="assign-tier-two-user" name="assigned_to_id" required class="ui-select mt-2">
                    <option value="">Pilih teknisi</option>
                    @foreach ($tierTwoUsers as $tierTwoUser)
                        <option value="{{ $tierTwoUser->id }}">{{ $tierTwoUser->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to_id')<x-field-error :message="$message" />@enderror
            </div>
            <div>
                <label for="assign-tier-two-reason" class="ui-field-label">Catatan penugasan</label>
                <textarea id="assign-tier-two-reason" name="reason" rows="3" class="ui-textarea mt-2" placeholder="Opsional: jelaskan konteks alih penanganan.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-secondary w-full" data-ticket-assignment-submit>
                <span data-ticket-assignment-submit-label>Tugaskan ke Tier 2</span>
                <span class="hidden" data-ticket-assignment-submit-loading aria-hidden="true">Menyimpan…</span>
            </button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

@if ($canReturnToTierOne)
    <x-ui.modal-panel id="ticket-return-tier-one-modal" labelledby="return-tier-one-heading" :auto-open="$errors->any() && old('_action_modal') === 'ticket-return-tier-one-modal'">
    <section class="ui-panel border-l-4 border-l-[color:var(--tm-warning-600)]" aria-labelledby="return-tier-one-heading">
        <div class="ui-panel-header">
            <h2 id="return-tier-one-heading" class="ui-section-title">Kembalikan ke Tier 1</h2>
        </div>
        <form method="POST" action="{{ route('tickets.return-to-tier-1', $ticket) }}" class="space-y-4 p-5 sm:p-6" data-ticket-return-form>
            @csrf
            <input type="hidden" name="_action_modal" value="ticket-return-tier-one-modal">
            <div>
                <label for="return-tier-one-reason" class="ui-field-label">Alasan pengembalian <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span></label>
                <textarea id="return-tier-one-reason" name="reason" rows="4" required class="ui-textarea mt-2" placeholder="Jelaskan informasi atau tindakan yang masih diperlukan.">{{ old('reason') }}</textarea>
                @error('reason')<x-field-error :message="$message" />@enderror
            </div>
            <button type="submit" class="ui-btn ui-btn-warning w-full" data-ticket-return-submit>Kembalikan ke Tier 1</button>
        </form>
    </section>
    </x-ui.modal-panel>
@endif

</div>
@endif
