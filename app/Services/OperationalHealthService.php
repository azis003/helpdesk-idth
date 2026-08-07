<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OperationalHealthService
{
    public function __construct(private readonly StorageCapacityService $storage) {}

    /**
     * Build a dependency report for readiness probes and operator commands.
     *
     * @return array{status:string,ready:bool,checked_at:string,checks:array<string,array<string,mixed>>}
     */
    public function check(): array
    {
        $checks = [
            'database' => $this->database(),
            'cache' => $this->cache(),
            'queue' => $this->queue(),
            'storage' => $this->storage(),
            'scheduler' => $this->scheduler(),
            'backup' => $this->backup(),
        ];

        $hasCritical = collect($checks)->contains(fn (array $check): bool => $check['status'] === 'critical');
        $hasWarning = collect($checks)->contains(fn (array $check): bool => in_array($check['status'], ['warning', 'unknown'], true));
        $status = $hasCritical ? 'unhealthy' : ($hasWarning ? 'degraded' : 'healthy');
        $ready = ! $hasCritical && (! $hasWarning || ! (bool) config('ops.health.fail_on_warning', false));

        return [
            'status' => $status,
            'ready' => $ready,
            'checked_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta'))->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /** @return array<string, mixed> */
    private function database(): array
    {
        try {
            DB::connection()->select('select 1');

            return $this->ok('Koneksi database siap.');
        } catch (Throwable $exception) {
            return $this->critical('Koneksi database gagal.', ['exception' => $exception::class]);
        }
    }

    /** @return array<string, mixed> */
    private function cache(): array
    {
        $key = 'sihati:ops:health-probe';

        try {
            $value = bin2hex(random_bytes(12));
            $store = Cache::store((string) config('cache.default'));
            $store->put($key, $value, now()->addSeconds((int) config('ops.health.health_probe_cache_ttl_seconds', 10)));

            if ($store->get($key) !== $value) {
                return $this->critical('Cache tidak mengembalikan nilai probe.');
            }

            return $this->ok('Koneksi cache siap.');
        } catch (Throwable $exception) {
            return $this->critical('Koneksi cache gagal.', ['exception' => $exception::class]);
        }
    }

    /** @return array<string, mixed> */
    private function queue(): array
    {
        $connectionName = (string) config('queue.default', 'sync');
        $queueName = (string) config("queue.connections.{$connectionName}.queue", 'default');

        try {
            $size = (int) Queue::connection($connectionName)->size($queueName);
            $maxDepth = (int) config('ops.health.queue_max_depth', 100);
            $failedJobs = 0;

            if (Schema::hasTable('failed_jobs')) {
                $failedJobs = (int) DB::table('failed_jobs')->count();
            }

            if ($size > $maxDepth) {
                return $this->warning('Antrean queue melewati ambang monitoring.', [
                    'depth' => $size,
                    'max_depth' => $maxDepth,
                    'failed_jobs' => $failedJobs,
                ]);
            }

            if ($failedJobs > (int) config('ops.health.failed_jobs_warning_count', 0)) {
                return $this->warning('Terdapat failed job yang perlu ditangani.', [
                    'depth' => $size,
                    'failed_jobs' => $failedJobs,
                ]);
            }

            return $this->ok('Queue siap.', [
                'depth' => $size,
                'failed_jobs' => $failedJobs,
            ]);
        } catch (Throwable $exception) {
            return $this->critical('Koneksi queue gagal.', ['exception' => $exception::class]);
        }
    }

    /** @return array<string, mixed> */
    private function storage(): array
    {
        try {
            $snapshot = $this->storage->inspect();
            $status = (string) ($snapshot['status'] ?? 'unknown');

            if ($status === 'unknown' && (bool) config('ops.health.require_storage_check', false)) {
                $snapshot['status'] = 'critical';
            }

            return $snapshot;
        } catch (Throwable $exception) {
            return $this->critical('Pemeriksaan storage gagal.', ['exception' => $exception::class]);
        }
    }

    /** @return array<string, mixed> */
    private function scheduler(): array
    {
        $key = (string) config('ops.scheduler_heartbeat_cache_key', 'sihati:ops:scheduler-heartbeat');
        $lastHeartbeat = null;

        try {
            $lastHeartbeat = Cache::store((string) config('cache.default'))->get($key);
        } catch (Throwable $exception) {
            return $this->critical('Heartbeat scheduler tidak dapat dibaca.', ['exception' => $exception::class]);
        }

        if (! is_string($lastHeartbeat) || trim($lastHeartbeat) === '') {
            return (bool) config('ops.health.require_scheduler_heartbeat', false)
                ? $this->critical('Heartbeat scheduler belum tersedia.')
                : $this->warning('Heartbeat scheduler belum tersedia.');
        }

        try {
            $age = max(0, Carbon::now()->diffInSeconds(Carbon::parse($lastHeartbeat)));
        } catch (Throwable) {
            return $this->critical('Format heartbeat scheduler tidak valid.');
        }

        if ($age > (int) config('ops.health.scheduler_max_age_seconds', 180)) {
            return $this->critical('Heartbeat scheduler melewati batas waktu.', ['age_seconds' => $age]);
        }

        return $this->ok('Scheduler aktif.', ['age_seconds' => $age]);
    }

    /** @return array<string, mixed> */
    private function backup(): array
    {
        $path = (string) config('ops.backup_status_file', '');

        if ($path === '' || ! is_file($path)) {
            return (bool) config('ops.health.require_backup_status', false)
                ? $this->critical('Status backup belum tersedia.')
                : $this->warning('Status backup belum dikonfigurasi atau belum tersedia.');
        }

        $payload = json_decode((string) file_get_contents($path), true);
        $fileTimestamp = @filemtime($path);

        if (! is_array($payload)) {
            return $this->critical('Status backup tidak berformat JSON.');
        }

        $fullAt = $this->timestampFromPayload($payload['full_backup_completed_at'] ?? null, $fileTimestamp);
        $walAt = $this->timestampFromPayload($payload['wal_archived_at'] ?? null, $fileTimestamp);
        $now = Carbon::now((string) config('app.timezone', 'Asia/Jakarta'));

        if ($fullAt === null || $walAt === null) {
            return $this->critical('Status backup tidak memiliki timestamp lengkap.');
        }

        $fullAge = max(0, $now->diffInSeconds($fullAt));
        $walAge = max(0, $now->diffInSeconds($walAt));
        $metadata = [
            'full_age_seconds' => $fullAge,
            'wal_age_seconds' => $walAge,
        ];

        if ($fullAge > (int) config('ops.health.backup_full_max_age_seconds', 90000)) {
            return $this->critical('Backup penuh melewati batas waktu.', $metadata);
        }

        if ($walAge > (int) config('ops.health.backup_wal_max_age_seconds', 3600)) {
            return $this->critical('Arsip WAL melewati batas waktu.', $metadata);
        }

        return $this->ok('Backup penuh dan WAL masih segar.', $metadata);
    }

    private function timestampFromPayload(mixed $value, mixed $fallbackTimestamp): ?Carbon
    {
        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value, (string) config('app.timezone', 'Asia/Jakarta'));
            } catch (Throwable) {
                return null;
            }
        }

        return is_int($fallbackTimestamp) || is_float($fallbackTimestamp)
            ? Carbon::createFromTimestamp((int) $fallbackTimestamp, (string) config('app.timezone', 'Asia/Jakarta'))
            : null;
    }

    /** @param array<string, mixed> $meta */
    private function ok(string $message, array $meta = []): array
    {
        return ['status' => 'ok', 'message' => $message, 'meta' => $meta];
    }

    /** @param array<string, mixed> $meta */
    private function warning(string $message, array $meta = []): array
    {
        return ['status' => 'warning', 'message' => $message, 'meta' => $meta];
    }

    /** @param array<string, mixed> $meta */
    private function critical(string $message, array $meta = []): array
    {
        return ['status' => 'critical', 'message' => $message, 'meta' => $meta];
    }
}
