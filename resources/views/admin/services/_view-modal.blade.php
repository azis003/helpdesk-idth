@php
    $categoryLabel = $serviceType->ticket_class ?: 'Belum diatur';
    $activeSlaPolicy = $serviceType->activeSlaPolicy;
    $requesterFields = $serviceType->activeFieldDefinitions->filter(fn ($field) => in_array($field->visibility, ['requester', 'both'], true));
    $internalFields = $serviceType->activeFieldDefinitions->filter(fn ($field) => $field->visibility === 'internal');

    // Presentasional saja - tidak mengubah data maupun logika.
    $serviceViewPanel = 'relative h-[calc(100dvh-2rem)] max-h-[calc(100dvh-2rem)] w-full max-w-3xl overflow-y-auto overscroll-contain rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)] sm:h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-4rem)]';
    $serviceViewHeader = 'sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-7';
    $serviceViewIconTile = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $serviceViewClose = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] transition-[background-color,border-color,color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]';
    $serviceViewEyebrow = 'text-[0.65rem] font-bold uppercase tracking-[0.14em] text-[color:var(--tm-brand-700)]';
    $serviceViewStat = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
    $serviceViewStatLabel = 'text-[0.65rem] font-extrabold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $serviceViewStatValue = 'mt-1.5 text-lg font-extrabold text-[color:var(--tm-text)]';
    $serviceViewSection = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)]';
    $serviceViewSectionHead = 'border-b border-[color:var(--tm-border-subtle)] px-4 py-3';
    $serviceViewSectionTitle = 'text-sm font-extrabold text-[color:var(--tm-text)]';
    $serviceViewTile = 'rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-3 py-2.5';
    $serviceViewMuted = 'text-xs leading-5 text-[color:var(--tm-text-muted)]';
@endphp

<div id="service-view-modal-{{ $serviceType->id }}" data-ui-modal class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
    <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
        <section role="dialog" aria-modal="true" aria-labelledby="service-view-title-{{ $serviceType->id }}" class="{{ $serviceViewPanel }}">
            <div class="{{ $serviceViewHeader }}">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="{{ $serviceViewIconTile }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="{{ $serviceViewEyebrow }}">Detail layanan</p>
                        <h2 id="service-view-title-{{ $serviceType->id }}" class="mt-1 text-lg font-extrabold tracking-tight text-[color:var(--tm-text)]">{{ $serviceType->name }}</h2>
                        <p class="mt-1 text-xs text-[color:var(--tm-text-muted)]">Ringkasan data master dan template formulir yang sedang aktif.</p>
                    </div>
                </div>
                <button type="button" data-ui-modal-close class="{{ $serviceViewClose }}" aria-label="Tutup detail layanan {{ $serviceType->name }}">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                </button>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="{{ $serviceViewStat }}"><dt class="{{ $serviceViewStatLabel }}">Kode layanan</dt><dd class="{{ $serviceViewStatValue }} tabular-nums">{{ $serviceType->code }}</dd></div>
                    <div class="{{ $serviceViewStat }}"><dt class="{{ $serviceViewStatLabel }}">Kategori</dt><dd class="{{ $serviceViewStatValue }}">{{ $categoryLabel }}</dd></div>
                    <div class="{{ $serviceViewStat }}"><dt class="{{ $serviceViewStatLabel }}">Target SLA</dt><dd class="{{ $serviceViewStatValue }} tabular-nums">@if ($activeSlaPolicy?->uses_sla && $activeSlaPolicy->target_working_days){{ $activeSlaPolicy->target_working_days }} hari kerja @elseif ($activeSlaPolicy)Tidak digunakan @else Belum diatur @endif</dd></div>
                    <div class="{{ $serviceViewStat }}"><dt class="{{ $serviceViewStatLabel }}">Status</dt><dd class="mt-1.5"><span class="ui-status {{ $serviceType->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $serviceType->is_active ? 'Aktif' : 'Nonaktif' }}</span></dd></div>
                </dl>

                <section class="{{ $serviceViewSection }}" aria-labelledby="service-view-description-{{ $serviceType->id }}">
                    <div class="{{ $serviceViewSectionHead }}"><h3 id="service-view-description-{{ $serviceType->id }}" class="{{ $serviceViewSectionTitle }}">Deskripsi layanan</h3></div>
                    <p class="px-4 py-4 text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $serviceType->description ?: 'Belum ada deskripsi layanan untuk layanan ini.' }}</p>
                </section>

                <section class="{{ $serviceViewSection }}" aria-labelledby="service-view-skills-{{ $serviceType->id }}">
                    <div class="{{ $serviceViewSectionHead }}"><h3 id="service-view-skills-{{ $serviceType->id }}" class="{{ $serviceViewSectionTitle }}">Syarat keahlian</h3></div>
                    <div class="p-4">
                        @if ($serviceType->skills->isNotEmpty())
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @foreach ($serviceType->skills as $skill)
                                    <li class="{{ $serviceViewTile }}"><p class="text-xs font-extrabold text-[color:var(--tm-text)]">{{ $skill->name }}</p>@if ($skill->description)<p class="mt-1 text-[0.68rem] leading-4 text-[color:var(--tm-text-muted)]">{{ $skill->description }}</p>@endif</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="{{ $serviceViewMuted }}">Belum ada syarat keahlian yang dipetakan.</p>
                        @endif
                    </div>
                </section>

                <section class="{{ $serviceViewSection }}" aria-labelledby="service-view-fields-{{ $serviceType->id }}">
                    <div class="flex items-center justify-between gap-3 {{ $serviceViewSectionHead }}"><h3 id="service-view-fields-{{ $serviceType->id }}" class="{{ $serviceViewSectionTitle }}">Template formulir aktif</h3><span class="ui-count tabular-nums">{{ $serviceType->activeFieldDefinitions->count() }} field</span></div>
                    <div class="p-4">
                        @if ($serviceType->activeFieldDefinitions->isNotEmpty())
                            <ol class="space-y-2">
                                @foreach ($serviceType->activeFieldDefinitions as $field)
                                    <li class="flex items-start gap-3 {{ $serviceViewTile }} py-3">
                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-[var(--tm-r-xs)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] text-xs font-extrabold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $loop->iteration }}</span>
                                        <div class="min-w-0 flex-1"><p class="text-xs font-extrabold text-[color:var(--tm-text)]">{{ $field->label }} @if ($field->is_required)<span class="text-[color:var(--tm-danger-600)]">*</span>@endif</p><p class="mt-1 text-[0.68rem] text-[color:var(--tm-text-muted)]">{{ $field->key }} · {{ $fieldTypes[$field->field_type] ?? $field->field_type }} · {{ $field->visibility === 'requester' ? 'Pemohon' : ($field->visibility === 'internal' ? 'Tim TI' : 'Pemohon dan Tim TI') }}</p></div>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <p class="{{ $serviceViewMuted }}">Belum ada field formulir aktif.</p>
                        @endif
                    </div>
                </section>

                <div class="flex flex-wrap justify-end gap-2 border-t border-[color:var(--tm-border-subtle)] pt-5">
                    <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost !min-h-10">Tutup</button>
                    <button type="button" data-ui-modal-close data-ui-modal-open="service-edit-modal-{{ $serviceType->id }}" class="ui-btn ui-btn-primary !min-h-10">Edit layanan</button>
                </div>
            </div>
        </section>
    </div>
</div>
