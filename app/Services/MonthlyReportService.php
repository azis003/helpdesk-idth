<?php

namespace App\Services;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Models\ReportExport;
use App\Models\Ticket;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MonthlyReportService
{
    public const TIMEZONE = 'Asia/Jakarta';

    /** @var array<string, string> */
    public const COLUMNS = [
        'ticket_number' => 'Nomor Tiket',
        'requester_name' => 'Nama Pemohon',
        'requester_nip' => 'NIP Pemohon',
        'requester_team' => 'Tim Kerja Saat Tiket Dibuat',
        'description' => 'Deskripsi',
        'service' => 'Layanan',
        'category' => 'Kategori',
        'priority' => 'Prioritas',
        'reported_at' => 'Waktu Melapor',
        'completed_at' => 'Waktu Selesai',
        'closed_at' => 'Waktu Tutup',
        'final_status' => 'Status Akhir',
        'assignee' => 'Penanggung Jawab',
        'solution' => 'Solusi',
        'creator' => 'Pembuat Tiket',
        'is_self_created' => 'Flag Tiket Mandiri',
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return array{columns:array<string,string>,headers:list<string>,rows:Collection<int,array<string,string>>,row_count:int}
     */
    public function forPeriod(Carbon $start, Carbon $end): array
    {
        $tickets = Ticket::query()
            ->with([
                'requester',
                'creator',
                'assignee',
                'serviceType',
                'problemCategory',
                'categoryHistories',
                'assignmentHistories.toUser',
                'statusHistories',
            ])
            ->where(function ($query) use ($start, $end): void {
                $query
                    ->whereBetween('submitted_at', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end): void {
                        $query
                            ->whereNull('submitted_at')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->orderByRaw('COALESCE(submitted_at, created_at) ASC')
            ->orderBy('id')
            ->get();

        $rows = $tickets
            ->map(fn (Ticket $ticket): array => $this->rowFor($ticket))
            ->values();

        return [
            'columns' => self::COLUMNS,
            'headers' => array_values(self::COLUMNS),
            'rows' => $rows,
            'row_count' => $rows->count(),
        ];
    }

    /**
     * @param  array{columns:array<string,string>,headers:list<string>,rows:Collection<int,array<string,string>>,row_count:int}  $report
     */
    public function toExcel(array $report): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('SIHATI')
            ->setTitle('Laporan Tiket Bulanan')
            ->setSubject('Laporan tiket bulanan SIHATI');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Bulanan');

        $values = [$report['headers']];
        foreach ($report['rows'] as $row) {
            $values[] = array_values($row);
        }

        $sheet->fromArray($values, null, 'A1');

        $lastColumn = $this->excelColumn(count($report['headers']));
        $lastRow = max(1, count($values));
        $headerRange = "A1:{$lastColumn}1";
        $bodyRange = "A1:{$lastColumn}{$lastRow}";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '1D5D72'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle($bodyRange)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->getRowDimension(1)->setRowHeight(30);

        $widths = [
            18, 24, 16, 24, 42, 25, 22, 14,
            19, 19, 19, 22, 24, 42, 24, 18,
        ];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($this->excelColumn($index + 1))->setWidth($width);
        }

        $stream = fopen('php://memory', 'w+b');
        $writer = new Xlsx($spreadsheet);
        $writer->save($stream);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        $spreadsheet->disconnectWorksheets();

        return $content === false ? '' : $content;
    }

    /**
     * @param  array{columns:array<string,string>,headers:list<string>,rows:Collection<int,array<string,string>>,row_count:int}  $report
     */
    public function toPdf(array $report, string $periodLabel): string
    {
        $options = new Options;
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(
            view('reports.monthly-pdf', [
                'columns' => $report['columns'],
                'rows' => $report['rows'],
                'periodLabel' => $periodLabel,
            ])->render(),
            'UTF-8',
        );
        $dompdf->setPaper('a3', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    public function recordExport(
        User $actor,
        string $format,
        Carbon $start,
        Carbon $end,
        int $rowCount,
        string $fileName,
    ): ReportExport {
        $export = ReportExport::query()->create([
            'type' => ReportExport::TYPE_MONTHLY_TICKETS,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'requested_by_id' => $actor->getKey(),
            'format' => $format,
            'status' => ReportExport::STATUS_COMPLETED,
            'file_name' => $fileName,
            'row_count' => $rowCount,
            'generated_at' => Carbon::now(self::TIMEZONE),
        ]);

        $this->auditLogger->succeeded(
            $actor,
            'report.export',
            $export,
            'Laporan bulanan diekspor ke '.strtoupper($format).'.',
            null,
            [
                'type' => ReportExport::TYPE_MONTHLY_TICKETS,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'format' => $format,
                'row_count' => $rowCount,
                'file_name' => $fileName,
            ],
        );

        return $export;
    }

    /** @return array<string,string> */
    private function rowFor(Ticket $ticket): array
    {
        $reportedAt = $ticket->submitted_at ?? $ticket->created_at;

        return [
            'ticket_number' => (string) ($ticket->ticket_number ?: 'Tiket #'.$ticket->getKey()),
            'requester_name' => (string) ($ticket->requester_name_snapshot ?: $ticket->requester?->name ?: ''),
            'requester_nip' => (string) ($ticket->requester_nip_snapshot ?: $ticket->requester?->nip ?: ''),
            'requester_team' => (string) ($ticket->requester_team_snapshot ?: ''),
            'description' => (string) ($ticket->description ?: ''),
            'service' => (string) ($ticket->service_type_name_snapshot ?: $ticket->serviceType?->name ?: $ticket->service_type_code_snapshot ?: ''),
            'category' => $this->categoryName($ticket),
            'priority' => $this->priorityLabel($ticket),
            'reported_at' => $this->formatDate($reportedAt),
            'completed_at' => $this->formatDate($this->completedAt($ticket)),
            'closed_at' => $this->formatDate($ticket->closed_at),
            'final_status' => $this->statusLabel($ticket),
            'assignee' => $this->assigneeName($ticket),
            'solution' => (string) ($ticket->solution ?: ''),
            'creator' => (string) ($ticket->created_by_name_snapshot ?: $ticket->creator?->name ?: ''),
            'is_self_created' => $ticket->is_self_created ? 'Ya' : 'Tidak',
        ];
    }

    private function categoryName(Ticket $ticket): string
    {
        $latestHistory = $ticket->categoryHistories->last();

        return (string) ($latestHistory?->to_category_name ?: $ticket->problem_category_name_snapshot ?: $ticket->problemCategory?->name ?: '');
    }

    private function assigneeName(Ticket $ticket): string
    {
        $latestAssignment = $ticket->assignmentHistories
            ->filter(fn ($history): bool => $history->to_user_id !== null)
            ->last();

        return (string) ($latestAssignment?->to_user_name_snapshot ?: $latestAssignment?->toUser?->name ?: $ticket->assignee?->name ?: '');
    }

    private function completedAt(Ticket $ticket): mixed
    {
        return $ticket->statusHistories
            ->filter(fn ($history): bool => $history->action === 'ticket.completed')
            ->last()?->occurred_at
            ?? $ticket->confirmation_started_at;
    }

    private function priorityLabel(Ticket $ticket): string
    {
        if ($ticket->priority instanceof Priority) {
            return $ticket->priority->label();
        }

        return Priority::tryFrom((string) $ticket->priority)?->label() ?? (string) ($ticket->priority ?: '');
    }

    private function statusLabel(Ticket $ticket): string
    {
        if ($ticket->status instanceof TicketStatus) {
            return $ticket->status->label();
        }

        return TicketStatus::tryFrom((string) $ticket->status)?->label() ?? (string) ($ticket->status ?: '');
    }

    private function formatDate(mixed $date): string
    {
        if ($date === null) {
            return '';
        }

        $carbon = $date instanceof Carbon
            ? $date->copy()
            : Carbon::parse($date, self::TIMEZONE);

        return $carbon->timezone(self::TIMEZONE)->format('d/m/Y H:i');
    }

    private function excelColumn(int $number): string
    {
        $column = '';

        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder).$column;
            $number = intdiv($number - 1, 26);
        }

        return $column;
    }
}
