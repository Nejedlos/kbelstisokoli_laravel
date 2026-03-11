<?php

namespace Database\Seeders;

use Database\Seeders\Help\HelpArticleSeeder;
use Database\Seeders\Help\HelpCategorySeeder;
use Illuminate\Database\Seeder;

class HelpSeeder extends Seeder
{
    /**
     * Seed the help system.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            HelpCategorySeeder::class,
            HelpArticleSeeder::class,
        ]);
    }
}
