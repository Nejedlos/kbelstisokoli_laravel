<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PerformanceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'perf_scenario' => 'aggressive',
            'perf_full_page_cache' => '0',
            'perf_fragment_cache' => '1',
            'perf_html_minification' => '1',
            'perf_livewire_navigate' => '1',
            'perf_lazy_load_images' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
