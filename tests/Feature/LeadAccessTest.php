<?php

namespace Tests\Feature;

use App\Models\Lead;
use Tests\TestCase;

class LeadAccessTest extends TestCase
{
    /**
     * 1) Guest odešle kontaktní formulář:
     * - validní data -> záznam vznikne (lead/inquiry)
     * - success response / redirect správně
     * - required consent enforced
     */
    public function test_contact_form_submission(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+420 123 456 789',
            'subject' => 'Dotaz na členství',
            'message' => 'Dobrý den, chci se zeptat...',
            'consent' => '1',
        ];

        $response = $this->from(route('public.contact.index'))
            ->post(route('public.contact.store'), $data);

        $response->assertRedirect(route('public.contact.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'type' => 'contact',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * 2) Guest odešle náborový formulář:
     * - validní data -> záznam vznikne se správným typem/payloadem
     * - success response správně
     */
    public function test_recruitment_form_submission(): void
    {
        $data = [
            'name' => 'Malý Basketbalista',
            'email' => 'rodic@example.com',
            'phone' => '+420 987 654 321',
            'birth_year' => '2015',
            'message' => 'Chceme se přidat k týmu.',
            'consent' => '1',
        ];

        $response = $this->post(route('public.recruitment.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'type' => 'recruitment',
            'name' => 'Malý Basketbalista',
        ]);

        $lead = Lead::where('name', 'Malý Basketbalista')->first();
        $this->assertEquals('2015', $lead->payload['birth_year']);
    }

    /**
     * 3) Validace:
     * - nevalidní email / chybějící required pole / chybějící consent -> validační chyba
     * - formulář neuloží záznam
     */
    public function test_form_validation_errors(): void
    {
        // Chybějící povinná pole a neplatný email
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'consent' => '0',
        ];

        $response = $this->post(route('public.contact.store'), $data);

        $response->assertSessionHasErrors(['name', 'email', 'message', 'consent']);
        $this->assertEquals(0, Lead::count());
    }

    /**
     * 4) Admin access k leadům:
     * - neoprávněný uživatel nemá přístup k lead admin části
     * - oprávněný admin/trenér přístup má (dle permissions)
     */
    public function test_admin_access_to_leads(): void
    {
        // Neoprávněný (member/player)
        $member = $this->createMember();
        $this->actingAs($member);

        // V tomto projektu se k leadům dostává přes LeadResource v adminu.
        // Cesta by měla být /admin/leads
        $this->get('/admin/leads')->assertStatus(403);

        // Oprávněný (admin)
        $admin = $this->createAdmin();
        $this->with2FA($admin);
        session(['auth.2fa_confirmed_at' => now()->timestamp]);
        $this->actingAs($admin);

        $this->get('/admin/leads')->assertStatus(200);
    }
}
