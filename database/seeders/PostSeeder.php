<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kategorie novinek
        $generalCategory = PostCategory::updateOrCreate(
            ['slug' => 'obecne'],
            [
                'name' => ['cs' => 'Obecné', 'en' => 'General'],
            ]
        );

        $teamCategory = PostCategory::updateOrCreate(
            ['slug' => 'tymove-info'],
            [
                'name' => ['cs' => 'Týmové informace', 'en' => 'Team Info'],
            ]
        );

        // Ukázkový článek
        Post::updateOrCreate(
            ['slug' => 'vitejte-na-novem-webu'],
            [
                'category_id' => $generalCategory->id,
                'title' => [
                    'cs' => 'Vítejte na novém webu Kbelští sokoli!',
                    'en' => 'Welcome to the new Kbely Falcons website!',
                ],
                'excerpt' => [
                    'cs' => 'Spustili jsme nový web pro naše týmy Muži C & Muži E.',
                    'en' => 'We have launched a new website for our Men C & Men E teams.',
                ],
                'content' => [
                    'cs' => '<p>Máme velkou radost, že vám můžeme představit náš nový web, který bude sloužit jako hlavní informační kanál pro naše mužské týmy C & E. Najdete zde rozpis zápasů, výsledky, informace o týmech i novinky z klubového dění.</p>',
                    'en' => '<p>We are very happy to present our new website, which will serve as the main information channel for our men\'s teams C & E. You will find match schedules, results, team information and news from club events here.</p>',
                ],
                'status' => 'published',
                'is_visible' => true,
                'publish_at' => now(),
            ]
        );
    }
}
