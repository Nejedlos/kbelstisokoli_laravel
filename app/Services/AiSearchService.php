<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiSearchService
{
    public function __construct(
        protected AiIndexService $index,
        protected AiSettingsService $aiSettings
    ) {}

    /**
     * Chat s AI asistentem s podporou historie a lokálního kontextu.
     * Vrací pole: ['answer' => string, 'sources' => Collection<AiDocument>]
     */
    public function chat(array $history, string $locale = 'cs', string $section = 'frontend'): array
    {
        $settings = $this->aiSettings->getSettings();

        if (! ($settings['enabled'] ?? true)) {
            return [
                'answer' => 'AI asistent je momentálně vypnutý.',
                'sources' => collect(),
            ];
        }

        // Poslední zpráva uživatele pro vyhledávání kontextu
        $lastUserMessage = '';
        foreach (array_reverse($history) as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $lastUserMessage = $msg['content'] ?? '';
                break;
            }
        }

        $sources = collect();
        if ($lastUserMessage) {
            // Hledáme kandidáty v chunky pro danou sekci
            $sources = $this->index->search($lastUserMessage, $locale, 10, $section);
        }

        $system = $settings['system_prompt_search'] ?? $this->buildSystemPrompt($locale, $section);
        $context = $this->buildContextPrompt($sources, $locale);

        // Sestavení zpráv pro LLM
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $system."\n\n".$context];

        // Přidáme historii
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model' => $settings['default_chat_model'] ?? 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => (float) ($settings['temperature'] ?? 0.2),
            'max_tokens' => (int) ($settings['max_output_tokens'] ?? 1000),
        ];

        $start = microtime(true);
        try {
            $response = Http::timeout((int) ($settings['openai_timeout_seconds'] ?? 90))
                ->withToken($settings['openai_api_key'])
                ->baseUrl($settings['openai_base_url'] ?? 'https://api.openai.com/v1')
                ->post('/chat/completions', $payload)
                ->throw()
                ->json();

            $latency = (int) ((microtime(true) - $start) * 1000);
            $answer = Arr::get($response, 'choices.0.message.content');
            $usage = Arr::get($response, 'usage');

            $result = [
                'answer' => (string) $answer,
                'sources' => $sources,
            ];

            // Logování úspěchu
            $this->aiSettings->logRequest([
                'context' => $section.'_ai_chat',
                'model' => $payload['model'],
                'status' => 'success',
                'prompt_preview' => Str::limit($lastUserMessage, 255),
                'response_preview' => Str::limit($answer, 255),
                'latency_ms' => $latency,
                'token_usage' => $usage,
            ]);

            return $result;

        } catch (\Throwable $e) {
            $latency = (int) ((microtime(true) - $start) * 1000);

            // Logování chyby
            $this->aiSettings->logRequest([
                'context' => $section.'_ai_chat',
                'model' => $payload['model'] ?? 'unknown',
                'status' => 'error',
                'prompt_preview' => Str::limit($lastUserMessage, 255),
                'latency_ms' => $latency,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Položí dotaz AI asistentovi s využitím lokálního kontextu (zpětná kompatibilita).
     */
    public function ask(string $query, string $locale = 'cs', string $section = 'frontend'): array
    {
        return $this->chat([['role' => 'user', 'content' => $query]], $locale, $section);
    }

    private function buildSystemPrompt(string $locale, string $section = 'frontend'): string
    {
        $langInstruction = $locale === 'cs'
            ? 'Odpovídej česky, stručně a jasně. Používej informace z poskytnutého kontextu.'
            : 'Respond in English, briefly and clearly. Use information from the provided context.';

        $sectionName = match ($section) {
            'admin' => 'Administrace (Filament)',
            'member' => 'Členská sekce (Hráči/Členové)',
            default => 'Veřejný web (Frontend)',
        };

        return trim(
            $langInstruction."\n".
            "Jsi asistent pro web basketbalového klubu Kbelští sokoli. Právě se nacházíš v sekci: {$sectionName}."."\n".
            'Tvým úkolem je pomáhat uživatelům s informacemi a navigací VÝHRADNĚ v rámci této sekce a dostupných dokumentů.'."\n".
            'DŮLEŽITÉ: Pokud je v kontextu u zdroje uvedena URL adresa, VŽDY ji zahrň do své odpovědi jako Markdown odkaz.'."\n".
            'Pokud v kontextu vidíš návody z dokumentace (typ documentation.resource), řiď se jimi při radách uživateli.'."\n".
            'NIKDY nevymýšlej URL adresy, které nejsou v poskytnutém kontextu.'."\n".
            'Pokud uživatel položí dotaz, na který v kontextu není odpověď, slušně ho odkaž na kontakty klubu.'
        );
    }

    private function buildContextPrompt($sources, string $locale): string
    {
        $intro = $locale === 'cs'
            ? 'Následuje lokální kontext ze stránek projektu (výběr nejrelevantnějších úryvků):'
            : 'Below is the local context from the project pages (selected relevant snippets):';

        $chunks = $sources->map(function ($doc, $i) {
            $snippet = $doc->summary ? 'Shrnutí: '.$doc->summary."\nObsah: ".Str::limit($doc->content, 600) : Str::limit($doc->content, 800);
            $urlInfo = $doc->url ? ' (URL: '.$doc->url.')' : '';

            return ($i + 1).') ['.$doc->type.'] '.$doc->title.$urlInfo."\n".$snippet;
        })->implode("\n\n");

        return $intro."\n\n".$chunks;
    }
}
