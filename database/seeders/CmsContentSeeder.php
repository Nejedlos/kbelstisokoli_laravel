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
                        'cs' => 'Kbelští sokoli – Mužské týmy A–E | Basket Kbely Praha',
                        'en' => 'Kbely Falcons – Men\'s teams A–E | Basketball Kbely Prague'
                    ],
                    'description' => [
                        'cs' => 'Web mužských týmů Kbelští sokoli A–E v rámci TJ Sokol Kbely Basketbal. Zápasy, týmové informace, aktuality a odkazy na nábor a hlavní kbelský basket.',
                        'en' => 'Website of Men\'s teams Kbely Falcons A–E within TJ Sokol Kbely Basketball. Matches, team info, news and links to recruitment.'
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
                    'title' => ['cs' => 'Nábor nových hráčů | Kbelští sokoli', 'en' => 'Recruitment of new players | Kbely Falcons'],
                    'description' => ['cs' => 'Hledáme nové talenty! Přijďte si vyzkoušet basketbal do Kbel.', 'en' => 'We are looking for new talents! Come and try basketball in Kbely.'],
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
            ['label' => ['cs' => 'Muži A', 'en' => 'Men A'], 'url' => '/tymy/muzi-a', 'sort' => 10],
            ['label' => ['cs' => 'Muži B', 'en' => 'Men B'], 'url' => '/tymy/muzi-b', 'sort' => 20],
            ['label' => ['cs' => 'Muži C', 'en' => 'Men C'], 'url' => '/tymy/muzi-c', 'sort' => 30],
            ['label' => ['cs' => 'Muži D', 'en' => 'Men D'], 'url' => '/tymy/muzi-d', 'sort' => 40],
            ['label' => ['cs' => 'Muži E', 'en' => 'Men E'], 'url' => '/tymy/muzi-e', 'sort' => 50],
            ['label' => ['cs' => 'Hlavní web TJ Sokol Kbely Basketbal', 'en' => 'Main Club Website'], 'url' => 'https://www.basketkbely.cz/', 'sort' => 60, 'is_external' => true],
            ['label' => ['cs' => 'Nábor/Začni hrát', 'en' => 'Recruitment'], 'url' => 'https://www.basketkbely.cz/zacnihrat', 'sort' => 70, 'is_external' => true],
            ['label' => ['cs' => 'Družstva a mládež', 'en' => 'Teams & Youth'], 'url' => 'https://www.basketkbely.cz/druzstva', 'sort' => 80, 'is_external' => true],
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
                        'eyebrow' => 'KBELŠTÍ SOKOLI • MUŽI C & MUŽI E',
                        'headline' => "Basket ve Kbelích pro srdcaře,\ntýmové hráče a komunitu",
                        'subheadline' => 'Jsme týmy Muži C a Muži E v rámci TJ Sokol Kbely Basketbal. Na tomto webu najdete informace o našich zápasech, týmech a dění kolem nás – a zároveň cestu do širšího světa kbelského basketu, který staví na tradici, komunitu a práci s mládeží.',
                        'cta_label' => 'Zobrazit naše týmy',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'Jak začít hrát',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'cta_tertiary_label' => 'Navštívit hlavní web TJ Sokol Kbely',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Součást oddílu s tradicí basketbalu ve Kbelích a návazností na mládežnické kategorie.',
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
                        'title' => 'Tento web patří mužským týmům A–E',
                        'text' => 'Kbelští sokoli jsou součástí širšího oddílu TJ Sokol Kbely Basketbal. Náš web se zaměřuje na mužské týmy A až E – jejich informace, zápasy, týmové dění a komunikaci s hráči a fanoušky. Pokud hledáte mládežnické kategorie, přípravku nebo oficiální náborové informace pro děti, pokračujte na hlavní web oddílu. Pokud se chcete přidat k našim mužským týmům, navštivte naši náborovou stránku.',
                        'button_text' => 'Přehled všech týmů',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Nábor hráčů',
                        'secondary_button_url' => '/nabor',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Naše týmy',
                        'subtitle' => 'Pět mužských týmů, jeden klubový základ a společná chuť hrát basket.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Muži A',
                                'description' => 'Vlajková loď klubu hrající 2. ligu (skupina A). Tým s nejvyššími ambicemi a elitním basketbalem.',
                                'image_url' => 'assets/img/home/team-muzi-a.jpg',
                                'link' => '/tymy/muzi-a',
                                'link_label' => 'Detail týmu Muži A',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => '2. LIGA',
                            ],
                            [
                                'title' => 'Muži B',
                                'description' => 'Zkušený tým hrající Pražský přebor. Kvalitní basketbal a silné týmové jádro.',
                                'image_url' => 'assets/img/home/team-muzi-b.jpg',
                                'link' => '/tymy/muzi-b',
                                'link_label' => 'Detail týmu Muži B',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'PRAŽSKÝ PŘEBOR',
                            ],
                            [
                                'title' => 'Muži C',
                                'description' => 'Tým hrající Pražský přebor B. Dynamický celek s ambicí posouvat se v tabulce výše.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tymy/muzi-c',
                                'link_label' => 'Detail týmu Muži C',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'PŘEBOR B',
                            ],
                            [
                                'title' => 'Muži D',
                                'description' => 'Soutěžní basketbal v 1. třídě. Skvělá parta a radost z každého vítězného zápasu.',
                                'image_url' => 'assets/img/home/team-muzi-d.jpg',
                                'link' => '/tymy/muzi-d',
                                'link_label' => 'Detail týmu Muži D',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => '1. TŘÍDA',
                            ],
                            [
                                'title' => 'Muži E',
                                'description' => 'Hobby i soutěžní basket ve 3. třídě B. Pohodové tempo a radost ze hry pro všechny generace.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tymy/muzi-e',
                                'link_label' => 'Detail týmu Muži E',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => '3. TŘÍDA B',
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
                        'subtitle' => 'Rozlišujeme nábor do našich mužských týmů a do mládežnických kategorií oddílu.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Hráči pro mužské týmy',
                                'description' => 'Hledáme parťáky se zkušenostmi do všech našich týmů (A–E). Máme super partu a chuť vyhrávat, ale i si to užít. Stačí se ozvat.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => '/nabor',
                                'link_label' => 'Přejít na detail náboru',
                                'badge' => 'NÁBOR MUŽI',
                            ],
                            [
                                'title' => 'Děti, mládež a přípravka',
                                'description' => 'Hledáte basketbal pro děti? Kompletní informace o náborech mládeže do všech kategorií najdete na hlavním webu TJ Sokol Kbely Basketbal.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Nábor dětí a mládeže',
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
                        'text' => 'Sledujte dění kolem našich mužských týmů A až E, fanděte s námi a pokud hledáte basket pro děti nebo mládež, navštivte hlavní stránky kbelského basketu.',
                        'button_text' => 'Naše týmy',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Hlavní web TJ Sokol Kbely Basketbal',
                        'secondary_button_url' => 'https://www.basketkbely.cz/',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'eyebrow' => 'KBELY FALCONS • MEN A–E',
                        'headline' => "Basketball in Kbely for hearts,\nteam players and community",
                        'subheadline' => 'We are Men\'s teams A to E within TJ Sokol Kbely Basketbal. On this website you will find information about our matches, teams and happenings around us – and also a path to the wider world of Kbely basketball.',
                        'cta_label' => 'Show our teams',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'How to start playing',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'cta_tertiary_label' => 'Visit main TJ Sokol Kbely website',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Part of the club with basketball tradition in Kbely and connection to youth categories.',
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
                        'title' => 'This website belongs to Men\'s teams A–E',
                        'text' => 'Kbely Falcons are part of the wider TJ Sokol Kbely Basketbal club. Our website focuses on the Men\'s teams A to E – their info, matches and communication. If you are looking for youth categories, please proceed to the main club website.',
                        'button_text' => 'All Teams Overview',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Player Recruitment',
                        'secondary_button_url' => '/nabor',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Our Teams',
                        'subtitle' => 'Five men\'s teams, one club base and a shared passion for basketball.',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Men A',
                                'description' => 'The flagship team playing the 2nd League (Group A). A team with elite basketball ambitions.',
                                'image_url' => 'assets/img/home/team-muzi-a.jpg',
                                'link' => '/tymy/muzi-a',
                                'link_label' => 'Men A Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Schedule / Results',
                                'badge' => '2ND LEAGUE',
                            ],
                            [
                                'title' => 'Men B',
                                'description' => 'Experienced team playing the Prague Championship. Quality basketball and strong team core.',
                                'image_url' => 'assets/img/home/team-muzi-b.jpg',
                                'link' => '/tymy/muzi-b',
                                'link_label' => 'Men B Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Schedule / Results',
                                'badge' => 'PRAGUE CHAMPIONSHIP',
                            ],
                            [
                                'title' => 'Men C',
                                'description' => 'Team competing in the Prague Championship B. Dynamic squad aiming higher in the standings.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tymy/muzi-c',
                                'link_label' => 'Men C Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Schedule / Results',
                                'badge' => 'CHAMPIONSHIP B',
                            ],
                            [
                                'title' => 'Men D',
                                'description' => 'Competitive basketball in the 1st Class. Great group and joy of every victory.',
                                'image_url' => 'assets/img/home/team-muzi-d.jpg',
                                'link' => '/tymy/muzi-d',
                                'link_label' => 'Men D Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Schedule / Results',
                                'badge' => '1ST CLASS',
                            ],
                            [
                                'title' => 'Men E',
                                'description' => 'Hobby and competitive basketball in the 3rd Class B. Relaxed pace and joy of the game.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tymy/muzi-e',
                                'link_label' => 'Men E Details',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Schedule / Results',
                                'badge' => '3RD CLASS B',
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
                        'subtitle' => 'Choose between joining our men\'s teams or the club\'s youth categories.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Players for Men\'s Teams',
                                'description' => 'Looking for experienced teammates to join our squads (A–E). Great team spirit and competitive drive. Join us!',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => '/nabor',
                                'link_label' => 'View recruitment details',
                                'badge' => 'MEN RECRUITMENT',
                            ],
                            [
                                'title' => 'Youth & Minibasketball',
                                'description' => 'Looking for basketball for children? Complete info about youth recruitment for all categories can be found on the main club website.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Youth recruitment info',
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
                        'text' => 'Follow the Men C and Men E teams and cheer with us.',
                        'button_text' => 'Our teams',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Main Club Website',
                        'secondary_button_url' => 'https://www.basketkbely.cz/',
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
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Nábor do mužských týmů</h2><p>Tento web a náborový formulář slouží primárně pro zájemce o hraní v našich týmech <strong>Muži C</strong> a <strong>Muži E</strong>. Pokud máš za sebou basketbalovou minulost a chceš se vrátit k pravidelnému hraní v pohodovém, ale soutěživém kolektivu, jsi na správném místě.</p><h3>Hledáte basketbal pro děti?</h3><p>Pokud hledáte přípravku, žákovské nebo mládežnické kategorie, pokračujte prosím na hlavní klubový web, kde najdete veškeré informace k náborům dětí:</p><p><a href="https://www.basketkbely.cz/zacnihrat" class="btn btn-primary">Nábor mládeže (basketkbely.cz)</a></p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Chceš se k nám přidat do C nebo E?',
                        'text' => 'Neváhej nám napsat. Rádi tě uvidíme na tréninku, kde si můžeme vzájemně vyzkoušet, zda si sedneme na hřišti i v šatně.',
                        'button_text' => 'Kontaktovat',
                        'button_url' => '/kontakt',
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
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Men\'s Team Recruitment</h2><p>This website and recruitment form are primarily for those interested in playing for our <strong>Men C</strong> and <strong>Men E</strong> teams. If you have a basketball background and want to return to regular playing in a relaxed but competitive team, you are in the right place.</p><h3>Looking for basketball for kids?</h3><p>If you are looking for youth or mini-basketball categories, please proceed to the main club website:</p><p><a href="https://www.basketkbely.cz/zacnihrat" class="btn btn-primary">Youth Recruitment (basketkbely.cz)</a></p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Want to join C or E?',
                        'text' => 'Don\'t hesitate to contact us. We\'d love to see you at practice.',
                        'button_text' => 'Contact Us',
                        'button_url' => '/kontakt',
                    ]
                ],
            ],
        ];
    }
}
