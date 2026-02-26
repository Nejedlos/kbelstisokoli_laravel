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
                    'cs' => 'Tým Muži C působí v kategorii mužů a v sezóně 2025/2026 hraje Pražský přebor B.',
                    'en' => 'The Men C team competes in the senior category and plays the Prague Championship B in the 2025/2026 season.',
                ],
            ],
            'muzi-e' => [
                'name' => ['cs' => 'Muži E', 'en' => 'Men E'],
                'category' => 'senior',
                'description' => [
                    'cs' => 'Tým Muži E působí v kategorii mužů a v sezóně 2025/2026 hraje 3. třídu B.',
                    'en' => 'The Men E team competes in the senior category and plays the 3rd Class B in the 2025/2026 season.',
                ],
            ],
        ];

        // 2. Definice trenérů
        $coaches = [
            'muzi-c' => [
                'first_name' => 'Petr',
                'last_name' => 'Novotný',
                'email' => 'petr.novotny@kbelstisokoli.cz',
                'phone' => '+420 123 456 789',
            ],
            'muzi-e' => [
                'first_name' => 'Marek',
                'last_name' => 'Svoboda',
                'email' => 'marek.svoboda@kbelstisokoli.cz',
                'phone' => '+420 987 654 321',
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
