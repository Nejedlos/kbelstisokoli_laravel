<?php

namespace Tests\Feature\Seeders;

use App\Models\HelpCategory;
use App\Models\HelpArticle;
use Database\Seeders\Help\HelpSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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
        $this->assertDatabaseHas('help_articles', ['slug' => 'prvni-kroky']);
        $this->assertDatabaseHas('help_articles', ['slug' => 'sprava-dochazky']);
    }
}
