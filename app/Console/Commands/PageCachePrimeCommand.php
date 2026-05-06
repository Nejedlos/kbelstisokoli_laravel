<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PageCachePrimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:prime {--locale=all : Locale to prime (cs, en, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Naplní full-page cache pro veřejný web (crawlováním)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting page cache priming...');

        // Ensure full_page_cache is enabled for this command
        config(['performance.features.full_page_cache' => true]);

        $locales = $this->option('locale') === 'all' ? ['cs', 'en'] : [$this->option('locale')];

        $urls = $this->getPublicUrls();
        $this->info('Found ' . count($urls) . ' base public URLs to prime for ' . count($locales) . ' locales.');

        $originalLocale = app()->getLocale();

        try {
            foreach ($locales as $locale) {
                $this->info("--- Priming for locale: $locale ---");
                app()->setLocale($locale);

                foreach ($urls as $url) {
                    $this->primeUrl($url, $locale);
                }
            }
        } finally {
            app()->setLocale($originalLocale);
        }

        $this->info('Page cache priming finished.');

        return 0;
    }

    /**
     * Get all public URLs to prime.
     */
    protected function getPublicUrls(): array
    {
        $urls = [
            route('public.home'),
            route('public.teams.index'),
            route('public.galleries.index'),
            route('public.contact.index'),
            route('public.about'),
            route('public.history.index'),
            route('public.gdpr'),
        ];

        try {
            if (route('public.recruitment.index')) {
                $urls[] = route('public.recruitment.index');
            }
        } catch (\Exception $e) {}

        // Pages (Statické stránky z CMS)
        $excludedSlugs = ['home', 'novinky', 'zapasy', 'treninky', 'akce'];

        Page::where('status', 'published')
            ->where('is_visible', true)
            ->get()
            ->each(function ($page) use (&$urls, $excludedSlugs) {
                // Vyloučíme homepage (už tam je) a vyloučené sekce
                if (!in_array($page->slug, $excludedSlugs)) {
                    $urls[] = route('public.pages.show', $page->slug);
                }
            });

        // Teams - Detaily týmů (ty se tak často nemění, soupiska je ok)
        Team::all()->each(function ($team) use (&$urls) {
            $urls[] = route('public.teams.show', $team->slug);
        });

        // Galerie (Také relativně statické)
        Gallery::where('is_public', true)
            ->where('is_visible', true)
            ->get()
            ->each(function ($gallery) use (&$urls) {
                $urls[] = route('public.galleries.show', $gallery->slug);
            });

        return array_unique($urls);
    }

    /**
     * Prime a single URL.
     */
    protected function primeUrl(string $url, string $locale): void
    {
        $defaultLocale = 'cs';

        // Pro výchozí jazyk (cs) voláme čistou URL bez lang parametru,
        // protože to je to, co dostanou běžní návštěvníci (předpokládáme).
        // Pro ostatní jazyky musíme lang parametr poslat, aby SetLocale detekoval správný jazyk.
        $primeUrl = $url;
        if ($locale !== $defaultLocale) {
            $primeUrl = $url . (str_contains($url, '?') ? '&' : '?') . 'lang=' . $locale;
        }

        $this->comment("  Crawling: $primeUrl [$locale]");

        try {
            // Použijeme Http::get bez sledování přesměrování (protože SetLocaleMiddleware přesměrovává)
            // Ale my potřebujeme, aby se ten požadavek PROVEDL až k controlleru pro dané locale.
            // Pokud nás middleware přesměruje, tak se ten původní požadavek k controlleru nedostane.

            // Řešení: Použijeme cookie nebo header, který SetLocaleMiddleware pochopí bez redirectu?
            // Nebo prostě necháme Http klienta sledovat redirecty.

            $response = Http::withHeaders([
                'Accept-Language' => $locale,
                'X-App-Locale' => $locale,
                'X-Prime-Cache' => 'true',
            ])->get($primeUrl);

            if ($response->successful()) {
                $this->info("    OK: $url");
            } else {
                $this->warn("    Failed (" . $response->status() . "): $url");
            }
        } catch (\Exception $e) {
            $this->error("    Error crawling $url: " . $e->getMessage());
        }
    }
}
