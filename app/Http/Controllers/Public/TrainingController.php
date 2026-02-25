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
        $teams = Team::with(['trainings' => function($query) {
            $query->where('starts_at', '>=', now())
                  ->orderBy('starts_at', 'asc')
                  ->limit(5);
        }])->get();
        $page = \App\Models\Page::where('slug', 'treninky')->first();

        return view('public.trainings.index', compact('teams', 'page'));
    }
}
