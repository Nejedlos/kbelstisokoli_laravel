<?php

namespace Tests\Feature;

use App\Livewire\RecruitmentForm;
use App\Mail\LeadNotificationMail;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use App\Services\RecaptchaResult;
use App\Services\RecaptchaV3;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class RecruitmentFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submits_saves_lead_and_sends_emails()
    {
        Mail::fake();

        // Mock Recaptcha
        $recaptchaMock = $this->mock(RecaptchaV3::class);
        $recaptchaMock->shouldReceive('verify')->andReturn(new RecaptchaResult(passed: true));

        // Setup team and coach
        $team = Team::create([
            'slug' => 'muzi-e',
            'name' => ['cs' => 'Muži E'],
            'category' => 'senior',
        ]);

        $coach = User::factory()->create(['email' => 'coach@example.com']);
        $team->coaches()->attach($coach, ['email' => 'coach-pivot@example.com']);

        $response = Livewire::test(RecruitmentForm::class)
            ->set('selectedTeam', 'muzi-e')
            ->set('name', 'Jan Novák')
            ->set('email', 'jan@novak.cz')
            ->set('age', '25')
            ->set('height', '195')
            ->set('position', 'pg')
            ->set('level', '2. liga')
            ->set('message', 'Ahoj, chci hrát.')
            ->call('submit');

        $response->assertHasNoErrors();
        $this->assertTrue($response->get('success'));

        // Verify lead was created
        $this->assertDatabaseHas('leads', [
            'type' => 'recruitment',
            'name' => 'Jan Novák',
            'email' => 'jan@novak.cz',
            'message' => 'Ahoj, chci hrát.',
        ]);

        $lead = Lead::first();
        $this->assertEquals('muzi-e', $lead->payload['team_slug']);
        $this->assertEquals('Muži E', $lead->payload['team_name']);

        // E-mail is sent directly to the configured technical fallback when no owner is set.
        Mail::assertSent(LeadNotificationMail::class, function ($mail) use ($lead) {
            return $mail->hasTo(config('leads.technical_contact_email')) && $mail->lead->is($lead);
        });
        Mail::assertNotQueued(LeadNotificationMail::class);
    }
}
