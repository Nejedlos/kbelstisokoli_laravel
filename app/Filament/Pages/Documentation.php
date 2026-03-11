<?php

namespace App\Filament\Pages;

use App\Services\DocumentationService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

class Documentation extends Page
{
    protected string $view = 'filament.pages.documentation';

    protected static ?string $title = 'Dokumentace';

    protected static ?string $navigationLabel = 'Dokumentace';

    protected static ?int $navigationSort = 1;

    #[Url(as: 'file')]
    public ?string $currentFile = 'README.md';

    #[Url(as: 'q')]
    public string $searchQuery = '';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.documentation');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.documentation');
    }

    public function getTitle(): string
    {
        return __('admin.navigation.pages.documentation');
    }
    public function getTree(): Collection
    {
        return app(DocumentationService::class)->getTree();
    }

    public function getFile(): ?array
    {
        return app(DocumentationService::class)->getFileContent($this->currentFile);
    }

    public function getSearchResults(): Collection
    {
        return app(DocumentationService::class)->search($this->searchQuery);
    }

    public static function getNavigationIcon(): string|HtmlString|null
    {
        return FilamentIcon::render(AppIcon::DOCUMENTATION);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super_admin') || auth()->user()?->can('access_admin');
    }
}
