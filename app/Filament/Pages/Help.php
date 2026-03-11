<?php

namespace App\Filament\Pages;

use App\Services\Help\HelpService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

use function Filament\Support\original_request;
use Illuminate\Support\Facades\Log;

class Help extends Page
{
    public function getView(): string
    {
        $version = config('help.version', 'v2');
        return "filament.pages.help-{$version}";
    }

    public function mount(): void
    {
        $version = config('help.version', 'v2');

        // Pokud je v1, musíme se ujistit, že service má nastavenou locale
        if ($version === 'v1') {
            app(\App\Services\HelpService::class)->setPathByLocale(app()->getLocale());
        }
    }

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
        $icon = static::getNavigationIcon();
        $iconHtml = ($icon instanceof HtmlString) ? $icon->toHtml() : $icon;
        $label = __('admin.navigation.pages.help');

        return [
            NavigationItem::make('help')
                ->label(new HtmlString('<div class="flex items-center gap-1.5">' . $iconHtml . ' <span>' . $label . '</span></div>'))
                ->extraAttributes([
                    'class' => 'fi-help-nav-item',
                    'data-help-nav-item' => 'true',
                ])
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(null)
                ->activeIcon(null)
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

    public function render(): \Illuminate\Contracts\View\View
    {
        try {
            return parent::render();
        } catch (\Throwable $e) {
            Log::error("Help Page Render Error: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getTree(): Collection
    {
        $version = config('help.version', 'v2');
        if ($version === 'v1') {
            return $this->getHelpService()->getTree();
        }
        return $this->getHelpService()->getNavigationTree();
    }

    public function getHomeData(): array
    {
        return $this->getHelpService()->getHomeData();
    }

    public function getCategoryData(): ?array
    {
        if (!$this->currentCategory) {
            return null;
        }

        return $this->getHelpService()->getCategoryData($this->currentCategory);
    }

    public function getArticleData(): ?array
    {
        if (!$this->currentFile) {
            return null;
        }

        return $this->getHelpService()->getArticleData($this->currentFile);
    }

    public function getSearchResults(): Collection
    {
        return $this->getHelpService()->search($this->searchQuery);
    }

    /**
     * Legacy v1: Vrací info o aktuální kategorii
     */
    public function getCategoryInfo(): ?array
    {
        if (config('help.version', 'v2') !== 'v1' || !$this->currentCategory) {
            return null;
        }

        $tree = $this->getTree();
        return $tree->firstWhere('path', $this->currentCategory);
    }

    /**
     * Legacy v1: Vrací obsah aktuálního souboru
     */
    public function getFile(): ?array
    {
        if (config('help.version', 'v2') !== 'v1' || !$this->currentFile) {
            return null;
        }

        return $this->getHelpService()->getFileContent($this->currentFile);
    }

    protected ?\App\Services\Help\HelpService $helpServiceV2 = null;

    protected function getHelpService()
    {
        $version = config('help.version', 'v2');

        if ($version === 'v1') {
            return app(\App\Services\HelpService::class);
        }

        if ($this->helpServiceV2 === null) {
            $this->helpServiceV2 = app(\App\Services\Help\HelpService::class)->forAudience(auth()->user()->getRoleNames()->toArray());
        }

        return $this->helpServiceV2;
    }

    /**
     * Vrací data pro v1 systém (legacy markdown browser)
     */
    public function getHelpData(): ?array
    {
        if (config('help.version', 'v2') !== 'v1') {
            return null;
        }

        if ($this->searchQuery) {
            return [
                'type' => 'search',
                'query' => $this->searchQuery,
                'results' => $this->getHelpService()->search($this->searchQuery),
            ];
        }

        if ($this->currentFile) {
            $content = $this->getHelpService()->getFileContent($this->currentFile);
            if ($content) {
                return [
                    'type' => 'file',
                    'content' => $content,
                ];
            }
        }

        return [
            'type' => 'tree',
            'tree' => $this->getHelpService()->getTree(),
        ];
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
