<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Vytvoření oprávnění
        $permissions = [
            'access_admin',
            'manage_users',
            'manage_content',
            'manage_teams',
            'manage_rosters',
            'manage_attendance',
            'view_member_section',
            'use_raw_html',
            'manage_advanced_settings',
            'manage_stats',
            'manage_competitions',
            'manage_redirects',
            'manage_ai_settings',
            'manage_economy',
            'manage_matches',
            'manage_events',
            'impersonate_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Vytvoření rolí a přiřazení oprávnění

        // Admin - má všechna oprávnění
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Coach - správa týmů, docházky, zápasů, akcí, ekonomiky atd. (vše kromě admin nástrojů)
        $coachRole = Role::firstOrCreate(['name' => 'coach']);
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
            'view_member_section',
        ]);

        // Editor - jako coach, ale nemůže měnit soupisky (manage_rosters) a týmy (manage_teams)
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions([
            'access_admin',
            'manage_content',
            'manage_attendance',
            'manage_stats',
            'manage_competitions',
            'manage_economy',
            'manage_matches',
            'manage_events',
            'view_member_section',
        ]);

        // Player - členská sekce
        $playerRole = Role::firstOrCreate(['name' => 'player']);
        $playerRole->syncPermissions([
            'view_member_section',
        ]);

        // Parent - členská sekce (omezená)
        $parentRole = Role::firstOrCreate(['name' => 'parent']);
        $parentRole->syncPermissions([
            'view_member_section',
        ]);
    }
}
