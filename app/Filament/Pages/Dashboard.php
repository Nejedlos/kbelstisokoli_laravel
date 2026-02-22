<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Services\BackendSearchService;
use Filament\GlobalSearch\GlobalSearchResult;

class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.dashboard');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.navigation.pages.dashboard');
    }

    /**
     * Rozšíření globálního vyhledávání o AI navigaci ve Filamentu.
     */
    public static function getGlobalSearchResults(string $search): array
    {
        /** @var BackendSearchService $backendSearch */
        $backendSearch = app(BackendSearchService::class);
        $results = $backendSearch->search($search, 'admin');

        $output = [];
        foreach ($results as $result) {
            $output[] = new GlobalSearchResult(
                title: $result->title,
                url: $result->url,
                details: [
                    'AI Návrh' => $result->snippet
                ],
            );
        }

        return $output;
    }
}
