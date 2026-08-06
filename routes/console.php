<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sihati:tickets:expire-requester-waits')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);
