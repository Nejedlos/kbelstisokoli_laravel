<?php

namespace App\Services;

use App\Models\AiDocument;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiIndexService
{
    public function __construct(
        protected AiSettingsService $aiSettings
    ) {}

    /**
     * Provede kompletní reindex zdrojů (Blade views a Markdown v docs).
     */
    public function reindex(string $locale = 'cs', bool $fresh = false): int
    {
        if ($fresh) {
            AiDocument::query()->where('locale', $locale)->delete();
        }

        $count = 0;
        $count += $this->indexNavigation($locale);
        $count += $this->indexFilament($locale);
        $count += $this->indexMemberSection($locale);
        $count += $this->indexFrontend($locale);

        return $count;
    }

    /**
     * Obohatí dokument o AI generované shrnutí a klíčová slova.
     */
    public function enrichWithAi(AiDocument $doc): bool
    {
        $settings = $this->aiSettings->getSettings();
        if (! ($settings['enabled'] ?? true)) {
            return false;
        }

        $prompt = "Analyzuj následující metadata a obsah stránky webu basketbalového klubu Kbelští sokoli.
Vygeneruj:
1. Stručné a výstižné shrnutí (summary) v jedné větě, které popisuje, co uživatel na této stránce najde nebo jakou akci tam může provést. Toto shrnutí se zobrazí ve výsledcích vyhledávání.
2. Seznam klíčových slov a synonym, které by uživatelé mohli hledat.

Formát odpovědi (JSON):
{
  \"summary\": \"Zde bude shrnutí...\",
  \"keywords\": [\"slovo1\", \"slovo2\", ...]
}

Metadata stránky:
Název: {$doc->title}
Typ: {$doc->type}
Původní obsah (pro analýzu): ".Str::limit($doc->content, 2500);

        try {
            $response = Http::timeout(60)
                ->withToken($settings['openai_api_key'])
                ->baseUrl($settings['openai_base_url'] ?? 'https://api.openai.com/v1')
                ->post('/chat/completions', [
                    'model' => $settings['fast_model'] ?? 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Jsi expert na UX a SEO pro sportovní klubové weby. Vracej pouze validní JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                ])->json();

            $data = json_decode($response['choices'][0]['message']['content'] ?? '{}', true);

            if (!empty($data)) {
                $doc->update([
                    'summary' => $data['summary'] ?? $doc->summary,
                    'keywords' => array_unique($data['keywords'] ?? ($doc->keywords ?: [])),
                ]);

                return true;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Enrichment Error: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Uloží nebo aktualizuje dokument s kontrolou checksumu.
     */
    private function updateOrCreateDocument(array $data): AiDocument
    {
        $existing = AiDocument::where('source', $data['source'])
            ->where('locale', $data['locale'] ?? 'cs')
            ->where('type', $data['type'])
            ->first();

        if ($existing) {
            if ($existing->checksum !== $data['checksum']) {
                $existing->update($data);
                // Pokud se změnil obsah, vymažeme keywords, aby se znovu vygenerovaly (nebo je můžeme nechat a jen flagovat)
                $existing->update(['keywords' => null]);
            }

            return $existing;
        }

        return AiDocument::create($data);
    }

    private function indexNavigation(string $locale): int
    {
        $count = 0;
        try {
            $panel = \Filament\Facades\Filament::getPanel('admin');
            \Filament\Facades\Filament::setCurrentPanel($panel);
            $navigation = $panel->getNavigation();

            foreach ($navigation as $group) {
                $groupLabel = $group->getLabel();
                foreach ($group->getItems() as $item) {
                    $label = $item->getLabel();
                    $url = $item->getUrl();

                    if (! $label || ! $url) {
                        continue;
                    }

                    $content = "Položka menu: {$label}. Nachází se v sekci administrace: {$groupLabel}. ";
                    $content .= "Cíl: Administrační stránka nebo nástroj pro správu klubu. ";
                    $content .= "Klíčová slova z navigace: {$groupLabel}, {$label}.";

                    $this->updateOrCreateDocument([
                        'type' => 'admin.navigation',
                        'source' => 'navigation',
                        'title' => (string) $label,
                        'url' => $url,
                        'locale' => $locale,
                        'content' => $content,
                        'checksum' => hash('sha256', $content.$url),
                    ]);
                    $count++;
                }
            }
        } catch (\Throwable $e) {
            // V CLI může getNavigation selhat, pokud nejsou správně zinicializovány všechny služby
            // V takovém případě aspoň zalogujeme nebo ignorujeme
        }

        return $count;
    }

    private function indexFilament(string $locale): int
    {
        $count = 0;

        // Indexování stránek (Pages)
        $pages = \Filament\Facades\Filament::getPanel('admin')->getPages();
        foreach ($pages as $pageClass) {
            try {
                // Přeskočíme naši AI vyhledávací stránku a dashboard (ten má málo obsahu v třídě)
                if (str_contains($pageClass, 'AiSearch') || str_contains($pageClass, 'Dashboard')) {
                    continue;
                }

                $page = app($pageClass);
                $title = $page->getTitle() ?: $pageClass;
                $url = $pageClass::getUrl();

                // Zkusíme získat informaci o umístění v menu
                $group = null;
                if (method_exists($pageClass, 'getNavigationGroup')) {
                    $group = (string) $pageClass::getNavigationGroup();
                }

                if (! $group && property_exists($pageClass, 'navigationGroup')) {
                    $group = (string) $pageClass::$navigationGroup;
                }

                $content = '';

                // EXTRAKCE ZE SCHÉMATU (Formuláře na stránkách)
                try {
                    if (method_exists($page, 'form')) {
                        $schema = app(\Filament\Schemas\Schema::class);
                        // Některé stránky mohou vyžadovat inicializaci stavu, zkusíme to bezpečně
                        $page->form($schema);
                        $schemaTexts = $this->extractTextsFromSchema($schema);
                        if ($schemaTexts) {
                            $content .= 'Obsahuje pole a sekce: '.$schemaTexts.'. ';
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignorujeme chyby při extrakci schématu
                }

                // Zkusíme najít Blade soubor pro tuto stránku
                try {
                    $reflection = new \ReflectionClass($pageClass);
                    if ($reflection->hasProperty('view')) {
                        $viewProperty = $reflection->getProperty('view');
                        $viewProperty->setAccessible(true);
                        $viewName = $viewProperty->getValue($page);

                        if ($viewName && view()->exists($viewName)) {
                            $viewPath = view($viewName)->getPath();
                            $raw = File::get($viewPath);
                            $content .= $this->sanitizeBlade($raw);
                        }
                    }
                } catch (\Throwable $e) {
                }

                $navigationInfo = $group ? "Sekce administrace: {$group}. " : '';
                $typeInfo = "Typ: Administrační stránka (Page). ";

                $this->updateOrCreateDocument([
                    'type' => 'admin.page',
                    'source' => $pageClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo.$typeInfo.($content ?: 'Administrační stránka '.$title),
                    'checksum' => hash('sha256', $content.$url.$group),
                ]);
                $count++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Indexování resources
        $resources = \Filament\Facades\Filament::getPanel('admin')->getResources();
        foreach ($resources as $resourceClass) {
            try {
                $title = $resourceClass::getNavigationLabel();
                $url = $resourceClass::getUrl();
                $group = $resourceClass::getNavigationGroup();

                $content = "Správa sekce {$title}. Zde můžete přidávat, upravovat nebo mazat záznamy. ";

                // Extrakce formuláře
                try {
                    $schema = app(\Filament\Schemas\Schema::class);
                    $resourceClass::form($schema);
                    $schemaTexts = $this->extractTextsFromSchema($schema);
                    if ($schemaTexts) {
                        $content .= 'Formulář obsahuje: '.$schemaTexts.'. ';
                    }
                } catch (\Throwable $e) {
                }

                // Extrakce tabulky
                try {
                    // Pro tabulku potřebujeme instanci, která může být složitější na vytvoření v CLI
                    // Ale zkusíme alespoň základní extrakci pokud configure metoda existuje v oddělené třídě
                    // nebo pokud můžeme zavolat table() na resource.
                    $table = \Filament\Tables\Table::make(app(\Filament\Pages\Dashboard::class));
                    $resourceClass::table($table);
                    $tableTexts = $this->extractTextsFromTable($table);
                    if ($tableTexts) {
                        $content .= 'Tabulka přehledu obsahuje: '.$tableTexts.'. ';
                    }
                } catch (\Throwable $e) {
                }

                $navigationInfo = $group ? "Sekce administrace: {$group}. " : '';
                $typeInfo = "Typ: Správa dat (Resource). ";

                $this->updateOrCreateDocument([
                    'type' => 'admin.resource',
                    'source' => $resourceClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo.$typeInfo.$content,
                    'checksum' => hash('sha256', $resourceClass.$url.$group.$content),
                ]);
                $count++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $count;
    }

    private function extractTextsFromSchema($schema): string
    {
        if (! $schema) {
            return '';
        }
        $texts = [];
        $components = method_exists($schema, 'getComponents') ? $schema->getComponents() : [];
        $this->collectComponentTexts($components, $texts);

        return implode('; ', array_filter(array_unique($texts)));
    }

    private function collectComponentTexts(array $components, array &$texts): void
    {
        foreach ($components as $component) {
            if (! is_object($component)) {
                continue;
            }

            // Label
            if (method_exists($component, 'getLabel')) {
                $label = $component->getLabel();
                if ($label instanceof \Illuminate\Contracts\Support\Htmlable) {
                    $label = $label->toHtml();
                }
                $label = strip_tags((string) $label);
                if ($label && ! str_contains($label, 'filament::')) {
                    $texts[] = $label;
                }
            }

            // Heading / Title (pro sekce/karty)
            foreach (['getHeading', 'getTitle', 'getLabel'] as $method) {
                if (method_exists($component, $method)) {
                    $val = $component->$method();
                    if ($val instanceof \Illuminate\Contracts\Support\Htmlable) {
                        $val = $val->toHtml();
                    }
                    $val = strip_tags((string) $val);
                    if ($val && ! str_contains($val, 'filament::')) {
                        $texts[] = $val;
                    }
                }
            }

            // Placeholder
            if (method_exists($component, 'getPlaceholder')) {
                $placeholder = strip_tags((string) $component->getPlaceholder());
                if ($placeholder) {
                    $texts[] = $placeholder;
                }
            }

            // Description / Helper Text
            foreach (['getDescription', 'getHelperText'] as $method) {
                if (method_exists($component, $method)) {
                    $val = $component->$method();
                    if ($val instanceof \Illuminate\Contracts\Support\Htmlable) {
                        $val = $val->toHtml();
                    }
                    $val = strip_tags((string) $val);
                    if ($val) {
                        $texts[] = $val;
                    }
                }
            }

            // Rekurze
            $children = [];
            if (method_exists($component, 'getChildComponents')) {
                $children = $component->getChildComponents();
            } elseif (method_exists($component, 'getComponents')) {
                $children = $component->getComponents();
            }

            if (! empty($children)) {
                $this->collectComponentTexts($children, $texts);
            }
        }
    }

    private function extractTextsFromTable($table): string
    {
        if (! $table) {
            return '';
        }
        $texts = [];

        // Sloupce
        if (method_exists($table, 'getColumns')) {
            foreach ($table->getColumns() as $column) {
                if (method_exists($column, 'getLabel')) {
                    $label = strip_tags((string) $column->getLabel());
                    if ($label && ! str_contains($label, 'filament::')) {
                        $texts[] = $label;
                    }
                }
            }
        }

        // Filtry
        if (method_exists($table, 'getFilters')) {
            foreach ($table->getFilters() as $filter) {
                if (method_exists($filter, 'getLabel')) {
                    $label = strip_tags((string) $filter->getLabel());
                    if ($label) {
                        $texts[] = $label;
                    }
                }
            }
        }

        return implode(', ', array_filter(array_unique($texts)));
    }

    private function indexMemberSection(string $locale): int
    {
        $count = 0;
        $routes = [
            'member.dashboard' => ['title' => 'Nástěnka člena', 'view' => 'member.dashboard'],
            'member.attendance.index' => ['title' => 'Program a docházka', 'view' => 'member.attendance.index'],
            'member.profile.edit' => ['title' => 'Můj profil', 'view' => 'member.profile.edit'],
            'member.economy.index' => ['title' => 'Moje platby a ekonomika', 'view' => 'member.economy.index'],
            'member.notifications.index' => ['title' => 'Notifikace', 'view' => 'member.notifications.index'],
            'member.teams.index' => ['title' => 'Týmové přehledy (pro trenéry)', 'view' => 'member.teams.index'],
        ];

        foreach ($routes as $routeName => $info) {
            try {
                $url = route($routeName);
                $content = '';
                if (view()->exists($info['view'])) {
                    $viewPath = view($info['view'])->getPath();
                    $raw = File::get($viewPath);
                    $content = $this->sanitizeBlade($raw);
                }

                $this->updateOrCreateDocument([
                    'type' => 'member.page',
                    'source' => $routeName,
                    'title' => $info['title'],
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $content ?: $info['title'],
                    'checksum' => hash('sha256', $content.$url),
                ]);
                $count++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $count;
    }

    private function indexFrontend(string $locale): int
    {
        $count = 0;

        // 1. Indexace stránek (Pages)
        $pages = Page::query()
            ->where('is_visible', true)
            ->where('status', 'published')
            ->get();

        foreach ($pages as $page) {
            $title = $page->getTranslation('title', $locale);
            $content = $page->getTranslation('content', $locale);

            if (is_array($content)) {
                $content = $this->extractStringsFromBlocks($content);
            }

            $url = $page->slug === 'home' ? route('public.home') : route('public.pages.show', $page->slug);

            $this->updateOrCreateDocument([
                'type' => 'frontend.page',
                'source' => 'page:'.$page->id,
                'title' => $title,
                'url' => $url,
                'locale' => $locale,
                'content' => strip_tags((string) $content),
                'checksum' => hash('sha256', $content.$url.$title),
            ]);
            $count++;
        }

        // 2. Indexace aktualit (Posts)
        $posts = Post::query()
            ->where('is_visible', true)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->get();

        foreach ($posts as $post) {
            $title = $post->getTranslation('title', $locale);
            $excerpt = $post->getTranslation('excerpt', $locale);
            $content = $post->getTranslation('content', $locale);

            $fullContent = $excerpt.' '.$content;

            $url = route('public.news.show', $post->slug);

            $this->updateOrCreateDocument([
                'type' => 'frontend.post',
                'source' => 'post:'.$post->id,
                'title' => $title,
                'url' => $url,
                'locale' => $locale,
                'content' => strip_tags((string) $fullContent),
                'metadata' => [
                    'image' => $post->featured_image,
                ],
                'checksum' => hash('sha256', $fullContent.$url.$title.$post->featured_image),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Pomocná metoda pro extrakci textu z blokového editoru (Filament Builder/Fabricator)
     */
    private function extractStringsFromBlocks(array $blocks): string
    {
        $texts = [];
        foreach ($blocks as $block) {
            if (isset($block['data']) && is_array($block['data'])) {
                $this->collectStringsRecursive($block['data'], $texts);
            }
        }

        return implode(' ', array_filter($texts));
    }

    private function collectStringsRecursive(array $data, array &$texts): void
    {
        foreach ($data as $value) {
            if (is_string($value)) {
                $texts[] = $value;
            } elseif (is_array($value)) {
                $this->collectStringsRecursive($value, $texts);
            }
        }
    }

    public function search(string $query, string $locale = 'cs', int $limit = 8, ?string $context = null)
    {
        $q = Str::lower($query);

        // Jednoduché LIKE vyhledávání + heuristické seřazení v PHP
        $queryBuilder = AiDocument::query()
            ->where('locale', $locale);

        if ($context === 'admin') {
            $queryBuilder->where('type', 'like', 'admin.%');
        } elseif ($context === 'member') {
            $queryBuilder->where('type', 'like', 'member.%');
        } elseif ($context === 'frontend') {
            $queryBuilder->where('type', 'like', 'frontend.%');
        }

        $candidates = $queryBuilder->where(function ($w) use ($q) {
            $w->whereRaw('LOWER(title) LIKE ?', ['%'.$q.'%'])
                ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$q.'%'])
                ->orWhereRaw('LOWER(keywords) LIKE ?', ['%'.$q.'%']);
        })
            ->limit(100)
            ->get();

        $scored = $candidates->map(function (AiDocument $doc) use ($q) {
            $title = Str::lower($doc->title);
            $content = Str::lower(Str::limit($doc->content, 2000, ''));
            $keywords = $doc->keywords ?? [];

            $score = 0;

            // Shoda v titulku (velmi vysoká váha)
            if (Str::contains($title, $q)) {
                $score += 50;
                if ($title === $q) {
                    $score += 50;
                } // Přesná shoda
            }

            // Shoda v klíčových slovech (vysoká váha)
            foreach ($keywords as $keyword) {
                $keywordLower = Str::lower($keyword);
                if ($keywordLower === $q) {
                    $score += 40;
                } elseif (Str::contains($keywordLower, $q)) {
                    $score += 10;
                }
            }

            // Shoda v obsahu (nižší váha)
            $contentMatchCount = substr_count($content, $q);
            $score += min($contentMatchCount * 2, 20); // Zastropujeme, aby dlouhé texty nepřebily vše

            // Bonus za pozici v obsahu
            $pos = strpos($content, $q);
            if ($pos !== false && $pos < 500) {
                $score += (500 - $pos) / 50;
            }

            // Typový boost (upřednostnit admin sekci)
            if (str_starts_with($doc->type, 'admin.')) {
                $score += 30;
            }

            return [$doc, $score];
        })->sortByDesc(fn ($pair) => $pair[1])
            ->take($limit)
            ->map(fn ($pair) => $pair[0])
            ->values();

        return $scored;
    }

    private function sanitizeBlade(string $raw): string
    {
        // Odstranit Blade direktivy a proměnné
        $text = preg_replace('/@\w+(\(.*?\))?/s', ' ', $raw) ?? $raw;
        $text = preg_replace('/{{.*?}}/s', ' ', $text) ?? $text;
        $text = preg_replace('/{\!\!.*?\!\!}/s', ' ', $text) ?? $text;
        // Odstranit HTML tagy
        $text = strip_tags($text);

        // Komprimovat whitespace
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }
}
