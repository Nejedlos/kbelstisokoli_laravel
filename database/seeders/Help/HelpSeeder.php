<?php

namespace Database\Seeders\Help;

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
