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
            'perf_scenario' => 'standard',
            'perf_full_page_cache' => '0',
            'perf_fragment_cache' => '0',
            'perf_html_minification' => '0',
            'perf_livewire_navigate' => '0',
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
