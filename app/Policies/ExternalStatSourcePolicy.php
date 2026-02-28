<?php

namespace App\Policies;

use App\Models\ExternalStatSource;
use App\Models\User;

class ExternalStatSourcePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExternalStatSource $externalStatSource): bool
    {
        return $user->can('manage_stats');
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
    public function update(User $user, ExternalStatSource $externalStatSource): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExternalStatSource $externalStatSource): bool
    {
        return $user->can('manage_stats');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExternalStatSource $externalStatSource): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExternalStatSource $externalStatSource): bool
    {
        return false;
    }
}
