<?php

namespace Tests\Feature;

use App\Models\TicketNumberSequence;
use App\Services\TicketNumberAllocator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TicketNumberAllocatorTest extends TestCase
{
    public function test_postgresql_workers_allocate_unique_numbers_when_the_first_sequence_row_is_raced(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Uji worker concurrency membutuhkan PostgreSQL.');
        }

        $ticketClass = 'INC';
        $ticketYear = 2099;
        $barrier = storage_path('framework/testing/ticket-number-'.Str::uuid());
        $workerScript = base_path('tests/Support/ticket_number_worker.php');
        $workers = [
            new Process([PHP_BINARY, $workerScript, 'allocate', $ticketClass, (string) $ticketYear, $barrier, 'one'], base_path()),
            new Process([PHP_BINARY, $workerScript, 'allocate', $ticketClass, (string) $ticketYear, $barrier, 'two'], base_path()),
        ];

        File::ensureDirectoryExists(dirname($barrier));

        try {
            foreach ($workers as $worker) {
                $worker->start();
            }

            $deadline = microtime(true) + 20;
            while (count(glob($barrier.'.*.ready') ?: []) < 2 && microtime(true) < $deadline) {
                usleep(10_000);
            }

            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
            }

            $allocations = collect($workers)
                ->map(fn (Process $worker): array => json_decode($worker->getOutput(), true, 512, JSON_THROW_ON_ERROR))
                ->sortBy('sequence')
                ->values();

            $this->assertSame([1, 2], $allocations->pluck('sequence')->all());
            $this->assertSame(['INC-2099-00001', 'INC-2099-00002'], $allocations->pluck('number')->all());
            $this->assertDatabaseHas('ticket_number_sequences', [
                'ticket_class' => $ticketClass,
                'ticket_year' => $ticketYear,
                'last_sequence' => 2,
            ]);
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(1);
                }
            }

            $cleanup = new Process([
                PHP_BINARY,
                $workerScript,
                'cleanup',
                $ticketClass,
                (string) $ticketYear,
            ], base_path());
            $cleanup->run();

            File::delete(glob($barrier.'.*.ready') ?: []);
        }
    }

    public function test_a_reserved_number_is_not_reused_after_a_failed_transaction(): void
    {
        $allocator = app(TicketNumberAllocator::class);

        $first = $allocator->next('REQ', 2098);

        try {
            DB::transaction(function (): void {
                throw new \RuntimeException('Simulasi kegagalan transaksi tiket.');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulasi kegagalan transaksi tiket.', $exception->getMessage());
        }

        $second = $allocator->next('REQ', 2098);

        $this->assertSame('REQ-2098-00001', $first['number']);
        $this->assertSame('REQ-2098-00002', $second['number']);
        $this->assertSame(2, TicketNumberSequence::query()
            ->where('ticket_class', 'REQ')
            ->where('ticket_year', 2098)
            ->value('last_sequence'));
    }
}
