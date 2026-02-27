<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeamCoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definice týmů
        $teams = [
            'muzi-c' => [
                'name' => ['cs' => 'Muži C', 'en' => 'Men C'],
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži C hraje Pražský přebor B. Jsme jádro naší basketbalové komunity v Letňanech (Třinecká 650). Zakládáme si na týmovém duchu a chceme se v sezóně 2025/2026 posunout v tabulce výše.',
                    'en' => 'The Men C team competes in the Prague Championship B. We are the core of our basketball community in Letňany (Třinecká 650). We focus on team spirit and aim to move up the table in the 2025/2026 season.',
                ],
            ],
            'muzi-e' => [
                'name' => ['cs' => 'Muži E', 'en' => 'Men E'],
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži E hraje 3. třídu B v naší RumcajsAreně v Letňanech. Ideální místo pro ty, co milují basketbal, dobrou partu a chtějí hrát pro radost i v soutěžním tempu.',
                    'en' => 'The Men E team plays the 3rd Class B in our RumcajsArena in Letňany. Perfect place for those who love basketball, a great community, and want to play for joy even at a competitive pace.',
                ],
            ],
        ];

        // 2. Definice trenérů
        $coaches = [
            'muzi-c' => [
                'first_name' => 'Tomáš',
                'last_name' => 'Spanilý',
                'email' => 'spanily@pro-nemo.cz',
                'phone' => '+420602285447',
            ],
            'muzi-e' => [
                'first_name' => 'Lubor',
                'last_name' => 'Viktorin',
                'email' => 'lubor.viktorin@avikotime.cz',
                'phone' => '+420604122454',
            ],
        ];

        foreach ($teams as $slug => $data) {
            // Vytvoření/aktualizace týmu
            $team = Team::updateOrCreate(['slug' => $slug], $data);

            if (isset($coaches[$slug])) {
                $coachData = $coaches[$slug];

                // Vytvoření/aktualizace uživatele
                $user = User::updateOrCreate(
                    ['email' => $coachData['email']],
                    [
                        'first_name' => $coachData['first_name'],
                        'last_name' => $coachData['last_name'],
                        'phone' => $coachData['phone'],
                        'password' => Hash::make('password'),
                        'is_active' => true,
                    ]
                );

                // Přiřazení role trenéra
                if (!$user->hasRole('coach')) {
                    $user->assignRole('coach');
                }

                // Propojení trenéra s týmem v pivot tabulce
                // Použijeme syncWithoutDetaching, aby se nepřidávali duplicitně
                $team->coaches()->syncWithoutDetaching([
                    $user->id => ['email' => $user->email]
                ]);
            }
        }
    }
}
