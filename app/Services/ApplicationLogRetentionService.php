<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class ApplicationLogRetentionService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function purge(?Carbon $now = null, ?string $directory = null): int
    {
        $now ??= Carbon::now((string) config('app.timezone', 'Asia/Jakarta'));
        $directory ??= storage_path('logs');
        $cutoff = $now->copy()->subDays((int) config('retention.application_log_days', 30))->startOfDay();
        $deleted = 0;

        if (! is_dir($directory)) {
            return 0;
        }

        foreach (glob(rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'laravel-*.log') ?: [] as $path) {
            $date = $this->dateFromPath($path);

            if ($date === null || $date->greaterThanOrEqualTo($cutoff)) {
                continue;
            }

            if (@unlink($path)) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->auditLogger->succeeded(
                null,
                'application_log.retention_deleted',
                null,
                'Berkas log aplikasi yang melewati retensi minimum dihapus.',
                null,
                [
                    'deleted_count' => $deleted,
                    'retention_days' => (int) config('retention.application_log_days', 30),
                    'cutoff' => $cutoff->toIso8601String(),
                ],
            );
        }

        return $deleted;
    }

    private function dateFromPath(string $path): ?Carbon
    {
        $name = pathinfo($path, PATHINFO_FILENAME);
        $date = substr($name, -10);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date, (string) config('app.timezone', 'Asia/Jakarta'))->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
