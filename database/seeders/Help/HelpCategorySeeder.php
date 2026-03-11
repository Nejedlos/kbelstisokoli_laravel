<?php

namespace Database\Seeders\Help;

use App\Models\HelpCategory;
use App\Traits\SeedsHelpContent;
use Illuminate\Database\Seeder;

class HelpCategorySeeder extends Seeder
{
    use SeedsHelpContent;

    /**
     * Seed categories.
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            [
                'data' => [
                    'slug' => 'uvod',
                    'icon' => 'fa-light fa-house-sparkles',
                    'color' => 'sky',
                    'sort_order' => 10,
                    'is_active' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Úvod a Onboarding',
                        'en' => 'Introduction & Onboarding',
                    ],
                    'description' => [
                        'cs' => 'Základní informace pro nové uživatele, jak se v systému zorientovat a nastavit si účet.',
                        'en' => 'Basic information for new users on how to navigate the system and set up an account.',
                    ],
                ],
            ],
            [
                'data' => [
                    'slug' => 'sport',
                    'icon' => 'fa-light fa-basketball-hoop',
                    'color' => 'orange',
                    'sort_order' => 20,
                    'is_active' => true,
                    'is_featured' => true,
                    'audience_roles' => ['coach', 'admin', 'editor'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Sportovní agenda',
                        'en' => 'Sports Agenda',
                    ],
                    'description' => [
                        'cs' => 'Srdce systému – správa týmů, hráčů, zápasů a tréninkového procesu.',
                        'en' => 'The heart of the system – management of teams, players, matches and the training process.',
                    ],
                ],
            ],
            [
                'data' => [
                    'slug' => 'finance',
                    'icon' => 'fa-light fa-wallet',
                    'color' => 'emerald',
                    'sort_order' => 30,
                    'is_active' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'editor', 'coach'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Ekonomika a finance',
                        'en' => 'Economy & Finance',
                    ],
                    'description' => [
                        'cs' => 'Správa členských příspěvků, plateb, tarifů a finanční integrity klubu.',
                        'en' => 'Management of membership fees, payments, tariffs and financial integrity of the club.',
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $this->upsertHelpItem(HelpCategory::class, $category['data'], $category['translations']);
        }
    }
}
