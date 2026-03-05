<?php

namespace App\Livewire\Member;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        $latestNotifications = $user->notifications()->take(5)->get();

        return view('livewire.member.notification-dropdown', [
            'unreadCount' => $unreadCount,
            'latestNotifications' => $latestNotifications,
        ]);
    }
}
