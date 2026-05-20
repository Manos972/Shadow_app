<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('market:fetch')->everyFifteenMinutes()->between('9:00', '22:00');
Schedule::command('alerts:process')->everyFiveMinutes()->between('9:00', '22:00');
Schedule::command('recurring:process')->daily();
