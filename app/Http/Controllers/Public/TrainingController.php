<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $currentSeasonId = \App\Models\Season::where('is_active', true)->first()?->id;

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
                // Musíme to počítat unikátně, protože hráč může být ve více týmech assigned k jednomu tréninku
                $expectedUserIds = collect();
                foreach ($training->teams as $t) {
                    // Načteme hráče týmu a jejich trackování docházky
                    // Tady je to trochu neefektivní, ale pro 5 tréninků na stránku to nevadí
                    $userIds = $t->activePlayers()
                        ->whereHas('user.userSeasonConfigs', function ($sq) use ($currentSeasonId) {
                            $sq->where('season_id', $currentSeasonId)
                                ->where('track_attendance', true);
                        })
                        ->pluck('user_id');
                    
                    $expectedUserIds = $expectedUserIds->concat($userIds);
                }
                
                $training->total_expected_count = $expectedUserIds->unique()->count();
            }

            $team->setRelation('trainings', $trainings);
        }

        $page = \App\Models\Page::where('slug', 'treninky')->first();

        return view('public.trainings.index', compact('teams', 'page'));
    }
}
