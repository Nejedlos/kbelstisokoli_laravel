<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $matches = BasketballMatch::with(['team', 'opponent', 'season'])
            ->orderBy('scheduled_at', 'desc')
            ->paginate(20);

        return view('public.matches.index', compact('matches'));
    }

    public function show(int $id): View
    {
        $match = BasketballMatch::with(['team', 'opponent', 'season'])
            ->findOrFail($id);

        return view('public.matches.show', compact('match'));
    }
}
