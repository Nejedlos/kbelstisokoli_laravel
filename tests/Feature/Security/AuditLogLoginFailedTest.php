<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditLogLoginFailedTest extends TestCase
{
    /**
     * Ověří, že při neúspěšném přihlášení (špatné heslo) existujícího uživatele
     * je tento uživatel zapsán jako aktér v audit logu.
     */
    public function test_failed_login_logs_actor_if_user_exists(): void
    {
        $user = User::factory()->create([
            'email' => 'test-failed-login@example.com',
            'password' => bcrypt('CorrectPassword123!'),
        ]);

        $this->assertGuest();

        // Simulujeme pokus o přihlášení se špatným heslem
        Auth::attempt([
            'email' => 'test-failed-login@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $this->assertGuest();

        // Kontrola audit logu
        $log = AuditLog::where('event_key', 'auth.login_failed')
            ->where('metadata->email', 'test-failed-login@example.com')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Audit log pro neúspěšné přihlášení nebyl vytvořen.');

        // V tuto chvíli očekáváme, že test selže, protože actor_user_id bude null
        $this->assertEquals($user->id, $log->actor_user_id, 'actor_user_id v audit logu by měl odpovídat existujícímu uživateli.');
        $this->assertEquals(User::class, $log->actor_type);
    }

    /**
     * Ověří, že při neúspěšném přihlášení neexistujícího uživatele
     * zůstane aktér v audit logu null.
     */
    public function test_failed_login_logs_null_actor_for_unknown_user(): void
    {
        $email = 'nonexistent-' . uniqid() . '@example.com';

        Auth::attempt([
            'email' => $email,
            'password' => 'SomePassword123!',
        ]);

        $log = AuditLog::where('event_key', 'auth.login_failed')
            ->where('metadata->email', $email)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertNull($log->actor_user_id, 'actor_user_id by měl být null pro neexistujícího uživatele.');
    }
}
