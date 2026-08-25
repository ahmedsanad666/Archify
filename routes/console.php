<?php

use App\Jobs\RetryFailedTranslationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Shared-hosting queue pattern (project brief §8):
| Cron every minute runs `schedule:run`, which drains the queue briefly
| instead of a long-lived `queue:work` daemon.
*/
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::job(new RetryFailedTranslationsJob)->daily();
