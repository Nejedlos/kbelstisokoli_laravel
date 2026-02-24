<?php

namespace Tests\Feature;

use App\Mail\FeedbackMessage;
use App\Models\PlayerProfile;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class MemberFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_send_admin_feedback_uses_fallback_when_setting_missing(): void
    {
        Mail::fake();

        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);
        Permission::findOrCreate('view_member_section');
        $user->givePermissionTo('view_member_section');

        // Ujisti se, že není nastaveno admin_contact_email
        Setting::where('key', 'admin_contact_email')->delete();

        $response = $this->post(route('member.contact.admin.send'), [
            'subject' => 'Test admin feedback',
            'message' => 'Some message body ...',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        Mail::assertQueued(FeedbackMessage::class, function (FeedbackMessage $mail) {
            return $mail->hasTo(env('ERROR_REPORT_EMAIL'));
        });
    }

    public function test_member_can_send_coach_feedback_to_team_coaches(): void
    {
        Mail::fake();

        $member = User::factory()->create(['is_active' => true]);
        $this->actingAs($member);
        Permission::findOrCreate('view_member_section');
        $member->givePermissionTo('view_member_section');

        $profile = PlayerProfile::create([
            'user_id' => $member->id,
            'is_active' => true,
        ]);

        $team = Team::create([
            'name' => ['cs' => 'U13', 'en' => 'U13'],
            'slug' => 'u13',
            'category' => 'U13',
            'description' => ['cs' => null, 'en' => null],
        ]);

        // Přiřadit člena do týmu jako hráče
        $profile->teams()->attach($team->id, [
            'role_in_team' => 'player',
        ]);

        // Vytvořit trenéra a přiřadit k týmu
        $coach = User::factory()->create(['email' => 'coach@example.com', 'is_active' => true]);
        $team->coaches()->attach($coach->id, ['email' => 'coach-team@example.com']);

        $response = $this->post(route('member.contact.coach.send'), [
            'team_id' => $team->id,
            'subject' => 'Test coach feedback',
            'message' => 'Hello coach!'
        ]);

        $response->assertRedirect(route('member.dashboard'));

        Mail::assertQueued(FeedbackMessage::class, function (FeedbackMessage $mail) {
            return $mail->hasTo('coach-team@example.com');
        });
    }
}
