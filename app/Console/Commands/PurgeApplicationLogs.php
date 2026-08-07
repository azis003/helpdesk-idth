<?php

namespace App\Console\Commands;

use App\Services\ApplicationLogRetentionService;
use Illuminate\Console\Command;

class PurgeApplicationLogs extends Command
{
    protected $signature = 'sihati:logs:purge';

    protected $aliases = [
        'sihati:application-logs:purge',
    ];

    protected $description = 'Menghapus log aplikasi terputar yang melewati retensi minimum 30 hari.';

    public function handle(ApplicationLogRetentionService $retention): int
    {
        $count = $retention->purge();
        $this->info("{$count} berkas log aplikasi dihapus.");

        return self::SUCCESS;
    }
}
