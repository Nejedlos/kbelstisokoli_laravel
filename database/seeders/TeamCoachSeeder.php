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
                'name' => ['cs' => 'Sokol Kbely C', 'en' => 'Sokol Kbely C'],
                'category' => 'senior',
                'description' => [
                    'cs' => 'Náš elitní tým hrající Pražský přebor B. Jsme hrdou součástí TJ Sokol Kbely Basketball a srdcem naší komunity v Letňanech. Zakládáme si na týmové chemii a ambicích posouvat se v tabulce neustále výše.',
                    'en' => 'Our elite team competing in the Prague Championship B. We are a proud part of TJ Sokol Kbely Basketball and the heart of our community in Letňany. We pride ourselves on team chemistry and ambitions to constantly move up the table.',
                ],
            ],
            'muzi-e' => [
                'name' => ['cs' => 'Sokol Kbely E', 'en' => 'Sokol Kbely E'],
                'category' => 'senior',
                'description' => [
                    'cs' => 'Zkušený tým hrající 3. třídu B v naší RumcajsAreně. Jsme součástí oddílu TJ Sokol Kbely Basketball. Ideální volba pro ty, co milují basketbal, skvělou partu a chtějí hrát s radostí i v soutěžním tempu.',
                    'en' => 'Experienced team playing the 3rd Class B in our RumcajsArena. We are part of the TJ Sokol Kbely Basketball club. Perfect choice for those who love basketball, a great group, and want to play with joy even at a competitive pace.',
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
                if (! $user->hasRole('coach')) {
                    $user->assignRole('coach');
                }

                // Propojení trenéra s týmem v pivot tabulce
                // Použijeme syncWithoutDetaching, aby se nepřidávali duplicitně
                $team->coaches()->syncWithoutDetaching([
                    $user->id => ['email' => $user->email],
                ]);
            }
        }
    }
}
