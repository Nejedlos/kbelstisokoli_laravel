<?php

namespace App\Livewire\Public;

use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use Livewire\Component;
use Illuminate\Support\Collection;

class StandingsTable extends Component
{
    public ?int $seasonId = null;
    public ?int $teamId = null;
    public ?string $competitionUrl = null;
    public bool $showFilters = true;
    public ?int $limit = null;
    public bool $highlightOurTeam = true;
    public bool $compact = false;
    public array $expanded = [];

    public function mount(?int $seasonId = null, ?int $teamId = null, ?string $competitionUrl = null, bool $showFilters = true, ?int $limit = null, bool $compact = false)
    {
        $this->seasonId = $seasonId ?? Season::where('is_active', true)->first()?->id ?? Season::latest('id')->first()?->id;
        $this->teamId = $teamId;
        $this->competitionUrl = $competitionUrl;
        $this->showFilters = $showFilters;
        $this->limit = $limit;
        $this->compact = $compact;
    }

    public function toggleExpand(string $url): void
    {
        $this->expanded[$url] = !($this->expanded[$url] ?? false);
    }

    public function getStandingsProperty(): Collection
    {
        if (!$this->seasonId) {
            return collect();
        }

        $query = CompetitionStanding::where('season_id', $this->seasonId);

        if ($this->competitionUrl) {
            $query->where('competition_url', $this->competitionUrl);
        } elseif ($this->teamId) {
            $competitionUrls = ExternalTeamSeasonConfig::where('team_id', $this->teamId)
                ->where('season_id', $this->seasonId)
                ->pluck('competition_url')
                ->filter()
                ->unique();

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

        return view('livewire.public.standings-table', [
            'groupedStandings' => $groupedStandings,
            'seasons' => $this->showFilters ? Season::orderByDesc('name')->get() : collect(),
            'teams' => $this->showFilters ? Team::orderBy('name')->get() : collect(),
        ]);
    }
}
