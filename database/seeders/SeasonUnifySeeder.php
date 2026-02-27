<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeasonUnifySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Season Unification...');

        // 1. Remove empty seasons
        Season::where('name', '')->orWhereNull('name')->delete();

        // 2. Identify and fix format
        $seasons = Season::all();

        foreach ($seasons as $season) {
            $oldName = $season->name;
            $newName = str_replace('-', '/', $oldName);

            if ($oldName !== $newName) {
                // Check if the new name already exists
                $existing = Season::where('name', $newName)->where('id', '!=', $season->id)->first();

                if ($existing) {
                    $this->command->info("Merging season {$oldName} (ID: {$season->id}) into {$newName} (ID: {$existing->id})");
                    $this->mergeSeasons($season->id, $existing->id);
                    $season->delete();
                } else {
                    $this->command->info("Renaming season {$oldName} (ID: {$season->id}) to {$newName}");
                    $season->update(['name' => $newName]);
                }
            }
        }

        // 3. Ensure latest season is active
        $latest = Season::orderBy('name', 'desc')->first();
        if ($latest) {
            Season::where('id', '!=', $latest->id)->update(['is_active' => false]);
            $latest->update(['is_active' => true]);
            $this->command->info("Season {$latest->name} set as active.");
        }

        $this->command->info('Season Unification completed.');
    }

    protected function mergeSeasons(int $oldId, int $newId): void
    {
        $tables = [
            'basketball_matches' => 'season_id',
            'club_competitions' => 'season_id',
            'statistic_rows' => 'season_id',
            'user_season_configs' => 'season_id',
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->where($column, $oldId)->update([$column => $newId]);
                if ($count > 0) {
                    $this->command->info("Updated {$count} rows in {$table}.");
                }
            }
        }
    }
}
