<?php

namespace App\Filament\Pages;

use App\Services\HelpService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

use function Filament\Support\original_request;

class Help extends Page
{
    protected string $view = 'filament.pages.help';

    protected static ?string $title = 'Nápověda';

    protected static ?string $navigationLabel = 'Nápověda';

    protected static ?int $navigationSort = 2;

    #[Url(as: 'file')]
    public ?string $currentFile = null;

    #[Url(as: 'cat')]
    public ?string $currentCategory = null;

    #[Url(as: 'q')]
    public string $searchQuery = '';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.help');
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('help')
                ->label(__('admin.navigation.pages.help'))
                ->extraAttributes([
                    'class' => 'fi-help-nav-item',
                    'data-help-nav-item' => 'true',
                ])
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => original_request()->routeIs(static::getNavigationItemActiveRoutePattern()))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->url(static::getNavigationUrl()),
        ];
    }

    public function getTitle(): string
    {
        return __('admin.navigation.pages.help');
    }

    public function getTree(): Collection
    {
        return app(HelpService::class)->getTree();
    }

    public function getCategoryContents(): ?Collection
    {
        if (!$this->currentCategory) {
            return null;
        }

        $tree = $this->getTree();
        $category = $tree->firstWhere('path', $this->currentCategory);

        return $category ? collect($category['children']) : null;
    }

    public function getCategoryInfo(): ?array
    {
        if (!$this->currentCategory) {
            return null;
        }

        $tree = $this->getTree();
        return $tree->firstWhere('path', $this->currentCategory);
    }

    public function getFile(): ?array
    {
        if (!$this->currentFile) {
            return null;
        }
        return app(HelpService::class)->getFileContent($this->currentFile);
    }

    public function getSearchResults(): Collection
    {
        return app(HelpService::class)->search($this->searchQuery);
    }

    public static function getNavigationIcon(): string|HtmlString|null
    {
        return new HtmlString('
            <div class="relative inline-flex items-center justify-center">
                <i class="fa-light fa-circle-question fa-fw text-primary-600 scale-125 mr-1"></i>
                <span class="absolute -top-1 -right-0.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-600 shadow-[0_0_8px_rgba(var(--primary-600),0.4)]"></span>
                </span>
            </div>
        ');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function submitContactForm(): void
    {
        // Redirect to dashboard where contact form is or just show notification
        \Filament\Notifications\Notification::make()
            ->title(__('admin.navigation.pages.help_contact_title'))
            ->body(__('admin.navigation.pages.help_contact_body'))
            ->info()
            ->send();
    }
}
