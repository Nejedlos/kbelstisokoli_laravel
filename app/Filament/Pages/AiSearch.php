<?php

namespace App\Filament\Pages;

use App\Services\AiSearchService;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class AiSearch extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'fal-sparkles';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.ai_search.navigation_label');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.ai_search.title');
    }

    protected string $view = 'filament.pages.ai-search';

    protected static bool $shouldRegisterNavigation = false;

    public string $query = '';

    public string $answer = '';

    public array $messages = [];

    public $sources;

    public bool $isProcessing = false;

    public function mount(Request $request): void
    {
        $this->query = (string) $request->input('q', '');
        $this->sources = collect();
    }

    public function askAi(): void
    {
        if (mb_strlen($this->query) < 2) {
            return;
        }

        $this->isProcessing = true;

        // Přidáme zprávu uživatele do historie
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->query,
            'time' => now()->format('H:i'),
        ];

        $userQuery = $this->query;
        $this->query = ''; // Vyčistíme input

        try {
            $ai = app(AiSearchService::class);
            $locale = app()->getLocale();

            $result = $ai->chat($this->messages, $locale, 'admin');

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $result['answer'] ?? '',
                'time' => now()->format('H:i'),
            ];

            $this->answer = $result['answer'] ?? '';
            $this->sources = $result['sources'] ?? collect();
        } catch (\Throwable $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => __('admin.ai_search.error_message') . ' ('.$e->getMessage().')',
                'time' => now()->format('H:i'),
            ];
        }

        $this->isProcessing = false;
    }
}
