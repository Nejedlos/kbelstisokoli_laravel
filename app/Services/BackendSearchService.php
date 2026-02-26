<?php

namespace App\Services;

use App\DataTransferObjects\SearchResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BackendSearchService
{
    protected array $searchTargets = [];

    public function __construct(
        protected AiIndexService $aiIndexService,
        protected AiSettingsService $aiSettingsService
    ) {
        $this->initializeTargets();
    }

    /**
     * Inicializuje seznam možných cílů pro vyhledávání (akce, zdroje, stránky).
     */
    protected function initializeTargets(): void
    {
        // Administrace (Filament Resources) - Mapování pro AI, co který resource dělá
        $this->searchTargets['admin'] = [
            [
                'title' => 'Správa uživatelů',
                'description' => 'Seznam uživatelů, editace profilů, reset hesla, přidávání nových členů.',
                'url' => '/admin/users',
                'permission' => 'view_any_user',
                'keywords' => ['členové', 'lidé', 'registrace', 'heslo']
            ],
            [
                'title' => 'Správa stránek (CMS)',
                'description' => 'Editace textů na webu, tvorba nových stránek, správa bloků a obsahu.',
                'url' => '/admin/pages',
                'permission' => 'view_any_page',
                'keywords' => ['web', 'obsah', 'texty', 'cms']
            ],
            [
                'title' => 'Aktuality a novinky',
                'description' => 'Psaní článků, zprávy z klubu, blogové příspěvky.',
                'url' => '/admin/posts',
                'permission' => 'view_any_post',
                'keywords' => ['blog', 'články', 'zprávy', 'novinky']
            ],
            [
                'title' => 'Finance a platby',
                'description' => 'Přehled plateb, členské příspěvky, bankovní výpisy, dlužníci.',
                'url' => '/admin/finance-payments',
                'permission' => 'view_any_finance_payment',
                'keywords' => ['peníze', 'dluhy', 'příspěvky', 'banka', 'platby']
            ],
            [
                'title' => 'Tréninky a rozvrh',
                'description' => 'Správa tréninkových jednotek, docházka, místa konání.',
                'url' => '/admin/trainings',
                'permission' => 'view_any_training',
                'keywords' => ['kalendář', 'rozvrh', 'hala', 'tělocvična', 'docházka']
            ],
            [
                'title' => 'Zápasy a výsledky',
                'description' => 'Plánování utkání, statistiky zápasů, soupeři, skóre.',
                'url' => '/admin/basketball-matches',
                'permission' => 'view_any_basketball_match',
                'keywords' => ['utkání', 'výsledky', 'basket', 'skóre', 'turnaje']
            ],
            [
                'title' => 'Nastavení brandingu',
                'description' => 'Změna loga, barev webu, názvu klubu a sloganů.',
                'url' => '/admin/branding-settings',
                'permission' => 'manage_branding',
                'keywords' => ['barvy', 'logo', 'design', 'vzhled']
            ],
            [
                'title' => 'Role a oprávnění',
                'description' => 'Nastavení kdo co může dělat, přístupy do adminu, definice rolí.',
                'url' => '/admin/roles',
                'permission' => 'view_any_role',
                'keywords' => ['přístupy', 'práva', 'admini', 'role']
            ],
            // ... další targety lze přidat
        ];

        // Členská sekce
        $this->searchTargets['member'] = [
            [
                'title' => 'Můj program a docházka',
                'description' => 'Přehled nadcházejících tréninků a zápasů, omluvenky, historie docházky.',
                'url' => '/member/program',
                'keywords' => ['trénink', 'omluva', 'zápas', 'kdy mám trénink']
            ],
            [
                'title' => 'Moje platby',
                'description' => 'Stav mých členských příspěvků, QR kódy pro platbu, historie plateb.',
                'url' => '/member/platby',
                'keywords' => ['platba', 'příspěvky', 'peníze', 'qr kód', 'dluh']
            ],
            [
                'title' => 'Můj profil',
                'description' => 'Změna osobních údajů, kontakty, změna hesla.',
                'url' => '/member/profil',
                'keywords' => ['nastavení', 'profil', 'údaje', 'heslo']
            ],
            [
                'title' => 'Týmové přehledy',
                'description' => 'Soupisky týmů, kontakty na spoluhráče a trenéry.',
                'url' => '/member/tymove-prehledy',
                'keywords' => ['tým', 'soupiska', 'kontakty', 'spoluhráči']
            ],
            [
                'title' => 'Notifikace',
                'description' => 'Upozornění na nové zprávy, platby nebo změny v programu.',
                'url' => '/member/notifikace',
                'keywords' => ['zprávy', 'upozornění', 'novinky']
            ],
        ];
    }

    /**
     * Vyhledá nejvhodnější cíle na základě dotazu.
     */
    public function search(string $query, string $context = 'admin'): Collection
    {
        if (empty($query) || strlen($query) < 3) {
            return collect();
        }

        $locale = app()->getLocale();
        $aiResults = $this->aiIndexService->search($query, $locale, 10, $context);

        if ($aiResults->isNotEmpty()) {
            return $aiResults->map(function ($doc) {
                return new SearchResult(
                    title: $doc->title,
                    snippet: $doc->summary ?: mb_substr(strip_tags($doc->content), 0, 160) . '...',
                    url: $doc->url ?? '#',
                    type: $this->getDocTypeLabel($doc->type)
                );
            });
        }

        // Fallback na statické targety, pokud AI index nic nenašel
        return $this->fallbackSearch($query, $context);
    }

    protected function getDocTypeLabel(string $type): string
    {
        return match ($type) {
            'admin.resource' => __('admin.search.categories.resources'),
            'member.resource' => __('admin.search.categories.pages'), // Nebo vhodnější label
            default => __('admin.search.categories.other'),
        };
    }

    /**
     * Sémantické vyhledávání pomocí OpenAI.
     */
    protected function aiSearch(string $query, string $context): Collection
    {
        $targets = $this->getAvailableTargets($context);

        // Připravíme kontext pro AI
        $targetsJson = json_encode($targets, JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Jsi navigační asistent pro sportovní klubový systém. Na základě dotazu uživatele vyber ze seznamu cílů ty nejrelevantnější (max 3). Vrať pouze JSON pole indexů vybraných cílů ze seznamu. Pokud nic neodpovídá, vrať prázdné pole [].\n\nSeznam cílů:\n" . $targetsJson
                        ],
                        [
                            'role' => 'user',
                            'content' => $query
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'];
                $selectedIndexes = json_decode($content, true)['indexes'] ?? [];

                $results = collect();
                foreach ($selectedIndexes as $index) {
                    if (isset($targets[$index])) {
                        $target = $targets[$index];
                        $results->push(new SearchResult(
                            title: $target['title'],
                            snippet: $target['description'],
                            url: $target['url'],
                            type: __('search.types.navigation')
                        ));
                    }
                }

                return $results;
            }
        } catch (\Exception $e) {
            Log::error('AI Search failed: ' . $e->getMessage());
        }

        return $this->fallbackSearch($query, $context);
    }

    /**
     * Jednoduché vyhledávání v klíčových slovech a popisech.
     */
    protected function fallbackSearch(string $query, string $context): Collection
    {
        $targets = $this->getAvailableTargets($context);
        $results = collect();
        $queryLower = mb_strtolower($query);

        foreach ($targets as $target) {
            $score = 0;

            if (Str::contains(mb_strtolower($target['title']), $queryLower)) $score += 10;
            if (Str::contains(mb_strtolower($target['description']), $queryLower)) $score += 5;

            foreach ($target['keywords'] ?? [] as $keyword) {
                if (Str::contains(mb_strtolower($keyword), $queryLower)) $score += 8;
            }

            if ($score > 0) {
                $results->push([
                    'score' => $score,
                    'result' => new SearchResult(
                        title: $target['title'],
                        snippet: $target['description'],
                        url: $target['url'],
                        type: __('search.types.navigation')
                    )
                ]);
            }
        }

        return $results->sortByDesc('score')->map(fn($item) => $item['result'])->values();
    }

    /**
     * Vrátí cíle, ke kterým má aktuální uživatel přístup.
     */
    protected function getAvailableTargets(string $context): array
    {
        $targets = $this->searchTargets[$context] ?? [];
        $user = auth()->user();

        if (!$user) return [];

        return array_values(array_filter($targets, function ($target) use ($user) {
            if (!isset($target['permission'])) return true;
            return $user->can($target['permission']);
        }));
    }
}
