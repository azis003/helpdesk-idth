<?php

namespace App\Http\Controllers;

use App\Models\ReportExport;
use App\Services\DomainAuthorization;
use App\Services\MonthlyReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly MonthlyReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeReports($request);
        [$start, $end, $periodQuery] = $this->resolvePeriod($request);
        $report = $this->reports->forPeriod($start, $end);

        return view('reports.monthly', [
            'report' => $report,
            'periodStart' => $start,
            'periodEnd' => $end,
            'periodLabel' => $this->periodLabel($start, $end),
            'periodMonth' => $periodQuery['month'] ?? null,
            'periodQuery' => $periodQuery,
            'periodTimezone' => MonthlyReportService::TIMEZONE,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        return $this->export($request, 'xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        return $this->export($request, 'pdf');
    }

    private function export(Request $request, string $format): Response
    {
        $this->authorizeReports($request);
        [$start, $end] = $this->resolvePeriod($request);
        $report = $this->reports->forPeriod($start, $end);
        $periodSlug = $start->format('Y-m') === $end->format('Y-m')
            ? $start->format('Y-m')
            : $start->format('Y-m-d').'-'.$end->format('Y-m-d');
        $fileName = "laporan-tiket-bulanan-{$periodSlug}.{$format}";
        $periodLabel = $this->periodLabel($start, $end);
        $content = $format === 'xlsx'
            ? $this->reports->toExcel($report)
            : $this->reports->toPdf($report, $periodLabel);

        $this->reports->recordExport(
            $request->user(),
            $format === 'xlsx' ? 'excel' : 'pdf',
            $start,
            $end,
            $report['row_count'],
            $fileName,
        );

        return response($content, 200, [
            'Content-Type' => $format === 'xlsx'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Content-Length' => (string) strlen($content),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeReports(Request $request): void
    {
        $this->authorization->authorize(
            $request->user(),
            'viewAny',
            ReportExport::class,
            'report.view',
        );
    }

    /** @return array{0:Carbon,1:Carbon,2:array<string,string>} */
    private function resolvePeriod(Request $request): array
    {
        $monthInput = $request->query('month');
        $startInput = $request->query('start_date', $request->query('from'));
        $endInput = $request->query('end_date', $request->query('to'));

        Validator::make(
            [
                'month' => $monthInput,
                'start_date' => $startInput,
                'end_date' => $endInput,
            ],
            [
                'month' => ['nullable', 'date_format:Y-m'],
                'start_date' => ['nullable', 'date_format:Y-m-d'],
                'end_date' => ['nullable', 'date_format:Y-m-d'],
            ],
            [
                'month.date_format' => 'Bulan laporan harus menggunakan format YYYY-MM.',
                'start_date.date_format' => 'Tanggal mulai harus menggunakan format YYYY-MM-DD.',
                'end_date.date_format' => 'Tanggal akhir harus menggunakan format YYYY-MM-DD.',
            ],
        )->validate();

        if (filled($monthInput)) {
            $start = Carbon::createFromFormat('!Y-m', (string) $monthInput, MonthlyReportService::TIMEZONE)->startOfMonth();

            return [$start, $start->copy()->endOfMonth(), ['month' => (string) $monthInput]];
        }

        $now = Carbon::now(MonthlyReportService::TIMEZONE);
        $start = filled($startInput)
            ? Carbon::createFromFormat('!Y-m-d', (string) $startInput, MonthlyReportService::TIMEZONE)->startOfDay()
            : (filled($endInput)
                ? Carbon::createFromFormat('!Y-m-d', (string) $endInput, MonthlyReportService::TIMEZONE)->startOfMonth()
                : $now->copy()->startOfMonth());
        $end = filled($endInput)
            ? Carbon::createFromFormat('!Y-m-d', (string) $endInput, MonthlyReportService::TIMEZONE)->endOfDay()
            : (filled($startInput) ? $start->copy()->endOfMonth() : $now->copy()->endOfMonth());

        if ($end->lessThan($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal akhir tidak boleh lebih awal daripada tanggal mulai.',
            ]);
        }

        $query = [];
        if (filled($startInput)) {
            $query['start_date'] = (string) $startInput;
        }
        if (filled($endInput)) {
            $query['end_date'] = (string) $endInput;
        }
        if ($query === []) {
            $query['month'] = $now->format('Y-m');
        }

        return [$start, $end, $query];
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        $startLabel = $start->copy()->locale('id')->translatedFormat('d M Y');
        $endLabel = $end->copy()->locale('id')->translatedFormat('d M Y');

        return $startLabel === $endLabel ? $startLabel : $startLabel.' – '.$endLabel;
    }
}
