<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\Help;
use App\Http\Middleware\CheckTwoFactorTimeout;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            EnsureTwoFactorEnabled::class,
            CheckTwoFactorTimeout::class,
        ]);
    }

    #[Test]
    public function it_can_access_help_page_as_admin()
    {
        $admin = $this->createAdmin();
        $admin = $this->with2FA($admin);

        $this->actingAs($admin);

        $response = $this->get(Help::getUrl());

        if ($response->status() !== 200) {
            dump('Redirected to: '.$response->headers->get('Location'));
            dump('Response content: '.mb_substr($response->content(), 0, 500));
        }

        $response->assertStatus(200);
        $response->assertSee(__('admin.navigation.pages.help'));
    }

    #[Test]
    public function it_can_see_categories_on_help_home()
    {
        $admin = $this->createAdmin();
        $admin = $this->with2FA($admin);

        HelpCategory::create([
            'name' => ['cs' => 'Sportovní agenda', 'en' => 'Sports Agenda'],
            'slug' => 'sport',
            'is_published' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(Help::getUrl());

        $response->assertStatus(200);
        $response->assertSee('Sportovní agenda');
    }

    #[Test]
    public function it_can_see_article_detail()
    {
        $admin = $this->with2FA($this->createAdmin());
        $this->actingAs($admin);
        $article = $this->createArticle();
        $this->get(Help::getUrl(['file' => $article->slug]))
            ->assertOk()->assertSee('Jedinečný obsah nápovědy');
    }

    #[Test]
    public function it_can_search_on_help_page()
    {
        $admin = $this->with2FA($this->createAdmin());
        $this->actingAs($admin);
        $this->createArticle();
        $this->get(Help::getUrl(['q' => 'Jedinečný']))
            ->assertOk()->assertSee('Jedinečný návod');
    }

    private function createArticle(): HelpArticle
    {
        $category = HelpCategory::create(['name' => ['cs' => 'Testovací kategorie'], 'slug' => 'test-category', 'is_published' => true]);

        return HelpArticle::create([
            'category_id' => $category->id,
            'title' => ['cs' => 'Jedinečný návod'],
            'slug' => 'unique-guide',
            'content' => ['cs' => 'Jedinečný obsah nápovědy'],
            'metadata' => ['cs' => ['section' => 'admin']],
            'is_published' => true,
        ]);
    }
}
