<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Vytvoření rolí a přiřazení oprávnění

        // Super Admin - má absolutně vše
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->setTranslations('display_name', ['cs' => 'Super administrátor', 'en' => 'Super administrator']);
        $superAdminRole->save();
        $superAdminRole->syncPermissions(Permission::all());

        // Admin - má všechna oprávnění
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->setTranslations('display_name', ['cs' => 'Administrátor', 'en' => 'Administrator']);
        $adminRole->save();
        $adminRole->syncPermissions(Permission::all());

        // Coach - správa týmů, docházky, zápasů, akcí, ekonomiky atd. (vše kromě admin nástrojů)
        $coachRole = Role::firstOrCreate(['name' => 'coach']);
        $coachRole->setTranslations('display_name', ['cs' => 'Trenér', 'en' => 'Coach']);
        $coachRole->save();
        $coachRole->syncPermissions([
            'access_admin',
            'manage_content',
            'manage_teams',
            'manage_rosters',
            'manage_attendance',
            'manage_stats',
            'manage_competitions',
            'manage_economy',
            'manage_matches',
            'manage_events',
            'view_member_media',
            'view_member_section',
        ]);

        // Editor - jako coach, ale nemůže měnit soupisky (manage_rosters) a týmy (manage_teams)
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->setTranslations('display_name', ['cs' => 'Editor', 'en' => 'Editor']);
        $editorRole->save();
        $editorRole->syncPermissions([
            'access_admin',
            'manage_content',
            'manage_attendance',
            'manage_stats',
            'manage_competitions',
            'manage_economy',
            'manage_matches',
            'manage_events',
            'view_member_media',
            'view_member_section',
        ]);

        // Player - členská sekce
        $playerRole = Role::firstOrCreate(['name' => 'player']);
        $playerRole->setTranslations('display_name', ['cs' => 'Hráč', 'en' => 'Player']);
        $playerRole->save();
        $playerRole->syncPermissions([
            'view_member_section',
            'view_member_media',
        ]);

        // Parent - členská sekce (omezená)
        $parentRole = Role::firstOrCreate(['name' => 'parent']);
        $parentRole->setTranslations('display_name', ['cs' => 'Rodič', 'en' => 'Parent']);
        $parentRole->save();
        $parentRole->syncPermissions([
            'view_member_section',
            'view_member_media',
        ]);
    }
}
