<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Demo data is relative to today, so the live demo starts each day fresh.
Schedule::command('migrate:fresh --seed --force')
    ->dailyAt('03:00')
    ->timezone('Asia/Jakarta')
    ->environments('production');
