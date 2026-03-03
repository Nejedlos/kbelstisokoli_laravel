<?php

namespace App\Livewire\Member;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\PlayerStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyStatistics extends Component
{
    public $seasonId;
    public $teamId;

    public $summary = [];
    public $perGameSeries = [];

    public function mount()
    {
        $this->seasonId = Season::where('is_active', true)->first()?->id ?? Season::latest()->first()?->id;

        // Zkusíme najít první tým uživatele v dané sezóně
        $user = Auth::user();
        $this->teamId = $user->playerProfile?->teams()
            ->wherePivot('is_on_roster', true)
            ->first()?->id;

        $this->loadStats();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['seasonId', 'teamId'])) {
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        if (!$this->seasonId) return;

        $service = app(PlayerStatsService::class);
        $userId = Auth::id();

        $this->summary = $service->getSeasonSummary($userId, $this->seasonId, $this->teamId);
        $this->perGameSeries = $service->getPerGameSeries($userId, $this->seasonId, $this->teamId)->toArray();
    }

    public function render()
    {
        return view('livewire.member.my-statistics', [
            'seasons' => Season::orderBy('name', 'desc')->get(),
            'teams' => Auth::user()->playerProfile?->teams ?? collect(),
        ]);
    }
}
