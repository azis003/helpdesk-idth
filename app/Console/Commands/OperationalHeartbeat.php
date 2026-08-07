<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class OperationalHeartbeat extends Command
{
    protected $signature = 'sihati:ops:heartbeat';

    protected $aliases = ['ops:heartbeat'];

    protected $description = 'Mencatat heartbeat scheduler untuk monitoring operasional.';

    public function handle(): int
    {
        $now = Carbon::now((string) config('app.timezone', 'Asia/Jakarta'));
        $key = (string) config('ops.scheduler_heartbeat_cache_key', 'sihati:ops:scheduler-heartbeat');
        $ttl = max(300, (int) config('ops.health.scheduler_max_age_seconds', 180) * 3);

        Cache::store((string) config('cache.default'))->put($key, $now->toIso8601String(), $now->copy()->addSeconds($ttl));

        $path = (string) config('ops.scheduler_heartbeat_file', '');

        if ($path !== '') {
            try {
                $directory = dirname($path);

                if (! is_dir($directory)) {
                    @mkdir($directory, 0770, true);
                }

                $temporaryPath = $path.'.tmp.'.getmypid();
                if (file_put_contents($temporaryPath, json_encode([
                    'updated_at' => $now->toIso8601String(),
                ], JSON_THROW_ON_ERROR)) === false || ! @rename($temporaryPath, $path)) {
                    throw new \RuntimeException('Marker file tidak dapat ditulis secara atomic.');
                }
            } catch (Throwable $exception) {
                $this->warn('Heartbeat cache tersimpan, tetapi marker file gagal ditulis: '.$exception::class);

                return self::FAILURE;
            }
        }

        $this->info('Heartbeat scheduler diperbarui pada '.$now->toIso8601String().'.');

        return self::SUCCESS;
    }
}
