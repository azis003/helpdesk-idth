<?php

namespace App\Console\Commands;

use App\Services\TicketResolutionService;
use Illuminate\Console\Command;

class AutoCloseConfirmationTickets extends Command
{
    protected $signature = 'sihati:tickets:auto-close-confirmations';

    protected $aliases = [
        'sihati:tickets:auto-close',
    ];

    protected $description = 'Menutup otomatis tiket yang melewati batas konfirmasi Pemohon.';

    public function handle(TicketResolutionService $resolution): int
    {
        $count = $resolution->autoCloseDueTickets();
        $this->info("{$count} tiket ditutup otomatis.");

        return self::SUCCESS;
    }
}
