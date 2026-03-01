<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Výpis notifikací uživatele.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);

        return view('member.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Označí konkrétní notifikaci jako přečtenou a přesměruje na cílovou URL.
     */
    public function readAndRedirect(string $id): RedirectResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);

        if (!$notification->read_at) {
            $user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
        }

        $actionUrl = $notification->data['action_url'] ?? route('member.dashboard');

        return redirect($actionUrl);
    }

    /**
     * Označí konkrétní notifikaci jako přečtenou.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        auth()->user()->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);

        return back()->with('status', __('member.notifications.marked_read'));
    }

    /**
     * Označí všechny notifikace jako přečtené.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', __('member.notifications.marked_all_read'));
    }
}
