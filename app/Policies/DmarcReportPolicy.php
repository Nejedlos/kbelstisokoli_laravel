<?php

namespace App\Policies;

use App\Models\Dmarc\DmarcReport;
use App\Models\User;

class DmarcReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function view(User $user, DmarcReport $model): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return false; // Created via ingest command
    }

    public function update(User $user, DmarcReport $model): bool
    {
        return false;
    }

    public function delete(User $user, DmarcReport $model): bool
    {
        return $user->hasRole('admin');
    }
}
