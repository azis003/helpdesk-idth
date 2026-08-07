<?php

namespace App\Console\Commands;

use App\Services\StorageCapacityService;
use Illuminate\Console\Command;

class CheckStorageCapacity extends Command
{
    protected $signature = 'sihati:ops:check-storage {--disk= : Nama disk yang diperiksa} {--json : Tampilkan JSON untuk monitoring}';

    protected $aliases = ['ops:check-storage'];

    protected $description = 'Memeriksa kapasitas private storage dan memberi peringatan pada ambang 80 persen.';

    public function handle(StorageCapacityService $storage): int
    {
        $snapshot = $storage->inspect($this->option('disk') ?: null);
        $storage->alert($snapshot);

        if ($this->option('json')) {
            $this->line((string) json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Disk', 'Status', 'Terpakai', 'Pesan'],
                [[
                    $snapshot['disk'] ?? '-',
                    $snapshot['status'] ?? 'unknown',
                    isset($snapshot['used_percent']) ? $snapshot['used_percent'].'%' : '-',
                    $snapshot['message'] ?? '-',
                ]],
            );
        }

        return match ($snapshot['status'] ?? 'unknown') {
            'ok' => self::SUCCESS,
            'warning', 'unknown' => self::FAILURE,
            default => 2,
        };
    }
}
