<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\DB;

Artisan::command('telescope:clear', function () {
    $this->info('Starting telescope entries maintenance...');

    $commands = Artisan::all();
    if (isset($commands['telescope:prune'])) {
        $this->call('telescope:prune');
        $this->info('Telescope entries pruned using artisan command.');
    } else {
        $this->warn('Artisan command telescope:prune not found.');
        $this->info('Attempting manual cleanup of entries older than 24 hours...');

        $count = DB::table('telescope_entries')
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $this->info("Successfully deleted $count old telescope entries directly from DB.");
    }
})->purpose('Prune old telescope entries (keeps last 24h)');

use Illuminate\Support\Facades\Schedule;

Schedule::command('seo:generate-sitemap')->dailyAt('03:00');

// Automatická obnova sezóny - 31. srpna ve 23:55
Schedule::command('season:renew')->cron('55 23 31 8 *');

// Automatické promazávání Telescope záznamů (zachová posledních 24 hodin)
Schedule::command('telescope:clear')->daily();
