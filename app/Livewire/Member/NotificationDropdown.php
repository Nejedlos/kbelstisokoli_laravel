<?php

namespace App\Livewire\Member;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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
