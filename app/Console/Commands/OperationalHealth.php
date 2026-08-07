<?php

namespace App\Console\Commands;

use App\Services\OperationalHealthService;
use Illuminate\Console\Command;

class OperationalHealth extends Command
{
    protected $signature = 'sihati:ops:health {--json : Tampilkan JSON untuk readiness probe}';

    protected $aliases = ['ops:health'];

    protected $description = 'Memeriksa database, cache, queue, storage, scheduler, dan backup.';

    public function handle(OperationalHealthService $health): int
    {
        $report = $health->check();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Check', 'Status', 'Pesan'],
                collect($report['checks'])
                    ->map(fn (array $check, string $name): array => [$name, $check['status'], $check['message']])
                    ->values()
                    ->all(),
            );
            $this->line('Status: '.$report['status'].'; ready='.($report['ready'] ? 'yes' : 'no'));
        }

        return $report['ready'] ? self::SUCCESS : self::FAILURE;
    }
}
