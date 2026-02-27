<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SeoMetadata;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
        $this->seedMenus();

        // Vyčistit cache po seedování, aby se změny projevily hned
        try {
            app(\App\Services\BrandingService::class)->clearCache();
        } catch (\Throwable $e) {
            // Ignorovat, pokud služba není dostupná
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            'club_name' => [
                'cs' => 'Kbelští sokoli',
                'en' => 'Kbely Falcons',
            ],
            'club_short_name' => [
                'cs' => 'Sokoli',
                'en' => 'Falcons',
            ],
            'slogan' => [
                'cs' => 'Více než jen basketbal. Jsme rodina.',
                'en' => 'More than just basketball. We are family.',
            ],
            'contact_email' => null,
            'contact_phone' => null,
            'contact_address' => 'Toužimská 700, Praha 9 - Kbely',
            'social_facebook' => null,
            'social_instagram' => null,
            'main_club_url' => 'https://www.basketkbely.cz/',
            'recruitment_url' => 'https://www.basketkbely.cz/zacnihrat',
            'maintenance_mode' => 'false',
            'venue_name' => 'RumcajsArena',
            'venue_street' => 'Třinecká 650',
            'venue_city' => 'Letňany',
            'venue_gps' => '50°8\'2.97"N, 14°30\'37.31"E',
            'venue_map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2557.423791906335!2d14.508026677153266!3d50.134503371533754!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470bed3fb2d307c7%3A0x80b9ba0fd7daac96!2zVMWZaW5lY2vDoSA2NTAsIDE5OSAwMCBMZXTFiGFueQ!5e0!3m2!1scs!2scz!4v1772030905282!5m2!1scs!2scz',
            'match_day' => 'Pátek 19:30 hod.',
            'contact_person' => 'Tomáš Spanilý',
            'contact_role' => [
                'cs' => 'vedoucí týmu',
                'en' => 'team leader',
            ],
            'contact_street' => 'Kovářská 17',
            'contact_city' => 'Praha 9',
            'contact_phone' => '+420 602 285 447',
            'contact_fax' => '+420 266 315 868',
            'contact_email' => 'spanily@keep69.cz',
            'footer_text' => [
                'cs' => '© ' . date('Y') . ' Basketbalový klub Kbelští sokoli. Všechna práva vyhrazena.',
                'en' => '© ' . date('Y') . ' Basketball club Kbely Falcons. All rights reserved.',
            ],
            'seo_description' => [
                'cs' => 'Oficiální web basketbalového klubu Kbelští sokoli. Informace o týmech, trénincích, zápasech a náborech pro děti i dospělé v Praze 9.',
                'en' => 'Official website of the Kbely Falcons basketball club. Information about teams, trainings, matches and recruitment for children and adults in Prague 9.',
            ],
            'seo_title_suffix' => ' | Kbelští sokoli',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    protected function seedPages(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => ['cs' => 'Domů', 'en' => 'Home'],
                'content' => $this->getHomeBlocks(),
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => [
                        'cs' => 'Basketbal Letňany & Kbely – Týmy C & E | Kbelští sokoli',
                        'en' => 'Basketball Letňany & Kbely – Teams C & E | Kbely Falcons'
                    ],
                    'description' => [
                        'cs' => 'Oficiální web basketbalových týmů Kbelští sokoli C & E (TJ Sokol Kbely). Hrajeme v Letňanech. Aktuální výsledky, tréninky a nábor nových hráčů.',
                        'en' => 'Official website of basketball teams Kbely Falcons C & E (TJ Sokol Kbely). We play in Letňany. Match results, trainings and recruitment.'
                    ],
                ],
            ],
            [
                'slug' => 'o-klubu',
                'title' => ['cs' => 'O klubu', 'en' => 'About club'],
                'content' => $this->getAboutBlocks(),
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'O klubu | Kbelští sokoli', 'en' => 'About Club | Kbely Falcons'],
                    'description' => ['cs' => 'Historie a vize basketbalového klubu Kbelští sokoli.', 'en' => 'History and vision of the Kbely Falcons basketball club.'],
                ],
            ],
            [
                'slug' => 'nabor',
                'title' => ['cs' => 'Nábor', 'en' => 'Join us'],
                'content' => $this->getJoinUsBlocks(),
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'Nábor basketbal Letňany – Muži C & E | Kbelští sokoli', 'en' => 'Basketball Recruitment Letňany – Men C & E | Kbely Falcons'],
                    'description' => ['cs' => 'Přijď si zahrát basketbal do Letňan! Týmy Kbelští sokoli C & E hledají nové spoluhráče. Tréninky v RumcajsAreně.', 'en' => 'Come play basketball in Letňany! Kbely Falcons C & E teams are looking for new teammates. Trainings in RumcajsArena.'],
                ],
            ],
            [
                'slug' => 'treninky',
                'title' => ['cs' => 'Tréninky', 'en' => 'Trainings'],
                'content' => [],
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'Rozpis tréninků | Kbelští sokoli', 'en' => 'Training Schedule | Kbely Falcons'],
                ],
            ],
            [
                'slug' => 'zapasy',
                'title' => ['cs' => 'Zápasy', 'en' => 'Matches'],
                'content' => [],
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'Rozpis a výsledky zápasů | Kbelští sokoli', 'en' => 'Match Schedule & Results | Kbely Falcons'],
                ],
            ],
            [
                'slug' => 'tymy',
                'title' => ['cs' => 'Týmy', 'en' => 'Teams'],
                'content' => [],
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'Naše týmy | Kbelští sokoli', 'en' => 'Our Teams | Kbely Falcons'],
                ],
            ],
            [
                'slug' => 'kontakt',
                'title' => ['cs' => 'Kontakt', 'en' => 'Contact'],
                'content' => [],
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'Kontaktujte nás | Kbelští sokoli', 'en' => 'Contact Us | Kbely Falcons'],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $seoData = $pageData['seo'] ?? null;
            unset($pageData['seo']);

            $page = Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );

            if ($seoData) {
                $page->seo()->updateOrCreate(
                    ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                    $seoData
                );
            }
        }
    }

    protected function seedMenus(): void
    {
        // Hlavní menu
        $headerMenu = Menu::updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Hlavní navigace']
        );

        // Vyčistit existující položky hlavního menu pro zamezení duplicitám
        $headerMenu->items()->delete();

        $items = [
            ['label' => ['cs' => 'Domů', 'en' => 'Home'], 'url' => '/', 'sort' => 10],
            ['label' => ['cs' => 'O klubu', 'en' => 'About'], 'url' => '/o-klubu', 'sort' => 20],
            ['label' => ['cs' => 'Týmy', 'en' => 'Teams'], 'url' => '/tymy', 'sort' => 30],
            ['label' => ['cs' => 'Tréninky', 'en' => 'Trainings'], 'url' => '/treninky', 'sort' => 40],
            ['label' => ['cs' => 'Zápasy', 'en' => 'Matches'], 'url' => '/zapasy', 'sort' => 50],
            ['label' => ['cs' => 'Nábor', 'en' => 'Join us'], 'url' => '/nabor', 'sort' => 60],
            ['label' => ['cs' => 'Kontakt', 'en' => 'Contact'], 'url' => '/kontakt', 'sort' => 70],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $headerMenu->id, 'url' => $item['url']],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort'],
                    'is_visible' => true,
                ]
            );
        }

        // Footer menu
        $footerMenu = Menu::updateOrCreate(
            ['location' => 'footer'],
            ['name' => 'Patička']
        );

        // Vyčistit existující položky menu pro zamezení duplicitám
        $footerMenu->items()->delete();

        $footerItems = [
            ['label' => ['cs' => 'O klubu', 'en' => 'About'], 'url' => '/o-klubu', 'sort' => 10],
            ['label' => ['cs' => 'Nábor', 'en' => 'Join us'], 'url' => '/nabor', 'sort' => 20],
            ['label' => ['cs' => 'Kontakt', 'en' => 'Contact'], 'url' => '/kontakt', 'sort' => 30],
            ['label' => ['cs' => 'GDPR', 'en' => 'GDPR'], 'url' => '/gdpr', 'sort' => 40],
        ];

        foreach ($footerItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerMenu->id, 'url' => $item['url']],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort'],
                    'is_visible' => true,
                ]
            );
        }

        // Footer Club menu
        $footerClubMenu = Menu::updateOrCreate(
            ['location' => 'footer_club'],
            ['name' => 'Patička - Týmy a klub']
        );

        // Vyčistit existující položky menu pro zamezení duplicitám
        $footerClubMenu->items()->delete();

        $footerClubItems = [
            ['label' => ['cs' => 'Muži C', 'en' => 'Men C'], 'url' => '/tymy/muzi-c', 'sort' => 10],
            ['label' => ['cs' => 'Muži E', 'en' => 'Men E'], 'url' => '/tymy/muzi-e', 'sort' => 20],
            ['label' => ['cs' => 'Hlavní web TJ Sokol Kbely Basketbal', 'en' => 'Main Club Website'], 'url' => 'https://www.basketkbely.cz/', 'sort' => 30, 'is_external' => true],
            ['label' => ['cs' => 'Nábor mládeže / Začni hrát', 'en' => 'Youth Recruitment'], 'url' => 'https://www.basketkbely.cz/zacnihrat', 'sort' => 40, 'is_external' => true],
            ['label' => ['cs' => 'Družstva (A, B, D a mládež)', 'en' => 'Teams (A, B, D & Youth)'], 'url' => 'https://www.basketkbely.cz/druzstva', 'sort' => 50, 'is_external' => true],
        ];

        foreach ($footerClubItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerClubMenu->id, 'url' => $item['url']],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort'],
                    'is_visible' => true,
                    // 'is_external' => $item['is_external'] ?? false, // Pokud existuje sloupec, jinak to vyřešíme v Blade
                ]
            );
        }
    }

    protected function getHomeBlocks(): array
    {
        return [
            'cs' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'eyebrow' => 'KBELŠTÍ SOKOLI • C & E',
                        'headline' => "Basketbal v Letňanech\npro týmy Sokol Kbely C & E",
                        'subheadline' => 'Vítejte na stránkách týmů C & E, které hrají v RumcajsAreně (Třinecká 650, Letňany). Jsme hrdou součástí TJ Sokol Kbely Basketball. Hledáte tým s tradicí, skvělou partou a chutí vyhrávat? Jste na správném místě.',
                        'cta_label' => 'Chci hrát za C & E',
                        'cta_url' => '/join',
                        'cta_secondary_label' => 'Ostatní týmy (Mládež & Elita)',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'microtext' => 'Domácí hala: Letňany, Třinecká 650. Součást TJ Sokol Kbely.',
                        'image_url' => 'assets/img/home/home-hero.jpg',
                        'video_url' => 'assets/video/hero.mp4',
                        'variant' => 'standard',
                        'alignment' => 'left',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'outline',
                        'alignment' => 'left',
                        'title' => 'Tento web patří týmům C & E',
                        'text' => 'Kbelští sokoli C & E jsou součástí širšího oddílu TJ Sokol Kbely Basketbal. Náš web se zaměřuje na týmy C a E – jejich zápasy, týmové dění a komunitu v Letňanech. Pokud hledáte elitní týmy (A, B) nebo mládežnické kategorie, pokračujte na hlavní web oddílu.',
                        'button_text' => 'Naše týmy (C & E)',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Ostatní nábory (Mládež & Elita)',
                        'secondary_button_url' => 'https://www.basketkbely.cz/zacnihrat',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Naše týmy a funnel',
                        'subtitle' => 'Hrajeme v Letňanech, ale patříme do velké kbelské rodiny.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Tým Muži C',
                                'description' => 'Pražský přebor B. Dynamický celek s ambicí posouvat se v tabulce výše. Naše jádro v Letňanech.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tymy/muzi-c',
                                'link_label' => 'Detail týmu C',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Zápasy C',
                                'badge' => 'PŘEBOR B',
                            ],
                            [
                                'title' => 'Tým Muži E',
                                'description' => '3. třída B. Hobby i soutěžní basket v RumcajsAreně. Pohodové tempo a radost ze hry.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tymy/muzi-e',
                                'link_label' => 'Detail týmu E',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Zápasy E',
                                'badge' => '3. TŘÍDA B',
                            ],
                            [
                                'title' => 'Ostatní týmy (A, B, D)',
                                'description' => 'Druhý konec Kbelského basketbalu. Elitní 2. liga i další soutěže na hlavním webu.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => 'https://www.basketkbely.cz/druzstva',
                                'link_label' => 'Zobrazit na basketkbely.cz',
                                'badge' => 'HLAVNÍ ODDÍL',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'secondary',
                        'title' => 'Navazujeme na tradici kbelského basketu',
                        'text' => 'TJ Sokol Kbely Basketbal sdružuje dospělé týmy i mládežnické kategorie a dlouhodobě buduje basketbalovou komunitu ve Kbelích. Naším cílem je držet kvalitní týmové prostředí, chuť hrát a dobré jméno klubu – na hřišti i mimo něj.',
                        'alignment' => 'center',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Chcete se k nám přidat?',
                        'subtitle' => 'Rozlišujeme nábor do našich mužských týmů C & E a do ostatních kategorií oddílu.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Nábor do Týmu C',
                                'description' => 'Hledáme zkušené parťáky pro Pražský přebor B. Pokud chceš hrát kvalitní basket a být součástí skvělé party v Letňanech, ozvi se.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/join/muzi-c',
                                'link_label' => 'Chci hrát za C',
                                'badge' => 'PŘEBOR B',
                            ],
                            [
                                'title' => 'Nábor do Týmu E',
                                'description' => 'Hledáš pohodový basket ve 3. třídě? Do našeho týmu E v Letňanech rádi přivítáme nové tváře, co milují hru a dobrou partu.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/join/muzi-e',
                                'link_label' => 'Chci hrát za E',
                                'badge' => '3. TŘÍDA B',
                            ],
                            [
                                'title' => 'Ostatní (A, B, D & Mládež)',
                                'description' => 'Hledáte basketbal pro děti nebo elitní týmy (A, B)? Kompletní informace o náborech zbytku oddílu najdete na hlavním webu.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Nábor dětí a elit',
                                'badge' => 'HLAVNÍ ODDÍL',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Co na tomto webu najdete',
                        'columns' => 4,
                        'cards' => [
                            [
                                'title' => 'Zápasy a program',
                                'description' => 'Přehled utkání, výsledků a týmového programu pro všechny naše mužské týmy.',
                                'icon' => 'calendar-days',
                                'link' => '/zapasy',
                            ],
                            [
                                'title' => 'Týmy a informace',
                                'description' => 'Základní informace o týmech, organizační přehled a kontaktní body.',
                                'icon' => 'users-gear',
                                'link' => '/tymy',
                            ],
                            [
                                'title' => 'Aktuality a dění',
                                'description' => 'Novinky z týmového života, oznámení a důležité informace pro hráče i fanoušky.',
                                'icon' => 'newspaper',
                                'link' => '/novinky',
                            ],
                            [
                                'title' => 'Členská sekce',
                                'description' => 'Přihlášení hráčů a členů pro docházku, RSVP a interní přehledy (chráněná část).',
                                'icon' => 'lock',
                                'link' => '/login',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'news_listing',
                    'data' => [
                        'title' => 'Novinky z našich týmů',
                        'subtitle' => 'Aktuality budou průběžně doplňovány.',
                        'limit' => 3,
                        'layout' => 'grid',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'light',
                        'title' => 'Hrajeme pro radost ze hry, tým a Kbely',
                        'text' => 'Sledujte dění kolem našich mužských týmů C & E, fanděte s námi a pokud hledáte basket pro děti nebo mládež, navštivte hlavní stránky kbelského basketu.',
                        'button_text' => 'Chci hrát za C & E',
                        'button_url' => '/join',
                        'secondary_button_text' => 'Nábor Mládež & Elita',
                        'secondary_button_url' => 'https://www.basketkbely.cz/zacnihrat',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'eyebrow' => 'KBELY FALCONS • C & E',
                        'headline' => "Basketball in Letňany\nfor Sokol Kbely C & E teams",
                        'subheadline' => 'Welcome to the pages of teams C & E, playing in RumcajsArena (Třinecká 650, Letňany). We are proud members of TJ Sokol Kbely Basketball.',
                        'cta_label' => 'Join C & E',
                        'cta_url' => '/join',
                        'cta_secondary_label' => 'Other Teams (Youth & Elite)',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'microtext' => 'Home court: Letňany, Třinecká 650. Part of TJ Sokol Kbely.',
                        'image_url' => 'assets/img/home/home-hero.jpg',
                        'video_url' => 'assets/video/hero.mp4',
                        'variant' => 'standard',
                        'alignment' => 'left',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'outline',
                        'alignment' => 'left',
                        'title' => 'This website belongs to teams C & E',
                        'text' => 'Kbely Falcons C & E are part of the wider TJ Sokol Kbely Basketbal club. Our website focuses on teams C and E – their matches, team life and community in Letňany.',
                        'button_text' => 'Our Teams (C & E)',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Other Recruitments (Youth & Elite)',
                        'secondary_button_url' => 'https://www.basketkbely.cz/zacnihrat',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Our Teams & Funnel',
                        'subtitle' => 'Playing in Letňany, belonging to the Kbely family.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Men C Team',
                                'description' => 'Prague Championship B. Dynamic squad aiming higher in the standings. Our core in Letňany.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tymy/muzi-c',
                                'link_label' => 'Men C Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Matches C',
                                'badge' => 'CHAMPIONSHIP B',
                            ],
                            [
                                'title' => 'Men E Team',
                                'description' => '3rd Class B. Hobby and competitive basketball in RumcajsArena. Relaxed pace and joy of the game.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tymy/muzi-e',
                                'link_label' => 'Men E Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Matches E',
                                'badge' => '3RD CLASS B',
                            ],
                            [
                                'title' => 'Other Teams (A, B, D)',
                                'description' => 'The other part of Kbely Basketball. Elite 2nd league and other competitions on the main website.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => 'https://www.basketkbely.cz/druzstva',
                                'link_label' => 'View on basketkbely.cz',
                                'badge' => 'MAIN CLUB',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'secondary',
                        'title' => 'Following the tradition of Kbely basketball',
                        'text' => 'TJ Sokol Kbely Basketbal brings together adult teams and youth categories. Our goal is to maintain a quality team environment and the good name of the club.',
                        'alignment' => 'center',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Want to Join Us?',
                        'subtitle' => 'Recruitment is separate for our men\'s teams C & E and for the rest of the club.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Join Team C',
                                'description' => 'Looking for experienced teammates for Prague Championship B. If you want to play quality basketball in Letňany, join us.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/join/muzi-c',
                                'link_label' => 'Join Team C',
                                'badge' => 'CHAMPIONSHIP B',
                            ],
                            [
                                'title' => 'Join Team E',
                                'description' => 'Looking for relaxed basketball in 3rd Class? Our Team E in Letňany welcomes new faces who love the game and a great team.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/join/muzi-e',
                                'link_label' => 'Join Team E',
                                'badge' => '3RD CLASS B',
                            ],
                            [
                                'title' => 'Others (A, B, D & Youth)',
                                'description' => 'Looking for basketball for kids or elite teams (A, B)? Complete info about recruitment for the rest of the club can be found on the main website.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Youth & Elite Recruitment',
                                'badge' => 'MAIN CLUB',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'What you will find here',
                        'columns' => 4,
                        'cards' => [
                            [
                                'title' => 'Matches and program',
                                'description' => 'Overview of matches and results for Men C and Men E.',
                                'icon' => 'calendar-days',
                                'link' => '/zapasy',
                            ],
                            [
                                'title' => 'Teams and information',
                                'description' => 'Basic team information and contact points.',
                                'icon' => 'users-gear',
                                'link' => '/tymy',
                            ],
                            [
                                'title' => 'News and events',
                                'description' => 'Latest news from team life and important info for fans.',
                                'icon' => 'newspaper',
                                'link' => '/novinky',
                            ],
                            [
                                'title' => 'Member section',
                                'description' => 'Login for players for attendance and RSVP.',
                                'icon' => 'lock',
                                'link' => '/login',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'news_listing',
                    'data' => [
                        'title' => 'News from C & E teams',
                        'subtitle' => 'News will be updated continuously.',
                        'limit' => 3,
                        'layout' => 'grid',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'light',
                        'title' => 'We play for the joy of the game, the team and Kbely',
                        'text' => 'Follow the activities of our C & E teams, cheer with us, and if you are looking for basketball for children or youth, visit the main pages of Kbely basketball.',
                        'button_text' => 'Our Teams',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Join Youth & Elite',
                        'secondary_button_url' => 'https://www.basketkbely.cz/zacnihrat',
                    ]
                ],
            ],
        ];
    }

    protected function getAboutBlocks(): array
    {
        return [
            'cs' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>O nás</h2><p>Kbelští sokoli vznikli s vizí vytvořit místo, kde se děti i dospělí mohou věnovat basketbalu na profesionální i rekreační úrovni.</p><h3>Naše hodnoty</h3><ul><li>Týmovost</li><li>Respekt</li><li>Vytrvalost</li><li>Radost</li></ul>',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>About Us</h2><p>Kbely Falcons were founded with a vision to create a place where children and adults can dedicate themselves to basketball on both professional and recreational levels.</p><h3>Our Values</h3><ul><li>Teamwork</li><li>Respect</li><li>Perseverance</li><li>Joy</li></ul>',
                    ]
                ],
            ],
        ];
    }

    protected function getJoinUsBlocks(): array
    {
        return [
            'cs' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => 'Pojď hrát za Kbelští sokoli C a E',
                        'subheadline' => 'Hledáme zkušené hráče pro naše mužské týmy v Pražském přeboru. Staň se součástí naší basketbalové rodiny.',
                        'variant' => 'minimal',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Který tým je pro tebe?',
                        'subtitle' => 'Vyber si podle svých zkušeností a časových možností. Oba týmy hrají v Letňanech (RumcajsArena).',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Tým Muži C',
                                'description' => 'Hledáme zkušené parťáky pro Pražský přebor B. Pokud chceš hrát kvalitní basket, pravidelně trénovat a být součástí ambiciózního týmu v Letňanech, ozvi se nám. Naše zázemí v RumcajsAreně je připraveno pro hráče s chutí vyhrávat a posouvat se v tabulce výše.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/join/muzi-c',
                                'link_label' => 'Chci hrát za C',
                                'badge' => 'PŘEBOR B',
                            ],
                            [
                                'title' => 'Tým Muži E',
                                'description' => 'Hledáš pohodový basket, ale stále se soutěžním duchem? Náš tým E hrající 3. třídu B v Letňanech je ideální volbou. Zakládáme si na skvělé partě, radosti ze hry a vítáme nové tváře, které se chtějí vrátit k basketbalu po pauze nebo hrát pro zábavu v super kolektivu.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/join/muzi-e',
                                'link_label' => 'Chci hrát za E',
                                'badge' => '3. TŘÍDA B',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'livewire',
                    'data' => [
                        'component' => 'recruitment-form',
                        'custom_id' => 'join',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Nábor do mužských týmů</h2><p>Tento web a náborový formulář slouží primárně pro zájemce o hraní v našich týmech <strong>Muži C</strong> a <strong>Muži E</strong>. Pokud máš za sebou basketbalovou minulost a chceš se vrátit k pravidelnému hraní v pohodovém, ale soutěživém kolektivu, jsi na správném místě.</p>',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h3>Hledáte basketbal pro děti?</h3><p>Pokud hledáte přípravku, žákovské nebo mládežnické kategorie, pokračujte prosím na hlavní klubový web, kde najdete veškeré informace k náborům dětí:</p><p><a href="https://www.basketkbely.cz/zacnihrat" class="btn btn-primary">Nábor mládeže (basketkbely.cz)</a></p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Chceš se k nám přidat do C nebo E?',
                        'text' => 'Neváhej nám napsat přes náš náborový formulář. Rádi tě uvidíme na tréninku, kde si můžeme vzájemně vyzkoušet, zda si sedneme na hřišti i v šatně.',
                        'button_text' => 'Chci se přidat',
                        'button_url' => '/join',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => 'Join Kbely Falcons C & E',
                        'subheadline' => 'We are looking for experienced players for our men\'s teams in the Prague Championship.',
                        'variant' => 'minimal',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Which team is for you?',
                        'subtitle' => 'Choose based on your experience and time availability. Both teams play in Letňany (RumcajsArena).',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Men C Team',
                                'description' => 'Looking for experienced teammates for Prague Championship B. If you want to play quality basketball, train regularly and be part of an ambitious team in Letňany, join us. Our facilities in RumcajsArena are ready for players who want to win and move up the table.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/join/muzi-c',
                                'link_label' => 'Join Team C',
                                'badge' => 'CHAMPIONSHIP B',
                            ],
                            [
                                'title' => 'Men E Team',
                                'description' => 'Looking for relaxed basketball but still with a competitive spirit? Our Team E playing in the 3rd Class B in Letňany is the ideal choice. We pride ourselves on a great community, joy of the game and welcome new faces who want to return to basketball after a break or play for fun.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/join/muzi-e',
                                'link_label' => 'Join Team E',
                                'badge' => '3RD CLASS B',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'livewire',
                    'data' => [
                        'component' => 'recruitment-form',
                        'custom_id' => 'join',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Men\'s Team Recruitment</h2><p>This website and recruitment form are primarily for those interested in playing for our <strong>Men C</strong> and <strong>Men E</strong> teams. If you have a basketball background and want to return to regular playing in a relaxed but competitive team, you are in the right place.</p>',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h3>Looking for basketball for kids?</h3><p>If you are looking for youth or mini-basketball categories, please proceed to the main club website:</p><p><a href="https://www.basketkbely.cz/zacnihrat" class="btn btn-primary">Youth Recruitment (basketkbely.cz)</a></p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Want to join C or E?',
                        'text' => 'Don\'t hesitate to contact us via our recruitment form. We\'d love to see you at practice.',
                        'button_text' => 'Join us',
                        'button_url' => '/join',
                    ]
                ],
            ],
        ];
    }
}
