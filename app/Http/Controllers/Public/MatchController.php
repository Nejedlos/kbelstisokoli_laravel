<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type', 'upcoming');

        $query = BasketballMatch::with(['team', 'opponent', 'season']);

        if ($type === 'latest') {
            $query->where('status', 'completed')
                  ->orderBy('scheduled_at', 'desc');
        } else {
            $query->where('status', '!=', 'completed')
                  ->where('scheduled_at', '>=', now()->subHours(3)) // Zobrazit i probíhající
                  ->orderBy('scheduled_at', 'asc');
        }

        $matches = $query->paginate(15);

        return view('public.matches.index', compact('matches', 'type'));
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
