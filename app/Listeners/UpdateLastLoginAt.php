<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastLoginAt
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $updates = [
            'last_login_at' => now(),
        ];

        // Automatická aktivace uživatele při prvním přihlášení do systému
        if ($user->membership_status === \App\Enums\MembershipStatus::Pending) {
            $updates['membership_status'] = \App\Enums\MembershipStatus::Active;
        }

        $user->update($updates);
    }
}
