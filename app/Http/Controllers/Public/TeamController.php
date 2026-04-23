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

    public function roster(): View
    {
        $teams = \App\Models\Team::with(['rosterPlayers.user'])
            ->get()
            ->map(function ($team) {
                // Seřadíme hráče podle příjmení uživatele
                $sortedRoster = $team->rosterPlayers->sortBy(function ($profile) {
                    return $profile->user->last_name ?? '';
                });
                $team->setRelation('rosterPlayers', $sortedRoster);

                return $team;
            })
            ->filter(function ($team) {
                return $team->rosterPlayers->count() > 0;
            });

        // Přidáme SEO data
        $page = \App\Models\Page::where('slug', 'tymy')->first();

        return view('public.teams.roster', compact('teams', 'page'));
    }

    public function show(string $slug): View
    {
        $team = \App\Models\Team::where('slug', $slug)
            ->with(['coaches', 'seo', 'rosterPlayers.user'])
            ->firstOrFail();

        // Seřadíme hráče podle příjmení uživatele
        $sortedRoster = $team->rosterPlayers->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        });
        $team->setRelation('rosterPlayers', $sortedRoster);

        $randomPhotos = \App\Support\PhotoGallery::getRandomPhotos(8, $team->id);

        // Pokud pro tým nejsou žádné fotky, zkusíme vzít jakékoliv náhodné
        if ($randomPhotos->isEmpty()) {
            $randomPhotos = \App\Support\PhotoGallery::getRandomPhotos(8);
        }

        return view('public.teams.show', compact('team', 'randomPhotos'));
    }
}
