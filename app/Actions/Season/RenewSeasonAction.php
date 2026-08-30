<?php

namespace App\Actions\Season;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\UserSeasonConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewSeasonAction
{
    public function execute(?int $targetSeasonId = null, ?int $sourceSeasonId = null): array
    {
        $targetSeason = $targetSeasonId
            ? Season::findOrFail($targetSeasonId)
            : Season::firstOrCreate(['name' => Season::getExpectedCurrentSeasonName()]);
        $sourceSeason = $sourceSeasonId
            ? Season::findOrFail($sourceSeasonId)
            : $this->findSourceSeason($targetSeason);

        if (! $sourceSeason || $sourceSeason->is($targetSeason)) {
            throw new \RuntimeException('Pro obnovu sezóny nebyla nalezena platná předchozí sezóna.');
        }

        $result = ['created' => 0, 'skipped' => 0, 'external_created' => 0, 'external_skipped' => 0, 'activated' => false];

        DB::transaction(function () use ($sourceSeason, $targetSeason, &$result): void {
            if ($targetSeason->wasRecentlyCreated) {
                $targetSeason->fill([
                    'fine_no_response' => $sourceSeason->fine_no_response,
                    'fine_no_show' => $sourceSeason->fine_no_show,
                    'fine_unannounced_show' => $sourceSeason->fine_unannounced_show,
                    'fine_excused_show' => $sourceSeason->fine_excused_show,
                    'fine_missed_free_throw' => $sourceSeason->fine_missed_free_throw,
                ])->save();
            }

            UserSeasonConfig::withoutEvents(function () use ($sourceSeason, $targetSeason, &$result): void {
                UserSeasonConfig::where('season_id', $sourceSeason->id)->orderBy('id')
                    ->each(function (UserSeasonConfig $source) use ($sourceSeason, $targetSeason, &$result): void {
                        if (UserSeasonConfig::where('user_id', $source->user_id)->where('season_id', $targetSeason->id)->exists()) {
                            $result['skipped']++;

                            return;
                        }

                        UserSeasonConfig::create([
                            'user_id' => $source->user_id,
                            'season_id' => $targetSeason->id,
                            'financial_tariff_id' => $source->financial_tariff_id,
                            'billing_start_month' => $source->billing_start_month,
                            'billing_end_month' => $source->billing_end_month,
                            'exemption_start_month' => $source->exemption_start_month,
                            'exemption_end_month' => $source->exemption_end_month,
                            'track_attendance' => $source->track_attendance,
                            'opening_balance' => $this->netBalanceForUser($source->user_id),
                            'metadata' => ['renewed_from_season_id' => $sourceSeason->id, 'renewed_at' => now()->toIso8601String()],
                        ]);
                        $result['created']++;
                    });
            });

            ExternalTeamSeasonConfig::where('season_id', $sourceSeason->id)->orderBy('id')
                ->each(function (ExternalTeamSeasonConfig $source) use ($targetSeason, &$result): void {
                    if (ExternalTeamSeasonConfig::where(['source_key' => $source->source_key, 'season_id' => $targetSeason->id, 'team_id' => $source->team_id])->exists()) {
                        $result['external_skipped']++;

                        return;
                    }

                    $year = $this->seasonStartYear($targetSeason);
                    $metadata = $source->metadata ?? [];
                    $metadata['renewed_from_config_id'] = $source->id;
                    $metadata['requires_competition_review'] = true;
                    ExternalTeamSeasonConfig::create([
                        'source_key' => $source->source_key,
                        'season_id' => $targetSeason->id,
                        'team_id' => $source->team_id,
                        'external_team_id' => $source->external_team_id,
                        'external_season_year' => $year,
                        'team_season_url' => $this->replaceSeasonYear($source->team_season_url, $year),
                        'competition_url' => $source->competition_url,
                        'matches_list_url' => $this->replaceSeasonYear($source->matches_list_url, $year),
                        'competition_label' => $source->competition_label,
                        'team_name_in_source' => $source->team_name_in_source,
                        'is_enabled' => false,
                        'last_synced_at' => null,
                        'metadata' => $metadata,
                    ]);
                    $result['external_created']++;
                });

            Season::whereKeyNot($targetSeason->id)->where('is_active', true)->update(['is_active' => false]);
            if (! $targetSeason->is_active) {
                $targetSeason->update(['is_active' => true]);
                $result['activated'] = true;
            }
        });

        Log::info('Season renewal completed.', ['source_season_id' => $sourceSeason->id, 'target_season_id' => $targetSeason->id, ...$result]);

        return $result;
    }

    private function findSourceSeason(Season $targetSeason): ?Season
    {
        return Season::where('name', Season::getPreviousSeasonNameFrom($targetSeason->name))->first()
            ?? Season::whereKeyNot($targetSeason->id)->orderByDesc('name')->first();
    }

    private function netBalanceForUser(int $userId): float
    {
        $debt = (float) DB::table('finance_charges')->where('user_id', $userId)->whereIn('status', ['open', 'partially_paid', 'overdue'])->sum('amount_total');
        $paidAgainstDebt = (float) DB::table('charge_payment_allocations')->join('finance_charges', 'finance_charges.id', '=', 'charge_payment_allocations.finance_charge_id')->where('finance_charges.user_id', $userId)->whereIn('finance_charges.status', ['open', 'partially_paid', 'overdue'])->sum('charge_payment_allocations.amount');
        $payments = (float) DB::table('finance_payments')->where('user_id', $userId)->whereIn('status', ['recorded', 'completed'])->sum('amount');
        $allocated = (float) DB::table('charge_payment_allocations')->join('finance_payments', 'finance_payments.id', '=', 'charge_payment_allocations.finance_payment_id')->where('finance_payments.user_id', $userId)->whereIn('finance_payments.status', ['recorded', 'completed'])->sum('charge_payment_allocations.amount');

        return round(($payments - $allocated) - ($debt - $paidAgainstDebt), 2);
    }

    private function seasonStartYear(Season $season): int
    {
        return (int) explode('/', Season::normalizeName($season->name))[0];
    }

    private function replaceSeasonYear(?string $url, int $year): ?string
    {
        return $url ? preg_replace_callback('/([?&]y=)\d{4}/', fn (array $match) => $match[1].$year, $url) : $url;
    }
}
