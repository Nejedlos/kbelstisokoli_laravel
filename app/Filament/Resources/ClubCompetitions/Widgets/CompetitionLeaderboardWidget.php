<?php

namespace App\Filament\Resources\ClubCompetitions\Widgets;

use App\Models\ClubCompetitionEntry;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompetitionLeaderboardWidget extends Widget
{
    public ?Model $ownerRecord = null;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.resources.club-competitions.widgets.leaderboard-widget';

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
            ->where("{$tableName}.club_competition_id", $this->ownerRecord->id)
            ->leftJoin($usersTable, "{$usersTable}.id", '=', "{$tableName}.player_id")
            ->select(
                "{$tableName}.player_id",
                "{$tableName}.label",
                "{$usersTable}.name as user_name",
                DB::raw("SUM({$tableName}.value) as total_value"),
                DB::raw("RANK() OVER (ORDER BY SUM({$tableName}.value) DESC) as competition_rank")
            )
            ->groupBy("{$tableName}.player_id", "{$tableName}.label", "{$usersTable}.name")
            ->orderBy('total_value', 'desc')
            ->get();

        return [
            'entries' => $entries,
        ];
    }
}
