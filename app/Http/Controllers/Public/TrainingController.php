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

        // Načteme očekávané počty pro všechny týmy jednou, abychom je mohli sčítat u sdílených tréninků
        $teamExpectedCounts = Team::withCount([
            'activePlayers as expected_count' => function ($q) use ($currentSeasonId) {
                if (! $currentSeasonId) {
                    return $q->whereRaw('1 = 0');
                }
                $q->whereHas('user.userSeasonConfigs', function ($sq) use ($currentSeasonId) {
                    $sq->where('season_id', $currentSeasonId)
                        ->where('track_attendance', true);
                });
            }
        ])->get()->pluck('expected_count', 'id');

        // Vynecháme virtuální tým "Celý klub" z hlavního výpisu
        $teams = Team::where('slug', '!=', 'klub')
            ->with(['trainings' => function ($query) {
                $query->where('starts_at', '>=', now())
                    ->orderBy('starts_at', 'asc')
                    ->with(['teams']) // Potřebujeme týmy pro výpočet celkové očekávané účasti
                    ->withCount([
                        'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                        'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                    ]);
            }])->get();

        foreach ($teams as $team) {
            $trainings = $team->trainings->take(5);

            foreach ($trainings as $training) {
                // Pro každý trénink vypočítáme celkový počet očekávaných hráčů ze všech přiřazených týmů
                $training->total_expected_count = $training->teams->sum(function ($t) use ($teamExpectedCounts) {
                    return $teamExpectedCounts[$t->id] ?? 0;
                });
            }

            $team->setRelation('trainings', $trainings);
        }

        $page = \App\Models\Page::where('slug', 'treninky')->first();

        return view('public.trainings.index', compact('teams', 'page'));
    }
}
