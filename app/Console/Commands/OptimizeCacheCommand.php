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

        // 1b. Event cachování
        $this->info('>>> Cachuji eventy...');
        $this->call('event:cache');

        // 1c. View cachování (pro jistotu, optimize už by měl mít)
        $this->info('>>> Cachuji views...');
        $this->call('view:cache');

        // 1d. Filament specifické cachování
        $this->info('>>> Optimalizuji Filament...');
        try {
            $this->call('filament:cache-components');
        } catch (\Throwable $e) {
            $this->warn('Filament cache-components selhal nebo není dostupný.');
        }

        // 1e. Blade Icons cache
        $this->info('>>> Optimalizuji ikony...');
        try {
            $this->call('icons:cache');
        } catch (\Throwable $e) {
            $this->warn('Icons cache selhal.');
        }

        // 2. Naplnění page cache pro veřejný web
        $this->info('>>> Nyní naplním full-page cache (priming)...');
        $this->call('page-cache:prime');

        $this->info('>>> Kompletní optimalizace dokončena.');

        return 0;
    }
}
