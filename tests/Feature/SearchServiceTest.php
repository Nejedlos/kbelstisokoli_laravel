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
        // Vytvoření testovací stránky s překlady
        Page::create([
            'title' => ['cs' => 'Nábor dětí', 'en' => 'Recruitment of children'],
            'slug' => 'nabor',
            'content' => ['cs' => 'Hledáme nové sokolíky do našich oddílů.', 'en' => 'We are looking for new little falcons for our sections.'],
            'is_visible' => true,
        ]);

        $searchService = new SearchService();

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
        $category = PostCategory::create([
            'name' => ['cs' => 'Zprávy', 'en' => 'News'],
            'slug' => 'zpravy',
        ]);

        Post::create([
            'category_id' => $category->id,
            'title' => ['cs' => 'Nový trenér', 'en' => 'New coach'],
            'slug' => 'novy-trener',
            'excerpt' => ['cs' => 'Máme nového trenéra pro mladší žáky.', 'en' => 'We have a new coach for younger pupils.'],
            'content' => ['cs' => 'Detailní informace o novém trenérovi.', 'en' => 'Detailed information about the new coach.'],
            'is_visible' => true,
            'status' => 'published',
        ]);

        $searchService = new SearchService();
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
        Page::create([
            'title' => ['cs' => 'Kontakt', 'en' => 'Contact Us'],
            'slug' => 'kontakt',
            'content' => ['cs' => 'Napište nám zprávu.', 'en' => 'Send us a message.'],
            'is_visible' => true,
        ]);

        $searchService = new SearchService();

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
