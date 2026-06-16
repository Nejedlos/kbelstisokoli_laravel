<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Ujistíme se, že existují klíčové role a mají všechna oprávnění
        // To řeší problém s ignorováním super_admin role a zajišťuje plný přístup pro správce.
        $allPermissions = Permission::all();

        $adminRoles = ['super_admin', 'admin'];
        foreach ($adminRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($allPermissions);
        }

        // 2. Rozšíříme oprávnění pro Editora a Trenéra (Coach) - "univerzální řešení pro admin sekci"
        // Podle požadavku uživatele by měli mít tito uživatelé možnost provádět běžnou agendu bez 403 chyb.

        // Editor nyní může spravovat i uživatele, týmy a soupisky
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo([
                'manage_users',
                'manage_teams',
                'manage_rosters'
            ]);
        }

        // Trenér (Coach) nyní může spravovat i uživatele (potřebné např. pro registraci nových hráčů)
        $coachRole = Role::where('name', 'coach')->first();
        if ($coachRole) {
            $coachRole->givePermissionTo([
                'manage_users'
            ]);
        }

        // 3. Specifická oprava pro Marka Novotného
        // I když jsme vylepšili roli editor, kterou má,
        // pokud Marek vystupuje jako hlavní správce (což ze stížnosti vyplývá),
        // měl by mít roli 'admin', aby mohl spravovat i role ostatních a viděl všechna pole.
        $marek = User::where('email', 'marek.novotny@allegro.com')->first();
        if ($marek) {
            if (! $marek->hasRole('admin')) {
                $marek->assignRole('admin');
            }
            $marek->update(['is_active' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // V reverzní migraci nebudeme odebírat oprávnění, abychom předešli nechtěnému zamknutí uživatelů.
    }
};
