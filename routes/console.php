<?php

use App\Models\SimulationLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    SimulationLog::query()
        ->where('created_at', '<', now()->subHours(25))
        ->delete();
})->hourly();
