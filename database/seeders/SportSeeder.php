<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSeasons();
        $this->seedTeams();
    }

    protected function seedSeasons(): void
    {
        $seasons = [
            ['name' => '2023/2024', 'is_active' => false],
            ['name' => '2024/2025', 'is_active' => false],
            ['name' => '2025/2026', 'is_active' => true],
        ];

        foreach ($seasons as $season) {
            Season::updateOrCreate(
                ['name' => $season['name']],
                ['is_active' => $season['is_active']]
            );
        }
    }

    protected function seedTeams(): void
    {
        $teams = [
            [
                'name' => ['cs' => 'Muži C', 'en' => 'Men C'],
                'slug' => 'muzi-c',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži C působí v kategorii mužů a v sezóně 2025/2026 hraje Pražský přebor B.',
                    'en' => 'The Men C team competes in the senior category and plays the Prague Championship B in the 2025/2026 season.',
                ],
            ],
            [
                'name' => ['cs' => 'Muži E', 'en' => 'Men E'],
                'slug' => 'muzi-e',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži E působí v kategorii mužů a v sezóně 2025/26 hraje Pražský přebor 3. třída B.',
                    'en' => 'The Men E team competes in the senior category and plays the Prague Championship 3rd Class B in the 2025/26 season.',
                ],
            ],
        ];

        foreach ($teams as $teamData) {
            Team::updateOrCreate(
                ['slug' => $teamData['slug']],
                $teamData
            );
        }
    }
}
