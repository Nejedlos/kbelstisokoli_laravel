<?php

namespace App\Services\Stats;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class StandingsConfigService
{
    /**
     * Return configurations that may provide standings without enabling other imports.
     *
     * Eligibility is evaluated in PHP because metadata is stored as portable longText
     * rather than relying on database-specific JSON operators.
     *
     * @return EloquentCollection<int, ExternalTeamSeasonConfig>
     */
    public function forSeason(Season|int|null $season, ?int $teamId = null): EloquentCollection
    {
        if ($season === null) {
            return new EloquentCollection;
        }

        return $this->eligibleConfigs(
            $season instanceof Season ? $season->getKey() : $season,
            $teamId,
        );
    }

    /**
     * @return EloquentCollection<int, ExternalTeamSeasonConfig>
     */
    public function forTeam(int $teamId): EloquentCollection
    {
        return $this->eligibleConfigs(null, $teamId);
    }

    /**
     * @return EloquentCollection<int, ExternalTeamSeasonConfig>
     */
    private function eligibleConfigs(?int $seasonId, ?int $teamId): EloquentCollection
    {
        $configs = ExternalTeamSeasonConfig::query()
            ->when($seasonId, fn ($query) => $query->where('season_id', $seasonId))
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->whereNotNull('competition_url')
            ->get();

        return $configs
            ->filter(fn (ExternalTeamSeasonConfig $config): bool => $this->isEligible($config))
            ->values();
    }

    public function isEligible(ExternalTeamSeasonConfig $config): bool
    {
        return $config->is_enabled || data_get($config->metadata, 'standings_enabled') === true;
    }

    /**
     * @return Collection<int, string>
     */
    public function competitionUrls(Season|int|null $season, ?int $teamId = null): Collection
    {
        return $this->forSeason($season, $teamId)
            ->pluck('competition_url')
            ->filter()
            ->unique()
            ->values();
    }
}
