<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSecuritySeeder extends Seeder
{
    /**
     * Tento seeder slouží k synchronizaci bezpečnostních nastavení (včetně 2FA)
     * uživatele Michal Nejedlý z lokálního prostředí na produkci.
     *
     * POZOR: Obsahuje zašifrované citlivé údaje.
     */
    public function run(): void
    {
        $user = User::where('email', 'nejedlymi@gmail.com')->first();

        if ($user) {
            $user->update([
                'password' => '$2y$12$lxnhOk0dcKxChTYwLvrtTu.qa.YBelzMxqu9zTs.AffMVCw7RPIga',
                'two_factor_secret' => 'eyJpdiI6Imc5dlZGQ1lZZ2ZhMGx4bFZOYlBQQ2c9PSIsInZhbHVlIjoiaVRoV1doSEgyMi9iWmpGS052bkVTdW1MRjBuQXlTcTJFU29LTWJ3MjI3Zz0iLCJtYWMiOiI4OTBjODk5MGEyNjMyYTQxMjI4ZjA5YTg4ZDNlMGY3MzA1MGI0NWNkYmViNjMxNWE4ZDc2MDhjYzllZDJkNzU2IiwidGFnIjoiIn0=',
                'two_factor_recovery_codes' => 'eyJpdiI6IkJWMTFleUpTNTdiWlFub0E3VDVyNVE9PSIsInZhbHVlIjoiZW9IYzg3YU9WSEJ1Z2ZkcFZ4cmV4a2dhK1J6Vk5aM1R1L0R2THVreEVHd2hPN0gzaldVckIxSDBNcm41VUNuSWs2TWowWUxJbnhHU0JCZlVFVElmekhjYWc3SzlmeVFlOW5pUHpTQ1REWXdqTjFkdlYrVG9kNTJ6UGZ1Y3RDN0JLKzB5SVpKVDU5QnQxc2U0Y1pwRlZzbHRkSFRrKzBSRkhodDV1bXFIUU5vU1lmcDYzV29wRkRuL2IyeWdnTm1rM3hYTmxvTEZld01RSnVkVVhTRG9XcTJqYy9SenpNaktQSGpzSWhEQk1vR2dOTmZsOTNoT3BWSkNBcEM0bjd0TlNxNFFmRHo3SDFwYnRzTGIzVHVlUXc9PSIsIm1hYyI6ImI4ZWU1MDc1YWIwMzU5NDZiMmE0MGFhZjEwNjg5YTVmMjZiOWRlYTUzOGI5MGUzMjQ1YzMzODgyODE0NjA5NmIiLCJ0YWciOiIifQ==',
                'two_factor_confirmed_at' => '2026-02-23 14:57:52',
                'email_verified_at' => '2026-02-23 15:12:19',
            ]);

            $this->command->info('Bezpečnostní nastavení pro nejedlymi@gmail.com byla aktualizována.');
        } else {
            $this->command->error('Uživatel nejedlymi@gmail.com nebyl nalezen. Spusťte nejdříve UserSeeder.');
        }
    }
}
