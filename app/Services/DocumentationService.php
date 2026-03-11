<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Finder\Finder;

class DocumentationService
{
    protected string $docsPath;
    protected string $baseDocsPath;

    public function __construct()
    {
        $this->baseDocsPath = base_path('docs');
        $this->setPathByLocale();
    }

    protected function setPathByLocale(?string $locale = null): void
    {
        $locale = $locale ?: app()->getLocale();
        $path = $this->baseDocsPath . DIRECTORY_SEPARATOR . $locale;

        // Fallback na cs, pokud složka pro daný jazyk neexistuje
        if (!File::exists($path)) {
            $path = $this->baseDocsPath . DIRECTORY_SEPARATOR . 'cs';
        }

        $this->docsPath = $path;
    }

    /**
     * Získá strukturu dokumentace pro sidebar.
     */
    public function getTree(): Collection
    {
        $tree = collect();

        // README z kořene jako úvodní přehled
        $tree->push([
            'type' => 'file',
            'name' => '🚀 Přehled systému',
            'slug' => 'README.md',
            'path' => 'README.md',
            'is_root' => true,
        ]);

        if (!File::exists($this->docsPath)) {
            // Pokud ani cs neexistuje, zkusíme kořen jako nouzovku (pro zpětnou kompatibilitu)
            if (!File::exists($this->baseDocsPath)) {
                return $tree;
            }
            return $tree->concat($this->scanDirectory($this->baseDocsPath));
        }

        return $tree->concat($this->scanDirectory($this->docsPath));
    }

    protected function scanDirectory(string $path): Collection
    {
        $items = collect();
        $files = File::files($path);
        $directories = File::directories($path);

        // Zpracování složek
        foreach ($directories as $directory) {
            $name = basename($directory);
            if ($name === 'changes') continue; // Ignorujeme changes pro hlavní navi, pokud chceme

            $items->push([
                'type' => 'directory',
                'name' => $this->formatName($name),
                'slug' => $name,
                'children' => $this->scanDirectory($directory),
            ]);
        }

        // Zpracování souborů
        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') continue;

            $name = $file->getFilenameWithoutExtension();
            if ($name === 'index' && $path === $this->docsPath) continue;

            $items->push([
                'type' => 'file',
                'name' => $this->formatName($name),
                'slug' => Str::slug($this->getRelativePath($file->getPathname())),
                'path' => $this->getRelativePath($file->getPathname()),
            ]);
        }

        return $items->sortBy('slug')->values();
    }

    /**
     * Načte a převede Markdown soubor na HTML.
     */
    public function getFileContent(string $relativePath): ?array
    {
        if ($relativePath === 'README.md') {
            $fullPath = base_path('README.md');
        } elseif (Str::startsWith($relativePath, 'docs/')) {
            $fullPath = base_path($relativePath);
        } else {
            $fullPath = $this->docsPath . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
        }

        if (!File::exists($fullPath) || !File::isFile($fullPath)) {
            return null;
        }

        $content = File::get($fullPath);

        // Oprava relativních linků v markdownu pro README.md a ostatní
        $content = preg_replace_callback('/\[([^\]]+)\]\(([^)]+\.md)\)/', function($matches) use ($relativePath) {
            $text = $matches[1];
            $link = $matches[2];

            if (!Str::startsWith($link, 'http') && !Str::startsWith($link, '/')) {
                // Pokud jsme v README, linky jsou už pravděpodobně docs/cs/...
                if ($relativePath === 'README.md') {
                    return "[{$text}](?file={$link})";
                }

                // Pokud jsme v docs/cs/..., musíme zajistit, aby linky byly relativní ke kořenu docs
                $currentDir = dirname($relativePath);
                if ($currentDir === '.') {
                    return "[{$text}](?file={$link})";
                }

                $newLink = $currentDir . '/' . $link;
                // Zjednodušení cesty (např. odstranění /./)
                $newLink = str_replace(['/./', './'], '', $newLink);
                return "[{$text}](?file={$newLink})";
            }
            return $matches[0];
        }, $content);

        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return [
            'title' => $relativePath === 'README.md' ? 'Přehled systému' : $this->formatName(basename($fullPath, '.md')),
            'content' => $converter->convert($content)->getContent(),
            'raw' => $content,
        ];
    }

    /**
     * Vyhledávání v dokumentaci (v rámci aktuálního jazyka).
     */
    public function search(string $query): Collection
    {
        $query = trim($query);
        if (empty($query)) return collect();

        $path = File::exists($this->docsPath) ? $this->docsPath : $this->baseDocsPath;

        $finder = new Finder();
        $finder->files()->in($path)->name('*.md');

        $results = collect();
        $lowercaseQuery = mb_strtolower($query);

        foreach ($finder as $file) {
            $content = $file->getContents();
            $filename = $file->getFilename();
            $formattedName = $this->formatName($file->getFilenameWithoutExtension());

            if (Str::contains(mb_strtolower($content), $lowercaseQuery) ||
                Str::contains(mb_strtolower($filename), $lowercaseQuery) ||
                Str::contains(mb_strtolower($formattedName), $lowercaseQuery)) {

                $results->push([
                    'title' => $formattedName,
                    'path' => $this->getRelativePath($file->getPathname()),
                    'excerpt' => $this->getExcerpt($content, $query),
                ]);
            }
        }

        return $results;
    }

    protected function formatName(string $name): string
    {
        $originalName = $name;

        // Odstranění číselných předpon typu 01-
        $name = preg_replace('/^\d+-/', '', $name);

        // Mapování lokalizovaných názvů složek (pokud je locale cs)
        if (app()->getLocale() === 'cs') {
            $mapping = [
                'general' => 'Základní koncepty',
                'development' => 'Vývoj a standardy',
                'administration' => 'Administrace a Systém',
                'modules' => 'Funkční moduly',
                'ai' => 'AI a Pokročilé funkce',
                'ops' => 'Provoz a Nasazení',
                'manuals' => 'Manuály a QA',
                'users-profiles' => 'Uživatelé a profily',
                'sports-module' => 'Sportovní modul',
                'economy-module' => 'Ekonomický modul',
                'web-cms' => 'Web a CMS',
                'external-data' => 'Externí data a Importy',
                'sync-architecture' => 'Architektura synchronizace',
                'cbf-czbasketball' => 'Zdroj: cz.basketball',
                'legacy-import' => 'Historické importy',
                'system' => 'Systémové nastavení',
                'email' => 'E-mailová bezpečnost',
                'migration' => 'Migrační plán a strategie',
                'branding' => 'Branding a vizuální styl',
                'fixes' => 'Opravy a drobné změny',
            ];

            if (isset($mapping[$name])) {
                return $mapping[$name];
            }
        }

        // Nahrazení pomlček mezerami a kapitálky
        return Str::headline($name);
    }

    protected function getRelativePath(string $fullPath): string
    {
        return str_replace($this->docsPath . DIRECTORY_SEPARATOR, '', $fullPath);
    }

    protected function getExcerpt(string $content, string $query): string
    {
        $content = strip_tags($content);
        $pos = mb_stripos($content, $query);

        $start = max(0, $pos - 50);
        $length = mb_strlen($query) + 100;

        $excerpt = mb_substr($content, $start, $length);

        if ($start > 0) $excerpt = '...' . $excerpt;
        if ($start + $length < mb_strlen($content)) $excerpt .= '...';

        return $excerpt;
    }
}
