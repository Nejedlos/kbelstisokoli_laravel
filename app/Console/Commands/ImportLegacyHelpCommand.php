<?php

namespace App\Console\Commands;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportLegacyHelpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'help:import-legacy {--force : Vynutit přepsání i upravených záznamů}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importuje nápovědu ze starého Markdown systému do databáze';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $basePath = base_path('docs/help');
        $csPath = $basePath . '/cs';
        $enPath = $basePath . '/en';

        if (!File::isDirectory($csPath)) {
            $this->error("Adresář s českou nápovědou nebyl nalezen: {$csPath}");
            return Command::FAILURE;
        }

        $this->info('Zahajuji import staré nápovědy...');

        $directories = File::directories($csPath);
        $totalCategories = 0;
        $totalArticles = 0;

        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $slug = $this->extractSlug($dirName);
            $sortOrder = $this->extractSortOrder($dirName);

            $this->info("Importuji kategorii: {$slug}");

            // Metadata kategorie
            $readmePath = $directory . '/README.md';
            $names = ['cs' => $this->formatName($dirName)];
            $descriptions = ['cs' => ''];

            if (File::exists($readmePath)) {
                $readmeContent = File::get($readmePath);
                $names['cs'] = $this->extractTitle($readmeContent, $names['cs']);
                $descriptions['cs'] = $this->extractFirstParagraph($readmeContent);
            }

            // Anglická verze kategorie
            $enDir = $enPath . '/' . $dirName;
            if (File::isDirectory($enDir)) {
                $enReadmePath = $enDir . '/README.md';
                if (File::exists($enReadmePath)) {
                    $enReadmeContent = File::get($enReadmePath);
                    $names['en'] = $this->extractTitle($enReadmeContent, $slug);
                    $descriptions['en'] = $this->extractFirstParagraph($enReadmeContent);
                }
            }

            $category = HelpCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $names,
                    'description' => $descriptions,
                    'sort_order' => $sortOrder,
                    'icon' => $this->guessIcon($slug),
                    'color' => $this->guessColor($slug),
                    'is_active' => true,
                    'is_customized' => false,
                ]
            );

            $totalCategories++;

            // Import článků v kategorii
            $files = File::files($directory);
            foreach ($files as $file) {
                $fileName = $file->getFilename();
                if ($fileName === 'README.md' || !Str::endsWith($fileName, '.md')) {
                    continue;
                }

                $articleSlug = $this->extractSlug($fileName);
                $articleSortOrder = $this->extractSortOrder($fileName);

                $this->line("  - Importuji článek: {$articleSlug}");

                $csContent = File::get($file->getPathname());
                $titles = ['cs' => $this->extractTitle($csContent, $this->formatName($fileName))];
                $contents = ['cs' => $this->removeTitle($csContent)];
                $excerpts = ['cs' => $this->extractFirstParagraph($csContent)];

                $metadata = [
                    'cs' => [
                        'short_intro' => $excerpts['cs'],
                        'audience' => $this->detectAudience($csContent),
                    ],
                ];

                // Anglická verze článku
                $enFile = $enDir . '/' . $fileName;
                if (File::exists($enFile)) {
                    $enContent = File::get($enFile);
                    $titles['en'] = $this->extractTitle($enContent, $articleSlug);
                    $contents['en'] = $this->removeTitle($enContent);
                    $excerpts['en'] = $this->extractFirstParagraph($enContent);
                    $metadata['en'] = [
                        'short_intro' => $excerpts['en'],
                        'audience' => $this->detectAudience($enContent),
                    ];
                }

                $article = HelpArticle::where('slug', $articleSlug)->where('category_id', $category->id)->first();

                if ($article && $article->is_customized && !$this->option('force')) {
                    $this->warn("    Článek {$articleSlug} byl upraven ručně, přeskakuji (použijte --force pro přepsání)");
                    continue;
                }

                $audienceRoles = $metadata['cs']['audience'] ?? [];
                if (isset($metadata['en']['audience'])) {
                    $audienceRoles = array_unique(array_merge($audienceRoles ?? [], $metadata['en']['audience'] ?? []));
                }

                HelpArticle::updateOrCreate(
                    ['slug' => $articleSlug, 'category_id' => $category->id],
                    [
                        'title' => $titles,
                        'content' => $contents,
                        'excerpt' => $excerpts,
                        'sort_order' => $articleSortOrder,
                        'is_published' => true,
                        'is_customized' => false,
                        'audience_roles' => $audienceRoles,
                        'metadata' => $metadata,
                        'source_hash' => md5(serialize($contents)),
                    ]
                );

                $totalArticles++;
            }
        }

        // Speciální případ: volné soubory v kořeni csPath
        $rootFiles = File::files($csPath);
        if (count($rootFiles) > 0) {
            $this->info('Importuji volné soubory z kořene...');
            // Pro jednoduchost je dáme do kategorie "Ostatní" pokud neexistuje vhodnější
            $miscCategory = HelpCategory::firstOrCreate(
                ['slug' => 'ostatni'],
                [
                    'name' => ['cs' => 'Ostatní', 'en' => 'Miscellaneous'],
                    'sort_order' => 99,
                    'icon' => 'fa-circle-info',
                    'color' => 'slate',
                    'is_active' => true,
                ]
            );

            foreach ($rootFiles as $file) {
                $fileName = $file->getFilename();
                if (!Str::endsWith($fileName, '.md')) continue;

                $articleSlug = $this->extractSlug($fileName);
                $csContent = File::get($file->getPathname());

                $titles = ['cs' => $this->extractTitle($csContent, $this->formatName($fileName))];
                $contents = ['cs' => $this->removeTitle($csContent)];

                HelpArticle::updateOrCreate(
                    ['slug' => $articleSlug, 'category_id' => $miscCategory->id],
                    [
                        'title' => $titles,
                        'content' => $contents,
                        'is_published' => true,
                        'is_customized' => false,
                    ]
                );
                $totalArticles++;
            }
        }

        $this->success("Import dokončen! Importováno {$totalCategories} kategorií a {$totalArticles} článků.");

        return Command::SUCCESS;
    }

    protected function extractSlug(string $name): string
    {
        $name = Str::replaceLast('.md', '', $name);
        return preg_replace('/^\d+-/', '', $name);
    }

    protected function extractSortOrder(string $name): int
    {
        if (preg_match('/^(\d+)-/', $name, $matches)) {
            return (int) $matches[1];
        }
        return 50;
    }

    protected function formatName(string $name): string
    {
        $name = $this->extractSlug($name);
        $name = str_replace('-', ' ', $name);
        return Str::ucfirst($name);
    }

    protected function extractTitle(string $content, string $fallback): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return $fallback;
    }

    protected function removeTitle(string $content): string
    {
        return trim(preg_replace('/^#\s+.+$/m', '', $content));
    }

    protected function extractFirstParagraph(string $content): string
    {
        $content = $this->removeTitle($content);
        $paragraphs = preg_split('/\n\s*\n/', $content);
        if (isset($paragraphs[0])) {
            return Str::limit(strip_tags(Str::markdown($paragraphs[0])), 200);
        }
        return '';
    }

    protected function guessIcon(string $slug): string
    {
        return match (true) {
            Str::contains($slug, 'sport') => 'fa-basketball',
            Str::contains($slug, 'lide') || Str::contains($slug, 'people') => 'fa-users',
            Str::contains($slug, 'ekonom') || Str::contains($slug, 'financ') => 'fa-money-bill-transfer',
            Str::contains($slug, 'obsah') || Str::contains($slug, 'content') => 'fa-newspaper',
            Str::contains($slug, 'system') => 'fa-gear',
            default => 'fa-circle-info',
        };
    }

    protected function guessColor(string $slug): string
    {
        return match (true) {
            Str::contains($slug, 'sport') => 'orange',
            Str::contains($slug, 'lide') || Str::contains($slug, 'people') => 'blue',
            Str::contains($slug, 'ekonom') || Str::contains($slug, 'financ') => 'green',
            Str::contains($slug, 'obsah') || Str::contains($slug, 'content') => 'purple',
            Str::contains($slug, 'system') => 'red',
            default => 'slate',
        };
    }

    protected function detectAudience(string $content): ?array
    {
        $roles = [];
        if (preg_match('/Pro koho je sekce určena\s*(.+?)(?=###|$)/si', $content, $matches)) {
            $section = Str::lower($matches[1]);
            if (Str::contains($section, 'admin')) $roles[] = 'admin';
            if (Str::contains($section, 'trenér') || Str::contains($section, 'kouč') || Str::contains($section, 'coach')) $roles[] = 'coach';
            if (Str::contains($section, 'editor')) $roles[] = 'editor';
            if (Str::contains($section, 'ekonom')) $roles[] = 'economist';
        }

        return count($roles) > 0 ? $roles : null;
    }

    protected function success(string $message): void
    {
        $this->output->writeln("<info>{$message}</info>");
    }
}
