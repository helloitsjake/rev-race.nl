<?php

use App\Console\Commands\NewsCrawlCommand;
use App\Models\SimulationLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('news:crawl {--dry-run : Only log findings, do not save or publish}', function () {
    $this->call(NewsCrawlCommand::class, [
        '--dry-run' => $this->option('dry-run'),
    ]);
})->purpose('Crawl motor news');

Schedule::call(function (): void {
    SimulationLog::query()
        ->where('created_at', '<', now()->subHours(25))
        ->delete();
})->hourly();
