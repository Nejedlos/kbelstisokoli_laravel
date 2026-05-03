<?php

namespace App\Policies;

use App\Models\Dmarc\DmarcIncident;
use App\Models\User;

class DmarcIncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function view(User $user, DmarcIncident $model): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return false; // Created via incident service
    }

    public function update(User $user, DmarcIncident $model): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function delete(User $user, DmarcIncident $model): bool
    {
        return $user->hasRole('admin');
    }
}
