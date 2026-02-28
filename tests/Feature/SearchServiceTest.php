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
            'section' => 'frontend',
            'title' => 'Nábor dětí',
            'content' => 'Hledáme nové sokolíky do našich oddílů.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/nabor',
            'checksum' => 'test1',
            'source' => 'page:1',
            'is_active' => true,
        ]);

        $searchService = app(SearchService::class);
        app()->setLocale('cs');

        $results = $searchService->search('nábor', section: 'frontend');
        $this->assertCount(1, $results);
        $this->assertEquals('Nábor dětí', $results->first()->title);
    }

    public function test_search_posts_returns_results_without_type_error()
    {
        // Vytvoření testovacího AI dokumentu
        \App\Models\AiDocument::create([
            'section' => 'frontend',
            'title' => 'Nový trenér',
            'content' => 'Máme nového trenéra pro mladší žáky. Detailní informace o novém trenérovi.',
            'summary' => 'Máme nového trenéra pro mladší žáky.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/news/novy-trener',
            'checksum' => 'test2',
            'source' => 'post:1',
            'is_active' => true,
        ]);

        $searchService = app(SearchService::class);
        app()->setLocale('cs');

        $results = $searchService->search('trenér', section: 'frontend');
        $this->assertCount(1, $results);
        $this->assertEquals('Nový trenér', $results->first()->title);
    }

    public function test_search_respects_sections()
    {
        // Frontend dokument
        \App\Models\AiDocument::create([
            'section' => 'frontend',
            'title' => 'Veřejná stránka',
            'content' => 'Obsah veřejné stránky o basketbalu.',
            'locale' => 'cs',
            'type' => 'frontend.resource',
            'url' => '/verejna',
            'checksum' => 'f1',
            'source' => 'page:1',
            'is_active' => true,
        ]);

        // Member dokument
        \App\Models\AiDocument::create([
            'section' => 'member',
            'title' => 'Členská stránka',
            'content' => 'Obsah pro přihlášené členy klubu.',
            'locale' => 'cs',
            'type' => 'member.resource',
            'url' => '/member/stranka',
            'checksum' => 'm1',
            'source' => 'page:2',
            'is_active' => true,
        ]);

        // Admin dokument
        \App\Models\AiDocument::create([
            'section' => 'admin',
            'title' => 'Admin nastavení',
            'content' => 'Konfigurace systému pro správce.',
            'locale' => 'cs',
            'type' => 'admin.resource',
            'url' => '/admin/settings',
            'checksum' => 'a1',
            'source' => 'page:3',
            'is_active' => true,
        ]);

        $searchService = app(SearchService::class);
        app()->setLocale('cs');

        // Hledání ve frontend sekci
        $results = $searchService->search('stránka', section: 'frontend');
        $this->assertCount(1, $results);
        $this->assertEquals('Veřejná stránka', $results->first()->title);

        // Hledání v member sekci
        $results = $searchService->search('stránka', section: 'member');
        $this->assertCount(1, $results);
        $this->assertEquals('Členská stránka', $results->first()->title);

        // Hledání v admin sekci
        $results = $searchService->search('nastavení', section: 'admin');
        $this->assertCount(1, $results);
        $this->assertEquals('Admin nastavení', $results->first()->title);
    }
}
