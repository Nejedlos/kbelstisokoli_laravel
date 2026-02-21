<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FinanceSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Spouštím synchronizaci financí...');
        \App\Jobs\FinanceSyncJob::dispatchSync();
        $this->info('Hotovo.');
    }
}
