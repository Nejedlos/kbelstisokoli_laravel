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
        // Ponecháme pouze týmy Muži C, Muži E a Klub
        $allowedSlugs = ['muzi-c', 'muzi-e', 'klub'];

        // Smažeme případné jiné týmy, aby zůstaly jen požadované
        Team::whereNotIn('slug', $allowedSlugs)->delete();

        $teams = [
            [
                'name' => ['cs' => 'Muži C', 'en' => 'Men C'],
                'slug' => 'muzi-c',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži C hraje Pražský přebor B. Jsme jádro naší basketbalové komunity v Letňanech (Třinecká 650). Zakládáme si na týmovém duchu a chceme se v sezóně 2025/2026 posunout v tabulce výše.',
                    'en' => 'The Men C team competes in the Prague Championship B. We are the core of our basketball community in Letňany (Třinecká 650). We focus on team spirit and aim to move up the table in the 2025/2026 season.',
                ],
            ],
            [
                'name' => ['cs' => 'Muži E', 'en' => 'Men E'],
                'slug' => 'muzi-e',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži E hraje 3. třídu B v naší RumcajsAreně v Letňanech. Ideální místo pro ty, co milují basketbal, dobrou partu a chtějí hrát pro radost i v soutěžním tempu.',
                    'en' => 'The Men E team plays the 3rd Class B in our RumcajsArena in Letňany. Perfect place for those who love basketball, a great community, and want to play for joy even at a competitive pace.',
                ],
            ],
            [
                'name' => ['cs' => 'Sokoli (Celý klub)', 'en' => 'Sokoli (Whole Club)'],
                'slug' => 'klub',
                'category' => 'all',
                'description' => [
                    'cs' => 'Zápasy a akce, které se týkají všech členů klubu nebo obou našich týmů.',
                    'en' => 'Matches and events concerning all club members or both our teams.',
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
