<?php

namespace App\Filament\Pages;

use App\Services\Help\HelpService;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Log;

use function Filament\Support\original_request;

class Help extends Page
{
    public function getView(): string
    {
        return 'filament.pages.help';
    }

    public function mount(): void
    {
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
        $label = __('admin.navigation.pages.help');

        return [
            NavigationItem::make('help')
                ->label($label)
                ->extraAttributes([
                    'class' => 'fi-help-nav-item',
                    'data-help-nav-item' => 'true',
                ])
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon($icon)
                ->activeIcon($icon)
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
        return $this->getHelpService()->getNavigationTree();
    }

    public function getHomeData(): array
    {
        return $this->getHelpService()->getHomeData();
    }

    public function getSearchResults(): Collection
    {
        return $this->getHelpService()->search($this->searchQuery);
    }

    /**
     * @return array|null
     */
    public function getCategoryData(): ?array
    {
        if (!$this->currentCategory) {
            return null;
        }

        return $this->getHelpService()->getCategoryData($this->currentCategory);
    }

    /**
     * @return array|null
     */
    public function getArticleData(): ?array
    {
        if (!$this->currentFile) {
            return null;
        }

        return $this->getHelpService()->getArticleData($this->currentFile);
    }

    protected ?HelpService $helpService = null;

    protected function getHelpService(): HelpService
    {
        if ($this->helpService === null) {
            $this->helpService = app(HelpService::class)
                ->forSection('admin')
                ->forAudience(auth()->user()->getRoleNames()->toArray());
        }

        return $this->helpService;
    }

    /**
     * Vrací data pro nápovědu
     */
    public function getHelpData(): ?array
    {
        if ($this->searchQuery) {
            return [
                'type' => 'search',
                'query' => $this->searchQuery,
                'results' => $this->getHelpService()->search($this->searchQuery),
            ];
        }

        if ($this->currentFile) {
            return [
                'type' => 'file',
                'file' => $this->getHelpService()->getArticleData($this->currentFile),
            ];
        }

        return [
            'type' => 'home',
            'home' => $this->getHelpService()->getHomeData(),
        ];
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
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

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access_admin') ?? false;
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
