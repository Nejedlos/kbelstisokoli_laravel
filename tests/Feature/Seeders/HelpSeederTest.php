<?php

namespace Tests\Feature\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Database\Seeders\Help\HelpSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_help_structure_and_content()
    {
        // Spustíme seeder
        $this->seed(HelpSeeder::class);

        // Ověříme kategorie (mělo by jich být 6 podle architektury)
        $this->assertGreaterThanOrEqual(6, HelpCategory::count());
        $this->assertDatabaseHas('help_categories', ['slug' => 'uvod']);
        $this->assertDatabaseHas('help_categories', ['slug' => 'sport']);
        $this->assertDatabaseHas('help_categories', ['slug' => 'finance']);

        // Ověříme články
        $this->assertGreaterThan(0, HelpArticle::count());

        // Zkusíme najít konkrétní známé články ze seederu
        $this->assertDatabaseHas('help_articles', ['slug' => 'vstup-do-kabiny']);
        $this->assertDatabaseHas('help_articles', ['slug' => 'treninky-a-dochazka']);
    }
}
