<?php

namespace Tests\Feature\Filament\Pages;

use App\Models\User;
use App\Models\HelpCategory;
use App\Models\HelpArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureTwoFactorEnabled::class,
            \App\Http\Middleware\CheckTwoFactorTimeout::class,
        ]);
    }

    /** @test */
    public function it_can_access_help_page_as_admin()
    {
        $admin = $this->createAdmin();
        $admin = $this->with2FA($admin);

        $this->actingAs($admin);

        $response = $this->get(\App\Filament\Pages\Help::getUrl());

        if ($response->status() !== 200) {
            dump("Redirected to: " . $response->headers->get('Location'));
            dump("Response content: " . mb_substr($response->content(), 0, 500));
        }

        $response->assertStatus(200);
        $response->assertSee(__('admin.navigation.pages.help'));
    }

    /** @test */
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

        $response = $this->get(\App\Filament\Pages\Help::getUrl());

        $response->assertStatus(200);
        $response->assertSee('Sportovní agenda');
    }

    /** @test */
    public function it_can_see_article_detail()
    {
        $admin = $this->createAdmin();
        $admin = $this->with2FA($admin);

        $cat = HelpCategory::create([
            'name' => ['cs' => 'Sport', 'en' => 'Sport'],
            'slug' => 'sport',
            'is_published' => true,
        ]);

        $article = HelpArticle::create([
            'title' => ['cs' => 'Jak vytvořit trénink', 'en' => 'How to create training'],
            'slug' => 'create-training',
            'category_id' => $cat->id,
            'content' => ['cs' => 'Detailed instructions...', 'en' => 'Detailed instructions...'],
            'is_published' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(\App\Filament\Pages\Help::getUrl(['file' => 'create-training']));

        $response->assertStatus(200);
        $response->assertSee('Jak vytvořit trénink');
        $response->assertSee('Detailed instructions...');
    }

    /** @test */
    public function it_can_search_on_help_page()
    {
        $admin = $this->createAdmin();
        $admin = $this->with2FA($admin);

        $cat = HelpCategory::create([
            'name' => ['cs' => 'Category', 'en' => 'Category'],
            'slug' => 'cat',
            'is_published' => true,
        ]);

        HelpArticle::create([
            'title' => ['cs' => 'Unikátní Článek', 'en' => 'Unique Article'],
            'slug' => 'unique-article',
            'category_id' => $cat->id,
            'is_published' => true,
            'content' => ['cs' => 'Obsah...', 'en' => 'Content...'],
        ]);

        $this->actingAs($admin);

        $response = $this->get(\App\Filament\Pages\Help::getUrl(['q' => 'Unikátní']));

        $response->assertStatus(200);
        $response->assertSee('Unikátní Článek');
    }
}
