<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('telescope:clear {--all : Smazat úplně všechno}', function () {
    $this->info('Starting telescope entries maintenance...');
    $this->info('Memory peak usage: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB');

    try {
        if (!Schema::hasTable('telescope_entries')) {
            $this->error('Table telescope_entries does not exist in database.');
            return;
        }

        $totalBefore = DB::table('telescope_entries')->count();
        $tagsBefore = DB::table('telescope_entries_tags')->count();

        $this->info("Current state: Total $totalBefore entries in main table.");
        $this->info("Tags table has $tagsBefore records.");

        if ($this->option('all')) {
            $this->warn('Force deleting ALL entries as requested by --all flag...');
            DB::table('telescope_entries')->delete();
            $this->info('All entries cleared.');
        } else {
            $oldEntries = DB::table('telescope_entries')
                ->where('created_at', '<', now()->subDay())
                ->count();

            $this->info("Entries older than 24h: $oldEntries.");

            if (isset(Artisan::all()['telescope:prune'])) {
                $this->info('Calling native telescope:prune command...');
                $this->call('telescope:prune');
            } else {
                $this->info('Native telescope:prune not found. Performing manual cleanup...');

                $deleted = DB::table('telescope_entries')
                    ->where('created_at', '<', now()->subDay())
                    ->delete();

                $this->info("Manual cleanup finished. Deleted $deleted entries.");
            }
        }

        $totalAfter = DB::table('telescope_entries')->count();
        $tagsAfter = DB::table('telescope_entries_tags')->count();

        $this->info("Maintenance complete.");
        $this->info("Final total entries: $totalAfter (Deleted: " . ($totalBefore - $totalAfter) . ")");
        $this->info("Final tags count: $tagsAfter (Deleted tags: " . ($tagsBefore - $tagsAfter) . ")");
        $this->info('Memory peak usage: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB');

    } catch (\Throwable $e) {
        $this->error('Telescope maintenance failed: ' . $e->getMessage());
        Log::error('Telescope Clear Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
    }
})->purpose('Prune old telescope entries (keeps last 24h or clears all with --all)');

use Illuminate\Support\Facades\Schedule;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;

// Pravidelný priming cache pro veřejný web (každé 3 hodiny)
Schedule::call(fn() => Artisan::call('page-cache:prime'))->name('page-cache:prime')->everyThreeHours()->onOneServer();

// Hook pro smazání full-page cache při volání optimize:clear
Event::listen(CommandFinished::class, function (CommandFinished $event) {
    if ($event->command === 'optimize:clear') {
        Artisan::call('page-cache:clear', [], $event->output);
    }
});

// Automatická údržba Telescope (každý den ve 3:15) - ponechá jen posledních 24h
Schedule::call(fn() => Artisan::call('telescope:clear'))->dailyAt('03:15');

Schedule::call(fn() => Artisan::call('seo:generate-sitemap'))->dailyAt('03:00');

// Automatická obnova sezóny - 31. srpna ve 23:55
Schedule::call(fn() => Artisan::call('season:renew'))->cron('55 23 31 8 *');

// Synchronizace hráčů (denní pro aktuální sezónu)
Schedule::call(fn() => Artisan::call('stats:sync-players'))->dailyAt('04:00');

// Hloubková (excesivní) synchronizace historie hráčů - každou neděli ve 02:00
Schedule::call(fn() => Artisan::call('stats:sync-players', ['--excesive' => true]))->weeklyOn(0, '02:00');

// Pravidelná synchronizace všech týmů v aktivní sezóně (denně v 4:30)
Schedule::call(fn() => Artisan::call('stats:import', ['--queue' => true]))->dailyAt('04:30');

// Častější synchronizace nedávných zápasů (každou hodinu) pro čerstvé výsledky a statistiky
Schedule::call(fn() => Artisan::call('stats:import', ['--recent' => true]))->hourly();

// Pravidelný přepočet statistik po synchronizaci (každou hodinu v 15. minutě)
Schedule::call(fn() => Artisan::call('stats:recompute'))->hourlyAt(15);

// Čištění duplicit (každý den ve 4:00)
Schedule::call(fn() => Artisan::call('stats:cleanup-duplicates'))->dailyAt('04:00');

// Synchronizace výchozích týmů členů (každou hodinu)
Schedule::call(fn() => Artisan::call('app:sync-member-default-teams'))->hourly();

// Měsíční hloubková (excesivní) synchronizace všech historických sezón týmů (1. v měsíci v 1:00)
Schedule::call(function () {
    $configs = \App\Models\ExternalTeamSeasonConfig::where('is_enabled', true)->get();

    foreach ($configs as $config) {
        \App\Jobs\Stats\SyncTeamSeasonJob::dispatch($config->team_id, $config->season_id, ['excesive' => true]);
    }
})->monthlyOn(1, '01:00');
