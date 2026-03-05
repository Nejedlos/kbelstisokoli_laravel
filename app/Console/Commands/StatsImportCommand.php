<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StatsImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:import {--recent : Synchronizuje pouze nedávné zápasy pro všechny týmy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí hromadnou synchronizaci externích statistik pro všechny povolené týmy.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $recent = (bool) $this->option('recent');

        $this->info($recent
            ? 'Spouštím prioritní synchronizaci nedávných zápasů (Recent)...'
            : 'Spouštím celkovou synchronizaci sezóny pro všechny týmy (Baseline)...'
        );

        \App\Jobs\Stats\ExternalStatsSchedulerJob::dispatch($recent);

        $this->info('Úloha byla zařazena do fronty k hromadnému zpracování.');
    }
}
