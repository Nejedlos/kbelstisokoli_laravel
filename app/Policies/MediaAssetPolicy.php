<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MediaAssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('access_admin');
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('access_admin');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_content');
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('manage_content');
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->can('manage_content');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MediaAsset $mediaAsset): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MediaAsset $mediaAsset): bool
    {
        return false;
    }
}
