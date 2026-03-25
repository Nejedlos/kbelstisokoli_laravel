<?php

namespace App\Support;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Team;
use Illuminate\Support\Collection;

class HeroEventsHelper
{
    /**
     * Získá nejbližší akci (zápas nebo klubovou akci) pro každý aktivní tým.
     */
    public static function getUpcomingEvents(): Collection
    {
        $activeTeams = Team::all();
        $events = collect();
        $now = now();

        foreach ($activeTeams as $team) {
            // Najdeme nejbližší zápas
            $match = BasketballMatch::whereHas('teams', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->where('scheduled_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->first();

            // Najdeme nejbližší klubovou akci
            $clubEvent = ClubEvent::whereHas('teams', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->where('starts_at', '>', $now)
            ->where('is_public', true)
            ->orderBy('starts_at')
            ->first();

            // Vybereme to, co je dřív
            $chosen = null;
            if ($match && $clubEvent) {
                $chosen = $match->scheduled_at < $clubEvent->starts_at ? $match : $clubEvent;
            } elseif ($match) {
                $chosen = $match;
            } elseif ($clubEvent) {
                $chosen = $clubEvent;
            }

            if ($chosen) {
                $events->push(self::formatEvent($chosen, $team));
            }
        }

        return $events->sortBy('date')->take(2);
    }

    protected static function formatEvent($event, Team $team): array
    {
        $isMatch = $event instanceof BasketballMatch;

        return [
            'id' => $event->id,
            'type' => $isMatch ? 'match' : 'event',
            'team_name' => $team->getTranslation('name', app()->getLocale()),
            'team_short' => str_replace('Sokol Kbely ', '', $team->getTranslation('name', app()->getLocale())),
            'title' => $isMatch
                ? ($event->is_home
                    ? $event->official_team_name . ' – ' . $event->official_opponent_name
                    : $event->official_opponent_name . ' – ' . $event->official_team_name)
                : $event->getTranslation('title', app()->getLocale()),
            'date' => $isMatch ? $event->scheduled_at : $event->starts_at,
            'location' => $isMatch ? ($event->venue?->name ?? $event->location) : $event->location,
            'is_home' => $isMatch ? $event->is_home : true,
            'opponent' => $isMatch ? $event->official_opponent_name : null,
            'url' => $isMatch ? route('public.matches.show', $event->id) : route('public.events.show', $event->id),
        ];
    }
}
