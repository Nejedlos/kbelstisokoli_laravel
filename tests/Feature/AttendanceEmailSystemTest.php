<?php

namespace Tests\Feature;

use App\Jobs\SendAttendanceEmailJob;
use App\Mail\AttendanceReminderMail;
use App\Models\AttendanceEmailDelivery;
use App\Models\ClubEvent;
use App\Models\FinancialTariff;
use App\Models\PlayerProfile;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserSeasonConfig;
use App\Services\Attendance\AttendanceEmailService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AttendanceEmailSystemTest extends TestCase
{
    public function test_queued_job_sends_mail_and_marks_delivery_as_sent(): void
    {
        Mail::fake();
        [$user, $event] = $this->makeTrackedPlayerEvent(now()->addDays(3));
        $delivery = AttendanceEmailDelivery::create([
            'user_id' => $user->id,
            'attendable_id' => $event->id,
            'attendable_type' => $event::class,
            'kind' => 'reminder',
            'stage' => 'three_days',
        ]);

        (new SendAttendanceEmailJob($delivery->id))->handle();

        Mail::assertSent(AttendanceReminderMail::class, fn ($mail) => $mail->hasTo($user->email));
        $this->assertSame('sent', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->sent_at);
    }

    public function test_job_never_resends_sent_or_skipped_delivery(): void
    {
        Mail::fake();
        [$user, $event] = $this->makeTrackedPlayerEvent(now()->addDays(3));

        foreach ([
            ['status' => 'sent', 'sent_at' => now()],
            ['status' => 'skipped', 'sent_at' => null],
        ] as $state) {
            $delivery = AttendanceEmailDelivery::create([
                'user_id' => $user->id,
                'attendable_id' => $event->id,
                'attendable_type' => $event::class,
                'kind' => 'reminder',
                'stage' => $state['status'],
                ...$state,
            ]);
            (new SendAttendanceEmailJob($delivery->id))->handle();
        }

        Mail::assertNothingSent();
    }

    public function test_due_reminder_is_queued_once_on_critical_queue(): void
    {
        Carbon::setTestNow('2026-09-01 08:15:00');
        Queue::fake();
        [$user, $event] = $this->makeTrackedPlayerEvent(now()->addDays(7)->setTime(18, 0));

        $first = app(AttendanceEmailService::class)->dispatchDue(now());
        $second = app(AttendanceEmailService::class)->dispatchDue(now());

        $this->assertSame(1, $first['reminders']);
        $this->assertSame(0, $second['reminders']);
        $this->assertDatabaseCount('attendance_email_deliveries', 1);
        Queue::assertPushedOn('critical-mail', SendAttendanceEmailJob::class);
        $this->assertDatabaseHas('attendance_email_deliveries', ['user_id' => $user->id, 'attendable_id' => $event->id, 'stage' => 'week']);
    }

    public function test_signed_email_action_records_attendance(): void
    {
        Carbon::setTestNow('2026-09-01 08:15:00');
        [$user, $event] = $this->makeTrackedPlayerEvent(now()->addDays(3));
        $url = URL::temporarySignedRoute('attendance.email.respond', now()->addDay(), [
            'user' => $user->id, 'type' => 'event', 'event' => $event->id, 'status' => 'confirmed',
        ]);

        $this->get($url)->assertOk()->assertSee(__('attendance_mail.response.confirm'));
        $this->post($url)->assertOk()->assertSee(__('attendance_mail.response.saved'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id, 'attendable_id' => $event->id, 'attendable_type' => ClubEvent::class, 'planned_status' => 'confirmed',
        ]);
    }

    public function test_day_summary_goes_to_team_members_except_opted_out_users(): void
    {
        Carbon::setTestNow('2026-09-01 07:15:00');
        Queue::fake();
        [$subscribed, $event] = $this->makeTrackedPlayerEvent(now()->setTime(12, 0));
        $team = $event->teams()->first();
        $optedOut = User::factory()->create([
            'is_active' => true,
            'notification_preferences' => ['attendance_summaries' => ['mail' => false]],
        ]);
        $profile = PlayerProfile::create(['user_id' => $optedOut->id, 'is_active' => true]);
        $team->players()->attach($profile->id, ['is_on_roster' => true]);

        $counts = app(AttendanceEmailService::class)->dispatchDue(now());

        $this->assertSame(1, $counts['summaries']);
        $this->assertDatabaseHas('attendance_email_deliveries', ['user_id' => $subscribed->id, 'kind' => 'summary']);
        $this->assertDatabaseMissing('attendance_email_deliveries', ['user_id' => $optedOut->id, 'kind' => 'summary']);
    }

    public function test_unsubscribe_link_and_profile_preferences_are_respected(): void
    {
        $user = User::factory()->create(['notification_preferences' => null]);
        $url = URL::temporarySignedRoute('attendance.email.unsubscribe', now()->addDay(), [
            'user' => $user->id, 'preference' => 'attendance_reminders',
        ]);

        $this->get($url)->assertOk()->assertSee(__('attendance_mail.response.confirm_unsubscribe'));
        $this->post($url)->assertOk();

        $this->assertFalse($user->fresh()->prefersNotification('attendance_reminders'));
    }

    public function test_reminder_email_renders_both_response_actions(): void
    {
        [$user, $event] = $this->makeTrackedPlayerEvent(now()->addDays(3));
        $delivery = new AttendanceEmailDelivery(['kind' => 'reminder', 'stage' => 'three_days']);
        $delivery->user_id = $user->id;
        $delivery->setRelation('user', $user);
        $delivery->setRelation('attendable', $event->load('teams'));

        $html = (new AttendanceReminderMail($delivery))->render();

        $this->assertStringContainsString(__('attendance_mail.actions.yes'), $html);
        $this->assertStringContainsString(__('attendance_mail.actions.no'), $html);
        $this->assertStringContainsString('signature=', $html);
    }

    private function makeTrackedPlayerEvent(Carbon $startsAt): array
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $team = Team::create(['name' => ['cs' => 'Sokol Kbely E', 'en' => 'Sokol Kbely E'], 'slug' => 'sokol-kbely-e', 'category' => 'senior']);
        $user = User::factory()->create(['is_active' => true, 'preferred_locale' => 'cs']);
        $profile = PlayerProfile::create(['user_id' => $user->id, 'is_active' => true]);
        $team->players()->attach($profile->id, ['is_on_roster' => true]);
        $tariff = FinancialTariff::create(['name' => 'Test', 'base_amount' => 0]);
        UserSeasonConfig::create(['user_id' => $user->id, 'season_id' => $season->id, 'financial_tariff_id' => $tariff->id, 'track_attendance' => true]);
        $event = ClubEvent::create([
            'title' => ['cs' => 'Týmový trénink', 'en' => 'Team practice'],
            'location' => 'Hala Kbely', 'starts_at' => $startsAt, 'ends_at' => $startsAt->copy()->addHours(2),
            'is_public' => false, 'rsvp_enabled' => true,
        ]);
        $event->teams()->attach($team->id);

        return [$user, $event];
    }
}
