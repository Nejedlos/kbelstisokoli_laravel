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
        SportSeeder::class,
        UserSeeder::class,
        LegacyUserMigrationSeeder::class,
        UserSecuritySeeder::class,
        TeamCoachSeeder::class,
        SeasonMigrationSeeder::class,
        EventMigrationSeeder::class,
        AttendanceMigrationSeeder::class,
        FinanceMigrationSeeder::class,
        CronTaskSeeder::class,
        PostSeeder::class,
        CmsContentSeeder::class,
        GdprPageSeeder::class,
        SeoOptimizationSeeder::class,
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
        'player_profiles',
        'player_profile_team',
        'seasons',
        'financial_tariffs',
        'user_season_configs',
        'teams',
        'matches',
        'trainings',
        'attendances',
        'finance_charges',
        'finance_payments',
        'charge_payment_allocations',
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
