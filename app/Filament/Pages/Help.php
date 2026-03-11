<?php

namespace App\Filament\Pages;

use App\Services\HelpService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;

class Help extends Page
{
    protected string $view = 'filament.pages.help';

    protected static ?string $title = 'Nápověda';

    protected static ?string $navigationLabel = 'Nápověda';

    protected static ?int $navigationSort = 2;

    #[Url(as: 'file')]
    public ?string $currentFile = null;

    #[Url(as: 'q')]
    public string $searchQuery = '';

    public static function getNavigationLabel(): string|HtmlString
    {
        return new HtmlString('
            <div class="flex items-center justify-between w-full pr-1 group/help-nav">
                <div class="flex flex-col">
                    <span class="font-black text-primary-600 dark:text-primary-400 uppercase tracking-tighter text-[0.95rem] leading-none group-hover/help-nav:translate-x-0.5 transition-transform">
                        ' . __('admin.navigation.pages.help') . '
                    </span>
                    <span class="text-[0.55rem] text-gray-500 font-bold uppercase tracking-[0.2em] leading-tight mt-1 opacity-80 group-hover/help-nav:text-primary-500 transition-colors">
                        SOS / PODPORA
                    </span>
                </div>
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-600 shadow-[0_0_8px_rgba(var(--primary-600),0.4)]"></span>
                </span>
            </div>
        ');
    }

    public function getTitle(): string
    {
        return __('admin.navigation.pages.help');
    }

    public function getTree(): Collection
    {
        return app(HelpService::class)->getTree();
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
        return new HtmlString('<i class="fa-light fa-circle-question fa-fw text-primary-600 scale-125 mr-1"></i>');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function submitContactForm(): void
    {
        // Redirect to dashboard where contact form is or just show notification
        \Filament\Notifications\Notification::make()
            ->title('Potřebujete poradit?')
            ->body('Můžete nás kontaktovat přímo přes formulář na Nástěnce.')
            ->info()
            ->send();
    }
}
