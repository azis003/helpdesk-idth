<?php

namespace App\Services;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use App\Notifications\OperationalAlertNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class StorageCapacityService
{
    /**
     * Return a filesystem capacity snapshot without exposing the path to users.
     *
     * Remote object storage does not expose a portable capacity API, so it is
     * reported as unknown and must be monitored by the storage provider.
     *
     * @return array<string, mixed>
     */
    public function inspect(?string $disk = null): array
    {
        $disk ??= (string) config('ops.storage_disk', config('filesystems.attachment_disk', 'local'));
        $diskConfig = (array) config("filesystems.disks.{$disk}", []);
        $driver = (string) ($diskConfig['driver'] ?? 'unknown');
        $root = $diskConfig['root'] ?? null;

        if (! is_string($root) || $root === '') {
            return $this->unknown($disk, $driver, 'Kapasitas storage remote harus dipantau dari provider storage.');
        }

        if (! is_dir($root)) {
            return $this->critical($disk, $driver, 'Direktori storage privat tidak ditemukan.');
        }

        $totalBytes = @disk_total_space($root);
        $freeBytes = @disk_free_space($root);

        if (! is_int($totalBytes) && ! is_float($totalBytes)) {
            return $this->unknown($disk, $driver, 'Kapasitas total storage tidak dapat dibaca.');
        }

        if (! is_int($freeBytes) && ! is_float($freeBytes)) {
            return $this->unknown($disk, $driver, 'Kapasitas storage yang tersisa tidak dapat dibaca.');
        }

        $totalBytes = (float) $totalBytes;
        $freeBytes = (float) $freeBytes;
        $usedBytes = max(0.0, $totalBytes - $freeBytes);
        $usedPercent = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 2) : 100.0;

        $warningPercent = (int) config('ops.storage_warning_percent', 80);
        $criticalPercent = (int) config('ops.storage_critical_percent', 90);
        $status = $usedPercent >= $criticalPercent
            ? 'critical'
            : ($usedPercent >= $warningPercent ? 'warning' : 'ok');

        return [
            'disk' => $disk,
            'driver' => $driver,
            'status' => $status,
            'message' => match ($status) {
                'critical' => 'Storage privat melewati ambang kritis.',
                'warning' => 'Storage privat mencapai ambang peringatan.',
                default => 'Kapasitas storage privat normal.',
            },
            'used_percent' => $usedPercent,
            'total_bytes' => $totalBytes,
            'free_bytes' => $freeBytes,
            'checked_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta'))->toIso8601String(),
        ];
    }

    /**
     * Log and notify an operational transition. A cache key prevents an
     * alert storm when the scheduler runs while a disk remains full.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function alert(array $snapshot): bool
    {
        $disk = (string) ($snapshot['disk'] ?? 'unknown');
        $status = (string) ($snapshot['status'] ?? 'unknown');
        $cacheKey = (string) config('ops.storage_alert_cache_prefix', 'sihati:ops:storage:').$disk;
        $previous = Cache::get($cacheKey);
        $previousState = is_array($previous) ? $previous : null;
        $now = Carbon::now((string) config('app.timezone', 'Asia/Jakarta'));
        $shouldNotify = $this->shouldNotify($previousState, $status, $now);

        $level = match ($status) {
            'critical' => 'critical',
            'warning', 'unknown' => 'warning',
            default => 'info',
        };
        $context = [
            'disk' => $disk,
            'status' => $status,
            'used_percent' => $snapshot['used_percent'] ?? null,
            'free_bytes' => $snapshot['free_bytes'] ?? null,
            'total_bytes' => $snapshot['total_bytes'] ?? null,
        ];
        Log::channel((string) config('ops.log_channel', 'ops'))->{$level}(
            'Pemeriksaan kapasitas storage SIHATI.',
            $context,
        );

        if ($shouldNotify) {
            $this->notifyAdministrators($snapshot, $previousState, $now);
        }

        $ttlMinutes = max(5, (int) config('ops.storage_alert_cooldown_minutes', 60));
        Cache::put($cacheKey, [
            'status' => $status,
            'notified_at' => $shouldNotify ? $now->toIso8601String() : ($previousState['notified_at'] ?? null),
            'checked_at' => $now->toIso8601String(),
        ], $now->copy()->addMinutes($ttlMinutes));

        return $shouldNotify;
    }

    /** @return array<string, mixed> */
    private function unknown(string $disk, string $driver, string $message): array
    {
        return [
            'disk' => $disk,
            'driver' => $driver,
            'status' => 'unknown',
            'message' => $message,
            'used_percent' => null,
            'total_bytes' => null,
            'free_bytes' => null,
            'checked_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta'))->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function critical(string $disk, string $driver, string $message): array
    {
        return [
            'disk' => $disk,
            'driver' => $driver,
            'status' => 'critical',
            'message' => $message,
            'used_percent' => null,
            'total_bytes' => null,
            'free_bytes' => null,
            'checked_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta'))->toIso8601String(),
        ];
    }

    private function shouldNotify(mixed $previous, string $status, Carbon $now): bool
    {
        if (! is_array($previous)) {
            return $status !== 'ok';
        }

        if (($previous['status'] ?? null) !== $status) {
            return true;
        }

        if ($status === 'ok') {
            return false;
        }

        $notifiedAt = $previous['notified_at'] ?? null;

        if (! is_string($notifiedAt)) {
            return true;
        }

        try {
            return $now->diffInMinutes(Carbon::parse($notifiedAt, $now->getTimezone()))
                >= max(5, (int) config('ops.storage_alert_cooldown_minutes', 60));
        } catch (Throwable) {
            return true;
        }
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array<string, mixed>|null  $previous
     */
    private function notifyAdministrators(array $snapshot, ?array $previous, Carbon $now): void
    {
        $status = (string) ($snapshot['status'] ?? 'unknown');
        $wasAlert = is_array($previous) && in_array($previous['status'] ?? null, ['warning', 'critical', 'unknown'], true);
        $isRecovery = $status === 'ok' && $wasAlert;

        if ($status === 'ok' && ! $isRecovery) {
            return;
        }

        $severity = $isRecovery ? 'recovered' : ($status === 'critical' ? 'critical' : 'warning');
        $title = $isRecovery ? 'Storage SIHATI kembali normal' : 'Peringatan kapasitas storage SIHATI';
        $message = $isRecovery
            ? 'Kapasitas storage privat sudah berada di bawah ambang peringatan.'
            : (string) ($snapshot['message'] ?? 'Kapasitas storage perlu diperiksa.');

        try {
            $administrators = User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('slug', RoleEnum::SuperAdmin->value))
                ->get();

            Notification::sendNow(
                $administrators,
                new OperationalAlertNotification(
                    'storage.capacity',
                    $title,
                    $message,
                    $severity,
                    route('notifications.index'),
                ),
            );
        } catch (Throwable $exception) {
            Log::channel((string) config('ops.log_channel', 'ops'))->error(
                'Notifikasi kapasitas storage tidak dapat dikirim.',
                [
                    'exception' => $exception::class,
                    'checked_at' => $now->toIso8601String(),
                ],
            );
        }
    }
}
