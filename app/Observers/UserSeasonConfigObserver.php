<?php

namespace App\Observers;

use App\Models\UserSeasonConfig;

class UserSeasonConfigObserver
{
    /**
     * Handle the UserSeasonConfig "created" event.
     */
    public function created(UserSeasonConfig $userSeasonConfig): void
    {
        if ($userSeasonConfig->financial_tariff_id) {
            app(\App\Services\Finance\FinanceAutomationService::class)->generateInstallments($userSeasonConfig);
        }
    }

    /**
     * Handle the UserSeasonConfig "updated" event.
     */
    public function updated(UserSeasonConfig $userSeasonConfig): void
    {
        if ($userSeasonConfig->wasChanged('financial_tariff_id') && $userSeasonConfig->financial_tariff_id) {
            app(\App\Services\Finance\FinanceAutomationService::class)->generateInstallments($userSeasonConfig);
        }
    }

    /**
     * Handle the UserSeasonConfig "deleted" event.
     */
    public function deleted(UserSeasonConfig $userSeasonConfig): void
    {
        //
    }

    /**
     * Handle the UserSeasonConfig "restored" event.
     */
    public function restored(UserSeasonConfig $userSeasonConfig): void
    {
        //
    }

    /**
     * Handle the UserSeasonConfig "force deleted" event.
     */
    public function forceDeleted(UserSeasonConfig $userSeasonConfig): void
    {
        //
    }
}
