<?php

use App\Services\TicketNumberAllocator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = $argv[1] ?? 'allocate';
$ticketClass = $argv[2] ?? 'INC';
$ticketYear = (int) ($argv[3] ?? 2099);

if ($mode === 'cleanup') {
    DB::table('ticket_number_sequences')
        ->where('ticket_class', $ticketClass)
        ->where('ticket_year', $ticketYear)
        ->delete();

    exit(0);
}

$barrier = $argv[4] ?? null;
$workerId = $argv[5] ?? uniqid('worker-', true);

if ($barrier !== null) {
    file_put_contents($barrier.'.'.$workerId.'.ready', 'ready');

    $deadline = microtime(true) + 15;
    while (count(glob($barrier.'.*.ready') ?: []) < 2 && microtime(true) < $deadline) {
        usleep(10_000);
    }
}

try {
    $result = app(TicketNumberAllocator::class)->next($ticketClass, $ticketYear);
    fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR));
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage());
    exit(1);
}
