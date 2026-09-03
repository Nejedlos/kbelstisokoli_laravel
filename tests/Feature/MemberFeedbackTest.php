<?php

namespace Tests\Feature;

use App\Mail\FeedbackConfirmation;
use App\Mail\FeedbackMessage;
use App\Models\PlayerProfile;
use App\Models\Team;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MemberFeedbackTest extends TestCase
{
    public function test_member_can_send_admin_feedback_uses_fallback_when_setting_missing(): void
    {
        Mail::fake();
        config(['mail.error_reporting.email' => 'support@example.test']);
        $member = $this->createMember();
        $this->actingAs($member);
        $this->get(route('member.contact.admin.form'))->assertOk();

        Volt::test('member.admin-contact-form')
            ->set('subject', 'Testovací dotaz')
            ->set('message', 'Zpráva pro správce systému.')
            ->call('send')->assertHasNoErrors()->assertSet('success', true);

        Mail::assertQueued(FeedbackMessage::class, fn ($mail) => $mail->hasTo('support@example.test') && $mail->user->is($member));
        Mail::assertQueued(FeedbackConfirmation::class, fn ($mail) => $mail->hasTo($member->email));
    }

    public function test_member_can_send_coach_feedback_to_team_coaches(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $coach = $this->createMember();
        $coach->assignRole('coach');
        $team = Team::factory()->create();
        $profile = $member->playerProfile ?? PlayerProfile::create(['user_id' => $member->id]);
        $profile->teams()->attach($team);
        $team->coaches()->attach($coach, ['email' => 'coach-team@example.test']);
        $member->refresh();
        $this->actingAs($member);
        $this->get(route('member.contact.coach.form'))->assertOk();

        Volt::test('member.coach-contact-form')
            ->set('subject', 'Dotaz k tréninku')
            ->set('message', 'Testovací zpráva trenérovi týmu.')
            ->call('send')->assertHasNoErrors()->assertSet('success', true);

        Mail::assertQueued(FeedbackMessage::class, fn ($mail) => $mail->hasTo('coach-team@example.test') && $mail->team->is($team));
        Mail::assertQueued(FeedbackConfirmation::class, fn ($mail) => $mail->hasTo($member->email));
    }
}
