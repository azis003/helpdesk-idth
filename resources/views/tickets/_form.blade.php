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
        <h1 class="ui-page-title">Isi Formulir Layanan</h1>
    </div>
    <a href="{{ route('tickets.create') }}" class="ui-btn ui-btn-ghost">Ganti layanan</a>
</div>

<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" data-ticket-form data-loading-message="Mengirim tiket..." class="ui-panel ui-ticket-form mt-6 overflow-hidden" aria-labelledby="reporter-heading request-form-heading">
    @csrf
    <input type="hidden" name="service_type_id" value="{{ $selectedServiceType->getKey() }}">

    <section class="ui-form-section" aria-labelledby="reporter-heading">
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
                <dl class="ui-requester-facts grid gap-4 sm:grid-cols-2">
                    <div class="ui-requester-fact flex items-start gap-3">
                        <span class="ui-requester-icon mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2" /><path stroke-linecap="round" d="M5.5 19.2a6.5 6.5 0 0 1 13 0" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Nama</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($actor->name) }}</dd>
                        </div>
                    </div>
                    <div class="ui-requester-fact flex items-start gap-3">
                        <span class="ui-requester-icon mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h15M6 19.5V7.2a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v12.3M9 10h6M9 13.5h6M9 17h3" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">Tim kerja</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($teamName) }}</dd>
                        </div>
                    </div>
                    <div class="ui-requester-fact flex items-start gap-3">
                        <span class="ui-requester-icon mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="5.5" width="15" height="13" rx="1.5" /><path stroke-linecap="round" d="M8 9h8M8 12.5h4M8 16h6" /></svg>
                        </span>
                        <div>
                            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">NIP</dt>
                            <dd class="mt-1 text-sm font-bold text-[#35505b]">{{ $displayValue($actor->nip) }}</dd>
                        </div>
                    </div>
                    <div class="ui-requester-fact flex items-start gap-3">
                        <span class="ui-requester-icon mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1fbfe] text-[#147a79]" aria-hidden="true">
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

    <section class="ui-form-section border-t border-[#e7eef1]" aria-labelledby="request-form-heading">
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

            <div class="ui-ticket-field-grid grid gap-5 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="subject" class="ui-field-label">Judul <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="150" class="ui-input mt-2" placeholder="Contoh: Wi-Fi lantai 2 tidak tersambung" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                    @error('subject')<p id="subject-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="description" class="ui-field-label">Deskripsi <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                    <textarea id="description" name="description" rows="5" required maxlength="10000" class="ui-textarea mt-2" placeholder="Apa yang terjadi, kapan mulai, dan apa dampaknya?" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                    @error('description')<p id="description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="priority" class="ui-field-label">Prioritas <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
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
                        <label for="floor_id" class="ui-field-label">Lokasi <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">wajib</span></label>
                        <p id="floor_id-help" class="ui-field-help">Pilih gedung dan lantai. Wajib untuk layanan ini.</p>
                        <select id="floor_id" name="floor_id" required class="ui-select mt-2" @error('floor_id') aria-invalid="true" aria-describedby="floor_id-error" @else aria-describedby="floor_id-help" @enderror>
                            <option value="">Pilih gedung dan lantai</option>
                            @foreach ($buildings as $building)
                                @if ($building->floors->isNotEmpty())
                                    <optgroup label="{{ $building->name }}">
                                        @foreach ($building->floors as $floor)
                                            <option value="{{ $floor->id }}" @selected((string) old('floor_id') === (string) $floor->id)>{{ $building->name }} — {{ $floor->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        @error('floor_id')<p id="floor_id-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>

            @if ($requesterFields->isNotEmpty())
                <div class="mt-6 space-y-4 border-t border-[#e7eef1] pt-6">
                    @foreach ($requesterFields as $field)
                        <x-tickets.dynamic-field :field="$field" :service="$selectedServiceType" />
                    @endforeach
                </div>
            @endif

            @if ($serviceAttachmentPolicies->isNotEmpty())
                <div class="mt-6 border-t border-[#e7eef1] pt-6">
                    <p class="ui-field-label">Lampiran <span class="font-normal text-[#78909a]">(opsional)</span></p>
                    <div class="mt-3 space-y-4">
                    @foreach ($serviceAttachmentPolicies as $policy)
                        @php
                            $accept = collect($policy->allowed_mimes ?? [])->merge(collect($policy->allowed_extensions ?? [])->map(fn ($extension) => '.'.ltrim($extension, '.')))->implode(',');
                            $fileSize = $policy->max_file_size_kb % 1024 === 0
                                ? intdiv($policy->max_file_size_kb, 1024).' MB'
                                : $policy->max_file_size_kb.' KB';
                            $formatHint = $policy->service_type_id === null ? 'Dokumen atau gambar · ' : '';
                        @endphp
                        <div class="{{ $loop->last ? '' : 'border-b border-[#e7eef1] pb-4' }}">
                            <label for="attachment-policy-{{ $policy->id }}" class="ui-field-label">{{ $policy->label }}</label>
                            <p id="attachment-policy-{{ $policy->id }}-help" class="ui-field-help">{{ $formatHint }}Maks. {{ $policy->max_file_count }} berkas · {{ $fileSize }} per berkas.</p>
                            <input id="attachment-policy-{{ $policy->id }}" name="attachments[{{ $policy->id }}][]" type="file" class="ui-file-input mt-2" multiple @if ($accept !== '') accept="{{ $accept }}" @endif aria-describedby="attachment-policy-{{ $policy->id }}-help">
                        </div>
                    @endforeach
                    </div>
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
