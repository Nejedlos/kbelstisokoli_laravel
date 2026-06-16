<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Najdeme všechny aktivní uživatele, kteří nemají žádnou roli
        $usersWithoutRoles = \App\Models\User::where('is_active', true)
            ->whereDoesntHave('roles')
            ->get();

        foreach ($usersWithoutRoles as $user) {
            $user->assignRole('player');
        }

        // 2. Specifická oprava pro Miroslava Šosa
        // Uživatel si stěžoval, že se nemůže přihlásit (403).
        // Kromě role mu chybí i ověření e-mailu, což by byla další překážka.
        $sos = \App\Models\User::where('email', 'mira.sosik@gmail.com')->first();
        if ($sos) {
            if (! $sos->hasRole('player')) {
                $sos->assignRole('player');
            }

            if ($sos->email_verified_at === null) {
                $sos->update(['email_verified_at' => now()]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
