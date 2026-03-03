<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use App\Models\Team;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SeoGenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vygeneruje XML sitemapu pro veřejný web';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generuji sitemapu...');

        $sitemap = Sitemap::create();

        // Statické a hlavní stránky
        $sitemap->add(Url::create(route('public.home'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add(Url::create(route('public.news.index'))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY));
        $sitemap->add(Url::create(route('public.teams.index'))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create(route('public.galleries.index'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create(route('public.contact.index'))->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        // Zkusíme přidat nábor, pokud existuje routa
        try {
            $sitemap->add(Url::create(route('public.recruitment.index'))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        } catch (\Exception $e) {}

        // Stránky (Pages)
        Page::where('status', 'published')
            ->where('is_visible', true)
            ->get()
            ->each(function (Page $page) use ($sitemap) {
                $sitemap->add(Url::create(route('public.pages.show', $page->slug))
                    ->setLastModificationDate($page->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.6));
            });

        // Články (Posts)
        Post::where('status', 'published')
            ->where('is_visible', true)
            ->where('publish_at', '<=', now())
            ->get()
            ->each(function (Post $post) use ($sitemap) {
                $sitemap->add(Url::create(route('public.news.show', $post->slug))
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7));
            });

        // Týmy (Teams)
        Team::all()->each(function (Team $team) use ($sitemap) {
            $sitemap->add(Url::create(route('public.teams.show', $team->slug))
                ->setLastModificationDate($team->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.8));
        });

        // Galerie (Galleries)
        Gallery::where('is_public', true)
            ->where('is_visible', true)
            ->get()
            ->each(function (Gallery $gallery) use ($sitemap) {
                $sitemap->add(Url::create(route('public.galleries.show', $gallery->slug))
                    ->setLastModificationDate($gallery->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.5));
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemapa byla úspěšně vygenerována do public/sitemap.xml');
    }
}
