<?php

namespace App\Livewire\Public;

use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\PublicMatchListingService;
use App\Services\Stats\StandingsConfigService;
use Illuminate\Support\Collection;
use Livewire\Component;

class StandingsTable extends Component
{
    public ?int $seasonId = null;

    public ?int $teamId = null;

    public ?string $competitionUrl = null;

    public bool $showFilters = true;

    public ?int $limit = null;

    public bool $highlightOurTeam = true;

    public bool $compact = false;

    public bool $configuredOnly = false;

    public array $expanded = [];

    public function mount(?int $seasonId = null, ?int $teamId = null, ?string $competitionUrl = null, bool $showFilters = true, ?int $limit = null, bool $compact = false, bool $configuredOnly = false)
    {
        $this->seasonId = $seasonId ?? ($configuredOnly
            ? app(PublicMatchListingService::class)->currentSeason()?->id
            : Season::where('is_active', true)->first()?->id ?? Season::latest('id')->first()?->id);
        $this->teamId = $teamId;
        $this->competitionUrl = $competitionUrl;
        $this->showFilters = $showFilters;
        $this->limit = $limit;
        $this->compact = $compact;
        $this->configuredOnly = $configuredOnly;
    }

    public function toggleExpand(string $url): void
    {
        $this->expanded[$url] = ! ($this->expanded[$url] ?? false);
    }

    public function getStandingsProperty(): Collection
    {
        if (! $this->seasonId) {
            return collect();
        }

        $query = CompetitionStanding::where('season_id', $this->seasonId);

        if ($this->competitionUrl) {
            $query->where('competition_url', $this->competitionUrl);
        } elseif ($this->teamId || $this->configuredOnly) {
            $competitionUrls = app(StandingsConfigService::class)
                ->competitionUrls($this->seasonId, $this->teamId);

            if ($competitionUrls->isEmpty()) {
                return collect();
            }

            $query->whereIn('competition_url', $competitionUrls);
        }

        return $query->orderBy('rank')->get();
    }

    public function render()
    {
        $standings = $this->standings;
        $groupedStandings = $standings->groupBy('competition_url');
        $configuredTeamNames = app(StandingsConfigService::class)
            ->forSeason($this->seasonId)
            ->load('team:id,name')
            ->whereIn('competition_url', $groupedStandings->keys())
            ->groupBy('competition_url')
            ->map(fn (Collection $configs) => $configs
                ->map(fn (ExternalTeamSeasonConfig $config) => $config->team_name_in_source ?: $config->team?->name)
                ->filter()
                ->map(fn (string $name) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name))))
                ->unique()
                ->values());

        return view('livewire.public.standings-table', [
            'groupedStandings' => $groupedStandings,
            'configuredTeamNames' => $configuredTeamNames,
            'seasons' => $this->showFilters ? Season::orderByDesc('name')->get() : collect(),
            'teams' => $this->showFilters ? Team::orderBy('name')->get() : collect(),
        ]);
    }
}
