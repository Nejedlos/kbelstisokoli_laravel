<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        $partner = Partner::updateOrCreate(
            ['slug' => 'eurotechno'],
            [
                'name' => 'Euro Techno',
                'type' => 'main_partner',
                'website_url' => 'https://www.eurotechno.eu/sluzby',
                'logo_path_png' => 'assets/img/partners/LOGO EUROTECHNO.png',
                'logo_path_webp' => 'assets/img/partners/LOGO EUROTECHNO.webp',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'show_on_homepage' => true,
                'show_below_hero' => true,
                'show_in_footer' => true,
                'show_on_match_pages' => true,
                'show_on_contact_page' => true,
                'show_on_recruitment_page' => true,
                'opened_in_new_tab' => true,
            ]
        );

        $partner->setTranslation('label', 'cs', 'Hlavní partner týmu');
        $partner->setTranslation('label', 'en', 'Main team partner');
        $partner->setTranslation('description', 'cs', 'Specialista na slaboproudé a silnoproudé systémy, bezpečnostní technologie, kamerové systémy a IT infrastrukturu.');
        $partner->setTranslation('description', 'en', 'Specialist in low-voltage and high-voltage systems, security technologies, camera systems, and IT infrastructure.');
        $partner->save();

        Cache::flush();
    }

    /**
     * Run the database seeds for backward compatibility with older Laravel versions
     */
    public function run(): void
    {
        $this->up();
    }
}
