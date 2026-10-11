<?php

namespace App\Services;

use App\Models\BasketballMatch;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PublicMatchListingService
{
    public function currentSeason(): ?Season
    {
        return Season::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first()
            ?? Season::query()->where('name', Season::getExpectedCurrentSeasonName())->first();
    }

    public function forCurrentSeason(string $type = 'upcoming', int $limit = 5): Collection
    {
        $season = $this->currentSeason();

        if (! $season) {
            return collect();
        }

        $query = BasketballMatch::query()
            ->with(['team', 'teams', 'opponent', 'season', 'prediction', 'venue'])
            ->where('season_id', $season->id);

        $this->restrictToConfiguredTeams($query, $season);

        if ($type === 'latest') {
            $query
                ->whereIn('status', ['finished', 'played', 'completed'])
                ->whereNotNull('score_home')
                ->whereNotNull('score_away')
                ->where('scheduled_at', '<=', now())
                ->orderByDesc('scheduled_at');
        } else {
            $query
                ->whereNotIn('status', ['finished', 'played', 'completed', 'cancelled'])
                ->where('scheduled_at', '>=', now()->subHours(3))
                ->orderBy('scheduled_at');
        }

        return $query->limit(max(1, min($limit, 12)))->get();
    }

    protected function restrictToConfiguredTeams(Builder $query, Season $season): void
    {
        $teamIds = ExternalTeamSeasonConfig::query()
            ->where('season_id', $season->id)
            ->where('is_enabled', true)
            ->pluck('team_id')
            ->unique()
            ->values();

        if ($teamIds->isEmpty()) {
            return;
        }

        $query->where(function (Builder $teamQuery) use ($teamIds): void {
            $teamQuery
                ->whereIn('team_id', $teamIds)
                ->orWhereHas('teams', fn (Builder $pivotQuery) => $pivotQuery->whereIn('teams.id', $teamIds));
        });
    }
}
