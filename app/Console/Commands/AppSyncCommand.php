<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync {--force : Přepíše existující data, pokud je to podporováno dílčími příkazy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provádí agregovanou synchronizaci aplikace (ikony, oznámení, finance, avatary, apod.).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('== APP SYNC START ==');

        // Ikony
        if (class_exists(\App\Console\Commands\IconsSyncCommand::class)) {
            $this->call('icons:sync');
        }

        // Oznámení
        if (class_exists(\App\Console\Commands\AnnouncementsSyncCommand::class)) {
            $this->call('announcements:sync');
        }

        // Finance
        if (class_exists(\App\Console\Commands\FinanceSyncCommand::class)) {
            $this->call('finance:sync');
        }

        // Avatary a hráčské fotky
        if (class_exists(\App\Console\Commands\AvatarsSyncCommand::class)) {
            $args = [];
            if ($this->option('force')) {
                $args['--force'] = true;
            }
            $this->call('avatars:sync', $args);
        }

        $this->info('== APP SYNC DONE ==');
        return self::SUCCESS;
    }
}
