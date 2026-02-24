<?php

namespace App\Services;

use App\Models\AiDocument;
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
        $count += $this->indexDocs(base_path('docs'), $locale);

        return $count;
    }

    /**
     * Generuje klíčová slova a synonyma pro dokument pomocí AI.
     */
    public function enrichWithAi(AiDocument $doc): bool
    {
        $settings = $this->aiSettings->getSettings();
        if (!($settings['enabled'] ?? true)) return false;

        $prompt = "Analyzuj následující obsah stránky a vygeneruj seznam klíčových slov a synonym (v jazyce {$doc->locale}), které by uživatelé mohli hledat, aby tuto stránku našli.
Zaměř se na akce, které lze na stránce provádět, a synonyma pro důležité termíny.
Příklad: Pokud jde o nastavení brandingu, klíčová slova mohou být: logo, barvy, vzhled, identita, změna loga.

Název: {$doc->title}
Typ: {$doc->type}
Obsah: " . Str::limit($doc->content, 2000);

        try {
            $response = Http::timeout(60)
                ->withToken($settings['openai_api_key'])
                ->baseUrl($settings['openai_base_url'] ?? 'https://api.openai.com/v1')
                ->post('/chat/completions', [
                    'model' => $settings['fast_model'] ?? 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Jsi expert na SEO a vyhledávání. Vracej pouze seznam klíčových slov oddělených čárkou.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.3,
                ])->json();

            $keywordsStr = $response['choices'][0]['message']['content'] ?? '';
            if ($keywordsStr) {
                $keywords = array_map('trim', explode(',', $keywordsStr));
                $doc->update(['keywords' => array_unique($keywords)]);
                return true;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AI Enrichment Error: " . $e->getMessage());
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

                    if (!$label || !$url) continue;

                    $content = "V hlavním menu se tato položka nachází v sekci \"{$groupLabel}\" pod názvem \"{$label}\". ";
                    $content .= "Uživatel ji najde v postranním panelu (sidebar).";

                    $this->updateOrCreateDocument([
                        'type' => 'admin.navigation',
                        'source' => 'navigation',
                        'title' => (string) $label,
                        'url' => $url,
                        'locale' => $locale,
                        'content' => $content,
                        'checksum' => hash('sha256', $content . $url),
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

                if (!$group && property_exists($pageClass, 'navigationGroup')) {
                    $group = (string) $pageClass::$navigationGroup;
                }

                $content = "";

                // EXTRAKCE ZE SCHÉMATU (Formuláře na stránkách)
                try {
                    if (method_exists($page, 'form')) {
                        $schema = app(\Filament\Schemas\Schema::class);
                        // Některé stránky mohou vyžadovat inicializaci stavu, zkusíme to bezpečně
                        $page->form($schema);
                        $schemaTexts = $this->extractTextsFromSchema($schema);
                        if ($schemaTexts) {
                            $content .= "Obsahuje pole a sekce: " . $schemaTexts . ". ";
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
                } catch (\Throwable $e) {}

                $navigationInfo = $group ? "Tato stránka se v menu nachází v sekci \"{$group}\". " : "";

                $this->updateOrCreateDocument([
                    'type' => 'admin.page',
                    'source' => $pageClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo . ($content ?: 'Administrační stránka ' . $title),
                    'checksum' => hash('sha256', $content . $url . $group),
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
                        $content .= "Formulář obsahuje: " . $schemaTexts . ". ";
                    }
                } catch (\Throwable $e) {}

                // Extrakce tabulky
                try {
                    // Pro tabulku potřebujeme instanci, která může být složitější na vytvoření v CLI
                    // Ale zkusíme alespoň základní extrakci pokud configure metoda existuje v oddělené třídě
                    // nebo pokud můžeme zavolat table() na resource.
                    $table = \Filament\Tables\Table::make(app(\Filament\Pages\Dashboard::class));
                    $resourceClass::table($table);
                    $tableTexts = $this->extractTextsFromTable($table);
                    if ($tableTexts) {
                        $content .= "Tabulka přehledu obsahuje: " . $tableTexts . ". ";
                    }
                } catch (\Throwable $e) {}

                $navigationInfo = $group ? "Tato sekce se v menu nachází v sekci \"{$group}\". " : "";

                $this->updateOrCreateDocument([
                    'type' => 'admin.resource',
                    'source' => $resourceClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo . $content,
                    'checksum' => hash('sha256', $resourceClass . $url . $group . $content),
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
                $content = "";
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
                    'checksum' => hash('sha256', $content . $url),
                ]);
                $count++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $count;
    }
    public function search(string $query, string $locale = 'cs', int $limit = 8, string $context = null)
    {
        $q = Str::lower($query);

        // Jednoduché LIKE vyhledávání + heuristické seřazení v PHP
        $queryBuilder = AiDocument::query()
            ->where('locale', $locale);

        if ($context === 'admin') {
            $queryBuilder->where(function ($w) {
                $w->where('type', 'like', 'admin.%')
                  ->orWhere('type', 'docs');
            });
        } elseif ($context === 'member') {
            $queryBuilder->where(function ($w) {
                $w->where('type', 'like', 'member.%')
                  ->orWhere('type', 'docs');
            });
        }

        $candidates = $queryBuilder->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(title) LIKE ?', ['%' . $q . '%'])
                  ->orWhereRaw('LOWER(content) LIKE ?', ['%' . $q . '%'])
                  ->orWhereRaw('LOWER(keywords) LIKE ?', ['%' . $q . '%'])
                  ->orWhereJsonContains('keywords', $q);
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
                if ($title === $q) $score += 50; // Přesná shoda
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

            // Typový boost (upřednostnit admin sekci před dokumentací)
            if (str_starts_with($doc->type, 'admin.')) {
                $score += 30;
            } elseif ($doc->type === 'docs') {
                $score -= 5; // mírná penalizace dokumentace v rámci admin kontextu
            }

            return [$doc, $score];
        })->sortByDesc(fn ($pair) => $pair[1])
          ->take($limit)
          ->map(fn ($pair) => $pair[0])
          ->values();

        return $scored;
    }

    private function indexBladeViews(string $dir, string $type, string $locale): int
    {
        if (! is_dir($dir)) {
            return 0;
        }

        $files = File::allFiles($dir);
        $count = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' && ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $path = $file->getRealPath();
            try {
                $raw = File::get($path);
            } catch (FileNotFoundException) {
                continue;
            }

            $text = $this->sanitizeBlade($raw);
            $title = $this->guessTitleFromBlade($raw) ?: basename($path);

            $checksum = hash('sha256', $raw);

                $this->updateOrCreateDocument([
                    'type' => $type,
                    'source' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path),
                    'title' => $title,
                    'url' => null,
                    'locale' => $locale,
                    'content' => $text,
                    'checksum' => $checksum,
                ]);

            $count++;
        }

        return $count;
    }

    private function indexDocs(string $dir, string $locale): int
    {
        if (! is_dir($dir)) {
            return 0;
        }

        $files = File::allFiles($dir);
        $count = 0;

        foreach ($files as $file) {
            if (! in_array(strtolower($file->getExtension()), ['md', 'markdown'], true)) {
                continue;
            }

            $path = $file->getRealPath();
            try {
                $raw = File::get($path);
            } catch (FileNotFoundException) {
                continue;
            }

            $text = $this->sanitizeMarkdown($raw);
            $title = $this->guessTitleFromMarkdown($raw) ?: basename($path);

            $checksum = hash('sha256', $raw);

            $this->updateOrCreateDocument([
                'type' => 'docs',
                'source' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path),
                'title' => $title,
                'url' => null,
                'locale' => $locale,
                'content' => $text,
                'checksum' => $checksum,
            ]);

            $count++;
        }

        return $count;
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

    private function sanitizeMarkdown(string $raw): string
    {
        // Odstranit kódové bloky
        $text = preg_replace('/```.*?```/s', ' ', $raw) ?? $raw;
        // Odstranit markdown syntaxi #,*,_,[]()
        $text = preg_replace('/[#*_`>\-]+/', ' ', $text) ?? $text;
        $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/', ' ', $text) ?? $text; // obrázky
        $text = preg_replace('/\[[^\]]*\]\([^)]*\)/', ' ', $text) ?? $text;  // odkazy
        $text = strip_tags($text);
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private function guessTitleFromBlade(string $raw): ?string
    {
        // Hledat <h1>...</h1> nebo @section('title', '...')
        if (preg_match('/<h1[^>]*>(.*?)<\\/h1>/si', $raw, $m)) {
            return trim(strip_tags($m[1]));
        }
        if (preg_match("/@section\(['\"]title['\"],\s*['\"](.*?)['\"]\)/si", $raw, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function guessTitleFromMarkdown(string $raw): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $raw, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
