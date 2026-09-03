<?php

namespace Tests\Unit\Services\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Services\Help\HelpSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HelpSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HelpSearchService;
    }

    #[Test]
    public function it_finds_articles_by_title()
    {
        $cat = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'cat',
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Jak se přihlásit', 'en' => 'How to login'],
            'slug' => 'login-guide',
            'content' => ['cs' => 'Nějaký text', 'en' => 'Some text'],
            'category_id' => $cat->id,
            'is_published' => true,
        ]);

        $results = $this->service->search('přihlásit');

        $this->assertCount(1, $results);
        $this->assertEquals('login-guide', $results->first()->slug);
    }

    #[Test]
    public function it_finds_articles_by_keywords()
    {
        $cat = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'cat',
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Platby', 'en' => 'Payments'],
            'slug' => 'payments',
            'category_id' => $cat->id,
            'search_keywords' => ['cs' => 'penize, ucet', 'en' => 'money, account'],
            'content' => ['cs' => '...', 'en' => '...'],
            'is_published' => true,
        ]);

        $results = $this->service->search('penize');

        $this->assertCount(1, $results);
        $this->assertEquals('payments', $results->first()->slug);
    }

    #[Test]
    public function it_filters_by_audience_role()
    {
        $cat = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'cat',
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Admin Only', 'en' => 'Admin Only'],
            'slug' => 'admin-only',
            'category_id' => $cat->id,
            'audience_roles' => ['admin'],
            'is_published' => true,
            'content' => ['cs' => '...', 'en' => '...'],
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Coach Only', 'en' => 'Coach Only'],
            'slug' => 'coach-only',
            'category_id' => $cat->id,
            'audience_roles' => ['coach'],
            'is_published' => true,
            'content' => ['cs' => '...', 'en' => '...'],
        ]);

        // Search for coach - should only find coach article
        $results = $this->service->forAudience('coach')->search('Only');
        $this->assertCount(1, $results);
        $this->assertEquals('coach-only', $results->first()->slug);

        // Search for admin - should find both
        $results = $this->service->forAudience('admin')->search('Only');
        $this->assertCount(2, $results);
    }

    #[Test]
    public function it_respects_published_status()
    {
        $cat = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'cat',
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Draft', 'en' => 'Draft'],
            'slug' => 'draft',
            'category_id' => $cat->id,
            'is_published' => false,
            'content' => ['cs' => '...', 'en' => '...'],
        ]);

        $results = $this->service->search('Draft');
        $this->assertCount(0, $results);
    }
}
