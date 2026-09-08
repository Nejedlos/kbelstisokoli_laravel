<?php

namespace Database\Seeders;

use App\Models\ExternalStatSource;
use Illuminate\Database\Seeder;

class ExternalStatSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExternalStatSource::updateOrCreate(
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
