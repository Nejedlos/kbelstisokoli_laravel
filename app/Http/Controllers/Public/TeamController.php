<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $allTeams = \App\Models\Team::orderBy('name')->get();

        $mainSlugs = ['muzi-c', 'muzi-e'];

        // Zachováme pořadí podle $mainSlugs
        $mainTeams = collect($mainSlugs)->map(function ($slug) use ($allTeams) {
            return $allTeams->firstWhere('slug', $slug);
        })->filter();

        $otherTeams = $allTeams->reject(fn ($team) => in_array($team->slug, $mainSlugs));

        $page = \App\Models\Page::where('slug', 'tymy')->first();

        return view('public.teams.index', compact('mainTeams', 'otherTeams', 'page'));
    }

    public function show(string $slug): View
    {
        $team = \App\Models\Team::where('slug', $slug)
            ->with(['coaches', 'seo'])
            ->firstOrFail();

        $randomPhotos = \App\Support\PhotoGallery::getRandomPhotos(8, $team->id);

        // Pokud pro tým nejsou žádné fotky, zkusíme vzít jakékoliv náhodné
        if ($randomPhotos->isEmpty()) {
            $randomPhotos = \App\Support\PhotoGallery::getRandomPhotos(8);
        }

        return view('public.teams.show', compact('team', 'randomPhotos'));
    }
}
