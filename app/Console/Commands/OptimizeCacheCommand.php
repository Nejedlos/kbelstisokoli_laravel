<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provede standardní optimalizaci a následně naplní page cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('>>> Spouštím kompletní optimalizaci systému...');

        // 1. Standardní Laravel optimalizace (config, route, views atd.)
        $this->call('optimize');

        // 1b. Filament specifické cachování (pokud existuje)
        try {
            if ($this->getApplication()->has('filament:cache-components')) {
                $this->call('filament:cache-components');
            }
        } catch (\Throwable $e) {
            // Ignorujeme pokud příkaz neexistuje nebo selže
        }

        // 2. Naplnění page cache pro veřejný web
        $this->info('>>> Nyní naplním full-page cache (priming)...');
        $this->call('page-cache:prime');

        $this->info('>>> Kompletní optimalizace dokončena.');

        return 0;
    }
}
