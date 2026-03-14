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
                    'slug' => 'clenska-sekce',
                    'icon' => 'fa-light fa-user-vneck',
                    'color' => 'sky',
                    'sort_order' => 5,
                    'is_active' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Členská sekce (Můj profil)',
                        'en' => 'Member Section (My Profile)',
                    ],
                    'description' => [
                        'cs' => 'Vše o vašem hráčském profilu, docházce, platbách a nastavení účtu.',
                        'en' => 'All about your player profile, attendance, payments and account settings.',
                    ],
                ],
            ],
            [
                'data' => [
                    'slug' => 'uvod',
                    'icon' => 'fa-light fa-rocket-launch',
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
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'editor'],
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
                    'audience_roles' => ['player', 'parent', 'admin', 'editor', 'coach'],
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
            [
                'data' => [
                    'slug' => 'lide',
                    'icon' => 'fa-light fa-users',
                    'color' => 'teal',
                    'sort_order' => 40,
                    'is_active' => true,
                    'is_featured' => false,
                    'audience_roles' => ['player', 'parent', 'admin', 'editor', 'coach', 'super_admin'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Členové a komunikace',
                        'en' => 'Members & Communication',
                    ],
                    'description' => [
                        'cs' => 'Evidence uživatelů, správa rolí, GDPR a vnitřní informační kanály.',
                        'en' => 'User evidence, role management, GDPR and internal communication channels.',
                    ],
                ],
            ],
            [
                'data' => [
                    'slug' => 'obsah',
                    'icon' => 'fa-light fa-newspaper',
                    'color' => 'amber',
                    'sort_order' => 50,
                    'is_active' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'editor'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Obsah a web',
                        'en' => 'Content & Web',
                    ],
                    'description' => [
                        'cs' => 'Editace veřejného webu, správa článků, fotogalerií a statických informací.',
                        'en' => 'Editing the public website, managing articles, photo galleries and static information.',
                    ],
                ],
            ],
            [
                'data' => [
                    'slug' => 'system',
                    'icon' => 'fa-light fa-gear',
                    'color' => 'slate',
                    'sort_order' => 60,
                    'is_active' => true,
                    'is_featured' => false,
                    'audience_roles' => ['super_admin'],
                ],
                'translations' => [
                    'name' => [
                        'cs' => 'Systém a nastavení',
                        'en' => 'System & Settings',
                    ],
                    'description' => [
                        'cs' => 'Globální konfigurace, technická údržba, logy a integrace.',
                        'en' => 'Global configuration, technical maintenance, logs and integrations.',
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $this->upsertHelpItem(HelpCategory::class, $category['data'], $category['translations']);
        }
    }
}
