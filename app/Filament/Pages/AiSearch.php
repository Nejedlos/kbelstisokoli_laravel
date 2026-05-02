<?php

namespace App\Filament\Pages;

use App\Services\AiSearchService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class AiSearch extends Page
{
    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::AI);
    }

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

        if (mb_strlen($this->query) >= 2) {
            $this->askAi();
        }
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

    public function getLocalizedValue(mixed $value): string
    {
        if (is_array($value)) {
            $locale = app()->getLocale();

            return $value[$locale] ?? $value['cs'] ?? array_values($value)[0] ?? '';
        }

        if (is_string($value)) {
            if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $locale = app()->getLocale();

                    return $decoded[$locale] ?? $decoded['cs'] ?? array_values($decoded)[0] ?? '';
                }
            }

            return $value;
        }

        return (string) ($value ?? '');
    }
}
