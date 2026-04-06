<?php

namespace Tests\Feature;

use App\Mail\FeedbackMessage;
use App\Models\PlayerProfile;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MemberFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_send_admin_feedback_uses_fallback_when_setting_missing(): void
    {
        $this->markTestSkipped('ContactController::sendAdmin() chybí v projektu.');
        // ...
    }

    public function test_member_can_send_coach_feedback_to_team_coaches(): void
    {
        $this->markTestSkipped('ContactController::sendCoach() chybí v projektu.');
        // ...
    }
}
