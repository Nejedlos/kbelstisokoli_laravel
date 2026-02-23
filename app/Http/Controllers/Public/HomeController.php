<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\BrandingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(BrandingService $brandingService): View
    {
        $branding = $brandingService->getSettings();
        $isAdmin = auth()->check() && auth()->user()->canAccessAdmin();

        // 1. Priorita: Manuálně zapnutý režim přípravy v administraci (bypass pro adminy)
        if (data_get($branding, 'maintenance_mode') && !$isAdmin) {
            return view('public.under-construction', [
                'branding' => $branding,
                'branding_css' => $brandingService->getCssVariables(),
                'title' => brand_text(data_get($branding, 'maintenance_title')),
                'text' => brand_text(data_get($branding, 'maintenance_text')),
            ]);
        }

        // 2. Priorita: Načtení homepage z CMS
        $homePage = Page::with('seo')
            ->where('slug', 'home')
            ->where('status', 'published')
            ->where('is_visible', true)
            ->first();

        // 3. Priorita: Automatický režim přípravy, pokud neexistují žádné publikované stránky (bypass pro adminy)
        if (!$homePage && !$isAdmin) {
            $hasAnyContent = Page::where('status', 'published')->where('is_visible', true)->exists();
            if (!$hasAnyContent) {
                return view('public.under-construction', [
                    'branding' => $branding,
                    'branding_css' => $brandingService->getCssVariables(),
                    'title' => brand_text('Web v přípravě'),
                    'text' => brand_text('Aktuálně pro ###TEAM_NAME### připravujeme obsah. Brzy se tu objeví něco velkého!'),
                ]);
            }
        }

        return view('public.home', [
            'page' => $homePage,
        ]);
    }
}
