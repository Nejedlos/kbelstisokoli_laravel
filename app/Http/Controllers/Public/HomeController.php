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

        // 1. Priorita: Manuálně zapnutý režim přípravy v administraci
        if (data_get($branding, 'maintenance_mode')) {
            return view('public.under-construction', [
                'branding' => $branding,
                'branding_css' => $brandingService->getCssVariables(),
                'title' => data_get($branding, 'maintenance_title'),
                'text' => data_get($branding, 'maintenance_text'),
            ]);
        }

        // 2. Priorita: Automatický režim přípravy, pokud neexistují žádné publikované stránky
        $hasContent = Page::where('status', 'published')->where('is_visible', true)->exists();

        if (!$hasContent) {
            return view('public.under-construction', [
                'branding' => $branding,
                'branding_css' => $brandingService->getCssVariables(),
                'title' => 'Web v přípravě',
                'text' => 'Aktuálně pro vás připravujeme obsah. Brzy se tu objeví něco velkého!',
            ]);
        }

        return view('public.home');
    }
}
