<?php

namespace Tests\Unit\Models;

use App\Models\HelpCategory;
use App\Models\HelpArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTranslationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_falls_back_to_czech_if_english_translation_is_missing()
    {
        $category = HelpCategory::create([
            'name' => ['cs' => 'Český název'], // Anglický chybí
            'slug' => 'test-category',
        ]);

        // Nastavíme locale na 'en'
        app()->setLocale('en');

        // Spatie translatable by měl vrátit cs pokud en chybí a je to tak nastaveno
        // V modelu HelpCategory jsme definovali getFallbackLocale()

        $this->assertEquals('Český název', $category->getTranslation('name', 'en'));
    }

    /** @test */
    public function it_returns_specific_locale_if_available()
    {
        $category = HelpCategory::create([
            'name' => ['cs' => 'Český název', 'en' => 'English Name'],
            'slug' => 'test-category',
        ]);

        $this->assertEquals('Český název', $category->getTranslation('name', 'cs'));
        $this->assertEquals('English Name', $category->getTranslation('name', 'en'));
    }

    /** @test */
    public function it_falls_back_to_czech_for_articles()
    {
        $cat = HelpCategory::create([
            'name' => ['cs' => 'Cat'],
            'slug' => 'cat',
        ]);

        $article = HelpArticle::create([
            'title' => ['cs' => 'Český článek'],
            'slug' => 'test-article',
            'category_id' => $cat->id,
            'content' => ['cs' => 'Obsah'],
        ]);

        $this->assertEquals('Český článek', $article->getTranslation('title', 'en'));
    }
}
