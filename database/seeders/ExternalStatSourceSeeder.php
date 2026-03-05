<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExternalStatSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ExternalStatSource::updateOrCreate(
            ['slug' => 'czbasketball'],
            [
                'name' => 'cz.basketball',
                'source_url' => 'https://cz.basketball',
                'source_type' => 'html_table',
                'is_active' => true,
                'notes' => 'Hlavní zdroj statistik pro ČBF.',
            ]
        );
    }
}
