<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type', 'upcoming');
        $teamId = $request->get('team_id');
        $seasonId = $request->get('season_id');
        $matchType = $request->get('match_type');

        $query = BasketballMatch::with(['team', 'opponent', 'season']);

        // Defaultní sezóna (aktuální), pokud není vybrána jiná
        if (!$seasonId) {
            $currentSeasonName = \App\Models\Season::getExpectedCurrentSeasonName();
            $currentSeason = \App\Models\Season::where('name', $currentSeasonName)->first();
            if ($currentSeason) {
                $seasonId = $currentSeason->id;
            }
        }

        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($matchType) {
            $query->where('match_type', $matchType);
        }

        if ($type === 'latest') {
            $query->whereIn('status', ['completed', 'played'])
                  ->orderBy('scheduled_at', 'desc');
        } else {
            $query->whereNotIn('status', ['completed', 'played'])
                  ->where('scheduled_at', '>=', now()->subHours(3)) // Zobrazit i probíhající
                  ->orderBy('scheduled_at', 'asc');
        }

        $matches = $query->paginate(15);
        $page = \App\Models\Page::where('slug', 'zapasy')->first();

        $seasons = \App\Models\Season::orderBy('name', 'desc')->get();
        $teams = \App\Models\Team::orderBy('name')->get();
        $matchTypes = [
            'MI' => __('matches.type_mi'),
            'PO' => __('matches.type_po'),
            'TUR' => __('matches.type_tur'),
            'PRATEL' => __('matches.type_pratel'),
        ];

        return view('public.matches.index', compact(
            'matches',
            'type',
            'page',
            'seasons',
            'teams',
            'matchTypes',
            'teamId',
            'seasonId',
            'matchType'
        ));
    }

    public function show(int $id): View
    {
        $match = BasketballMatch::with(['team', 'opponent', 'season'])
            ->findOrFail($id);

        return view('public.matches.show', [
            'match' => $match,
            'seo_title' => "{$match->team->name} vs {$match->opponent->name} | Zápasy",
            'seo_description' => "Detail zápasu {$match->team->name} proti {$match->opponent->name}. Termín: {$match->scheduled_at->format('d. m. Y H:i')}, místo: {$match->location}.",
        ]);
    }
}
