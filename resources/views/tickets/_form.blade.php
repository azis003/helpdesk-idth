@php
    $locationRequired = in_array($selectedServiceType->code, ['SVC-01', 'SVC-05'], true);
    $requesterFields = $selectedServiceType->activeFieldDefinitions
        ->whereIn('visibility', ['requester', 'both'])
        ->values();
    $serviceAttachmentPolicies = $attachmentPolicies
        ->filter(fn ($policy): bool => $policy->service_type_id === null || (int) $policy->service_type_id === (int) $selectedServiceType->getKey())
        ->values();
    $teamName = $actor->currentTeamMembership?->workTeam?->name;
    $displayValue = static fn (mixed $value): string => filled($value) ? (string) $value : 'Belum diisi';
@endphp

<div class="ui-page-header">
    <div>
        <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Tiket baru</p>
        <h1 class="ui-page-title">Isi formulir layanan</h1>
    </div>
    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-ghost">Ganti layanan</a>
</div>

<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" data-ticket-form data-loading-message="Mengirim tiket..." class="ui-panel mt-6 overflow-hidden" aria-labelledby="reporter-heading request-form-heading">
    @csrf
    <input type="hidden" name="service_type_id" value="{{ $selectedServiceType->getKey() }}">

    <section aria-labelledby="reporter-heading">
        <div class="ui-panel-header flex items-start gap-3">
            <!-- <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#e8f7fb] text-[#147a79]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2" /><path stroke-linecap="round" stroke-linejoin="round" d="M5.5 19.2a6.5 6.5 0 0 1 13 0M17.5 5.8a3.1 3.1 0 0 1 0 4.4M18.7 14.2a5 5 0 0 1 2.3 4.2" /></svg>
            </span> -->
            <div>
                <h2 id="reporter-heading" class="ui-section-title">Data Pelapor</h2>
            </div>
        </div>
        <div class="p-5 sm:p-6">
            @if ($canCreateForOthers)
                <label for="requester_id" class="ui-field-label">Pemohon <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                <p id="requester_id-help" class="ui-field-help">Pilih pegawai yang menyampaikan permintaan.</p>
                <select id="requester_id" name="requester_id" required class="ui-select mt-2" @error('requester_id') aria-invalid="true" aria-describedby="requester_id-error" @else aria-describedby="requester_id-help" @enderror>
                    <option value="">Pilih pemohon</option>
                    @foreach ($requesters as $requester)
                        <option value="{{ $requester->id }}" @selected((string) old('requester_id') === (string) $requester->id)>{{ $requester->name }}{{ $requester->nip ? ' — NIP '.$requester->nip : '' }}</option>
                    @endforeach
                </select>
                @error('requester_id')<p id="requester_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
            @else
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2" /><path stroke-linecap="round" d="M5.5 19.2a6.5 6.5 0 0 1 13 0" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Nama</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($actor->name) }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h15M6 19.5V7.2a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v12.3M9 10h6M9 13.5h6M9 17h3" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Tim kerja</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($teamName) }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="5.5" width="15" height="13" rx="1.5" /><path stroke-linecap="round" d="M8 9h8M8 12.5h4M8 16h6" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">NIP</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($actor->nip) }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="6.5" width="15" height="11" rx="1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="m5.5 8 6.5 5 6.5-5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Email</dt>
                            <dd class="mt-1 break-all text-sm font-bold text-[#35505b]">{{ $displayValue($actor->email) }}</dd>
                        </div>
                    </div>
                </dl>
                <input type="hidden" name="requester_id" value="{{ $actor->getKey() }}">
            @endif
        </div>
    </section>

    <section class="border-t border-[#e7eef1]" aria-labelledby="request-form-heading">
        <div class="ui-panel-header flex items-start gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#fff4d7] text-[#9a6700]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h9l3 3v12H6zM14 4.5v3h4M9 12h6M9 15.5h4" /><path stroke-linecap="round" d="m15.5 15.5 1.2 1.2 2.8-2.8" /></svg>
            </span>
            <div class="min-w-0">
                <h2 id="request-form-heading" class="ui-section-title">Formulir Permintaan</h2>
                <p class="ui-section-description">{{ $selectedServiceType->code }} · {{ $selectedServiceType->name }}</p>
            </div>
        </div>
        <div class="p-5 sm:p-6">
            @error('service_type_id')
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" role="alert">{{ $message }}</div>
            @enderror

            @if ($announcements->isNotEmpty())
                <div class="mb-6 rounded-xl border border-[#f0d89a] bg-[#fffaf0] p-4" role="status" aria-label="Pengumuman layanan">
                    @foreach ($announcements as $announcement)
                        <div class="flex items-start gap-3 {{ $loop->last ? '' : 'border-b border-[#f0d89a] pb-3 mb-3' }}">
                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#fff0b9] text-[#9a6700]" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M8 16.5 9.5 20h2L10 16.5M18.5 10a3 3 0 0 1 0 4" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-extrabold text-[#6f5300]">{{ $announcement->title }}</p>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-[#806f3f]">{{ $announcement->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="space-y-5">
                <div>
                    <label for="subject" class="ui-field-label">Judul <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Wi-Fi lantai 2 tidak tersambung" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                    @error('subject')<p id="subject-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="ui-field-label">Deskripsi <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <textarea id="description" name="description" rows="5" required maxlength="10000" class="ui-textarea mt-2" placeholder="Apa yang terjadi, kapan mulai, dan apa dampaknya?" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                    @error('description')<p id="description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="priority" class="ui-field-label">Prioritas <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <p id="priority-help" class="ui-field-help">Tim TI dapat menyesuaikannya saat triase.</p>
                    <select id="priority" name="priority" required class="ui-select mt-2" @error('priority') aria-invalid="true" aria-describedby="priority-error" @else aria-describedby="priority-help" @enderror>
                        <option value="">Pilih prioritas</option>
                        @foreach (\App\Enums\Priority::labels() as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')<p id="priority-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                @if ($locationRequired)
                    <div>
                        <label for="room_id" class="ui-field-label">Lokasi <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                        <p id="room_id-help" class="ui-field-help">Wajib untuk layanan ini.</p>
                        <select id="room_id" name="room_id" required class="ui-select mt-2" @error('room_id') aria-invalid="true" aria-describedby="room_id-error" @else aria-describedby="room_id-help" @enderror>
                            <option value="">Pilih lokasi</option>
                            @foreach ($buildings as $building)
                                @foreach ($building->floors as $floor)
                                    <optgroup label="{{ $building->name }} — {{ $floor->name }}">
                                        @foreach ($floor->rooms as $room)
                                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>{{ $room->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endforeach
                        </select>
                        @error('room_id')<p id="room_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>

            @if ($requesterFields->isNotEmpty())
                <div class="mt-6 space-y-4 border-t border-[#e7eef1] pt-6">
                    @foreach ($requesterFields as $field)
                        <x-tickets.dynamic-field :field="$field" :service="$selectedServiceType" />
                    @endforeach
                </div>
            @else
                <p class="ui-empty mt-6 !p-6">Belum ada field tambahan untuk layanan ini.</p>
            @endif

            @if ($serviceAttachmentPolicies->isNotEmpty())
                <div class="mt-6 space-y-4 border-t border-[#e7eef1] pt-6">
                    <div>
                        <p class="ui-field-label">Lampiran</p>
                        <p class="ui-field-help">Tambahkan berkas pendukung bila diperlukan.</p>
                    </div>
                    @foreach ($serviceAttachmentPolicies as $policy)
                        @php
                            $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                        @endphp
                        <div class="rounded-xl border border-[#e5edef] bg-[#fbfdfd] p-4 sm:p-5">
                            <label for="attachment-policy-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                            <p id="attachment-policy-{{ $policy->id }}-help" class="ui-field-help">Maksimal {{ $policy->max_file_count }} berkas, {{ $policy->max_file_size_kb }} KB per berkas{{ $policy->allowed_extensions ? ' · '.implode(', ', $policy->allowed_extensions) : '' }}.</p>
                            <input id="attachment-policy-{{ $policy->id }}" name="attachments[{{ $policy->id }}][]" type="file" class="ui-input mt-2 file:mr-3 file:rounded-md file:border-0 file:bg-[#e8f7fb] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#1d5d72]" multiple @if ($accept !== '') accept="{{ $accept }}" @endif aria-describedby="attachment-policy-{{ $policy->id }}-help">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="ui-sticky-actions !mx-0 rounded-none">
        <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-ghost">Kembali</a>
        <button type="submit" data-ticket-submit class="ui-btn ui-btn-primary min-w-32">
            <span data-ticket-submit-label>Kirim tiket</span>
            <span data-ticket-submit-loading class="hidden" aria-hidden="true">Mengirim…</span>
        </button>
    </div>
</form>
