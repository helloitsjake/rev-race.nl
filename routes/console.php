<?php

use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\NewsCrawlCommand;

Artisan::command('news:crawl {--dry-run : Only log findings, do not save or publish}', function () {
    $this->call(NewsCrawlCommand::class, [
        '--dry-run' => $this->option('dry-run'),
    ]);
})->purpose('Crawl motor news');
