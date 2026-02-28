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

        if ($section === 'documentation') {
            Log::info("AI Indexing: Starting documentation section for locale '{$locale}'");
            $count += $this->indexDocumentation($locale, $onProgress, $force);
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

        $prompt = "### SYSTEM PROMPT (Role: Semantic Search Architect)
Jsi expert na indexaci webového obsahu pro basketbalový klub \"Kbelští sokoli\". Tvým úkolem je transformovat surový obsah stránky do sémantického profilu, který umožní uživatelům najít stránku pomocí přirozených dotazů, záměrů (intents) a synonym.

### VSTUPNÍ KONTEXT
- URL: {$doc->url}
- SEKCE: {$doc->type} (např. Administrace, Členská sekce, Veřejný web)
- JAZYK: {$doc->locale} (cs/en)

### SUROVÝ OBSAH STRÁNKY
{$doc->content}

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
                    'keywords' => array_unique(array_merge($data['queries'] ?? [], $data['keywords'] ?? [])),
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
                'title' => (string) $title,
                'url' => $url,
                'locale' => $locale,
                'content' => $content,
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
        $docsPath = base_path('docs');

        if (! File::exists($docsPath)) {
            return 0;
        }

        $files = File::allFiles($docsPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $content = File::get($file->getPathname());

            // Základní extrakce titulku z Markdownu (# Title)
            $title = $relativePath;
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            }

            if ($onProgress) {
                $onProgress("Documentation: {$title}");
            }

            $this->updateOrCreateDocument([
                'type' => 'documentation.resource',
                'source' => 'docs/'.$relativePath,
                'title' => $title,
                'url' => null, // Dokumentace nemá přímou URL v aplikaci, ale slouží jako kontext
                'locale' => $locale, // Dokumentace je primárně česky, indexujeme do aktuálního locale
                'content' => $content,
                'checksum' => hash('sha256', $content.$relativePath),
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
                'title' => $title,
                'url' => $url,
                'locale' => $locale,
                'content' => $content,
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

            $this->updateOrCreateDocument([
                'type' => 'frontend.resource',
                'source' => 'post:'.$post->id,
                'title' => $title,
                'url' => $url,
                'locale' => $locale,
                'content' => $content,
                'metadata' => [
                    'image' => $post->featured_image,
                ],
                'checksum' => hash('sha256', $content.$url.$title.$post->featured_image),
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
        $q = Str::lower($query);
        $words = array_filter(explode(' ', $q), fn ($w) => mb_strlen($w) > 2);

        // Jednoduché LIKE vyhledávání + heuristické seřazení v PHP
        $queryBuilder = AiDocument::query()
            ->where('locale', $locale)
            ->where('is_active', true);

        if ($section) {
            $queryBuilder->where(function ($w) use ($section) {
                $w->where('section', $section)
                    ->orWhere('section', 'documentation');
            });
        }

        $candidates = $queryBuilder->where(function ($w) use ($q, $words) {
            $w->whereRaw('LOWER(title) LIKE ?', ['%'.$q.'%'])
                ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$q.'%'])
                ->orWhereRaw('LOWER(keywords) LIKE ?', ['%'.$q.'%']);

            // Fallback na jednotlivá slova pro lepší match v přirozeném jazyce
            foreach ($words as $word) {
                $w->orWhereRaw('LOWER(title) LIKE ?', ['%'.$word.'%'])
                    ->orWhereRaw('LOWER(content) LIKE ?', ['%'.$word.'%']);
            }
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
