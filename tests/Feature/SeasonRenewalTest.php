<?php

namespace Tests\Feature;

use App\Actions\Season\RenewSeasonAction;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\FinancialTariff;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserSeasonConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeasonRenewalTest extends TestCase
{
    public function test_it_copies_complete_member_configuration_balance_and_external_config_then_activates_target(): void
    {
        $source = Season::create(['name' => '2025/2026', 'is_active' => true, 'fine_no_show' => 250]);
        $target = Season::create(['name' => '2026/2027', 'is_active' => false]);
        $user = User::factory()->create();
        $tariff = FinancialTariff::create(['name' => 'Člen', 'base_amount' => 6000, 'unit' => 'season', 'type' => 'flat']);
        UserSeasonConfig::withoutEvents(fn () => UserSeasonConfig::create([
            'user_id' => $user->id,
            'season_id' => $source->id,
            'financial_tariff_id' => $tariff->id,
            'billing_start_month' => 9,
            'billing_end_month' => 6,
            'exemption_start_month' => 1,
            'exemption_end_month' => 2,
            'track_attendance' => true,
        ]));
        DB::table('finance_payments')->insert([
            'user_id' => $user->id, 'amount' => 500, 'currency' => 'CZK', 'paid_at' => now(),
            'payment_method' => 'bank_transfer', 'status' => 'recorded', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $team = Team::create(['name' => ['cs' => 'E', 'en' => 'E'], 'slug' => 'e', 'category' => 'adult']);
        ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball', 'season_id' => $source->id, 'team_id' => $team->id,
            'external_team_id' => '7761', 'external_season_year' => 2025,
            'team_season_url' => 'https://example.test/team/7761?y=2025',
            'competition_url' => 'https://example.test/competition/1',
            'matches_list_url' => 'https://example.test/team/7761?y=2025', 'is_enabled' => true,
        ]);

        $result = app(RenewSeasonAction::class)->execute($target->id, $source->id);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['external_created']);
        $this->assertFalse($source->fresh()->is_active);
        $this->assertTrue($target->fresh()->is_active);
        $config = UserSeasonConfig::where(['user_id' => $user->id, 'season_id' => $target->id])->firstOrFail();
        $this->assertSame(9, $config->billing_start_month);
        $this->assertSame(6, $config->billing_end_month);
        $this->assertSame(1, $config->exemption_start_month);
        $this->assertSame(2, $config->exemption_end_month);
        $this->assertTrue($config->track_attendance);
        $this->assertSame('500.00', $config->opening_balance);
        $external = ExternalTeamSeasonConfig::where('season_id', $target->id)->firstOrFail();
        $this->assertFalse($external->is_enabled);
        $this->assertSame(2026, $external->external_season_year);
        $this->assertStringContainsString('y=2026', $external->team_season_url);
        $this->assertTrue($external->metadata['requires_competition_review']);
    }

    public function test_it_is_idempotent_and_preserves_manually_initialized_target_records(): void
    {
        $source = Season::create(['name' => '2025/2026', 'is_active' => true]);
        $target = Season::create(['name' => '2026/2027']);
        $user = User::factory()->create();
        $tariff = FinancialTariff::create(['name' => 'Člen', 'base_amount' => 0, 'unit' => 'season', 'type' => 'flat']);
        UserSeasonConfig::withoutEvents(function () use ($source, $target, $user, $tariff): void {
            UserSeasonConfig::create(['user_id' => $user->id, 'season_id' => $source->id, 'financial_tariff_id' => $tariff->id, 'track_attendance' => true]);
            UserSeasonConfig::create(['user_id' => $user->id, 'season_id' => $target->id, 'financial_tariff_id' => $tariff->id, 'track_attendance' => false, 'opening_balance' => 123]);
        });

        $first = app(RenewSeasonAction::class)->execute($target->id, $source->id);
        $second = app(RenewSeasonAction::class)->execute($target->id, $source->id);

        $this->assertSame(0, $first['created']);
        $this->assertSame(1, $first['skipped']);
        $this->assertSame(1, $second['skipped']);
        $this->assertDatabaseCount('user_season_configs', 2);
        $saved = UserSeasonConfig::where(['user_id' => $user->id, 'season_id' => $target->id])->firstOrFail();
        $this->assertFalse($saved->track_attendance);
        $this->assertSame('123.00', $saved->opening_balance);
    }

    public function test_season_date_boundary_is_first_of_september(): void
    {
        $old = Season::create(['name' => '2025/2026']);
        $new = Season::create(['name' => '2026/2027']);

        $this->assertTrue($old->containsDate(Carbon::parse('2026-08-31 23:59:59')));
        $this->assertFalse($new->containsDate(Carbon::parse('2026-08-31 23:59:59')));
        $this->assertSame($old->id, Season::forDate(Carbon::parse('2026-08-31'))->id);
        $this->assertSame($new->id, Season::forDate(Carbon::parse('2026-09-01'))->id);
    }
}
