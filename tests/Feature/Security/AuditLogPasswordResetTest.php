<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\User;
use App\Filament\Pages\Auth\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogPasswordResetTest extends TestCase
{
    /**
     * Ověří, že při resetu hesla je v audit logu správně uveden aktér (uživatel),
     * přestože v daný moment není přihlášen.
     */
    public function test_password_reset_logs_actor_user_id(): void
    {
        // 1. Příprava uživatele a tokenu pro reset hesla
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = Password::broker()->createToken($user);

        // Ujistíme se, že nikdo není přihlášen
        $this->assertGuest();

        // 2. Simulace resetu hesla přes Filament stránku
        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => $user->email,
        ])
            ->set('password', 'NewPassword123!')
            ->set('passwordConfirmation', 'NewPassword123!')
            ->call('resetPassword')
            ->assertHasNoErrors();

        // 3. Ověření, že heslo bylo změněno
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));

        // 4. Kontrola audit logu
        $log = AuditLog::where('event_key', 'auth.password_reset')->latest()->first();

        $this->assertNotNull($log, 'Audit log pro reset hesla nebyl vytvořen.');
        $this->assertEquals($user->id, $log->actor_user_id, 'actor_user_id v audit logu neodpovídá uživateli.');
        $this->assertEquals(User::class, $log->actor_type);
        $this->assertFalse($log->is_system_event, 'Reset hesla uživatelem by neměl být označen jako systémová událost.');
    }
}
