<?php

namespace App\Listeners;

use App\Events\RsvpChanged;
use App\Notifications\RsvpChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRsvpNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(RsvpChanged $event): void
    {
        $attendance = $event->attendance;
        $user = $attendance->user;
        $eventModel = $attendance->attendable;

        if (!$user || !$eventModel) {
            return;
        }

        $user->notify(new RsvpChangedNotification(
            $eventModel->title ?? $eventModel->name ?? 'akci',
            $attendance->status
        ));
    }
}
