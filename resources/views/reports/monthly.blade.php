@extends('layouts.app')

@php
    // Presentasional saja - tidak mengubah data, logika, maupun alur ekspor.
    $repRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $repDivider = 'border-[color:var(--tm-border-subtle)]';
    $repErrorText = 'hidden mt-1 text-xs font-semibold text-[color:var(--tm-danger-700)]';
    $repTicketCell = 'whitespace-nowrap font-extrabold tabular-nums text-[color:var(--tm-brand-700)]';
    $repMobileCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
    $repMobileStatus = 'rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-2 py-1 text-[0.65rem] font-extrabold text-[color:var(--tm-text-secondary)]';
    $repMetaLabel = 'font-extrabold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $repMetaValue = 'mt-1 text-[color:var(--tm-text-secondary)]';
@endphp

@section('title', 'Laporan Bulanan — '.$branding['application_name'])
@section('header_title', 'Laporan Bulanan')

@section('content')
    <x-page-header
        eyebrow="Pelaporan · Rekap operasional"
        title="Laporan Bulanan"
        description="Pilih periode untuk meninjau, mengunduh, dan membandingkan rekap operasional layanan TI."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-label="Filter laporan">
        <div class="border-b {{ $repDivider }} bg-[color:var(--tm-sunken)] p-5 sm:p-6">
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:gap-8" data-report-filter-form>
                <div class="w-full sm:max-w-[30rem]">
                    <label for="report-month" class="ui-field-label">Bulan <span class="{{ $repRequiredMark }}">*</span></label>
                    <input id="report-month" name="month" type="month" value="{{ $periodMonth }}" max="{{ $periodMaxMonth }}" required class="ui-input mt-2 tabular-nums">
                </div>
                <button type="submit" class="ui-btn ui-btn-primary w-full shrink-0 sm:w-auto sm:min-w-[14rem]" data-report-filter-submit>
                    <span data-report-filter-label>Tampilkan laporan</span>
                    <span class="hidden" data-report-filter-loading aria-live="polite">Memuat laporan…</span>
                </button>
            </form>
        </div>

        @if ($reportRequested)
            @php
                $tableColumnKeys = [
                    'ticket_number',
                    'requester_name',
                    'description',
                    'service',
                    'category',
                    'priority',
                    'reported_at',
                    'completed_at',
                    'final_status',
                    'assignee',
                    'closed_at',
                ];
                $tableColumns = collect($tableColumnKeys)->mapWithKeys(fn (string $key): array => [$key => $report['columns'][$key]])->all();
            @endphp

            <div aria-labelledby="report-result-title">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b {{ $repDivider }} px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="report-result-title" class="ui-section-title">Data laporan</h2>
                        <p class="ui-section-description tabular-nums">{{ $report['row_count'] }} tiket tercatat pada periode ini.</p>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:justify-end">
                        <form method="GET" action="{{ route('reports.monthly.excel') }}" data-report-export-form>
                            @foreach ($periodQuery as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="ui-btn ui-btn-secondary w-full sm:w-auto" data-report-export-submit>
                                <span data-report-export-label>Unduh Excel</span>
                                <span class="hidden" data-report-export-loading aria-live="polite">Menyiapkan Excel…</span>
                            </button>
                            <p class="{{ $repErrorText }}" data-report-export-error role="alert">Unduhan gagal. Silakan coba lagi.</p>
                        </form>
                        <form method="GET" action="{{ route('reports.monthly.pdf') }}" data-report-export-form>
                            @foreach ($periodQuery as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="ui-btn ui-btn-ghost w-full sm:w-auto" data-report-export-submit>
                                <span data-report-export-label>Unduh PDF</span>
                                <span class="hidden" data-report-export-loading aria-live="polite">Menyiapkan PDF…</span>
                            </button>
                            <p class="{{ $repErrorText }}" data-report-export-error role="alert">Unduhan gagal. Silakan coba lagi.</p>
                        </form>
                    </div>
                </div>

                @if ($report['rows']->isEmpty())
                    <div class="p-4 sm:p-6">
                        <x-empty-state title="Belum ada tiket pada periode ini." description="Pilih bulan lain untuk melihat laporan yang tersedia." />
                    </div>
                @else
                    <div class="hidden overflow-x-auto md:block">
                        <table class="ui-table min-w-[1120px]" aria-describedby="report-result-title">
                            <caption class="sr-only">Laporan tiket periode {{ $periodLabel }}</caption>
                            <thead>
                                <tr>
                                    @foreach ($tableColumns as $label)
                                        <th scope="col">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['rows'] as $row)
                                    <tr>
                                        @foreach ($tableColumns as $key => $label)
                                            <td class="{{ $key === 'ticket_number' ? $repTicketCell : '' }} {{ $key === 'description' ? 'min-w-[18rem] max-w-[28rem] whitespace-pre-wrap' : '' }} {{ in_array($key, ['reported_at', 'completed_at', 'closed_at'], true) ? 'whitespace-nowrap tabular-nums' : '' }}">
                                                {{ $row[$key] !== '' ? $row[$key] : '—' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-3 p-4 md:hidden">
                        @foreach ($report['rows'] as $row)
                            <article class="{{ $repMobileCard }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-extrabold tabular-nums text-[color:var(--tm-brand-700)]">{{ $row['ticket_number'] }}</p>
                                        <h4 class="mt-2 text-sm font-extrabold leading-5 text-[color:var(--tm-text)]">{{ $row['service'] ?: 'Layanan belum tercatat' }}</h4>
                                    </div>
                                    <span class="{{ $repMobileStatus }}">{{ $row['final_status'] ?: 'Belum berstatus' }}</span>
                                </div>
                                <dl class="mt-4 grid gap-3 text-xs sm:grid-cols-2">
                                    <div><dt class="{{ $repMetaLabel }}">Pemohon</dt><dd class="{{ $repMetaValue }}">{{ $row['requester_name'] ?: '—' }}</dd></div>
                                    <div class="sm:col-span-2"><dt class="{{ $repMetaLabel }}">Deskripsi</dt><dd class="{{ $repMetaValue }} whitespace-pre-wrap leading-5">{{ $row['description'] ?: '—' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Kategori</dt><dd class="{{ $repMetaValue }}">{{ $row['category'] ?: '—' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Prioritas</dt><dd class="{{ $repMetaValue }}">{{ $row['priority'] ?: '—' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Waktu melapor</dt><dd class="{{ $repMetaValue }} tabular-nums">{{ $row['reported_at'] ?: '—' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Waktu selesai</dt><dd class="{{ $repMetaValue }} tabular-nums">{{ $row['completed_at'] ?: '—' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Penanggung jawab</dt><dd class="{{ $repMetaValue }}">{{ $row['assignee'] ?: 'Belum ditugaskan' }}</dd></div>
                                    <div><dt class="{{ $repMetaLabel }}">Waktu tutup</dt><dd class="{{ $repMetaValue }} tabular-nums">{{ $row['closed_at'] ?: '—' }}</dd></div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </section>
@endsection
