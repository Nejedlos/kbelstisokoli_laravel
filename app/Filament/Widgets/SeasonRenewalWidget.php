<?php

namespace App\Filament\Widgets;

use App\Models\Season;
use App\Models\UserSeasonConfig;
use Filament\Widgets\Widget;

class SeasonRenewalWidget extends Widget
{
    protected string $view = 'filament.widgets.season-renewal-widget';

    protected static ?int $sort = -180;

    protected int|string|array $columnSpan = 'full';

    public function isVisible(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        $expectedSeasonName = Season::getExpectedCurrentSeasonName();
        $altName = str_replace('/', '-', $expectedSeasonName);
        $seasonIds = Season::whereIn('name', [$expectedSeasonName, $altName])->pluck('id');

        $isDebug = request()->query('debug') == '1' || request()->header('referer') && str_contains(request()->header('referer'), 'debug=1');

        $showRenewalWarning = false;
        $month = (int) now()->format('m');

        // Pokud je debug=1, vynutíme zobrazení varování VŽDY
        if ($isDebug) {
            $showRenewalWarning = true;
        } elseif ($month >= 8 || $month <= 9) {
            // Logika pro reálné zobrazení na začátku sezóny
            if ($seasonIds->isEmpty()) {
                $showRenewalWarning = true;
            } else {
                $configCount = UserSeasonConfig::whereIn('season_id', $seasonIds)->count();
                $showRenewalWarning = ($configCount === 0);
            }
        }

        // --- ZDRAVÍ SYSTÉMU ---
        // 1. Docházka: Mismatche za posledních 30 dní
        $mismatchesCount = \App\Models\Attendance::where('is_mismatch', true)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        // 2. Finance: Aktivní uživatelé bez konfigurace pro AKTIVNÍ sezónu
        $currentSeason = Season::where('is_active', true)->first();
        $usersWithoutConfig = 0;
        if ($currentSeason) {
            $usersWithoutConfig = \App\Models\User::where('is_active', true)
                ->whereDoesntHave('userSeasonConfigs', fn ($q) => $q->where('season_id', $currentSeason->id))
                ->count();
        }

        // 3. Finance: Celkový neuhrazený dluh (otevřené poplatky)
        $totalDebt = \App\Models\FinanceCharge::whereNotIn('status', ['cancelled'])->sum('amount_total')
                   - \App\Models\ChargePaymentAllocation::sum('amount');

        // 4. Cron: (Best effort jako v SystemHealthWidget)
        $cronOk = false;
        $lastCronRun = null;
        if (class_exists(\App\Models\CronLog::class)) {
            $last = \App\Models\CronLog::query()->latest('created_at')->first();
            if ($last) {
                $lastCronRun = $last->created_at ?? $last->occurred_at ?? null;
            }
        }
        if ($lastCronRun) {
            $cronOk = now()->diffInMinutes($lastCronRun) <= 20;
        }

        return [
            'showRenewalWarning' => $showRenewalWarning,
            'expectedSeason' => $expectedSeasonName,
            'previousSeason' => Season::getPreviousSeasonName(),
            'renewalUrl' => route('filament.admin.pages.season-renewal'),
            'mismatchesCount' => $mismatchesCount,
            'usersWithoutConfig' => $usersWithoutConfig,
            'totalDebt' => number_format($totalDebt, 0, ',', ' ').' Kč',
            'cronOk' => $cronOk,
            'lastCronRun' => $lastCronRun ? $lastCronRun->diffForHumans() : 'Nikdy',
            'isDebug' => $isDebug,
            'userName' => auth()->user()?->first_name ?: auth()->user()?->name,
            'isNejedly' => auth()->user()?->email === 'nejedlymi@gmail.com',
        ];
    }
}
