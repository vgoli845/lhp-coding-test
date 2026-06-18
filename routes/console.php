<?php

use App\Console\Commands\SendEventReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check hourly for events crossing the 3-day or 24-hour reminder windows.
Schedule::command(SendEventReminders::class)->hourly()->withoutOverlapping();
