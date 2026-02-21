<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            'manage_attendance',
            'view_member_section',
            'use_raw_html',
            'manage_advanced_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Vytvoření rolí a přiřazení oprávnění

        // Admin - má všechna oprávnění
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Editor - správa obsahu a základní admin přístup (bez přístupu do členské sekce)
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions([
            'access_admin',
            'manage_content',
        ]);

        // Coach - správa týmů a docházky
        $coachRole = Role::firstOrCreate(['name' => 'coach']);
        $coachRole->syncPermissions([
            'access_admin',
            'manage_teams',
            'manage_attendance',
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
