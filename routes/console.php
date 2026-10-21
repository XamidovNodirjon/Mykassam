<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('debts:remind')->dailyAt('10:00')->withoutOverlapping();

/*
 * Shared hosting has no long-running queue worker, so the scheduler (cron, every minute) drains the
 * queue: SMS codes go out within a minute. A server with its own worker can keep this too.
 */
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
