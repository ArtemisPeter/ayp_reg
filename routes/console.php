<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --only-db')->daily()->at('02:00')->onOneServer();
Schedule::command('backup:clean')->daily()->at('02:30')->onOneServer();
Schedule::command('backup:monitor')->daily()->at('02:45')->onOneServer();
