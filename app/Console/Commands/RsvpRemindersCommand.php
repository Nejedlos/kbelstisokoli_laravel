<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RsvpRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsvp:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odešle upomínky na nepotvrzenou docházku (RSVP).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching RSVP reminders job...');
        \App\Jobs\RsvpReminderJob::dispatch();
        $this->info('Job dispatched successfully.');
    }
}
