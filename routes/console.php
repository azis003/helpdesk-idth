<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sihati:ops:heartbeat')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer();

Schedule::command('sihati:ops:check-storage')
    ->hourly()
    ->withoutOverlapping(10)
    ->onOneServer();

$queueConnection = (string) config('queue.default', 'sync');
$queueName = (string) config("queue.connections.{$queueConnection}.queue", 'default');

if ($queueConnection !== 'sync') {
    Schedule::command(sprintf(
        'queue:monitor %s:%s --max=%d',
        $queueConnection,
        $queueName,
        (int) config('ops.health.queue_max_depth', 100),
    ))
        ->everyMinute()
        ->withoutOverlapping(2)
        ->onOneServer();
}

Schedule::command('sihati:tickets:expire-requester-waits')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command('sihati:tickets:auto-close-confirmations')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command('sihati:attachments:purge-data-exports')
    ->dailyAt('01:00')
    ->withoutOverlapping(30);

Schedule::command('sihati:retention:purge-tickets')
    ->dailyAt('02:00')
    ->withoutOverlapping(30);

Schedule::command('sihati:logs:purge')
    ->dailyAt('02:30')
    ->withoutOverlapping(30);
