<?php

namespace Tests\Unit\Services\Help;

use App\Models\HelpCategory;
use App\Models\HelpArticle;
use App\Services\Help\HelpNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpNavigationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HelpNavigationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HelpNavigationService();
    }

    /** @test */
    public function it_generates_root_breadcrumbs()
    {
        $breadcrumbs = $this->service->getBreadcrumbs();

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals(__('admin.navigation.pages.help'), $breadcrumbs[0]['label']);
        $this->assertTrue($breadcrumbs[0]['is_active']);
    }

    /** @test */
    public function it_generates_category_breadcrumbs()
    {
        $root = HelpCategory::create([
            'name' => ['cs' => 'Root', 'en' => 'Root'],
            'slug' => 'root',
        ]);

        $child = HelpCategory::create([
            'name' => ['cs' => 'Child', 'en' => 'Child'],
            'slug' => 'child',
            'parent_id' => $root->id,
        ]);

        $breadcrumbs = $this->service->getBreadcrumbs($child);

        $this->assertCount(3, $breadcrumbs); // Help > Root > Child
        $this->assertEquals('Root', $breadcrumbs[1]['label']);
        $this->assertEquals('Child', $breadcrumbs[2]['label']);
        $this->assertTrue($breadcrumbs[2]['is_active']);
    }

    /** @test */
    public function it_generates_article_breadcrumbs()
    {
        $root = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'category',
        ]);

        $article = HelpArticle::create([
            'title' => ['cs' => 'Article', 'en' => 'Article'],
            'slug' => 'article',
            'category_id' => $root->id,
            'content' => ['cs' => 'Content', 'en' => 'Content'],
        ]);

        $breadcrumbs = $this->service->getBreadcrumbs($article);

        $this->assertCount(3, $breadcrumbs); // Help > Category > Article
        $this->assertEquals('Category', $breadcrumbs[1]['label']);
        $this->assertEquals('Article', $breadcrumbs[2]['label']);
        $this->assertTrue($breadcrumbs[2]['is_active']);
    }

    /** @test */
    public function it_prevents_infinite_recursion_in_breadcrumbs()
    {
        $cat1 = HelpCategory::create([
            'name' => ['cs' => 'Cat 1', 'en' => 'Cat 1'],
            'slug' => 'cat-1',
        ]);

        $cat2 = HelpCategory::create([
            'name' => ['cs' => 'Cat 2', 'en' => 'Cat 2'],
            'slug' => 'cat-2',
            'parent_id' => $cat1->id,
        ]);

        // Create a cycle manually (bypassing model events if any)
        \DB::table('help_categories')->where('id', $cat1->id)->update(['parent_id' => $cat2->id]);

        // This should not crash with out of memory
        $breadcrumbs = $this->service->getBreadcrumbs($cat2);

        $this->assertGreaterThan(1, $breadcrumbs->count());
        $this->assertLessThan(50, $breadcrumbs->count());
    }
}
