<?php

namespace App\Policies;

use App\Models\FinancePayment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinancePaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('access_admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FinancePayment $financePayment): bool
    {
        return $user->can('access_admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FinancePayment $financePayment): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancePayment $financePayment): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FinancePayment $financePayment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancePayment $financePayment): bool
    {
        return false;
    }
}
