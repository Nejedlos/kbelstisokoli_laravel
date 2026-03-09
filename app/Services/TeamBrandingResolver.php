<?php

namespace App\Services;

use App\Models\BasketballMatch;
use App\Models\Team;

class TeamBrandingResolver
{
    protected BrandingService $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    /**
     * Zjistí, zda jde o náš interní tým (C nebo E).
     */
    public function isInternalTeam(Team|string|null $team): bool
    {
        if (!$team) {
            return false;
        }

        $slug = $team instanceof Team ? $team->slug : $team;

        // Slugy pro týmy C a E (může jich být více variant, např. muzi-c, muzi-e)
        // Předpokládáme, že slug obsahuje 'muzi-c' nebo 'muzi-e' nebo 'tym-c' atd.
        // Dle zadání: "pokud zápas patří týmu C nebo E"
        return str_contains(strtolower($slug), '-c') || str_contains(strtolower($slug), '-e')
            || strtolower($slug) === 'c' || strtolower($slug) === 'e';
    }

    /**
     * Vrátí data brandingu pro daný tým.
     */
    public function getTeamBranding(Team|string|null $team): array
    {
        $branding = $this->brandingService->getSettings();
        $isInternal = $this->isInternalTeam($team);

        if ($isInternal) {
            return [
                'type' => 'primary',
                'logo_url' => web_asset($branding['team_logo']['paths']['mini'] ?? '', false),
                'logo_url_webp' => web_asset($branding['team_logo']['paths']['mini'] ?? '', true),
                'logo_url_large' => web_asset($branding['team_logo']['paths']['velke'] ?? '', false),
                'logo_url_large_webp' => web_asset($branding['team_logo']['paths']['velke'] ?? '', true),
                'alt' => 'Kbelští sokoli C & E logo',
                'is_internal' => true,
            ];
        }

        return [
            'type' => 'parent',
            'logo_url' => web_asset($branding['parent_logo']['paths']['mini'] ?? '', false),
            'logo_url_webp' => web_asset($branding['parent_logo']['paths']['mini'] ?? '', true),
            'logo_url_large' => web_asset($branding['parent_logo']['paths']['velke'] ?? '', false),
            'logo_url_large_webp' => web_asset($branding['parent_logo']['paths']['velke'] ?? '', true),
            'alt' => 'TJ Sokol Kbely C & E logo',
            'is_internal' => false,
        ];
    }

    /**
     * Vrátí logo pro zápas (podle toho, zda je náš tým domácí/hosté a zda je to C/E).
     */
    public function getMatchLogo(BasketballMatch $match, bool $forHomeTeam = true): ?array
    {
        // Pokud chceme logo pro soupeře, neřešíme ho zde (zadání: "opponent logo neřeš")
        if (($match->is_home && !$forHomeTeam) || (!$match->is_home && $forHomeTeam)) {
            return null;
        }

        return $this->getTeamBranding($match->team);
    }
}
