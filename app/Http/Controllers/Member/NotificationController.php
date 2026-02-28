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
     * Označí konkrétní notifikaci jako přečtenou.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('status', 'Notifikace byla označena jako přečtená.');
    }

    /**
     * Označí všechny notifikace jako přečtené.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Všechny notifikace byly označeny jako přečtené.');
    }
}
