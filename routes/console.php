<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Aggiorna tracking ogni 10 minuti
Schedule::command('app:update-tracking')
    ->everyTenMinutes()
    ->withoutOverlapping();

// Pulisce carrelli vecchi ogni giorno a mezzanotte
Schedule::command('cart:clear-old')
    ->dailyAt('00:00')
    ->withoutOverlapping();