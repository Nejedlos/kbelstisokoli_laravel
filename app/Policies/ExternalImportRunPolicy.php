<?php

namespace App\Policies;

use App\Models\ExternalImportRun;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExternalImportRunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function view(User $user, ExternalImportRun $externalImportRun): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function update(User $user, ExternalImportRun $externalImportRun): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function delete(User $user, ExternalImportRun $externalImportRun): bool
    {
        return $user->can('manage_advanced_settings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExternalImportRun $externalImportRun): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExternalImportRun $externalImportRun): bool
    {
        return false;
    }
}
