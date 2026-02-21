<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $now = now();

        // Tréninky (budoucí)
        $trainings = Training::with(['team', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('starts_at', '>=', $now)
            ->orderBy('starts_at')
            ->get()
            ->map(fn($item) => ['type' => 'training', 'data' => $item, 'time' => $item->starts_at]);

        // Zápasy (budoucí)
        $matches = BasketballMatch::with(['team', 'opponent', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($item) => ['type' => 'match', 'data' => $item, 'time' => $item->scheduled_at]);

        // Akce (budoucí)
        $events = ClubEvent::with(['team', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('starts_at', '>=', $now)
            ->where('rsvp_enabled', true)
            ->orderBy('starts_at')
            ->get()
            ->map(fn($item) => ['type' => 'event', 'data' => $item, 'time' => $item->starts_at]);

        // Spojení a seřazení
        $program = $trainings->concat($matches)->concat($events)->sortBy('time');

        return view('member.attendance.index', [
            'program' => $program,
        ]);
    }

    public function store(Request $request, string $type, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:confirmed,declined,maybe',
            'note' => 'nullable|string|max:255',
        ]);

        $modelClass = match ($type) {
            'training' => Training::class,
            'match' => BasketballMatch::class,
            'event' => ClubEvent::class,
            default => abort(404),
        };

        $item = $modelClass::findOrFail($id);

        Attendance::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'attendable_id' => $item->id,
                'attendable_type' => $modelClass,
            ],
            [
                'status' => $request->status,
                'note' => $request->note,
                'responded_at' => now(),
            ]
        );

        return back()->with('status', 'Vaše odpověď byla uložena.');
    }
}
