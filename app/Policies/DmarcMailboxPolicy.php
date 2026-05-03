<?php

namespace App\Policies;

use App\Models\Dmarc\DmarcMailbox;
use App\Models\User;

class DmarcMailboxPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function view(User $user, DmarcMailbox $model): bool
    {
        return $user->can('manage_advanced_settings') || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, DmarcMailbox $model): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, DmarcMailbox $model): bool
    {
        return $user->hasRole('admin');
    }
}
