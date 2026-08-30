<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceEmailService;
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
    protected $description = 'Odešle upomínky na nepotvrzenou docházku.';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceEmailService $service): int
    {
        $counts = $service->dispatchDue(now());
        $this->info("Queued {$counts['reminders']} reminders and {$counts['summaries']} summaries on critical-mail.");

        return self::SUCCESS;
    }
}
