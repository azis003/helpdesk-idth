<?php

namespace App\Console\Commands;

use App\Services\AttachmentRetentionService;
use Illuminate\Console\Command;

class PurgeDataExportAttachments extends Command
{
    protected $signature = 'sihati:attachments:purge-data-exports';

    protected $aliases = [
        'sihati:attachments:purge-data-export-results',
    ];

    protected $description = 'Menghapus file hasil tarik data yang melewati masa retensi 90 hari.';

    public function handle(AttachmentRetentionService $retention): int
    {
        $count = $retention->purgeDataExportResults();
        $this->info("{$count} lampiran hasil tarik data dihapus.");

        return self::SUCCESS;
    }
}
