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

        if (! $user || ! $eventModel) {
            return;
        }

        $title = $eventModel->title ?? $eventModel->name ?? 'akci';
        $type = match (get_class($eventModel)) {
            \App\Models\Training::class => 'training',
            \App\Models\BasketballMatch::class => 'match',
            \App\Models\ClubEvent::class => 'event',
            default => strtolower(class_basename($eventModel)),
        };

        $actionUrl = route('member.attendance.show', [
            'type' => $type,
            'id' => $eventModel->id
        ]);

        // 1. Notifikace pro uživatele (potvrzení)
        $user->notify(new RsvpChangedNotification($title, $attendance->status, null, $actionUrl));

        // 2. Notifikace pro rodiče
        if ($user->relationLoaded('parents') || $user->parents()->exists()) {
            foreach ($user->parents as $parent) {
                $parent->notify(new RsvpChangedNotification($title, $attendance->status, $user, $actionUrl));
            }
        }

        // 3. Notifikace pro trenéry
        $coaches = collect();

        // Zkusíme najít týmy akce
        $teams = collect();
        if (method_exists($eventModel, 'teams')) {
            $teams = $eventModel->teams;
        } elseif (method_exists($eventModel, 'team') && $eventModel->team) {
            $teams = collect([$eventModel->team]);
        }

        foreach ($teams as $team) {
            if ($team) {
                $coaches = $coaches->merge($team->activeCoaches()->get());
            }
        }

        // Unikátní trenéři, kteří nejsou samotný uživatel (pokud trenér mění RSVP sobě jako hráči)
        $coaches = $coaches->unique('id')->reject(fn($c) => $c->id === $user->id);

        foreach ($coaches as $coach) {
            $coach->notify(new RsvpChangedNotification($title, $attendance->status, $user, $actionUrl));
        }
    }
}
