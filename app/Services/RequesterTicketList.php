<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Read model untuk halaman "Tiket saya" milik Pemohon murni.
 *
 * Cakupan data dikunci pada tiket milik aktor, lalu tab cepat dan saringan
 * opsional ditumpuk di atasnya. Seluruh resolusi parameter dilakukan di sini
 * agar controller tetap tipis dan view hanya menampilkan.
 */
class RequesterTicketList
{
    public const TAB_ALL = 'semua';

    public const TAB_ACTIVE = 'aktif';

    public const TAB_ACTION = 'tindakan';

    public const TAB_DONE = 'selesai';

    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [10, 25, 50];

    /** @var array<string, string> */
    public const CLASS_LABELS = [
        'INC' => 'Insiden',
        'REQ' => 'Permintaan',
        'CHG' => 'Perubahan',
    ];

    /** @var array<string, string> */
    public const CLASS_OPTIONS = [
        'INC' => 'Insiden (Gangguan)',
        'REQ' => 'Permintaan Layanan',
        'CHG' => 'Permintaan Perubahan',
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, User $actor): array
    {
        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $tab = (string) $request->query('tab', self::TAB_ALL);

        if (! array_key_exists($tab, $this->tabStatuses())) {
            $tab = self::TAB_ALL;
        }

        $ticketClass = strtoupper(trim((string) $request->query('class', '')));

        if (! array_key_exists($ticketClass, self::CLASS_OPTIONS)) {
            $ticketClass = '';
        }

        $status = trim((string) $request->query('status', ''));

        if (TicketStatus::tryFrom($status) === null) {
            $status = '';
        }

        $from = $this->parseDate($request->query('from'));
        $to = $this->parseDate($request->query('to'));

        if ($from !== null && $to !== null && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [
            'tickets' => $this->paginate($actor, $tab, $search, $ticketClass, $status, $from, $to, $perPage),
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'activeTab' => $tab,
            'tabCounts' => $this->tabCounts($actor),
            'serviceClass' => $ticketClass,
            'statusFilter' => $status,
            'dateFrom' => $from?->toDateString() ?? '',
            'dateTo' => $to?->toDateString() ?? '',
            'ticketClassLabels' => self::CLASS_LABELS,
            'ticketClassOptions' => self::CLASS_OPTIONS,
            'statusOptions' => collect(TicketStatus::cases())
                ->mapWithKeys(static fn (TicketStatus $case): array => [$case->value => $case->label()])
                ->all(),
            'hasFilters' => $search !== ''
                || $ticketClass !== ''
                || $status !== ''
                || $from !== null
                || $to !== null,
        ];
    }

    /**
     * Jumlah tiket per tab dihitung dari seluruh tiket pemohon, bukan dari
     * hasil yang sedang tersaring, supaya angka tab tetap stabil.
     *
     * @return array<string, int>
     */
    public function tabCounts(User $actor): array
    {
        $counts = Ticket::query()
            ->where('requester_id', $actor->getKey())
            ->toBase()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $sum = static function (array $cases) use ($counts): int {
            $total = 0;

            foreach ($cases as $case) {
                $total += (int) ($counts[$case->value] ?? 0);
            }

            return $total;
        };

        return [
            self::TAB_ALL => (int) array_sum(array_map('intval', $counts)),
            self::TAB_ACTIVE => $sum(TicketStatus::activeCases()),
            self::TAB_ACTION => $sum(TicketStatus::requesterActionCases()),
            self::TAB_DONE => $sum(TicketStatus::closedCases()),
        ];
    }

    private function paginate(
        User $actor,
        string $tab,
        string $search,
        string $ticketClass,
        string $status,
        ?Carbon $from,
        ?Carbon $to,
        int $perPage,
    ): LengthAwarePaginator {
        $tabStatuses = $this->tabStatuses()[$tab] ?? null;

        return Ticket::query()
            ->with('serviceType')
            ->where('requester_id', $actor->getKey())
            ->when(
                $tabStatuses !== null,
                fn (Builder $query): Builder => $query->whereIn('status', TicketStatus::valuesOf($tabStatuses ?? [])),
            )
            ->when($ticketClass !== '', fn (Builder $query): Builder => $query->where('ticket_class', $ticketClass))
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($from !== null, fn (Builder $query): Builder => $query->whereDate('submitted_at', '>=', $from))
            ->when($to !== null, fn (Builder $query): Builder => $query->whereDate('submitted_at', '<=', $to))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('ticket_number', 'like', $like)
                        ->orWhere('subject', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('service_type_code_snapshot', 'like', $like)
                        ->orWhere('service_type_name_snapshot', 'like', $like);
                });
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, list<TicketStatus>|null>
     */
    private function tabStatuses(): array
    {
        return [
            self::TAB_ALL => null,
            self::TAB_ACTIVE => TicketStatus::activeCases(),
            self::TAB_ACTION => TicketStatus::requesterActionCases(),
            self::TAB_DONE => TicketStatus::closedCases(),
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', trim($value));
        } catch (\Throwable) {
            return null;
        }

        return $date instanceof Carbon ? $date->startOfDay() : null;
    }
}
