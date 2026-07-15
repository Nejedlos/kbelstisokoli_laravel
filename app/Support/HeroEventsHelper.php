<?php

namespace App\Support;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Team;
use App\Models\Training;
use Illuminate\Support\Collection;

class HeroEventsHelper
{
    /**
     * Získá nejbližší akce (zápasy, klubové akce nebo dnešní tréninky) napříč všemi týmy.
     */
    public static function getUpcomingEvents(): Collection
    {
        $now = now();
        $today = today();

        // 1. Získáme nejbližší zápasy (přednačteme týmy a počet přihlášených)
        $matches = BasketballMatch::with('teams')
            ->withCount(['attendances as confirmed_count' => function ($query) {
                $query->where('planned_status', 'confirmed');
            }])
            ->where('scheduled_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->whereHas('teams')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // 2. Získáme nejbližší klubové akce (přednačteme týmy a počet přihlášených)
        $clubEvents = ClubEvent::with('teams')
            ->withCount(['attendances as confirmed_count' => function ($query) {
                $query->where('planned_status', 'confirmed');
            }])
            ->where('starts_at', '>', $now)
            ->where('is_public', true)
            ->whereHas('teams')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        // 3. Získáme dnešní tréninky (přednačteme týmy a počet přihlášených)
        $trainings = Training::with('teams')
            ->withCount(['attendances as confirmed_count' => function ($query) {
                $query->where('planned_status', 'confirmed');
            }])
            ->whereDate('starts_at', $today)
            ->where('starts_at', '>', $now->subHours(2)) // Zobrazujeme i nedávno začaté
            ->whereHas('teams')
            ->orderBy('starts_at')
            ->get();

        // 4. Sloučíme, seřadíme a naformátujeme
        return $trainings->concat($matches)->concat($clubEvents)
            ->sortBy(fn($event) => $event instanceof BasketballMatch ? $event->scheduled_at : $event->starts_at)
            ->take(3)
            ->map(fn($event) => self::formatEvent($event));
    }

    protected static function formatEvent($event): array
    {
        $isMatch = $event instanceof BasketballMatch;
        $isTraining = $event instanceof Training;
        $locale = app()->getLocale();

        // Sestavíme zkratky všech zapojených týmů
        $teamShorts = $event->teams->map(function ($team) use ($locale) {
            $name = $team->getTranslation('name', $locale);
            return trim(str_replace('Sokol Kbely ', '', $name));
        })->unique();

        $teamShort = $teamShorts->implode(' & ');

        $title = '';
        if ($isMatch) {
            $title = $event->is_home
                ? $event->official_team_name . ' – ' . $event->official_opponent_name
                : $event->official_opponent_name . ' – ' . $event->official_team_name;
        } elseif ($isTraining) {
            $sport = $event->sport ?? 'basketball';
            $title = __('member.attendance.event_types.training_' . $sport);
        } else {
            $title = $event->getTranslation('title', $locale);
        }

        $url = '';
        if (auth()->check()) {
            $type = $isMatch ? 'match' : ($isTraining ? 'training' : 'event');
            $url = route('member.attendance.show', ['type' => $type, 'id' => $event->id]);
        } else {
            if ($isMatch) {
                $url = route('public.matches.show', $event->id);
            } elseif ($isTraining) {
                $url = route('public.trainings.index');
            } else {
                $url = route('public.events.show', $event->id);
            }
        }

        return [
            'id' => $event->id,
            'type' => $isMatch ? 'match' : ($isTraining ? 'training' : 'event'),
            'sport' => $isTraining ? ($event->sport ?? 'basketball') : 'basketball',
            'team_name' => $teamShort,
            'team_short' => $teamShort,
            'title' => $title,
            'date' => $isMatch ? $event->scheduled_at : $event->starts_at,
            'location' => $isMatch ? ($event->venue?->name ?? $event->location) : $event->location,
            'is_home' => $isMatch ? $event->is_home : true,
            'opponent' => $isMatch ? $event->official_opponent_name : null,
            'url' => $url,
            'confirmed_count' => $event->confirmed_count ?? 0,
        ];
    }
}
