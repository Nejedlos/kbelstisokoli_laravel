<?php

namespace App\Policies;

use App\Models\StatisticSet;
use App\Models\User;

class StatisticSetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_stats') || $user->can('access_admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StatisticSet $statisticSet): bool
    {
        return $user->can('manage_stats') || $user->can('access_admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StatisticSet $statisticSet): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StatisticSet $statisticSet): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StatisticSet $statisticSet): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StatisticSet $statisticSet): bool
    {
        return false;
    }
}
