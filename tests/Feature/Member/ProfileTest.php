<?php

namespace Tests\Feature\Member;

use App\Models\PlayerProfile;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    /**
     * Testuje, že stránka profilu je dostupná.
     */
    public function test_profile_page_is_accessible(): void
    {
        $user = $this->createMember();

        $response = $this->actingAs($user)
            ->get(route('member.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    /**
     * Testuje úspěšnou aktualizaci profilu.
     */
    public function test_profile_can_be_updated(): void
    {
        $user = $this->createMember();
        $profile = PlayerProfile::create([
            'user_id' => $user->id,
            'jersey_number' => '10',
            'public_bio' => 'Staré bio.',
        ]);

        $newData = [
            'name' => 'Nové Jméno',
            'phone' => '+420777666555',
            'public_bio' => 'Moje nové bio.',
            'jersey_number' => '99',
        ];

        $response = $this->actingAs($user)
            ->from(route('member.profile.edit'))
            ->post(route('member.profile.update'), $newData);

        $response->assertRedirect(route('member.profile.edit'));
        $response->assertSessionHas('status', 'Váš profil byl úspěšně aktualizován.');

        $user->refresh();
        $profile->refresh();

        $this->assertEquals('Nové Jméno', $user->name);
        $this->assertEquals('+420777666555', $user->phone);
        $this->assertEquals('Moje nové bio.', $profile->public_bio);
        $this->assertEquals('99', $profile->jersey_number);
    }

    /**
     * Testuje validaci při aktualizaci profilu.
     */
    public function test_profile_update_validation(): void
    {
        $user = $this->createMember();

        $response = $this->actingAs($user)
            ->from(route('member.profile.edit'))
            ->post(route('member.profile.update'), [
                'name' => '', // Povinné
            ]);

        $response->assertRedirect(route('member.profile.edit'));
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Testuje změnu hesla.
     */
    public function test_password_can_be_changed(): void
    {
        $user = $this->createMember();

        $response = $this->actingAs($user)
            ->from(route('member.profile.edit'))
            ->post(route('member.profile.update'), [
                'name' => $user->name,
                'current_password' => 'password',
                'new_password' => 'New-password-123',
                'new_password_confirmation' => 'New-password-123',
            ]);

        $response->assertRedirect(route('member.profile.edit'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('New-password-123', $user->password));
    }

    /**
     * Profil odkazuje na společný průvodce nastavením 2FA.
     */
    public function test_profile_links_to_shared_two_factor_setup(): void
    {
        $user = $this->createMember();

        $response = $this->actingAs($user)
            ->get(route('member.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee(route('auth.two-factor-setup'), false);
        $response->assertSee(__('two-factor.optional'));
    }
}
