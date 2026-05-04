<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ClubEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubEventController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type', 'upcoming');
        $eventType = $request->get('event_type');
        $teamId = $request->get('team_id');

        $query = ClubEvent::with(['teams'])
            ->withCount([
                'attendances as confirmed_count' => function ($query) {
                    $query->where('planned_status', 'confirmed');
                },
                'attendances as declined_count' => function ($query) {
                    $query->where('planned_status', 'declined');
                },
                'attendances as maybe_count' => function ($query) {
                    $query->where('planned_status', 'maybe');
                },
            ])
            ->where('is_public', true);

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        if ($teamId) {
            $query->whereHas('teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        if ($type === 'past') {
            $query->where('ends_at', '<', now())
                ->orderBy('starts_at', 'desc');
        } else {
            $query->where('ends_at', '>=', now())
                ->orderBy('starts_at', 'asc');
        }

        $events = $query->paginate(12);
        $page = \App\Models\Page::where('slug', 'akce')->first();

        $eventTypes = [
            'camp' => __('events.filter_type_camp'),
            'tournament' => __('events.filter_type_tournament'),
            'social' => __('events.filter_type_social'),
            'meeting' => __('events.filter_type_meeting'),
            'volunteer' => __('events.filter_type_volunteer'),
            'other' => __('events.filter_type_other'),
        ];

        $teams = \App\Models\Team::orderBy('name')->get();

        return view('public.events.index', compact(
            'events',
            'type',
            'eventType',
            'teamId',
            'page',
            'eventTypes',
            'teams'
        ));
    }

    public function show(int $id): View
    {
        $event = ClubEvent::with(['teams'])
            ->where('is_public', true)
            ->findOrFail($id);

        $attendanceStats = $event->attendances()
            ->selectRaw('planned_status, count(*) as count')
            ->groupBy('planned_status')
            ->pluck('count', 'planned_status')
            ->toArray();

        $stats = [
            'confirmed' => $attendanceStats['confirmed'] ?? 0,
            'declined' => $attendanceStats['declined'] ?? 0,
            'maybe' => $attendanceStats['maybe'] ?? 0,
        ];

        return view('public.events.show', [
            'event' => $event,
            'stats' => $stats,
            'seo_title' => $event->getTranslation('title', app()->getLocale()) . ' | Akce',
            'seo_description' => substr(strip_tags($event->getTranslation('description', app()->getLocale())), 0, 160),
        ]);
    }
}
