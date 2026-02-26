<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_results_without_type_error()
    {
        // Vytvoření testovacího AI dokumentu
        \App\Models\AiDocument::create([
            'title' => 'Nábor dětí',
            'content' => 'Hledáme nové sokolíky do našich oddílů.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/nabor',
            'checksum' => 'test1',
            'source' => 'page:1',
        ]);

        $searchService = app(SearchService::class);

        // Simulace hledání, které vyvolalo chybu
        // Musíme nastavit locale, protože SearchService ho používá
        app()->setLocale('cs');

        try {
            $results = $searchService->search('nábor');
            $this->assertCount(1, $results);
            $this->assertEquals('Nábor dětí', $results->first()->title);
            $this->assertEquals('Hledáme nové sokolíky do našich oddílů.', $results->first()->snippet);
        } catch (\TypeError $e) {
            $this->fail('SearchService threw a TypeError: ' . $e->getMessage());
        }
    }

    public function test_search_posts_returns_results_without_type_error()
    {
        // Vytvoření testovacího AI dokumentu
        \App\Models\AiDocument::create([
            'title' => 'Nový trenér',
            'content' => 'Máme nového trenéra pro mladší žáky. Detailní informace o novém trenérovi.',
            'summary' => 'Máme nového trenéra pro mladší žáky.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/news/novy-trener',
            'checksum' => 'test2',
            'source' => 'post:1',
        ]);

        $searchService = app(SearchService::class);
        app()->setLocale('cs');

        try {
            $results = $searchService->search('trenér');
            $this->assertCount(1, $results);
            $this->assertEquals('Nový trenér', $results->first()->title);
            $this->assertEquals('Máme nového trenéra pro mladší žáky.', $results->first()->snippet);
        } catch (\TypeError $e) {
            $this->fail('SearchService threw a TypeError: ' . $e->getMessage());
        }
    }

    public function test_search_works_in_different_locales()
    {
        \App\Models\AiDocument::create([
            'title' => 'Kontakt',
            'content' => 'Napište nám zprávu.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/kontakt',
            'checksum' => 'test3',
            'source' => 'page:2',
        ]);

        \App\Models\AiDocument::create([
            'title' => 'Contact Us',
            'content' => 'Send us a message.',
            'locale' => 'en',
            'type' => 'frontend.resource',
            'url' => '/contact',
            'checksum' => 'test4',
            'source' => 'page:2',
        ]);

        $searchService = app(SearchService::class);

        // Test Czech
        app()->setLocale('cs');
        $resultsCs = $searchService->search('Kontakt');
        $this->assertCount(1, $resultsCs);
        $this->assertEquals('Kontakt', $resultsCs->first()->title);
        $this->assertEquals('Napište nám zprávu.', $resultsCs->first()->snippet);

        // Test English
        app()->setLocale('en');
        $resultsEn = $searchService->search('Contact');
        $this->assertCount(1, $resultsEn);
        $this->assertEquals('Contact Us', $resultsEn->first()->title);
        $this->assertEquals('Send us a message.', $resultsEn->first()->snippet);
    }
}
