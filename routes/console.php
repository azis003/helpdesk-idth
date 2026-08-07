<?php

use Illuminate\Support\Facades\Schedule;

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
