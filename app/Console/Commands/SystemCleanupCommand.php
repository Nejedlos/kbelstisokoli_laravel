<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provede systémovou údržbu (promazání logů apod.).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching maintenance cleanup job...');
        \App\Jobs\MaintenanceCleanupJob::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
