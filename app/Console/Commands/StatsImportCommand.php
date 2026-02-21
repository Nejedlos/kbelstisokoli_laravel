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
    protected $signature = 'stats:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí import externích statistik.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching stats import job...');
        \App\Jobs\StatsImportJob::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
