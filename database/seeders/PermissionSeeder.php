<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Spustí seeder s definicí oprávnění a jejich překladů.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'access_admin' => [
                'cs' => 'Přístup do administrace',
                'en' => 'Admin access',
            ],
            'manage_users' => [
                'cs' => 'Správa uživatelů a rolí',
                'en' => 'User and role management',
            ],
            'manage_content' => [
                'cs' => 'Správa obsahu (články, stránky)',
                'en' => 'Content management (articles, pages)',
            ],
            'manage_teams' => [
                'cs' => 'Správa týmů',
                'en' => 'Team management',
            ],
            'manage_rosters' => [
                'cs' => 'Správa soupisek (přidávání hráčů)',
                'en' => 'Roster management (adding players)',
            ],
            'manage_attendance' => [
                'cs' => 'Správa docházky a tréninků',
                'en' => 'Attendance and training management',
            ],
            'view_member_section' => [
                'cs' => 'Přístup do členské sekce',
                'en' => 'Member section access',
            ],
            'use_raw_html' => [
                'cs' => 'Používání čistého HTML',
                'en' => 'Raw HTML usage',
            ],
            'manage_advanced_settings' => [
                'cs' => 'Pokročilé nastavení systému',
                'en' => 'Advanced system settings',
            ],
            'manage_stats' => [
                'cs' => 'Správa statistik',
                'en' => 'Stats management',
            ],
            'manage_competitions' => [
                'cs' => 'Správa soutěží',
                'en' => 'Competition management',
            ],
            'manage_redirects' => [
                'cs' => 'Správa přesměrování',
                'en' => 'Redirect management',
            ],
            'manage_ai_settings' => [
                'cs' => 'Nastavení AI modulů',
                'en' => 'AI modules settings',
            ],
            'manage_economy' => [
                'cs' => 'Správa ekonomiky (platby, tarify)',
                'en' => 'Economy management (payments, tariffs)',
            ],
            'manage_matches' => [
                'cs' => 'Správa zápasů',
                'en' => 'Match management',
            ],
            'manage_events' => [
                'cs' => 'Správa klubových akcí',
                'en' => 'Club event management',
            ],
            'view_member_media' => [
                'cs' => 'Zobrazení členských médií',
                'en' => 'View member media',
            ],
            'view_private_media' => [
                'cs' => 'Zobrazení soukromých médií',
                'en' => 'View private media',
            ],
            'impersonate_users' => [
                'cs' => 'Přihlášení za uživatele (impersonace)',
                'en' => 'Impersonate users',
            ],
        ];

        foreach ($permissions as $name => $displayName) {
            $permission = Permission::firstOrCreate(['name' => $name]);
            $permission->setTranslations('display_name', $displayName);
            $permission->save();
        }

        $this->command->info('Seeder oprávnění (Permissions) byl úspěšně spuštěn.');
    }
}
