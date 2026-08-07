<?php

namespace App\Console\Commands;

use App\Services\TicketRetentionService;
use Illuminate\Console\Command;

class PurgeExpiredTickets extends Command
{
    protected $signature = 'sihati:retention:purge-tickets';

    protected $aliases = [
        'sihati:tickets:purge-retention',
    ];

    protected $description = 'Menghapus tiket terminal dan histori bisnis yang melewati retensi lima tahun.';

    public function handle(TicketRetentionService $retention): int
    {
        $count = $retention->purgeExpiredTickets();
        $this->info("{$count} tiket kedaluwarsa dihapus.");

        return self::SUCCESS;
    }
}
