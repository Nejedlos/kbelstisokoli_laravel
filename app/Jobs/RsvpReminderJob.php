<?php

namespace App\Jobs;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use App\Models\User;
use App\Notifications\AttendanceReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RsvpReminderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('RSVP Reminder Job started.');

        $now = now();
        $in24Hours = $now->copy()->addHours(24);

        // 1. Zápasy
        $matches = BasketballMatch::where('scheduled_at', '>', $now)
            ->where('scheduled_at', '<=', $in24Hours)
            ->where('status', 'planned')
            ->get();

        foreach ($matches as $match) {
            $this->remindUsers($match);
        }

        // 2. Tréninky
        $trainings = Training::where('starts_at', '>', $now)
            ->where('starts_at', '<=', $in24Hours)
            ->get();

        foreach ($trainings as $training) {
            $this->remindUsers($training);
        }

        // 3. Klubové akce
        $events = ClubEvent::where('starts_at', '>', $now)
            ->where('starts_at', '<=', $in24Hours)
            ->where('rsvp_enabled', true)
            ->get();

        foreach ($events as $event) {
            $this->remindUsers($event);
        }

        Log::info('RSVP Reminder Job finished.');
    }

    protected function remindUsers($event): void
    {
        // Tady by byla logika pro vyhledání uživatelů, kteří mají být na akci a neodpověděli.
        // Pro zjednodušení skeletonu: všichni aktivní uživatelé s rolí player/coach, kteří nemají záznam v attendances pro tuto akci.

        $userIdsWithResponse = $event->attendances()->pluck('user_id')->toArray();

        $usersToRemind = User::active()
            ->role(['player', 'coach'])
            ->whereNotIn('id', $userIdsWithResponse)
            ->get();

        foreach ($usersToRemind as $user) {
            // Notifikace je skeleton, zatím ji nebudeme reálně posílat pokud neexistuje třída
            // Log::debug("Reminding user {$user->name} about event: " . ($event->title ?? $event->name ?? 'Zápas/Trénink'));
            // $user->notify(new AttendanceReminderNotification($event));
        }
    }
}
