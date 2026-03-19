<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class HelpService
{
    protected string $basePath;

    public function __construct()
    {
        $this->setPathByLocale(app()->getLocale());
    }

    public function setPathByLocale(?string $locale): void
    {
        $locale = $locale ?: config('app.fallback_locale', 'cs');
        $this->basePath = base_path("docs/help/{$locale}");

        if (!File::isDirectory($this->basePath)) {
            $this->basePath = base_path("docs/help/cs");
        }
    }

    public function getTree(): Collection
    {
        if (!File::isDirectory($this->basePath)) {
            return collect();
        }

        return $this->scanDirectory($this->basePath);
    }

    protected function scanDirectory(string $path): Collection
    {
        $items = collect();
        $files = File::files($path);
        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $items->push([
                'type' => 'directory',
                'name' => $this->getCategoryName($directory),
                'description' => $this->getCategoryDescription($directory),
                'slug' => $dirName,
                'path' => $this->getRelativePath($directory),
                'icon' => $this->getCategoryIcon($dirName),
                'color' => $this->getCategoryColor($dirName),
                'children' => $this->scanDirectory($directory),
            ]);
        }

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            if ($fileName === 'README.md' || !Str::endsWith($fileName, '.md')) {
                continue;
            }

            $items->push([
                'type' => 'file',
                'name' => $this->getFileName($file->getPathname()),
                'description' => $this->getFileDescription($file->getPathname()),
                'slug' => $this->getRelativePath($file->getPathname()),
                'path' => $this->getRelativePath($file->getPathname()),
                'icon' => 'fa-file-lines',
                'color' => 'slate',
                'children' => collect(),
            ]);
        }

        return $items->sortBy('slug');
    }

    public function getFileContent(string $relativePath): ?array
    {
        $fullPath = base_path($relativePath);

        // Security check - ensure path is within help directory
        if (!Str::startsWith(realpath($fullPath), base_path('docs/help'))) {
            // Try within current locale
            $fullPath = $this->basePath . '/' . ltrim($relativePath, '/');
            if (!File::exists($fullPath)) {
                return null;
            }
        }

        if (!File::exists($fullPath) || !File::isFile($fullPath)) {
            return null;
        }

        $content = File::get($fullPath);
        $title = $this->formatName(basename($fullPath));

        // Extract title from first H1 if exists
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = $matches[1];
        }

        return [
            'title' => $title,
            'content' => Str::markdown($content),
            'raw' => $content,
        ];
    }

    public function search(string $query): Collection
    {
        if (empty($query)) {
            return collect();
        }

        $results = collect();
        $allFiles = File::allFiles($this->basePath);

        foreach ($allFiles as $file) {
            if (!Str::endsWith($file->getFilename(), '.md')) {
                continue;
            }

            $content = File::get($file->getPathname());
            if (Str::contains(Str::lower($content), Str::lower($query)) || Str::contains(Str::lower($file->getFilename()), Str::lower($query))) {
                $title = $this->formatName($file->getFilename());
                if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                    $title = $matches[1];
                }

                $results->push([
                    'title' => $title,
                    'path' => $this->getRelativePath($file->getPathname()),
                    'excerpt' => $this->getExcerpt($content, $query),
                ]);
            }
        }

        return $results;
    }

    protected function formatName(string $name): string
    {
        $name = Str::replaceLast('.md', '', $name);
        $name = preg_replace('/^\d+-/', '', $name); // Remove leading numbers (01-, 02-)
        $name = str_replace('-', ' ', $name);
        return Str::ucfirst($name);
    }

    protected function getRelativePath(string $fullPath): string
    {
        return Str::after($fullPath, base_path() . '/');
    }

    protected function getExcerpt(string $content, string $query): string
    {
        $content = strip_tags(Str::markdown($content));
        $pos = mb_stripos($content, $query);

        $start = max(0, $pos - 50);
        $length = mb_strlen($query) + 100;

        $excerpt = mb_substr($content, $start, $length);

        if ($start > 0) $excerpt = '...' . $excerpt;
        if ($start + $length < mb_strlen($content)) $excerpt .= '...';

        return $excerpt;
    }

    protected function getCategoryName(string $directory): string
    {
        $dirName = basename($directory);
        $slug = preg_replace('/^\d+-/', '', $dirName);

        $translated = __("admin.help.categories.{$slug}.name");
        if ($translated !== "admin.help.categories.{$slug}.name") {
            return $translated;
        }

        // Try README.md H1
        $readmePath = $directory . '/README.md';
        if (File::exists($readmePath)) {
            $content = File::get($readmePath);
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return $this->formatName($dirName);
    }

    protected function getFileName(string $path): string
    {
        if (!File::exists($path)) {
            return $this->formatName(basename($path));
        }

        $content = File::get($path);
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return $this->formatName(basename($path));
    }

    protected function getFileDescription(string $path): string
    {
        if (!File::exists($path)) {
            return '';
        }

        $content = File::get($path);
        // Remove H1 title and return first paragraph
        $content = preg_replace('/^#\s+.+$/m', '', $content);
        $content = trim($content);
        return Str::limit(strip_tags(Str::markdown($content)), 120);
    }

    protected function getCategoryDescription(string $directory): string
    {
        $dirName = basename($directory);
        $slug = preg_replace('/^\d+-/', '', $dirName);

        $translated = __("admin.help.categories.{$slug}.description");
        if ($translated !== "admin.help.categories.{$slug}.description") {
            return $translated;
        }

        $readmePath = $directory . '/README.md';
        if (File::exists($readmePath)) {
            $content = File::get($readmePath);
            // Remove H1 title and return first paragraph
            $content = preg_replace('/^#\s+.+$/m', '', $content);
            $content = trim($content);
            return Str::limit(strip_tags(Str::markdown($content)), 120);
        }

        // Fallback descriptions based on directory name
        return match (true) {
            Str::contains($directory, 'sport') => 'Vše o zápasech, trénincích a sportovních akcích klubu.',
            Str::contains($directory, 'lid') || Str::contains($directory, 'peopl') => 'Správa uživatelů, profilů hráčů a členské základny.',
            Str::contains($directory, 'ekonom') || Str::contains($directory, 'financ') => 'Návody pro správu plateb, předpisů a finančního řízení.',
            Str::contains($directory, 'obsah') || Str::contains($directory, 'content') => 'Jak spravovat články, galerie a další obsah webu.',
            Str::contains($directory, 'system') => 'Technické nastavení systému, oprávnění a údržba.',
            default => 'Podrobný návod a postupy pro tuto sekci nápovědy.',
        };
    }

    protected function getCategoryIcon(string $dirName): string
    {
        return match (true) {
            Str::contains($dirName, 'sport') => 'fa-basketball',
            Str::contains($dirName, 'lide') || Str::contains($dirName, 'people') => 'fa-users',
            Str::contains($dirName, 'ekonomika') || Str::contains($dirName, 'finance') => 'fa-money-bill-transfer',
            Str::contains($dirName, 'obsah') || Str::contains($dirName, 'content') => 'fa-newspaper',
            Str::contains($dirName, 'system') => 'fa-gear',
            default => 'fa-circle-info',
        };
    }

    protected function getCategoryColor(string $dirName): string
    {
        return match (true) {
            Str::contains($dirName, 'sport') => 'orange',
            Str::contains($dirName, 'lide') || Str::contains($dirName, 'people') => 'blue',
            Str::contains($dirName, 'ekonomika') || Str::contains($dirName, 'finance') => 'green',
            Str::contains($dirName, 'obsah') || Str::contains($dirName, 'content') => 'purple',
            Str::contains($dirName, 'system') => 'red',
            default => 'slate',
        };
    }
}
