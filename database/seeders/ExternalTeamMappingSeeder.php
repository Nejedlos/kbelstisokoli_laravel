<?php

namespace Database\Seeders;

use App\Models\ExternalTeamMapping;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ExternalTeamMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappings = [
            'muzi-c' => '7761',
            'muzi-e' => '7738',
        ];

        foreach ($mappings as $slug => $externalId) {
            $team = Team::where('slug', $slug)->first();

            if ($team) {
                ExternalTeamMapping::updateOrCreate(
                    [
                        'source_key' => 'czbasketball',
                        'team_id' => $team->id,
                    ],
                    [
                        'external_team_id' => $externalId,
                        'base_team_url' => "https://cz.basketball/tym/{$externalId}",
                    ]
                );
            }
        }
    }
}
