<?php

namespace App\Support;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Team;
use Illuminate\Support\Collection;

class HeroEventsHelper
{
    /**
     * Získá nejbližší akce (zápasy nebo klubové akce) napříč všemi týmy.
     */
    public static function getUpcomingEvents(): Collection
    {
        $now = now();

        // 1. Získáme nejbližší zápasy (přednačteme týmy)
        $matches = BasketballMatch::with('teams')
            ->where('scheduled_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->whereHas('teams')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // 2. Získáme nejbližší klubové akce (přednačteme týmy)
        $clubEvents = ClubEvent::with('teams')
            ->where('starts_at', '>', $now)
            ->where('is_public', true)
            ->whereHas('teams')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        // 3. Sloučíme, seřadíme a naformátujeme
        return $matches->concat($clubEvents)
            ->sortBy(fn($event) => $event instanceof BasketballMatch ? $event->scheduled_at : $event->starts_at)
            ->take(2)
            ->map(fn($event) => self::formatEvent($event));
    }

    protected static function formatEvent($event): array
    {
        $isMatch = $event instanceof BasketballMatch;
        $locale = app()->getLocale();

        // Sestavíme zkratky všech zapojených týmů
        $teamShorts = $event->teams->map(function ($team) use ($locale) {
            $name = $team->getTranslation('name', $locale);
            return trim(str_replace('Sokol Kbely ', '', $name));
        })->unique();

        $teamShort = $teamShorts->implode(' & ');

        return [
            'id' => $event->id,
            'type' => $isMatch ? 'match' : 'event',
            'team_name' => $teamShort,
            'team_short' => $teamShort,
            'title' => $isMatch
                ? ($event->is_home
                    ? $event->official_team_name . ' – ' . $event->official_opponent_name
                    : $event->official_opponent_name . ' – ' . $event->official_team_name)
                : $event->getTranslation('title', $locale),
            'date' => $isMatch ? $event->scheduled_at : $event->starts_at,
            'location' => $isMatch ? ($event->venue?->name ?? $event->location) : $event->location,
            'is_home' => $isMatch ? $event->is_home : true,
            'opponent' => $isMatch ? $event->official_opponent_name : null,
            'url' => $isMatch ? route('public.matches.show', $event->id) : route('public.events.show', $event->id),
        ];
    }
}
