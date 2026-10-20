<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily Automated PhonePe Settlement Clearing (~11:00 AM IST)
\Illuminate\Support\Facades\Schedule::command('settlements:process')
    ->dailyAt('11:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();

// Auto-release abandoned online checkout stock every 15 minutes
\Illuminate\Support\Facades\Schedule::command('orders:release-unpaid-stock')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Expire finished shorts boost campaigns every hour
\Illuminate\Support\Facades\Schedule::command('shorts:expire-boosts')
    ->hourly()
    ->withoutOverlapping();

// Recalculate 7-day weighted trending scores daily
\Illuminate\Support\Facades\Schedule::command('shorts:recalculate-trending')
    ->dailyAt('04:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping();


