<?php

namespace App\Filament\Resources\ClubCompetitions\Widgets;

use App\Models\ClubCompetitionEntry;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class CompetitionLeaderboardWidget extends Widget
{
    public ?Model $ownerRecord = null;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.resources.club-competitions.widgets.leaderboard-widget';

    #[On('refreshLeaderboard')]
    public function refresh(): void
    {
        // Žádná explicitní akce není potřeba, Livewire se postará o překreslení díky volání getViewData()
    }

    protected function getViewData(): array
    {
        if (! $this->ownerRecord) {
            return [
                'entries' => collect(),
            ];
        }

        $entries = ClubCompetitionEntry::query()
            ->where("club_competition_id", $this->ownerRecord->id)
            ->leftJoin('users', "users.id", '=', "player_id")
            ->select(
                "player_id",
                "label",
                "users.name as user_name",
                DB::raw("SUM(value) as total_value")
            )
            ->groupBy("player_id", "label", "users.name")
            ->orderBy('total_value', 'desc')
            ->get();

        // Výpočet ranku v PHP (MySQL 5.5 nepodporuje RANK() OVER)
        $rank = 0;
        $prevValue = null;
        $count = 0;

        $entries = $entries->map(function ($entry) use (&$rank, &$prevValue, &$count) {
            $count++;
            if ($prevValue === null || $entry->total_value < $prevValue) {
                $rank = $count;
            }
            $entry->competition_rank = $rank;
            $prevValue = $entry->total_value;

            return $entry;
        });

        return [
            'entries' => $entries,
        ];
    }
}
