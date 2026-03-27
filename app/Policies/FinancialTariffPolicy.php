<?php

namespace App\Policies;

use App\Models\FinancialTariff;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinancialTariffPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_economy');
    }

    public function view(User $user, FinancialTariff $financialTariff): bool
    {
        return $user->can('manage_economy');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_economy');
    }

    public function update(User $user, FinancialTariff $financialTariff): bool
    {
        return $user->can('manage_economy');
    }

    public function delete(User $user, FinancialTariff $financialTariff): bool
    {
        return $user->can('manage_economy');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FinancialTariff $financialTariff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialTariff $financialTariff): bool
    {
        return false;
    }
}
