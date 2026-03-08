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

    public function __construct()
    {
        $this->docsPath = base_path('docs');
    }

    /**
     * Získá strukturu dokumentace pro sidebar.
     */
    public function getTree(): Collection
    {
        if (!File::exists($this->docsPath)) {
            return collect();
        }

        return $this->scanDirectory($this->docsPath);
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
        $fullPath = $this->docsPath . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);

        if (!File::exists($fullPath) || !File::isFile($fullPath)) {
            return null;
        }

        $content = File::get($fullPath);
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return [
            'title' => $this->formatName(basename($fullPath, '.md')),
            'content' => $converter->convert($content)->getContent(),
            'raw' => $content,
        ];
    }

    /**
     * Vyhledávání v dokumentaci.
     */
    public function search(string $query): Collection
    {
        if (empty($query)) return collect();

        $finder = new Finder();
        $finder->files()->in($this->docsPath)->name('*.md');

        $results = collect();

        foreach ($finder as $file) {
            $content = $file->getContents();
            if (Str::contains(mb_strtolower($content), mb_strtolower($query)) ||
                Str::contains(mb_strtolower($file->getFilename()), mb_strtolower($query))) {

                $results->push([
                    'title' => $this->formatName($file->getFilenameWithoutExtension()),
                    'path' => $this->getRelativePath($file->getPathname()),
                    'excerpt' => $this->getExcerpt($content, $query),
                ]);
            }
        }

        return $results;
    }

    protected function formatName(string $name): string
    {
        // Odstranění číselných předpon typu 01-
        $name = preg_replace('/^\d+-/', '', $name);
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
