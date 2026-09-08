<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Team;
use App\Support\PhotoGallery;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $allTeams = Team::orderBy('name')->get();

        $mainSlugs = ['muzi-c', 'muzi-e'];

        // Zachováme pořadí podle $mainSlugs
        $mainTeams = collect($mainSlugs)->map(function ($slug) use ($allTeams) {
            return $allTeams->firstWhere('slug', $slug);
        })->filter();

        $otherTeams = $allTeams->reject(fn ($team) => in_array($team->slug, $mainSlugs));

        $page = Page::where('slug', 'tymy')->first();

        return view('public.teams.index', compact('mainTeams', 'otherTeams', 'page'));
    }

    public function roster(): View
    {
        $teams = Team::with(['rosterPlayers.user'])
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
        $page = Page::where('slug', 'tymy')->first();

        return view('public.teams.roster', compact('teams', 'page'));
    }

    public function show(?string $slug = null): View
    {
        if (is_null($slug)) {
            abort(404);
        }
        $team = Team::where('slug', $slug)
            ->with(['coaches', 'seo', 'rosterPlayers.user'])
            ->firstOrFail();

        // Seřadíme hráče podle příjmení uživatele
        $sortedRoster = $team->rosterPlayers->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        });
        $team->setRelation('rosterPlayers', $sortedRoster);

        $randomPhotos = PhotoGallery::getRandomPhotos(8, $team->id);

        // Pokud pro tým nejsou žádné fotky, zkusíme vzít jakékoliv náhodné
        if ($randomPhotos->isEmpty()) {
            $randomPhotos = PhotoGallery::getRandomPhotos(8);
        }

        return view('public.teams.show', compact('team', 'randomPhotos'));
    }
}
