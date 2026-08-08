@extends('layouts.app')

@section('title', 'Kebijakan operasional — '.$branding['application_name'])
@section('header_kicker', 'Administrasi')
@section('header_title', 'Kebijakan operasional')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Kontrol workflow tiket</p>
            <h1 class="ui-page-title">Kebijakan operasional</h1>
            <p class="ui-page-description">Atur SLA, kalender jam layanan, batas waktu workflow, dan Manajer TI tanpa mengubah kode atau database secara langsung.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-ghost">Kembali ke dasbor <span aria-hidden="true">→</span></a>
    </div>

    <div class="mt-8 space-y-5">
        <section class="ui-panel overflow-hidden" aria-labelledby="sla-heading">
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Target penyelesaian</p>
                    <h2 id="sla-heading" class="ui-section-title">Target SLA per layanan</h2>
                    <p class="ui-section-description">Target dihitung dalam hari kerja dan disimpan sebagai versi baru agar tiket lama tetap memakai kebijakan yang berlaku saat dibuat.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.operational-policies.sla.update') }}" class="p-5 sm:p-6">
                @csrf
                @method('PUT')
                <div class="grid gap-3 lg:grid-cols-2">
                    @foreach ($serviceTypes as $serviceType)
                        @php
                            $sla = $serviceType->activeSlaPolicy;
                            $usesSla = $sla?->uses_sla ?? $serviceType->code !== 'SVC-07';
                            $targetDays = $sla?->target_working_days;
                        @endphp
                        <article class="rounded-xl border border-[#dce9ed] bg-[#f8fbfc] p-4 sm:p-5">
                            <input type="hidden" name="policies[{{ $serviceType->id }}][service_type_id]" value="{{ $serviceType->id }}">
                            <input type="hidden" name="policies[{{ $serviceType->id }}][uses_sla]" value="0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.13em] text-[#5e8792]">{{ $serviceType->code }}</p>
                                    <h3 class="mt-1 text-sm font-extrabold text-[#29434d]">{{ $serviceType->name }}</h3>
                                    <p class="mt-1 text-xs text-[#78909a]">Kelas nomor: {{ $serviceType->ticket_class ?? 'Ditentukan subjenis' }}</p>
                                </div>
                                @if ($serviceType->code === 'SVC-07')
                                    <span class="ui-status ui-status-inactive shrink-0">Tanpa SLA</span>
                                @else
                                    <label class="inline-flex min-h-9 shrink-0 items-center gap-2 rounded-lg border border-[#dce9ed] bg-white px-3 text-xs font-bold text-[#526f79]">
                                        <input type="checkbox" name="policies[{{ $serviceType->id }}][uses_sla]" value="1" class="ui-checkbox" @checked(old("policies.{$serviceType->id}.uses_sla", $usesSla))>
                                        Gunakan SLA
                                    </label>
                                @endif
                            </div>
                            <div class="mt-4 max-w-[14rem]">
                                <label for="sla-target-{{ $serviceType->id }}" class="ui-field-label">Target (hari kerja)</label>
                                <input id="sla-target-{{ $serviceType->id }}" name="policies[{{ $serviceType->id }}][target_working_days]" type="number" min="1" max="365" value="{{ old("policies.{$serviceType->id}.target_working_days", $targetDays) }}" class="ui-input mt-2" @disabled($serviceType->code === 'SVC-07')>
                                @if ($serviceType->code === 'SVC-07')
                                    <p class="mt-2 text-xs leading-5 text-[#78909a]">Layanan ini memang tidak menggunakan SLA sesuai PRD.</p>
                                @else
                                    <p class="mt-2 text-xs leading-5 text-[#78909a]">Prioritas tiket tidak mengubah target SLA.</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e7eef1] pt-5">
                    <p class="max-w-2xl text-xs leading-5 text-[#78909a]">Perubahan tidak mengubah snapshot kebijakan yang sudah dipakai transaksi sebelumnya.</p>
                    <button type="submit" class="ui-btn ui-btn-primary">Simpan target SLA</button>
                </div>
            </form>
        </section>

        <section class="ui-panel overflow-hidden" aria-labelledby="calendar-heading">
            @php
                $calendarDays = $calendar?->working_days ?? \App\Services\OperationalPolicyService::DEFAULT_WORKING_DAYS;
                $holidayLines = $calendar?->holidays?->map(fn ($holiday) => $holiday->holiday_date?->format('Y-m-d').'|'.$holiday->name)->implode("\n") ?? '';
            @endphp
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Waktu operasional</p>
                    <h2 id="calendar-heading" class="ui-section-title">Kalender jam layanan</h2>
                    <p class="ui-section-description">SLA hanya berjalan pada hari dan jam yang dipilih. Kalender yang digunakan transaksi lama tetap tersimpan sebagai snapshot.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.operational-policies.calendar.update') }}" class="space-y-5 p-5 sm:p-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="calendar-timezone" class="ui-field-label">Zona waktu</label>
                        <input id="calendar-timezone" name="timezone" value="Asia/Jakarta" readonly class="ui-input mt-2 bg-[#f1f6f7]" aria-describedby="calendar-timezone-help">
                        <p id="calendar-timezone-help" class="mt-2 text-xs leading-5 text-[#78909a]">Zona waktu {{ $branding['application_name'] }} ditetapkan Asia/Jakarta.</p>
                    </div>
                    <div>
                        <label for="calendar-opens" class="ui-field-label">Jam mulai layanan <span class="text-rose-600">*</span></label>
                        <input id="calendar-opens" name="opens_at" type="time" value="{{ old('opens_at', substr((string) ($calendar?->opens_at ?? '08:00'), 0, 5)) }}" required class="ui-input mt-2">
                    </div>
                    <div>
                        <label for="calendar-closes" class="ui-field-label">Jam selesai layanan <span class="text-rose-600">*</span></label>
                        <input id="calendar-closes" name="closes_at" type="time" value="{{ old('closes_at', substr((string) ($calendar?->closes_at ?? '16:00'), 0, 5)) }}" required class="ui-input mt-2">
                    </div>
                </div>

                <fieldset>
                    <legend class="ui-field-label">Hari kerja <span class="text-rose-600">*</span></legend>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                        @foreach ($workingDayLabels as $dayNumber => $dayLabel)
                            <label class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-[#dce9ed] bg-[#f8fbfc] px-3 text-sm font-bold text-[#526f79]">
                                <input type="checkbox" name="working_days[]" value="{{ $dayNumber }}" class="ui-checkbox" @checked(in_array($dayNumber, old('working_days', $calendarDays), true))>
                                {{ $dayLabel }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label for="calendar-holidays" class="ui-field-label">Hari libur</label>
                    <textarea id="calendar-holidays" name="holidays_text" rows="5" class="ui-textarea mt-2" aria-describedby="calendar-holidays-help">{{ old('holidays_text', $holidayLines) }}</textarea>
                    <p id="calendar-holidays-help" class="mt-2 text-xs leading-5 text-[#78909a]">Satu baris untuk setiap hari libur dengan format <code class="rounded bg-[#eef4f5] px-1 py-0.5 text-[0.7rem]">YYYY-MM-DD|Nama hari libur</code>. Kosongkan jika tidak ada.</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#e7eef1] pt-5">
                    <p class="max-w-2xl text-xs leading-5 text-[#78909a]">Jam selesai harus setelah jam mulai. Hari libur dicatat bersama revision kalender.</p>
                    <button type="submit" class="ui-btn ui-btn-primary">Simpan kalender layanan</button>
                </div>
            </form>
        </section>

        <section class="ui-panel overflow-hidden" aria-labelledby="settings-heading">
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Batas workflow</p>
                    <h2 id="settings-heading" class="ui-section-title">Ambang dan batas waktu</h2>
                    <p class="ui-section-description">Nilai ini menjadi sumber kebijakan untuk scheduler, indikator SLA, dan alur buka kembali pada issue berikutnya.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.operational-policies.settings.update') }}" class="p-5 sm:p-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="setting-warning" class="ui-field-label">Ambang mendekati SLA (%) <span class="text-rose-600">*</span></label>
                        <input id="setting-warning" name="sla_warning_percent" type="number" min="1" max="100" value="{{ old('sla_warning_percent', $settings['sla_warning_percent']) }}" required class="ui-input mt-2">
                        <p class="mt-2 text-xs leading-5 text-[#78909a]">Tiket mendekati batas saat sisa aktif berada di bawah ambang ini.</p>
                    </div>
                    <div>
                        <label for="setting-requester" class="ui-field-label">Menunggu Pemohon (hari kerja) <span class="text-rose-600">*</span></label>
                        <input id="setting-requester" name="requester_wait_working_days" type="number" min="1" max="365" value="{{ old('requester_wait_working_days', $settings['requester_wait_working_days']) }}" required class="ui-input mt-2">
                    </div>
                    <div>
                        <label for="setting-confirmation" class="ui-field-label">Menunggu Konfirmasi (hari kerja) <span class="text-rose-600">*</span></label>
                        <input id="setting-confirmation" name="confirmation_wait_working_days" type="number" min="1" max="365" value="{{ old('confirmation_wait_working_days', $settings['confirmation_wait_working_days']) }}" required class="ui-input mt-2">
                    </div>
                    <div>
                        <label for="setting-reopen-window" class="ui-field-label">Jendela buka kembali (hari kerja) <span class="text-rose-600">*</span></label>
                        <input id="setting-reopen-window" name="reopen_window_working_days" type="number" min="1" max="365" value="{{ old('reopen_window_working_days', $settings['reopen_window_working_days']) }}" required class="ui-input mt-2">
                    </div>
                    <div>
                        <label for="setting-reopen-count" class="ui-field-label">Maksimum buka kembali (kali) <span class="text-rose-600">*</span></label>
                        <input id="setting-reopen-count" name="max_reopen_count" type="number" min="1" max="20" value="{{ old('max_reopen_count', $settings['max_reopen_count']) }}" required class="ui-input mt-2">
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e7eef1] pt-5">
                    <p class="max-w-2xl text-xs leading-5 text-[#78909a]">Setiap nilai yang berubah menjadi revision baru dan tidak mengubah transaksi historis secara diam-diam.</p>
                    <button type="submit" class="ui-btn ui-btn-primary">Simpan batas workflow</button>
                </div>
            </form>
        </section>

        <section class="ui-panel overflow-hidden" aria-labelledby="approver-heading">
            <div class="ui-panel-header">
                <div>
                    <p class="ui-eyebrow"><span class="ui-eyebrow-dot !bg-[#e4a72c] !shadow-[0_0_0_4px_#fff4d7]" aria-hidden="true"></span>Persetujuan tunggal</p>
                    <h2 id="approver-heading" class="ui-section-title">Manajer TI / Approver aktif</h2>
                    <p class="ui-section-description">Hanya satu pengguna aktif yang menerima permintaan persetujuan. Penggantian memindahkan approval tertunda dalam transaksi yang sama.</p>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-xl border border-[#dce9ed] bg-[#f8fbfc] p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#6f8a92]">Penetapan saat ini</p>
                    @if ($currentApprover?->user)
                        <div class="mt-4 flex items-start gap-3">
                            <span class="ui-avatar !h-11 !w-11 !rounded-xl">{{ strtoupper(substr($currentApprover->user->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-[#29434d]">{{ $currentApprover->user->name }}</p>
                                <p class="mt-1 truncate text-xs text-[#78909a]">{{ '@'.$currentApprover->user->username }}{{ $currentApprover->user->nip ? ' · '.$currentApprover->user->nip : '' }}</p>
                                <span class="ui-status ui-status-active mt-3">Aktif</span>
                            </div>
                        </div>
                        <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <div class="rounded-lg bg-white p-3"><dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Mulai</dt><dd class="mt-1 text-xs font-bold text-[#526f79]">{{ $currentApprover->started_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                            <div class="rounded-lg bg-white p-3"><dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Approval tertunda</dt><dd class="mt-1 text-xs font-bold text-[#526f79]">{{ $pendingApprovalCount }} permintaan</dd></div>
                        </dl>
                    @else
                        <div class="mt-4 rounded-lg border border-amber-200 bg-[#fff9e9] p-4 text-sm leading-6 text-amber-900" role="status">Belum ada Manajer TI/Approver aktif. Tetapkan pengguna yang memenuhi syarat sebelum workflow persetujuan digunakan.</div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.operational-policies.approver.update') }}" class="space-y-4" data-swal-confirm="Tetapkan pengguna ini sebagai Manajer TI/Approver aktif dan pindahkan approval tertunda?">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="replacement-user" class="ui-field-label">Pengguna pengganti <span class="text-rose-600">*</span></label>
                        <select id="replacement-user" name="replacement_user_id" required class="ui-select mt-2">
                            <option value="">Pilih pengguna aktif dengan role Approver</option>
                            @foreach ($approverCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('replacement_user_id') == $candidate->id)>{{ $candidate->name }} · {{ '@'.$candidate->username }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs leading-5 text-[#78909a]">Akun harus aktif, memiliki role Approver, dan sudah mengganti password awal.</p>
                    </div>
                    <div>
                        <label for="approver-reason" class="ui-field-label">Alasan penetapan/penggantian <span class="text-rose-600">*</span></label>
                        <textarea id="approver-reason" name="reason" rows="4" required maxlength="500" class="ui-textarea mt-2" placeholder="Contoh: pergantian Manajer TI per 1 September 2026.">{{ old('reason') }}</textarea>
                    </div>
                    <label class="flex items-start gap-3 rounded-lg border border-[#dce9ed] bg-[#f8fbfc] p-4 text-sm leading-6 text-[#526f79]">
                        <input type="checkbox" name="transfer_pending_approvals" value="1" required class="ui-checkbox mt-1">
                        <span>Saya memahami bahwa seluruh approval tertunda akan dipindahkan kepada pengguna pengganti dan perubahan ini dicatat di audit log.</span>
                    </label>
                    <button type="submit" class="ui-btn ui-btn-primary w-full sm:w-auto">Simpan penetapan approver</button>
                </form>
            </div>
        </section>
    </div>
@endsection
