<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GlobalSeeder extends Seeder
{
    /**
     * Seznam seederů, které tvoří globální stav aplikace.
     * Sem přidávejte další seedery v pořadí, v jakém mají být spuštěny.
     */
    public const SEEDERS = [
        RoleSeeder::class,
        UserSeeder::class,
        CronTaskSeeder::class,
        SportSeeder::class,
        PostSeeder::class,
        CmsContentSeeder::class,
    ];

    /**
     * Seznam tabulek, které se mají promazat při --fresh režimu.
     */
    public const TABLES_TO_WIPE = [
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'users',
        'seasons',
        'teams',
        'post_categories',
        'posts',
        'cron_tasks',
        'settings',
        'pages',
        'page_blocks',
        'menus',
        'menu_items',
        'seo_metadatas',
    ];

    /**
     * Spustí globální seedování.
     */
    public function run(): void
    {
        $this->call(self::SEEDERS);
    }
}
