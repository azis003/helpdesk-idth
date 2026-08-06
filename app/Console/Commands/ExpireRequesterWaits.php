<?php

namespace App\Console\Commands;

use App\Services\TicketWaitingService;
use Illuminate\Console\Command;

class ExpireRequesterWaits extends Command
{
    protected $signature = 'sihati:tickets:expire-requester-waits';

    protected $description = 'Mengakhiri waktu tunggu Pemohon yang sudah melewati batas kerja.';

    public function handle(TicketWaitingService $waiting): int
    {
        $count = $waiting->expireRequesterWaits();
        $this->info("{$count} waktu tunggu Pemohon diakhiri.");

        return self::SUCCESS;
    }
}
