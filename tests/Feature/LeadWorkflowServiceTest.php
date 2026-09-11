<?php

namespace Tests\Feature;

use App\Mail\LeadNotificationMail;
use App\Models\LeadRoutingSetting;
use App\Models\User;
use App\Services\LeadWorkflowService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadWorkflowServiceTest extends TestCase
{
    public function test_it_offers_active_administrators_and_coaches_as_responsible_people(): void
    {
        $admin = $this->createAdmin(['name' => 'Admin']);
        $coach = User::factory()->create(['name' => 'Coach', 'is_active' => true]);
        $coach->assignRole('coach');
        $inactiveCoach = User::factory()->create(['name' => 'Inactive coach', 'is_active' => false]);
        $inactiveCoach->assignRole('coach');
        $member = $this->createMember(['name' => 'Member']);

        $eligibleIds = app(LeadWorkflowService::class)->eligibleUsers()->pluck('id')->all();

        $this->assertContains($admin->id, $eligibleIds);
        $this->assertContains($coach->id, $eligibleIds);
        $this->assertNotContains($inactiveCoach->id, $eligibleIds);
        $this->assertNotContains($member->id, $eligibleIds);
    }

    public function test_it_assigns_a_new_lead_to_the_configured_responsible_person_and_sends_immediately(): void
    {
        Mail::fake();
        $responsible = $this->createAdmin(['email' => 'responsible@example.test']);
        LeadRoutingSetting::create(['default_responsible_user_id' => $responsible->id]);

        $lead = app(LeadWorkflowService::class)->create([
            'type' => 'recruitment',
            'name' => 'Jan Novák',
            'email' => 'jan@example.test',
            'message' => 'Mám zájem hrát basketbal.',
            'payload' => ['team_name' => 'Muži C'],
        ]);

        $this->assertSame($responsible->id, $lead->responsible_user_id);
        $this->assertSame('new', $lead->status);
        Mail::assertSent(LeadNotificationMail::class, fn (LeadNotificationMail $mail) => $mail->hasTo('responsible@example.test') && $mail->lead->is($lead));
        Mail::assertNotQueued(LeadNotificationMail::class);
    }

    public function test_it_uses_technical_contact_when_no_responsible_person_is_configured(): void
    {
        Mail::fake();
        Config::set('leads.technical_contact_email', 'technical@example.test');

        app(LeadWorkflowService::class)->create([
            'type' => 'contact',
            'name' => 'Petra Nová',
            'email' => 'petra@example.test',
            'message' => 'Prosím o kontakt.',
        ]);

        Mail::assertSent(LeadNotificationMail::class, fn (LeadNotificationMail $mail) => $mail->hasTo('technical@example.test'));
        Mail::assertNotQueued(LeadNotificationMail::class);
    }

    public function test_it_notifies_the_new_responsible_person_after_manual_assignment(): void
    {
        Mail::fake();
        $responsible = $this->createAdmin(['email' => 'responsible@example.test']);
        $lead = app(LeadWorkflowService::class)->create([
            'type' => 'contact', 'name' => 'Petr', 'email' => 'petr@example.test', 'message' => 'Dobrý den.',
        ]);

        app(LeadWorkflowService::class)->assign($lead, $responsible->id);

        $this->assertSame($responsible->id, $lead->fresh()->responsible_user_id);
        Mail::assertSent(LeadNotificationMail::class, fn (LeadNotificationMail $mail) => $mail->hasTo('responsible@example.test') && $mail->assignmentNotice);
    }
}
