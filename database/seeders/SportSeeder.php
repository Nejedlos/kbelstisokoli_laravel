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
        // Ponecháme pouze týmy Sokol Kbely C a Sokol Kbely E
        $allowedSlugs = ['muzi-c', 'muzi-e'];

        // Smažeme případné jiné týmy, aby zůstaly jen požadované
        Team::whereNotIn('slug', $allowedSlugs)->delete();

        $teams = [
            [
                'name' => ['cs' => 'Sokol Kbely C', 'en' => 'Sokol Kbely C'],
                'slug' => 'muzi-c',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Náš elitní tým hrající Pražský přebor B. Jsme hrdou součástí TJ Sokol Kbely Basketball a srdcem naší komunity v Letňanech. Zakládáme si na týmové chemii a ambicích posouvat se v tabulce neustále výše.',
                    'en' => 'Our elite team competing in the Prague Championship B. We are a proud part of TJ Sokol Kbely Basketball and the heart of our community in Letňany. We pride ourselves on team chemistry and ambitions to constantly move up the table.',
                ],
            ],
            [
                'name' => ['cs' => 'Sokol Kbely E', 'en' => 'Sokol Kbely E'],
                'slug' => 'muzi-e',
                'category' => 'senior',
                'description' => [
                    'cs' => 'Zkušený tým hrající 3. třídu B v naší RumcajsAreně. Jsme součástí oddílu TJ Sokol Kbely Basketball. Ideální volba pro ty, co milují basketbal, skvělou partu a chtějí hrát s radostí i v soutěžním tempu.',
                    'en' => 'Experienced team playing the 3rd Class B in our RumcajsArena. We are part of the TJ Sokol Kbely Basketball club. Perfect choice for those who love basketball, a great group, and want to play with joy even at a competitive pace.',
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
