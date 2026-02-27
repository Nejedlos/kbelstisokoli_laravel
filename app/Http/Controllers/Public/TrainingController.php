<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\Team;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $currentSeasonId = \App\Models\Season::where('is_active', true)->first()?->id;

        $teams = Team::withCount([
            'rosterPlayers as expected_count' => function($q) use ($currentSeasonId) {
                if (!$currentSeasonId) {
                    return $q->whereRaw('1 = 0');
                }
                $q->whereHas('user.userSeasonConfigs', function ($sq) use ($currentSeasonId) {
                    $sq->where('season_id', $currentSeasonId)
                      ->where('track_attendance', true);
                });
            }
        ])->with(['trainings' => function ($query) {
            $query->where('starts_at', '>=', now())
                ->orderBy('starts_at', 'asc')
                ->withCount([
                    'attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed'),
                    'attendances as declined_count' => fn($q) => $q->where('planned_status', 'declined'),
                ]);
        }])->get();

        foreach ($teams as $team) {
            $team->setRelation('trainings', $team->trainings->take(5));
        }

        $page = \App\Models\Page::where('slug', 'treninky')->first();

        return view('public.trainings.index', compact('teams', 'page'));
    }
}
