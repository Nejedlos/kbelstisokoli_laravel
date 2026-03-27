<?php

namespace App\Policies;

use App\Models\BasketballMatch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BasketballMatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_matches');
    }

    public function view(User $user, BasketballMatch $basketballMatch): bool
    {
        return $user->can('manage_matches');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_matches');
    }

    public function update(User $user, BasketballMatch $basketballMatch): bool
    {
        return $user->can('manage_matches');
    }

    public function delete(User $user, BasketballMatch $basketballMatch): bool
    {
        return $user->can('manage_matches');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BasketballMatch $basketballMatch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BasketballMatch $basketballMatch): bool
    {
        return false;
    }
}
