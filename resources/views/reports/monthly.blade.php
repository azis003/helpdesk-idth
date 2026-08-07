@extends('layouts.app')

@section('title', 'Laporan bulanan — '.$branding['application_name'])
@section('header_kicker', 'Pelaporan')
@section('header_title', 'Laporan bulanan')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Sumber data operasional</p>
            <h1 class="ui-page-title">Laporan bulanan</h1>
            <p class="ui-page-description">Susun laporan tiket dari histori aplikasi untuk periode kalender Asia/Jakarta, lalu unduh dalam format Excel atau PDF.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-ghost">Kembali ke dasbor <span aria-hidden="true">→</span></a>
    </div>

    <section class="ui-panel mt-8" aria-labelledby="report-filter-title">
        <div class="ui-panel-header">
            <h2 id="report-filter-title" class="ui-section-title">Periode laporan</h2>
            <p class="ui-section-description">Periode aktif: {{ $periodLabel }} ({{ $periodTimezone }}).</p>
        </div>
        <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end" data-report-filter-form>
                <div class="min-w-0 sm:max-w-xs">
                    <label for="report-month" class="ui-field-label">Bulan laporan <span class="text-rose-600">*</span></label>
                    <input id="report-month" name="month" type="month" value="{{ $periodMonth }}" required class="ui-input mt-2" aria-describedby="report-month-help">
                    <p id="report-month-help" class="ui-field-help">Bulan mengikuti kalender Asia/Jakarta.</p>
                </div>
                <button type="submit" class="ui-btn ui-btn-primary sm:mb-[1.45rem]" data-report-filter-submit>
                    <span data-report-filter-label>Tampilkan laporan</span>
                    <span class="hidden" data-report-filter-loading aria-live="polite">Memuat laporan…</span>
                </button>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row lg:justify-end">
                <form method="GET" action="{{ route('reports.monthly.excel') }}" data-report-export-form>
                    @foreach ($periodQuery as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="ui-btn ui-btn-secondary w-full sm:w-auto" data-report-export-submit>
                        <span data-report-export-label>Unduh Excel</span>
                        <span class="hidden" data-report-export-loading aria-live="polite">Menyiapkan Excel…</span>
                    </button>
                </form>
                <form method="GET" action="{{ route('reports.monthly.pdf') }}" data-report-export-form>
                    @foreach ($periodQuery as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="ui-btn ui-btn-ghost w-full sm:w-auto" data-report-export-submit>
                        <span data-report-export-label>Unduh PDF</span>
                        <span class="hidden" data-report-export-loading aria-live="polite">Menyiapkan PDF…</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="report-result-title">
        <div class="ui-panel-header flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="report-result-title" class="ui-section-title">Data laporan</h2>
                <p class="ui-section-description">{{ $report['row_count'] }} tiket tercatat pada periode ini. Nilai historis menggunakan snapshot dan histori tiket.</p>
            </div>
            <span class="ui-chip" aria-label="{{ $report['row_count'] }} tiket">{{ $report['row_count'] }} tiket</span>
        </div>

        @if ($report['rows']->isEmpty())
            <div class="p-4 sm:p-6">
                <x-empty-state title="Belum ada tiket pada periode ini." description="Pilih bulan lain untuk melihat laporan yang tersedia. Ekspor tetap dapat dibuat dan akan berisi header kolom laporan." />
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="ui-table min-w-[1500px]" aria-describedby="report-result-title">
                    <caption class="sr-only">Laporan tiket periode {{ $periodLabel }}</caption>
                    <thead>
                        <tr>
                            @foreach ($report['columns'] as $label)
                                <th scope="col">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['rows'] as $row)
                            <tr>
                                @foreach ($report['columns'] as $key => $label)
                                    <td class="{{ in_array($key, ['description', 'solution'], true) ? 'min-w-[16rem] max-w-[24rem] whitespace-pre-wrap' : '' }} {{ in_array($key, ['reported_at', 'completed_at', 'closed_at'], true) ? 'whitespace-nowrap' : '' }}">
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
                    <article class="rounded-xl border border-[#dfe8ec] bg-[#fbfdfd] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold text-[#1d5d72]">{{ $row['ticket_number'] }}</p>
                                <h3 class="mt-2 text-sm font-extrabold leading-5 text-[#35505b]">{{ $row['service'] ?: 'Layanan belum tercatat' }}</h3>
                            </div>
                            <span class="rounded-lg border border-[#dce7eb] bg-white px-2 py-1 text-[0.65rem] font-extrabold text-[#526f79]">{{ $row['final_status'] ?: 'Belum berstatus' }}</span>
                        </div>
                        <dl class="mt-4 grid gap-3 text-xs sm:grid-cols-2">
                            <div><dt class="font-extrabold uppercase tracking-[0.08em] text-[#8aa0a8]">Pemohon</dt><dd class="mt-1 text-[#526f79]">{{ $row['requester_name'] ?: '—' }}</dd></div>
                            <div><dt class="font-extrabold uppercase tracking-[0.08em] text-[#8aa0a8]">Prioritas</dt><dd class="mt-1 text-[#526f79]">{{ $row['priority'] ?: '—' }}</dd></div>
                            <div><dt class="font-extrabold uppercase tracking-[0.08em] text-[#8aa0a8]">Waktu melapor</dt><dd class="mt-1 text-[#526f79]">{{ $row['reported_at'] ?: '—' }}</dd></div>
                            <div><dt class="font-extrabold uppercase tracking-[0.08em] text-[#8aa0a8]">Penanggung jawab</dt><dd class="mt-1 text-[#526f79]">{{ $row['assignee'] ?: 'Belum ditugaskan' }}</dd></div>
                        </dl>
                        <details class="mt-4 rounded-lg border border-[#e1eaed] bg-white p-3">
                            <summary class="ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[#45606a]">Lihat seluruh kolom</summary>
                            <dl class="mt-3 space-y-3 text-xs">
                                @foreach ($report['columns'] as $key => $label)
                                    <div>
                                        <dt class="font-extrabold uppercase tracking-[0.08em] text-[#8aa0a8]">{{ $label }}</dt>
                                        <dd class="mt-1 whitespace-pre-wrap leading-5 text-[#526f79]">{{ $row[$key] !== '' ? $row[$key] : '—' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </details>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
