<?php

namespace App\Listeners;

use App\Events\RsvpChanged;
use App\Notifications\RsvpChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RsvpChangedHandler implements ShouldQueue
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

        // Načteme relace soupeře pro zápasy a týmy pro ostatní, abychom měli všechna data pro URL a texty
        if ($eventModel instanceof \App\Models\BasketballMatch) {
            $eventModel->loadMissing(['opponent', 'team']);
        } else {
            $eventModel->loadMissing('teams');
        }

        // --- Identifikace události a štítku ---
        $eventLabelKey = match (get_class($eventModel)) {
            \App\Models\Training::class => 'training',
            \App\Models\BasketballMatch::class => 'match',
            \App\Models\ClubEvent::class => 'club_event',
            default => 'event',
        };

        $eventTitle = match (get_class($eventModel)) {
            \App\Models\Training::class => ($eventModel->location ? "Trénink ($eventModel->location)" : 'Trénink'),
            \App\Models\BasketballMatch::class => ($eventModel->team?->name ?? 'Sokoli').' vs. '.($eventModel->opponent?->name ?? 'soupeř'),
            \App\Models\ClubEvent::class => $eventModel->title,
            default => $eventModel->title ?? $eventModel->name ?? 'událost',
        };

        // Fallback pro prázdný název
        if (empty($eventTitle)) {
            $eventTitle = 'událost';
        }

        // --- URL a Datum ---
        $type = match (true) {
            $eventModel instanceof \App\Models\Training => 'training',
            $eventModel instanceof \App\Models\BasketballMatch => 'match',
            $eventModel instanceof \App\Models\ClubEvent => 'event',
            default => strtolower(class_basename($eventModel)),
        };

        $eventDate = match (true) {
            $eventModel instanceof \App\Models\Training => $eventModel->starts_at,
            $eventModel instanceof \App\Models\BasketballMatch => $eventModel->scheduled_at,
            $eventModel instanceof \App\Models\ClubEvent => $eventModel->starts_at,
            default => $eventModel->starts_at ?? $eventModel->scheduled_at ?? null,
        };

        $actionUrl = route('member.attendance.show', [
            'type' => $type,
            'id' => $eventModel->id,
        ]);

        // --- Příjemci a de-duplikace ---
        $notifiables = collect();

        // 1. Samotný uživatel (pokud je, dostane verzi "Tvoje účast...")
        $notifiables->put($user->id, [
            'notifiable' => $user,
            'is_self' => true,
        ]);

        // 2. Rodiče (dostanou verzi "Změna účasti [Jméno]...", pokud už nejsou self)
        if ($user->relationLoaded('parents') || $user->parents()->exists()) {
            foreach ($user->parents as $parent) {
                if (! $notifiables->has($parent->id)) {
                    $notifiables->put($parent->id, [
                        'notifiable' => $parent,
                        'is_self' => false,
                    ]);
                }
            }
        }

        // 3. Trenéři (dostanou verzi "Změna účasti [Jméno]...", pokud už nejsou self/parent)
        $teams = collect();
        if (method_exists($eventModel, 'teams')) {
            $teams = $eventModel->teams;
        } elseif (method_exists($eventModel, 'team') && $eventModel->team) {
            $teams = collect([$eventModel->team]);
        }

        if ($teams->isNotEmpty()) {
            $teamIds = $teams->pluck('id')->toArray();
            $coaches = \App\Models\User::whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            })->where('is_active', true)->get();

            foreach ($coaches as $coach) {
                if (! $notifiables->has($coach->id)) {
                    $notifiables->put($coach->id, [
                        'notifiable' => $coach,
                        'is_self' => false,
                    ]);
                }
            }
        }

        // --- Odeslání ---
        foreach ($notifiables as $data) {
            $notifiable = $data['notifiable'];
            $isSelf = $data['is_self'];

            // Pokud je notifikace pro samotného uživatele, nepředáváme $user objekt (v notifikaci bude null, což vyvolá "Tvoje účast...")
            $concernedUser = $isSelf ? null : $user;

            $notifiable->notify(new RsvpChangedNotification(
                $eventTitle,
                $attendance->status,
                $concernedUser,
                $actionUrl,
                $eventLabelKey,
                $eventDate
            ));
        }
    }
}
