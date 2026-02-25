<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $teams = \App\Models\Team::orderBy('name')->get()->groupBy('category');
        $page = \App\Models\Page::where('slug', 'tymy')->first();

        return view('public.teams.index', compact('teams', 'page'));
    }

    public function show(string $slug): View
    {
        $team = \App\Models\Team::where('slug', $slug)->firstOrFail();

        return view('public.teams.show', compact('team'));
    }
}
