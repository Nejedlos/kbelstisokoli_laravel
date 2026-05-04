<?php

namespace App\Services;

use App\Models\AiDocument;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiIndexService
{
    public function __construct(
        protected AiSettingsService $aiSettings,
        protected TextExtractionService $textExtraction
    ) {}

    /**
     * Provede kompletní reindex zdrojů (Blade views, DB záznamy).
     */
    public function reindex(string $locale = 'cs', bool $fresh = false, ?string $section = null, ?\Closure $onProgress = null, bool $force = false): int
    {
        // Nastavíme locale pro korektní překlady během indexace
        App::setLocale($locale);

        if ($fresh) {
            $query = AiDocument::query()->where('locale', $locale);
            if ($section) {
                $query->where('section', $section);
            }
            $query->delete();
        }

        // Deaktivujeme všechny dokumenty pro daný jazyk/sekci
        $query = AiDocument::query()->where('locale', $locale);
        if ($section) {
            $query->where('section', $section);
        }
        $query->update(['is_active' => false]);

        $count = 0;

        // Indexujeme pouze vybrané sekce
        if (! $section || $section === 'admin') {
            Log::info("AI Indexing: Starting admin section for locale '{$locale}'");
            $count += $this->indexFilament($locale, $onProgress, $force);
        }

        if (! $section || $section === 'member') {
            Log::info("AI Indexing: Starting member section for locale '{$locale}'");
            $count += $this->indexMemberSection($locale, $onProgress, $force);
        }

        if (! $section || $section === 'frontend') {
            Log::info("AI Indexing: Starting frontend section for locale '{$locale}'");
            $count += $this->indexFrontend($locale, $onProgress, $force);
        }

        if (! $section || $section === 'documentation') {
            Log::info("AI Indexing: Starting documentation section for locale '{$locale}'");
            $count += $this->indexDocumentation($locale, $onProgress, $force);
        }

        if (! $section || $section === 'help') {
            Log::info("AI Indexing: Starting help section for locale '{$locale}'");
            $count += $this->indexHelpArticles($locale, $onProgress, $force);
        }

        // Smažeme dokumenty, které již nejsou aktivní
        $query = AiDocument::query()->where('locale', $locale)->where('is_active', false);
        if ($section) {
            $query->where('section', $section);
        }
        $query->delete();

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

        // Robustní získání obsahu pro prompt
        $docContent = is_array($doc->content) ? ($doc->content[$doc->locale] ?? $doc->content['cs'] ?? array_values($doc->content)[0] ?? '') : $doc->content;

        $prompt = "### SYSTEM PROMPT (Role: Semantic Search Architect)
Jsi expert na indexaci webového obsahu pro basketbalový klub \"Kbelští sokoli\". Tvým úkolem je transformovat surový obsah stránky do sémantického profilu, který umožní uživatelům najít stránku pomocí přirozených dotazů, záměrů (intents) a synonym.

### VSTUPNÍ KONTEXT
- URL: {$doc->url}
- SEKCE: {$doc->type} (např. Administrace, Členská sekce, Veřejný web)
- JAZYK: {$doc->locale} (cs/en)

### SUROVÝ OBSAH STRÁNKY
{$docContent}

### TVÉ INSTRUKCE (ZÁVAZNÉ)

#### 1. Sémantické mapování (Záměr uživatele)
Nehledej jen texty, hledej význam.
- Pokud na stránce vidíš pole \"Znak klubu\" nebo \"Nahrát logo\", zaindexuj: \"změna loga\", \"nastavit logo\", \"brand\", \"identity\", \"nahrát obrázek týmu\".
- Pokud vidíš tabulku s platbami, zaindexuj: \"kolik dlužím\", \"bankovní spojení\", \"qr kód\", \"přehled příspěvků\".
- Pokud vidíš formulář hráče, zaindexuj: \"vytvořit člena\", \"přidat kluka do týmu\", \"registrace\".

#### 2. Struktura výstupu (Validní JSON)
Mustíš vrátit POUZE validní JSON. Nic jiného.
{
  \"title\": \"Lidsky srozumitelný název stránky (např. 'Správa identity' místo 'Settings')\",
  \"description\": \"Stručný jednovětý popis pro výsledky vyhledávání\",
  \"queries\": [\"seznam 15-20 pravděpodobných dotazů, otázek a synonym, které by uživatel mohl zadat\"],
  \"keywords\": [\"technická klíčová slova\"],
  \"priority\": 5
}

#### 3. Bezpečnost a soukromí (Kritické)
- **ANONYMIZACE**: Pokud v obsahu vidíš konkrétní jména (např. 'Jan Novák'), maily nebo telefony, NIKDY je neukládej do indexu. Nahraď je zástupnými symboly jako '[jméno_člena]' nebo '[kontakt]'. Indexuj pouze TYPY informací, ne konkrétní data.
- **FILTRACE**: Ignoruj UUID, CSRF tokeny, hash řetězce a technické chyby.

#### 4. Výkon a Jazyk
- Buď maximálně stručný a věcný.
- **DŮLEŽITÉ: Veškerý textový výstup (title, description, queries, keywords) generuj VÝHRADNĚ v jazyce stránky ({$doc->locale}).**
- Pokud je jazyk cs, piš česky. Pokud en, piš anglicky.

### ZPRACOVEJ TEĎ (ODPOVĚZ POUZE FORMÁTEM JSON):";

        try {
            $response = Http::timeout(60)
                ->withToken($settings['openai_api_key'])
                ->baseUrl($settings['openai_base_url'] ?? 'https://api.openai.com/v1')
                ->post('/chat/completions', [
                    'model' => $settings['analyze_model'] ?? $settings['fast_model'] ?? 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Jsi expert na UX a SEO pro sportovní klubové weby. Vracej pouze validní JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                ])->json();

            $data = json_decode($response['choices'][0]['message']['content'] ?? '{}', true);

            if (! empty($data)) {
                $doc->update([
                    'title' => $data['title'] ?? $doc->title,
                    'summary' => $data['description'] ?? $doc->summary,
                    'keywords' => array_unique(array_merge($doc->keywords ?? [], $data['queries'] ?? [], $data['keywords'] ?? [])),
                    'metadata' => array_merge($doc->metadata ?? [], ['priority' => $data['priority'] ?? 5]),
                ]);

                return true;
            }
        } catch (\Throwable $e) {
            Log::error('AI Enrichment Error: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Uloží nebo aktualizuje dokument s kontrolou checksumu.
     */
    private function updateOrCreateDocument(array $data, bool $force = false): AiDocument
    {
        $section = $data['section'] ?? $this->determineSection($data['type']);
        $locale = $data['locale'] ?? 'cs';

        // Pokud jsou pole (title, content, summary) jako pole (translatable), vybereme správnou verzi pro tento záznam
        foreach (['title', 'content', 'summary'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][$locale] ?? $data[$field]['cs'] ?? array_values($data[$field])[0] ?? '';
            }
        }

        $existing = AiDocument::where('source', $data['source'])
            ->where('locale', $locale)
            ->where('section', $section)
            ->first();

        $contentPlain = $this->textExtraction->extractPlaintext($data['content'] ?? '');
        $contentHash = hash('sha256', $contentPlain);

        $data['section'] = $section;
        $data['content'] = $contentPlain;
        $data['content_hash'] = $contentHash;
        $data['last_indexed_at'] = now();
        $data['is_active'] = true;

        if ($existing) {
            if ($force || $existing->content_hash !== $contentHash || ($data['checksum'] ?? null) !== $existing->checksum) {
                $existing->update($data);
                $this->updateChunks($existing);
            } else {
                $existing->update(['last_indexed_at' => now(), 'is_active' => true]);
            }

            return $existing;
        }

        $doc = AiDocument::create($data);
        $this->updateChunks($doc);

        return $doc;
    }

    private function determineSection(string $type): string
    {
        if (str_starts_with($type, 'admin.')) {
            return 'admin';
        }
        if (str_starts_with($type, 'member.')) {
            return 'member';
        }
        if (str_starts_with($type, 'documentation.')) {
            return 'documentation';
        }
        if (str_starts_with($type, 'help.')) {
            return 'help';
        }

        return 'frontend';
    }

    private function updateChunks(AiDocument $doc): void
    {
        $doc->chunks()->delete();

        if (config('ai.indexing.skip_chunks')) {
            return;
        }

        $chunks = $this->textExtraction->chunkText($doc->content);
        foreach ($chunks as $index => $text) {
            $doc->chunks()->create([
                'section' => $doc->section,
                'chunk_index' => $index,
                'chunk_text' => $text,
                'chunk_hash' => hash('sha256', $text),
            ]);
        }
    }

    private function indexFilament(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        Log::info('AI Indexing: [Filament] Starting...');
        $admin = null;
        try {
            $admin = User::role('admin')->first() ?: User::find(1);
        } catch (\Throwable $e) {
            $admin = User::find(1);
        }

        // 0. Indexování Photo Poolů
        $pools = \App\Models\PhotoPool::all();
        Log::info('AI Indexing: [Filament] Indexing '.$pools->count().' PhotoPools');
        foreach ($pools as $pool) {
            $title = $pool->getTranslation('title', $locale);
            $description = $pool->getTranslation('description', $locale);
            $url = \App\Filament\Resources\PhotoPools\PhotoPoolResource::getUrl('edit', ['record' => $pool]);

            $dateStr = '';
            if ($pool->event_date) {
                if ($pool->event_date->day === 1 && $pool->event_date->month === 1) {
                    $dateStr = $pool->event_date->format('Y');
                } else {
                    $dateStr = $pool->event_date->format('d.m.Y');
                }
            }

            $content = "Photo Pool (Galerie): {$title}. Datum: {$dateStr}. Popis: {$description}. Typ: {$pool->event_type}.";

            if ($onProgress) {
                $size = strlen($content);
                $onProgress("PhotoPool: {$title} [internal, {$size} chars]");
            }

            $this->updateOrCreateDocument([
                'type' => 'admin.resource',
                'source' => 'PhotoPool:'.$pool->id,
                'title' => [$locale => (string) $title],
                'url' => $url,
                'locale' => $locale,
                'content' => [$locale => $content],
                'checksum' => hash('sha256', $content.$url),
                'metadata' => ['group' => __('admin.navigation.groups.media'), 'model' => 'PhotoPool', 'id' => $pool->id],
            ], $force);
            $count++;
        }

        // Indexování stránek (Pages)
        $pages = \Filament\Facades\Filament::getPanel('admin')->getPages();
        Log::info('AI Indexing: [Filament] Indexing '.count($pages).' Pages');
        foreach ($pages as $pageClass) {
            try {
                // Přeskočíme naši AI vyhledávací stránku
                if (str_contains($pageClass, 'AiSearch')) {
                    continue;
                }

                $page = app($pageClass);
                $title = $page->getTitle() ?: $pageClass;
                $url = $pageClass::getUrl();

                // Zkusíme vyrenderovat obsah přes URL (Render-then-Analyze)
                $content = $this->renderUrl($url, $admin);
                $status = 'rendered';

                // Pokud rendering nic nevrátil, zkusíme aspoň extrakci ze schématu (fallback)
                if (empty($content)) {
                    $status = 'schema';
                    // EXTRAKCE ZE SCHÉMATU (Formuláře na stránkách)
                    try {
                        if (method_exists($page, 'form')) {
                            $schema = app(\Filament\Schemas\Schema::class);
                            $page->form($schema);
                            $content .= $this->extractTextsFromSchema($schema);
                        }
                    } catch (\Throwable $e) {
                    }
                }

                if ($onProgress) {
                    $size = strlen($content);
                    $onProgress("Page: {$title} [{$status}, {$size} chars]");
                }

                // Zkusíme získat informaci o umístění v menu
                $group = null;
                if (method_exists($pageClass, 'getNavigationGroup')) {
                    $group = (string) $pageClass::getNavigationGroup();
                }

                $navigationInfo = $group ? "Sekce administrace: {$group}. " : '';
                $typeInfo = 'Typ: '.($page->getTitle() ?: 'Administrační stránka').'. ';

                $this->updateOrCreateDocument([
                    'type' => 'admin.resource',
                    'source' => $pageClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo.$typeInfo.($content ?: 'Administrační stránka '.$title),
                    'checksum' => hash('sha256', $content.$url.$group),
                    'metadata' => ['group' => $group],
                ], $force);
                $count++;
            } catch (\Throwable $e) {
                Log::error("AI Indexing: Error indexing page {$pageClass}: ".$e->getMessage());

                continue;
            } finally {
                gc_collect_cycles();
            }
        }

        // Indexování resources
        $resources = \Filament\Facades\Filament::getPanel('admin')->getResources();
        Log::info('AI Indexing: [Filament] Indexing '.count($resources).' Resources');
        foreach ($resources as $resourceClass) {
            try {
                $title = $resourceClass::getNavigationLabel();
                $url = $resourceClass::getUrl();
                $group = $resourceClass::getNavigationGroup();

                // Pro resource zkusíme vyrenderovat index stránku
                $content = $this->renderUrl($url, $admin);
                $status = 'rendered';

                // Fallback na extrakci ze schématu
                if (empty($content)) {
                    $status = 'schema';
                    $content = "Správa sekce {$title}. Zde můžete přidávat, upravovat nebo mazat záznamy. ";
                    try {
                        $schema = app(\Filament\Schemas\Schema::class);
                        $resourceClass::form($schema);
                        $schemaTexts = $this->extractTextsFromSchema($schema);
                        if ($schemaTexts) {
                            $content .= 'Formulář obsahuje: '.$schemaTexts.'. ';
                        }
                    } catch (\Throwable $e) {
                    }
                }

                if ($onProgress) {
                    $size = strlen($content);
                    $onProgress("Resource: {$title} [{$status}, {$size} chars]");
                }

                $navigationInfo = $group ? "Sekce administrace: {$group}. " : '';
                $typeInfo = 'Typ: '.($title ?: 'Resource').'. ';

                $this->updateOrCreateDocument([
                    'type' => 'admin.resource',
                    'source' => $resourceClass,
                    'title' => (string) $title,
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $navigationInfo.$typeInfo.$content,
                    'checksum' => hash('sha256', $resourceClass.$url.$group.$content),
                    'metadata' => ['group' => $group],
                ], $force);
                $count++;
            } catch (\Throwable $e) {
                Log::error("AI Indexing: Error indexing resource {$resourceClass}: ".$e->getMessage());

                continue;
            } finally {
                gc_collect_cycles();
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

    private function indexMemberSection(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        Log::info('AI Indexing: [Member] Starting...');
        $member = null;
        try {
            // Pro indexaci členské sekce použijeme admina (má přístup i k trenérským přehledům)
            // nebo prvního hráče s verifikovaným e-mailem.
            $member = User::role('admin')->first()
                      ?: User::role('player')->whereNotNull('email_verified_at')->first()
                      ?: User::find(1);
        } catch (\Throwable $e) {
            $member = User::find(1);
        }

        $routes = [
            'member.dashboard' => ['title' => __('admin.navigation.pages.member_section')],
            'member.attendance.index' => ['title' => __('member.attendance.title')],
            'member.profile.edit' => ['title' => __('member.profile.title')],
            'member.economy.index' => ['title' => __('member.economy.title')],
            'member.notifications.index' => ['title' => __('member.notifications.title')],
            'member.statistics.index' => ['title' => __('nav.statistics')],
            'member.statistics.me' => ['title' => __('nav.my_statistics')],
            'member.statistics.players' => ['title' => __('nav.players_statistics')],
            'member.statistics.matches' => ['title' => __('nav.matches_statistics')],
            'member.teams.index' => ['title' => __('member.teams.title')],
        ];

        Log::info('AI Indexing: [Member] Indexing '.count($routes).' Routes');
        foreach ($routes as $routeName => $info) {
            try {
                $url = route($routeName);

                // Renderování stránky (Render-then-Analyze)
                $content = $this->renderUrl($url, $member);
                $status = $content ? 'rendered' : 'empty';

                if ($onProgress) {
                    $size = strlen($content);
                    $onProgress("Member Route: {$info['title']} [{$status}, {$size} chars]");
                }

                $this->updateOrCreateDocument([
                    'type' => 'member.resource',
                    'source' => $routeName,
                    'title' => $info['title'],
                    'url' => $url,
                    'locale' => $locale,
                    'content' => $content ?: $info['title'],
                    'checksum' => hash('sha256', ($content ?: '').$url),
                ], $force);
                $count++;
            } catch (\Throwable $e) {
                Log::error("AI Indexing: Error indexing member route {$routeName}: ".$e->getMessage());

                continue;
            } finally {
                gc_collect_cycles();
            }
        }

        return $count;
    }

    private function indexDocumentation(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        $docsPath = base_path('docs' . DIRECTORY_SEPARATOR . $locale);

        if (! File::exists($docsPath)) {
            // Pokud neexistuje složka pro daný locale, zkusíme fallback na cs pouze pokud indexujeme cs
            if ($locale === 'cs') {
                $docsPath = base_path('docs');
            } else {
                return 0;
            }
        }

        $files = File::allFiles($docsPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            // Ignorujeme kořenové složky cs/en pokud jsme v docs rootu (fallback)
            $relativePath = $file->getRelativePathname();
            if (($locale === 'cs' || $locale === 'en') && Str::startsWith($relativePath, [$locale . '/', $locale . '\\'])) {
                continue;
            }

            $content = File::get($file->getPathname());

            // Základní extrakce titulku z Markdownu (# Title)
            $title = $file->getFilenameWithoutExtension();
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            }

            if ($onProgress) {
                $onProgress("Documentation [{$locale}]: {$title}");
            }

            // Uložíme jako JSON pro bilingvnost
            $this->updateOrCreateDocument([
                'type' => 'documentation.resource',
                'source' => 'docs/'.$locale.'/'.$relativePath,
                'title' => [$locale => $title],
                'summary' => [$locale => Str::limit(strip_tags($content), 200)],
                'url' => route('filament.admin.pages.documentation', ['file' => $relativePath]),
                'locale' => $locale,
                'content' => [$locale => $content],
                'checksum' => hash('sha256', $content.$relativePath.$locale),
            ], $force);

            $count++;
        }

        return $count;
    }

    private function indexHelpArticles(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        // Používáme moderní systém HelpArticle
        $articles = \App\Models\HelpArticle::published()->get();

        foreach ($articles as $article) {
            $title = $article->getTranslation('title', $locale, false);
            $content = $article->getTranslation('content', $locale, false);

            if (empty($title) || empty($content)) {
                // Fallback na výchozí jazyk
                $fallback = $article->getFallbackLocale();
                $title = $article->getTranslation('title', $fallback, false);
                $content = $article->getTranslation('content', $fallback, false);
                if (empty($title) || empty($content)) {
                    continue;
                }
            }

            $summary = $article->getTranslation('excerpt', $locale, false) ?: Str::limit(strip_tags($content), 200);
            $keywords = $article->getTranslation('search_keywords', $locale, false) ?: [];

            // Obohatíme obsah o FAQ pro lepší AI odpovědi
            $faqs = $article->faqs;
            if ($faqs->isNotEmpty()) {
                $content .= "\n\n### Časté dotazy (FAQ):\n";
                foreach ($faqs as $faq) {
                    $q = $faq->getTranslation('question', $locale, false);
                    $a = $faq->getTranslation('answer', $locale, false);
                    if ($q && $a) {
                        $content .= "Otázka: ".$q."\n";
                        $content .= "Odpověď: ".$a."\n\n";
                    }
                }
            }

            if ($onProgress) {
                $onProgress("HelpArticle [{$locale}]: {$title}");
            }

            $this->updateOrCreateDocument([
                'type' => 'help.article',
                'source' => 'db.help_articles.'.$article->id,
                'source_type' => 'HelpArticle',
                'source_id' => $article->id,
                'title' => [$locale => $title],
                'summary' => [$locale => $summary],
                'url' => route('filament.admin.pages.help', ['file' => $article->slug]),
                'locale' => $locale,
                'content' => [$locale => $content],
                'keywords' => $keywords,
                'checksum' => hash('sha256', $content.$article->slug.$locale.json_encode($keywords)),
            ], $force);

            $count++;
        }

        return $count;
    }

    /**
     * @deprecated Používejte indexHelpArticles pro moderní systém nápovědy.
     */
    private function indexHelp(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        $helpPath = base_path('docs/help/'.$locale);

        if (! File::exists($helpPath)) {
            return 0;
        }

        $files = File::allFiles($helpPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $slug = Str::before($file->getFilename(), '.md');
            $content = File::get($file->getPathname());

            // Pokusíme se najít článek v databázi pro získání metadat a klíčových slov
            $article = \App\Models\HelpArticle::where('slug', $slug)->first();

            $title = $file->getFilenameWithoutExtension();
            $keywords = [];
            $summary = Str::limit(strip_tags($content), 200);

            if ($article) {
                // Použijeme data z DB (pokud existují)
                $title = $article->getTranslation('title', $locale, false) ?: $title;
                $keywords = $article->getTranslation('search_keywords', $locale, false) ?: [];
                $summary = $article->getTranslation('excerpt', $locale, false) ?: $summary;

                // Obohatíme obsah o FAQ pro lepší AI odpovědi
                $faqs = $article->faqs;
                if ($faqs->isNotEmpty()) {
                    $content .= "\n\n### Časté dotazy (FAQ):\n";
                    foreach ($faqs as $faq) {
                        $content .= "Otázka: ".$faq->getTranslation('question', $locale)."\n";
                        $content .= "Odpověď: ".$faq->getTranslation('answer', $locale)."\n\n";
                    }
                }
            } else {
                // Základní extrakce titulku z Markdownu (# Title)
                if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                    $title = trim($matches[1]);
                }
            }

            if ($onProgress) {
                $onProgress("Help [{$locale}]: {$title}");
            }

            // Uložíme jako JSON pro bilingvnost, aby AiSearch mohl použít překlady
            $this->updateOrCreateDocument([
                'type' => 'documentation.resource',
                'source' => 'help/'.$locale.'/'.$relativePath,
                'title' => [$locale => $title],
                'summary' => [$locale => $summary],
                'url' => route('filament.admin.pages.help', ['file' => 'docs/help/'.$locale.'/'.$relativePath]),
                'locale' => $locale,
                'content' => [$locale => $content],
                'keywords' => $keywords,
                'checksum' => hash('sha256', $content.$relativePath.$locale.json_encode($keywords)),
            ], $force);

            $count++;
        }

        return $count;
    }

    private function indexFrontend(string $locale, ?\Closure $onProgress = null, bool $force = false): int
    {
        $count = 0;
        Log::info('AI Indexing: [Frontend] Starting...');

        // 1. Indexace stránek (Pages)
        $pages = Page::query()
            ->where('is_visible', true)
            ->where('status', 'published')
            ->get();

        Log::info('AI Indexing: [Frontend] Indexing '.$pages->count().' Pages');
        foreach ($pages as $page) {
            $title = $page->getTranslation('title', $locale);
            $url = $page->slug === 'home' ? route('public.home') : route('public.pages.show', $page->slug);

            // Renderování stránky (Render-then-Analyze)
            $content = $this->renderUrl($url);
            $status = 'rendered';

            // Fallback pokud rendering selže
            if (empty($content)) {
                $status = 'fallback';
                Log::warning("AI Indexing: [Frontend] Rendering failed for Page: {$title} ({$url}), using fallback");
                $rawContent = $page->getTranslation('content', $locale);
                if (is_array($rawContent)) {
                    $content = $this->extractStringsFromBlocks($rawContent);
                } else {
                    $content = strip_tags((string) $rawContent);
                }
            }

            if ($onProgress) {
                $size = strlen($content);
                $onProgress("Frontend Page: {$title} [{$status}, {$size} chars]");
            }

            $this->updateOrCreateDocument([
                'type' => 'frontend.resource',
                'source' => 'page:'.$page->id,
                'title' => [$locale => $title],
                'url' => $url,
                'locale' => $locale,
                'content' => [$locale => $content],
                'checksum' => hash('sha256', $content.$url.$title),
            ], $force);
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

        Log::info('AI Indexing: [Frontend] Indexing '.$posts->count().' Posts');
        foreach ($posts as $post) {
            $title = $post->getTranslation('title', $locale);
            $url = route('public.news.show', $post->slug);

            // Renderování stránky (Render-then-Analyze)
            $content = $this->renderUrl($url);
            $status = 'rendered';

            // Fallback
            if (empty($content)) {
                $status = 'fallback';
                Log::warning("AI Indexing: [Frontend] Rendering failed for Post: {$title} ({$url}), using fallback");
                $excerpt = $post->getTranslation('excerpt', $locale);
                $rawContent = $post->getTranslation('content', $locale);
                $content = strip_tags($excerpt.' '.$rawContent);
            }

            if ($onProgress) {
                $size = strlen($content);
                $onProgress("Frontend Post: {$title} [{$status}, {$size} chars]");
            }

            $image = $post->getFirstMediaUrl('featured_image') ?: $post->featured_image;
            $this->updateOrCreateDocument([
                'type' => 'frontend.resource',
                'source' => 'post:'.$post->id,
                'title' => [$locale => $title],
                'url' => $url,
                'locale' => $locale,
                'content' => [$locale => $content],
                'metadata' => [
                    'image' => $image,
                ],
                'checksum' => hash('sha256', $content.$url.$title.$image),
            ], $force);
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

    public function search(string $query, string $locale = 'cs', int $limit = 8, ?string $section = null)
    {
        $q = Str::lower(trim($query));
        $stopWords = ['jak', 'jako', 'kdy', 'kde', 'kdo', 'pro', 'nad', 'pod', 'vše', 'všech', 'jsou', 'tento', 'tato', 'toto', 'nebo', 'vám', 'může', 'můžete'];
        $words = array_filter(explode(' ', $q), fn ($w) => mb_strlen($w) > 2 && ! in_array($w, $stopWords));

        // Vytvoříme "kmeny" pro češtinu (osekání koncovek u delších slov)
        $stems = [];
        if ($locale === 'cs') {
            foreach ($words as $word) {
                if (mb_strlen($word) > 4) {
                    $stems[] = mb_substr($word, 0, -2);
                }
            }
        }

        $queryBuilder = AiDocument::query()
            ->where('locale', $locale)
            ->where('is_active', true);

        if ($section) {
            $queryBuilder->where(function ($w) use ($section) {
                $w->where('section', $section)
                    ->orWhere('section', 'documentation')
                    ->orWhere('section', 'help');
            });
        }

        $candidates = $queryBuilder->where(function ($w) use ($q, $words, $stems) {
            // 1. Prioritní shoda s celým dotazem
            $w->where('title', 'LIKE', '%'.$q.'%')
                ->orWhere('content', 'LIKE', '%'.$q.'%')
                ->orWhere('keywords', 'LIKE', '%'.$q.'%')
                ->orWhere('summary', 'LIKE', '%'.$q.'%');

            // 2. Shoda s jednotlivými slovy
            foreach ($words as $word) {
                $w->orWhere('title', 'LIKE', '%'.$word.'%')
                    ->orWhere('content', 'LIKE', '%'.$word.'%')
                    ->orWhere('keywords', 'LIKE', '%'.$word.'%')
                    ->orWhere('summary', 'LIKE', '%'.$word.'%');
            }

            // 3. Shoda s kmeny (stemming)
            foreach ($stems as $stem) {
                $w->orWhere('title', 'LIKE', '%'.$stem.'%')
                    ->orWhere('keywords', 'LIKE', '%'.$stem.'%')
                    ->orWhere('summary', 'LIKE', '%'.$stem.'%');
            }
        })
            ->limit(100)
            ->get();

        $scored = $candidates->map(function (AiDocument $doc) use ($q, $words, $stems, $locale) {
            // Použijeme metodu z modelu pro získání lokalizovaných textů
            $title = Str::lower($doc->getLocalizedValue('title'));
            $content = Str::lower($doc->getLocalizedValue('content'));
            $summary = Str::lower($doc->getLocalizedValue('summary'));
            $keywords = array_map(fn ($k) => Str::lower($k), $doc->keywords ?? []);

            $score = 0;

            // --- A. SHODA CELÉHO DOTAZU ---
            if ($q !== '') {
                if (Str::contains($title, $q)) {
                    $score += 100;
                }
                if (Str::contains($summary, $q)) {
                    $score += 40;
                }
                if (Str::contains($content, $q)) {
                    $score += 20;
                }
            }

            // --- B. SHODA JEDNOTLIVÝCH SLOV ---
            foreach ($words as $word) {
                if (Str::contains($title, $word)) {
                    $score += 40;
                }

                // Klíčová slova - vysoká váha
                foreach ($keywords as $keyword) {
                    if ($keyword === $word) {
                        $score += 60;
                    } elseif (Str::contains($keyword, $word)) {
                        $score += 20;
                    }
                }

                if (Str::contains($summary, $word)) {
                    $score += 15;
                }
                if (Str::contains($content, $word)) {
                    $score += 2;
                }
            }

            // --- C. SHODA KMENŮ (STEMS) ---
            foreach ($stems as $stem) {
                if (Str::contains($title, $stem)) {
                    $score += 25;
                }
                foreach ($keywords as $keyword) {
                    if (Str::contains($keyword, $stem)) {
                        $score += 35;
                    }
                }
            }

            // Typový boost (upřednostnit konkrétní sekci)
            if (str_contains($doc->type, 'resource')) {
                $score += 5;
            }

            if ($doc->section === 'documentation' || $doc->section === 'help') {
                $score += 25;
            }

            $doc->score = $score;

            return [$doc, $score];
        })->sortByDesc(fn ($pair) => $pair[1])
            ->filter(fn ($pair) => $pair[1] > 0)
            ->take($limit)
            ->map(fn ($pair) => $pair[0])
            ->values();

        return $scored;
    }

    /**
     * Vyrenderuje URL a vrátí vyčištěný obsah.
     */
    public function renderUrl(string $url, ?User $user = null): string
    {
        // Vytvoříme request pro danou URL
        $request = Request::create($url, 'GET');

        if ($user) {
            Auth::login($user);
            Auth::shouldUse('web');
            Auth::setUser($user);

            // Podvržení uživatele přímo do requestu pro middleware Authenticate a auth()->user()
            $request->setUserResolver(fn () => $user);

            // Pro jistotu nastavíme uživatele i do guardu
            Auth::guard('web')->setUser($user);

            // Pokusíme se zajistit session, pokud je k dispozici
            try {
                $request->setLaravelSession(session()->driver());
            } catch (\Throwable $e) {
                // V CLI nemusí být session driver k dispozici
            }
        }

        // Předáme informaci o jazyku do requestu pro middleware
        $locale = App::getLocale();
        $request->cookies->set('filament_language_switch_locale', $locale);

        try {
            // Dočasně vypneme middleware pro tento interní request, abychom se vyhnuli redirectům na login
            // a dalším komplikacím s autentizací v CLI. Jelikož indexaci spouští administrátor,
            // předpokládáme, že má právo k obsahu přistoupit.
            app()->instance('middleware.disable', true);

            // Podvrhneme prázdný ErrorBag, protože ShareErrorsFromSession middleware je vypnutý
            \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag);

            // Použijeme app()->handle pro interní zpracování requestu bez sítě
            $response = app()->handle($request);

            // Vrátíme původní stav (pro jistotu, i když instance je per-request)
            app()->forgetInstance('middleware.disable');

            if (! $response->isSuccessful()) {
                if ($response->isRedirection()) {
                    Log::warning("Rendering redirected for {$url} to ".$response->headers->get('Location'));
                } else {
                    Log::warning("Rendering failed for {$url} with status {$response->getStatusCode()}");
                }

                return '';
            }

            $html = $response->getContent();

            return $this->preprocessHtml($html);
        } catch (\Throwable $e) {
            Log::error("Rendering failed for {$url}: ".$e->getMessage());

            return '';
        } finally {
            if ($user) {
                Auth::logout();
            }
        }
    }

    /**
     * Odstraní z HTML šum (head, script, style, nav, footer).
     */
    private function preprocessHtml(string $html): string
    {
        // Odstranění nepotřebných sekcí
        $html = preg_replace('/<head>.*?<\/head>/is', '', $html) ?: $html;
        $html = preg_replace('/<script.*?>.*?<\/script>/is', '', $html) ?: $html;
        $html = preg_replace('/<style.*?>.*?<\/style>/is', '', $html) ?: $html;
        $html = preg_replace('/<nav.*?>.*?<\/nav>/is', '', $html) ?: $html;
        $html = preg_replace('/<footer.*?>.*?<\/footer>/is', '', $html) ?: $html;
        $html = preg_replace('/<header.*?>.*?<\/header>/is', '', $html) ?: $html;

        // Zkusíme najít hlavní obsah
        if (preg_match('/<main.*?>.*?<\/main>/is', $html, $matches)) {
            $html = $matches[0];
        } elseif (preg_match('/<article.*?>.*?<\/article>/is', $html, $matches)) {
            $html = $matches[0];
        } elseif (preg_match('/<div[^>]+id=["\']content["\'].*?>.*?<\/div>/is', $html, $matches)) {
            $html = $matches[0];
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text);

        // Komprimace whitespace
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
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
