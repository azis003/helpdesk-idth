@extends('layouts.app')

@php
    $autoOpenForm = old('_sla_form');
    $serviceCount = $serviceTypes->count();
    $usingSlaCount = $serviceTypes->filter(fn ($serviceType) => $serviceType->activeSlaPolicy?->uses_sla === true)->count();
@endphp

@section('title', 'Manajemen SLA — '.$branding['application_name'])
@section('header_kicker', 'Konfigurasi layanan')
@section('header_title', 'Manajemen SLA')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold tracking-tight text-[#18252b]">Manajemen SLA</h1>
        <p class="mt-2 text-sm text-[#718088]">Target penyelesaian per layanan dikelola dari satu daftar dengan riwayat versi yang tetap aman.</p>
    </div>

    <section id="sla-heading" class="overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="sla-list-title">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="sla-list-title" class="text-xl font-extrabold tracking-tight">Daftar SLA</h2>
                <p class="mt-1 text-sm text-blue-100">{{ $serviceCount }} layanan · {{ $usingSlaCount }} menggunakan SLA</p>
            </div>
            <p class="text-xs font-bold text-blue-100">Edit target dari aksi pada setiap baris</p>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <p class="text-sm leading-6 text-[#718088]">Perubahan disimpan sebagai versi kebijakan baru. Snapshot SLA pada tiket lama tidak ikut berubah.</p>

        <div class="mt-4 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
            <table class="min-w-[1060px] w-full border-collapse text-left text-sm">
                <caption class="sr-only">Daftar kebijakan SLA, target hari kerja, status, versi, dan aksi</caption>
                <thead class="bg-[#fbfcfd] text-[#34495a]">
                    <tr>
                        <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                        <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Layanan</th>
                        <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Target SLA</th>
                        <th scope="col" class="w-28 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Status</th>
                        <th scope="col" class="w-24 border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Versi</th>
                        <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Berlaku mulai</th>
                        <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Diubah oleh</th>
                        <th scope="col" class="w-24 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceTypes as $serviceType)
                        @php
                            $sla = $serviceType->activeSlaPolicy;
                            $usesSla = $sla?->uses_sla ?? $serviceType->code !== 'SVC-07';
                            $targetDays = $sla?->target_working_days;
                            $statusLabel = $sla === null ? 'Belum diatur' : ($usesSla ? 'Aktif' : 'Tanpa SLA');
                        @endphp
                        <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                            <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ $loop->iteration }}</td>
                            <td class="border-b border-[#e5eaed] px-4 py-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex min-w-16 items-center justify-center rounded-md bg-[#e8f7fb] px-2 py-1 font-mono text-[0.68rem] font-extrabold tracking-[0.08em] text-[#1d6579]">{{ $serviceType->code }}</span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[#112b49]">{{ $serviceType->name }}</p>
                                        <p class="mt-1 text-xs text-[#78909a]">Kelas nomor: {{ $serviceType->ticket_class ?? 'Ditentukan subjenis' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="border-b border-[#e5eaed] px-4 py-5">
                                @if ($usesSla && $targetDays !== null)
                                    <p class="font-mono text-base font-extrabold tracking-tight text-[#172d45]">{{ $targetDays }} <span class="font-sans text-xs font-bold text-[#6a8089]">hari kerja</span></p>
                                    <p class="mt-1 text-[0.68rem] text-[#78909a]">Prioritas tidak mengubah target.</p>
                                @else
                                    <p class="font-semibold text-[#78909a]">Tidak menggunakan SLA</p>
                                    <p class="mt-1 text-[0.68rem] text-[#9aabb0]">Timer SLA tidak berjalan.</p>
                                @endif
                            </td>
                            <td class="border-b border-[#e5eaed] px-4 py-5 text-center"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $sla === null ? 'bg-[#fff6df] text-[#a16207]' : ($usesSla ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]') }}">{{ $statusLabel }}</span></td>
                            <td class="border-b border-[#e5eaed] px-4 py-5 font-mono text-xs font-semibold text-[#526f79]">{{ $sla ? 'v'.$sla->version : '—' }}</td>
                            <td class="whitespace-nowrap border-b border-[#e5eaed] px-4 py-5 text-xs font-semibold text-[#526f79]">
                                {{ $sla?->effective_from?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? 'Belum ada versi' }}
                            </td>
                            <td class="max-w-36 border-b border-[#e5eaed] px-4 py-5 text-xs text-[#6a8089]">{{ $sla?->changedBy?->name ?? 'Sistem awal' }}</td>
                            <td class="border-b border-[#e5eaed] px-4 py-5">
                                <div class="flex justify-center">
                                    <button type="button" data-ui-modal-open="sla-edit-modal-{{ $serviceType->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="{{ $sla ? 'Edit' : 'Atur' }} SLA {{ $serviceType->code }}" title="{{ $sla ? 'Edit SLA' : 'Atur SLA' }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        <span class="sr-only">{{ $sla ? 'Edit' : 'Atur' }} SLA {{ $serviceType->code }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="border-b border-[#e5eaed] px-4 py-12 text-center text-[#718088]">
                                <p class="font-extrabold text-[#35505b]">Belum ada layanan untuk dikonfigurasi.</p>
                                <p class="mt-2 text-sm text-[#78909a]">Tambahkan layanan terlebih dahulu dari Manajemen Layanan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#e5eaed] md:hidden">
            @forelse ($serviceTypes as $serviceType)
                @php
                    $sla = $serviceType->activeSlaPolicy;
                    $usesSla = $sla?->uses_sla ?? $serviceType->code !== 'SVC-07';
                    $targetDays = $sla?->target_working_days;
                    $statusLabel = $sla === null ? 'Belum diatur' : ($usesSla ? 'Aktif' : 'Tanpa SLA');
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#78909a]">No. {{ $loop->iteration }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex rounded-md bg-[#e8f7fb] px-2 py-1 font-mono text-[0.68rem] font-extrabold tracking-[0.08em] text-[#1d6579]">{{ $serviceType->code }}</span>
                                <h3 class="min-w-0 text-sm font-bold leading-5 text-[#112b49]">{{ $serviceType->name }}</h3>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $sla === null ? 'bg-[#fff6df] text-[#a16207]' : ($usesSla ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]') }}">{{ $statusLabel }}</span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-[#f8fafb] p-4 text-xs">
                        <div>
                            <dt class="font-bold uppercase tracking-wide text-[#78909a]">Target SLA</dt>
                            <dd class="mt-1 text-[#172d45]">{{ $usesSla && $targetDays !== null ? $targetDays.' hari kerja' : 'Tidak ada target' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wide text-[#78909a]">Versi</dt>
                            <dd class="mt-1 font-mono text-[#172d45]">{{ $sla ? 'v'.$sla->version : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wide text-[#78909a]">Berlaku mulai</dt>
                            <dd class="mt-1 text-[#172d45]">{{ $sla?->effective_from?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? 'Belum ada versi' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold uppercase tracking-wide text-[#78909a]">Diubah oleh</dt>
                            <dd class="mt-1 truncate text-[#172d45]">{{ $sla?->changedBy?->name ?? 'Sistem awal' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex justify-end">
                        <button type="button" data-ui-modal-open="sla-edit-modal-{{ $serviceType->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#f1b900] text-white transition hover:bg-[#d49f00] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f1b900] focus-visible:ring-offset-2" aria-label="{{ $sla ? 'Edit' : 'Atur' }} SLA {{ $serviceType->code }}" title="{{ $sla ? 'Edit SLA' : 'Atur SLA' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            <span class="sr-only">{{ $sla ? 'Edit' : 'Atur' }} SLA {{ $serviceType->code }}</span>
                        </button>
                    </div>
                </article>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="font-extrabold text-[#35505b]">Belum ada layanan untuk dikonfigurasi.</p>
                    <p class="mt-2 text-sm text-[#78909a]">Tambahkan layanan terlebih dahulu dari Manajemen Layanan.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $serviceCount }} layanan ditampilkan dalam daftar aktif.</p>
            <p>Target dihitung dalam hari kerja sesuai kalender layanan.</p>
        </div>
        </div>
    </section>

    @foreach ($serviceTypes as $serviceType)
        @php
            $sla = $serviceType->activeSlaPolicy;
            $usesSla = $sla?->uses_sla ?? $serviceType->code !== 'SVC-07';
            $targetDays = $sla?->target_working_days;
            $isAutoOpen = $autoOpenForm === 'edit-'.$serviceType->id && $errors->any();
        @endphp
        <div id="sla-edit-modal-{{ $serviceType->id }}" data-ui-modal data-auto-open="{{ $isAutoOpen ? 'true' : 'false' }}" data-reset-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="sla-edit-title-{{ $serviceType->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-xl overflow-y-auto rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:max-h-[calc(100vh-4rem)]">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                        <h2 id="sla-edit-title-{{ $serviceType->id }}" class="text-lg font-extrabold">{{ $sla ? 'Edit' : 'Atur' }} SLA {{ $serviceType->code }}</h2>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog {{ $sla ? 'edit' : 'atur' }} SLA {{ $serviceType->code }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.operational-policies.sla.update') }}" data-ui-modal-form data-loading-message="Menyimpan kebijakan SLA..." class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_sla_form" value="edit-{{ $serviceType->id }}">

                        @foreach ($serviceTypes as $formServiceType)
                            @php
                                $formSla = $formServiceType->activeSlaPolicy;
                                $formUsesSla = $formSla?->uses_sla ?? $formServiceType->code !== 'SVC-07';
                                $formTargetDays = $formSla?->target_working_days;
                                $isCurrentService = $formServiceType->is($serviceType);
                                $formUsesSlaValue = old("policies.{$formServiceType->id}.uses_sla", $formUsesSla) ? 1 : 0;
                                $formTargetValue = old("policies.{$formServiceType->id}.target_working_days", $formTargetDays);
                            @endphp
                            <input type="hidden" name="policies[{{ $formServiceType->id }}][service_type_id]" value="{{ $formServiceType->id }}">
                            @if ($isCurrentService)
                                <input type="hidden" name="policies[{{ $formServiceType->id }}][uses_sla]" value="0">
                            @else
                                <input type="hidden" name="policies[{{ $formServiceType->id }}][uses_sla]" value="{{ $formUsesSlaValue }}">
                                <input type="hidden" name="policies[{{ $formServiceType->id }}][target_working_days]" value="{{ $formTargetValue }}">
                            @endif
                        @endforeach

                        <div class="mt-1">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex min-w-16 items-center justify-center rounded-md bg-[#e8f7fb] px-2 py-1 font-mono text-[0.68rem] font-extrabold tracking-[0.08em] text-[#1d6579]">{{ $serviceType->code }}</span>
                                <div class="min-w-0">
                                    <p class="font-extrabold text-[#29434d]">{{ $serviceType->name }}</p>
                                    <p class="mt-1 text-xs text-[#78909a]">Kelas nomor: {{ $serviceType->ticket_class ?? 'Ditentukan subjenis' }}</p>
                                </div>
                            </div>

                            <fieldset class="mt-5">
                                <legend class="ui-field-label">Status kebijakan</legend>
                                @if ($serviceType->code === 'SVC-07')
                                    <p class="mt-2 inline-flex min-h-10 items-center rounded-lg border border-[#dfe5e7] bg-white px-3 text-xs font-bold text-[#657984]">Tanpa SLA sesuai karakter layanan</p>
                                @else
                                    <label for="sla-uses-{{ $serviceType->id }}" class="mt-2 inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#c4ebe5] bg-white px-3 text-xs font-bold text-[#526f79]">
                                        <input id="sla-uses-{{ $serviceType->id }}" name="policies[{{ $serviceType->id }}][uses_sla]" type="checkbox" value="1" class="ui-checkbox" @checked((bool) old("policies.{$serviceType->id}.uses_sla", $usesSla)) data-ui-modal-focus>
                                        Gunakan SLA untuk layanan ini
                                    </label>
                                @endif
                            </fieldset>

                            <div class="mt-5">
                                <label for="sla-target-{{ $serviceType->id }}" class="ui-field-label">Target SLA (hari kerja)</label>
                                <div class="mt-2 flex items-center gap-2">
                                    <input id="sla-target-{{ $serviceType->id }}" name="policies[{{ $serviceType->id }}][target_working_days]" type="number" min="1" max="365" value="{{ old("policies.{$serviceType->id}.target_working_days", $targetDays) }}" class="ui-input max-w-40 font-mono font-bold" @disabled($serviceType->code === 'SVC-07') @if ($serviceType->code === 'SVC-07') aria-describedby="sla-target-help-{{ $serviceType->id }}" @else data-ui-modal-focus @endif>
                                    <span class="text-xs font-bold text-[#6a8089]">hari kerja</span>
                                </div>
                                @error("policies.{$serviceType->id}.target_working_days")
                                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                                @enderror
                                <p id="sla-target-help-{{ $serviceType->id }}" class="mt-2 text-xs leading-5 text-[#78909a]">{{ $serviceType->code === 'SVC-07' ? 'Layanan ini tidak memakai timer SLA.' : 'Perubahan akan membuat versi kebijakan baru dan tidak mengubah snapshot tiket lama.' }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
                            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Batal</button>
                            <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                                <span data-ui-modal-label>Simpan versi SLA</span>
                                <span data-ui-modal-loading class="hidden" aria-live="polite">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    @endforeach
@endsection
