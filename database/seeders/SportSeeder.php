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
                'name' => ['cs' => 'Muži A', 'en' => 'Men A'],
                'slug' => 'muzi-a',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži A je vlajkovou lodí klubu a v sezóně 2025/2026 hraje 2. ligu (skupina A).',
                    'en' => 'The Men A team is the club\'s flagship and plays the 2nd League (Group A) in the 2025/2026 season.',
                ],
            ],
            [
                'name' => ['cs' => 'Muži B', 'en' => 'Men B'],
                'slug' => 'muzi-b',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži B působí v kategorii mužů a v sezóně 2025/2026 hraje Pražský přebor.',
                    'en' => 'The Men B team competes in the senior category and plays the Prague Championship in the 2025/2026 season.',
                ],
            ],
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
                'name' => ['cs' => 'Muži D', 'en' => 'Men D'],
                'slug' => 'muzi-d',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži D působí v kategorii mužů a v sezóně 2025/2026 hraje 1. třídu.',
                    'en' => 'The Men D team competes in the senior category and plays the 1st Class in the 2025/2026 season.',
                ],
            ],
            [
                'name' => ['cs' => 'Muži E', 'en' => 'Men E'],
                'slug' => 'muzi-e',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži E působí v kategorii mužů a v sezóně 2025/2026 hraje 3. třídu B.',
                    'en' => 'The Men E team competes in the senior category and plays the 3rd Class B in the 2025/2026 season.',
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
