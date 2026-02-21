<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AnnouncementsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje stav oznámení (deaktivuje expirovaná).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching Announcement sync job...');
        \App\Jobs\AnnouncementSyncJob::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
