<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NewsCrawlCommand extends Command
{
    protected $signature = 'news:crawl {--dry-run : Only log findings, do not save or publish}';
    protected $description = 'Crawl motornieuws, laat het herschrijven door AI en sla het op als concept-artikel.';

    public function handle(\App\Services\NewsCrawlService $service)
    {
        $this->info('Starting automated news crawl...');

        $service->crawl($this->option('dry-run'));

        $this->info('News crawl completed.');
    }
}
