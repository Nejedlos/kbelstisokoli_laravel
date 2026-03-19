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

        $tableName = (new ClubCompetitionEntry)->getTable();
        $usersTable = (new \App\Models\User)->getTable();

        // Debug log to terminal if possible, but let's just make it work
        $entries = ClubCompetitionEntry::query()
            ->where("club_competition_id", $this->ownerRecord->id)
            ->leftJoin($usersTable, "{$usersTable}.id", '=', "player_id")
            ->select(
                "player_id",
                "label",
                "{$usersTable}.name as user_name",
                DB::raw("SUM(value) as total_value"),
                DB::raw("RANK() OVER (ORDER BY SUM(value) DESC) as competition_rank")
            )
            ->groupBy("player_id", "label", "{$usersTable}.name")
            ->orderBy('total_value', 'desc')
            ->get();

        return [
            'entries' => $entries,
        ];
    }
}
