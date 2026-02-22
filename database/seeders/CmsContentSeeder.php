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
            'social_facebook' => 'https://facebook.com/kbelstisokoli',
            'social_instagram' => 'https://instagram.com/kbelstisokoli',
            'main_club_url' => 'https://www.basketkbely.cz/',
            'recruitment_url' => 'https://www.basketkbely.cz/nabor',
            'maintenance_mode' => 'false',
            'footer_text' => [
                'cs' => '© ' . date('Y') . ' Basketbalový klub Kbelští sokoli. Všechna práva vyhrazena.',
                'en' => '© ' . date('Y') . ' Basketball club Kbely Falcons. All rights reserved.',
            ],
            'seo_description' => [
                'cs' => 'Oficiální web basketbalového klubu Kbelští sokoli. Informace o týmech, trénincích, zápasech a náborech pro děti i dospělé v Praze 9.',
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
                        'cs' => 'Kbelští sokoli – Muži C a Muži E | Basket Kbely Praha',
                        'en' => 'Kbely Falcons – Men C and Men E | Basketball Kbely Prague'
                    ],
                    'description' => [
                        'cs' => 'Web týmů Kbelští sokoli Muži C a Muži E v rámci TJ Sokol Kbely Basketbal. Zápasy, týmové informace, aktuality a odkazy na nábor a hlavní kbelský basket.',
                        'en' => 'Website of teams Kbely Falcons Men C and Men E within TJ Sokol Kbely Basketball. Matches, team info, news and links to recruitment.'
                    ],
                ],
            ],
            [
                'slug' => 'o-klubu',
                'title' => ['cs' => 'O klubu', 'en' => 'About club'],
                'content' => $this->getAboutBlocks(),
                'status' => 'published',
                'is_visible' => true,
                'seo' => [
                    'title' => ['cs' => 'O klubu | Kbelští sokoli', 'en' => 'About Club | Kbely Falcons'],
                    'description' => ['cs' => 'Historie a vize basketbalového klubu Kbelští sokoli.', 'en' => 'History and vision of the Kbely Falcons basketball club.'],
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
                    'title' => ['cs' => 'Rozpis a výsledky zápasů | Kbelští sokoli', 'en' => 'Match Schedule & Results | Kbely Falcons'],
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

        $items = [
            ['label' => ['cs' => 'Domů', 'en' => 'Home'], 'url' => '/', 'sort' => 10],
            ['label' => ['cs' => 'O klubu', 'en' => 'About'], 'url' => '/o-klubu', 'sort' => 20],
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

        $footerItems = [
            ['label' => ['cs' => 'O klubu', 'en' => 'About'], 'url' => '/o-klubu', 'sort' => 10],
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
            ['name' => 'Patička - Týmy a klub']
        );

        $footerClubItems = [
            ['label' => ['cs' => 'Muži C', 'en' => 'Men C'], 'url' => '/tym/muzi-c', 'sort' => 10],
            ['label' => ['cs' => 'Muži E', 'en' => 'Men E'], 'url' => '/tym/muzi-e', 'sort' => 20],
            ['label' => ['cs' => 'Hlavní web TJ Sokol Kbely Basketbal', 'en' => 'Main Club Website'], 'url' => 'https://www.basketkbely.cz/', 'sort' => 30, 'is_external' => true],
            ['label' => ['cs' => 'Nábor / Začni hrát', 'en' => 'Recruitment'], 'url' => 'https://www.basketkbely.cz/nabor', 'sort' => 40, 'is_external' => true],
            ['label' => ['cs' => 'Družstva a mládež', 'en' => 'Teams & Youth'], 'url' => 'https://www.basketkbely.cz/druzstva', 'sort' => 50, 'is_external' => true],
        ];

        foreach ($footerClubItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerClubMenu->id, 'url' => $item['url']],
                [
                    'label' => $item['label'],
                    'sort_order' => $item['sort'],
                    'is_visible' => true,
                    // 'is_external' => $item['is_external'] ?? false, // Pokud existuje sloupec, jinak to vyřešíme v Blade
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
                        'eyebrow' => 'KBELŠTÍ SOKOLI • MUŽI C & MUŽI E',
                        'headline' => "Basket ve Kbelích pro srdcaře,\ntýmové hráče a komunitu",
                        'subheadline' => 'Jsme týmy Muži C a Muži E v rámci TJ Sokol Kbely Basketbal. Na tomto webu najdete informace o našich zápasech, týmech a dění kolem nás – a zároveň cestu do širšího světa kbelského basketu.',
                        'cta_label' => 'Naše týmy C a E',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'Chci začít s basketem',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/',
                        'cta_tertiary_label' => 'Hlavní web TJ Sokol Kbely Basketbal',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Součást oddílu s tradicí basketbalu ve Kbelích a návazností na mládežnické kategorie.',
                        'image_url' => '/assets/img/home/home-hero-basketball-team.jpg',
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
                        'title' => 'Tento web patří týmům Muži C a Muži E',
                        'text' => 'Kbelští sokoli jsou součástí širšího oddílu TJ Sokol Kbely Basketbal. Náš web se zaměřuje hlavně na mužské týmy C a E – jejich informace, zápasy, týmové dění a komunikaci s hráči a fanoušky. Pokud hledáte mládežnické kategorie, přípravku nebo oficiální náborové informace pro děti, pokračujte na hlavní web oddílu.',
                        'button_text' => 'Profil týmu Muži C',
                        'button_url' => '/tym/muzi-c',
                        'secondary_button_text' => 'Profil týmu Muži E',
                        'secondary_button_url' => '/tym/muzi-e',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Naše týmy',
                        'subtitle' => 'Dva mužské týmy, jeden klubový základ a společná chuť hrát basket.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Muži C',
                                'description' => 'Tým Muži C působí v kategorii mužů a v sezóně 2025/2026 je veden v soutěži Pražský přebor B. Na klubovém profilu je uveden trenér Tomáš Spanilý.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tym/muzi-c',
                                'link_label' => 'Detail týmu Muži C',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'Pražský přebor B',
                            ],
                            [
                                'title' => 'Muži E',
                                'description' => 'Tým Muži E působí v kategorii mužů a v sezóně 2025/26 je veden v soutěži Pražský přebor 3. třída B. Na klubovém profilu je uveden trenér Lubor Viktorin.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tym/muzi-e',
                                'link_label' => 'Detail týmu Muži E',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'Pražský přebor 3. třída B',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'secondary',
                        'title' => 'Navazujeme na tradici kbelského basketu',
                        'text' => 'TJ Sokol Kbely Basketbal sdružuje dospělé týmy i mládežnické kategorie a dlouhodobě buduje basketbalovou komunitu ve Kbelích. Naším cílem je držet kvalitní týmové prostředí, chuť hrát a dobré jméno klubu – na hřišti i mimo něj.',
                        'alignment' => 'center',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'primary',
                        'title' => 'Chcete začít s basketem ve Kbelích?',
                        'text' => 'Pokud hledáte basket pro děti, mládež nebo chcete začít sportovat v rámci kbelské basketbalové komunity, navštivte hlavní web TJ Sokol Kbely Basketbal. Najdete tam přehled družstev, přípravku, tréninků i aktuální náborové informace.',
                        'button_text' => 'Přejít na hlavní web oddílu',
                        'button_url' => 'https://www.basketkbely.cz/',
                        'secondary_button_text' => 'Kontakty / nábor',
                        'secondary_button_url' => '/kontakt',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Co na tomto webu najdete',
                        'columns' => 4,
                        'cards' => [
                            [
                                'title' => 'Zápasy a program',
                                'description' => 'Přehled utkání, výsledků a týmového programu pro Muže C a Muže E (postupně napojené na data).',
                                'icon' => 'calendar-days',
                                'link' => '/zapasy',
                            ],
                            [
                                'title' => 'Týmy a informace',
                                'description' => 'Základní informace o týmech, organizační přehled a kontaktní body.',
                                'icon' => 'users-gear',
                                'link' => '/tymy',
                            ],
                            [
                                'title' => 'Aktuality a dění',
                                'description' => 'Novinky z týmového života, oznámení a důležité informace pro hráče i fanoušky.',
                                'icon' => 'newspaper',
                                'link' => '/novinky',
                            ],
                            [
                                'title' => 'Členská sekce',
                                'description' => 'Přihlášení hráčů a členů pro docházku, RSVP a interní přehledy (chráněná část).',
                                'icon' => 'lock',
                                'link' => '/login',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'news_listing',
                    'data' => [
                        'title' => 'Novinky z týmů C a E',
                        'subtitle' => 'Aktuality budou průběžně doplňovány.',
                        'limit' => 3,
                        'layout' => 'grid',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'secondary',
                        'title' => 'Hrajeme pro radost ze hry, tým a Kbely',
                        'text' => 'Sledujte dění kolem týmů Muži C a Muži E, fanděte s námi a pokud hledáte basket pro děti nebo mládež, navštivte hlavní stránky kbelského basketu.',
                        'button_text' => 'Naše týmy',
                        'button_url' => '/tymy',
                        'secondary_button_text' => 'Hlavní web TJ Sokol Kbely Basketbal',
                        'secondary_button_url' => 'https://www.basketkbely.cz/',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'eyebrow' => 'KBELY FALCONS • MEN C & MEN E',
                        'headline' => "Basketball in Kbely for hearts,\nteam players and community",
                        'subheadline' => 'We are Men C and Men E teams within TJ Sokol Kbely Basketbal. On this website you will find information about our matches, teams and happenings around us.',
                        'cta_label' => 'Our C & E teams',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'I want to start playing',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/',
                        'cta_tertiary_label' => 'Main Club Website',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Part of the club with basketball tradition in Kbely and connection to youth categories.',
                        'image_url' => '/assets/img/home/home-hero-basketball-team.jpg',
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
                        'title' => 'This website belongs to Men C and Men E teams',
                        'text' => 'Kbely Falcons are part of the wider TJ Sokol Kbely Basketbal club. Our website focuses mainly on the Men C and E teams – their information, matches, team events and communication with players and fans. If you are looking for youth categories or official recruitment information for children, please proceed to the main club website.',
                        'button_text' => 'Men C Profile',
                        'button_url' => '/tym/muzi-c',
                        'secondary_button_text' => 'Men E Profile',
                        'secondary_button_url' => '/tym/muzi-e',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Our Teams',
                        'subtitle' => 'Two men\'s teams, one club base and a shared passion for playing basketball.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Men C',
                                'description' => 'The Men C team operates in the men\'s category and in the 2025/2026 season is led in the Prague Championship B competition. Coach Tomáš Spanilý is listed on the club profile.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tym/muzi-c',
                                'link_label' => 'Men C Details',
                                'badge' => 'Prague Championship B',
                            ],
                            [
                                'title' => 'Men E',
                                'description' => 'The Men E team operates in the men\'s category and in the 2025/26 season is led in the Prague Championship 3rd Class B competition. Coach Lubor Viktorin is listed on the club profile.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tym/muzi-e',
                                'link_label' => 'Men E Details',
                                'badge' => 'Prague Championship 3rd Class B',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'secondary',
                        'title' => 'Following the tradition of Kbely basketball',
                        'text' => 'TJ Sokol Kbely Basketbal brings together adult teams and youth categories and has been building a basketball community in Kbely for a long time. Our goal is to maintain a quality team environment, a desire to play and the good name of the club – on and off the court.',
                        'alignment' => 'center',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'primary',
                        'title' => 'Want to start with basketball in Kbely?',
                        'text' => 'If you are looking for basketball for children, youth or want to start sporting within the Kbely basketball community, visit the main TJ Sokol Kbely Basketbal website.',
                        'button_text' => 'Go to main club website',
                        'button_url' => 'https://www.basketkbely.cz/',
                        'secondary_button_text' => 'Contact / Recruitment',
                        'secondary_button_url' => '/kontakt',
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
                                'description' => 'Overview of matches, results and team program for Men C and Men E.',
                                'icon' => 'calendar-days',
                                'link' => '/zapasy',
                            ],
                            [
                                'title' => 'Teams and information',
                                'description' => 'Basic team info, organizational overview and contact points.',
                                'icon' => 'users-gear',
                                'link' => '/tymy',
                            ],
                            [
                                'title' => 'News and events',
                                'description' => 'News from team life, announcements and important info for players and fans.',
                                'icon' => 'newspaper',
                                'link' => '/novinky',
                            ],
                            [
                                'title' => 'Member section',
                                'description' => 'Login for players and members for attendance, RSVP and internal overviews.',
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
                        'style' => 'secondary',
                        'title' => 'We play for the joy of the game, the team and Kbely',
                        'text' => 'Follow the events around the Men C and Men E teams, cheer with us and if you are looking for basketball for children or youth, visit the main Kbely basketball website.',
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
                        'content' => '<h2>O nás</h2><p>Kbelští sokoli vznikli s vizí vytvořit místo, kde se děti i dospělí mohou věnovat basketbalu na profesionální i rekreační úrovni.</p><h3>Naše hodnoty</h3><ul><li>Týmovost</li><li>Respekt</li><li>Vytrvalost</li><li>Radost</li></ul>',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>About Us</h2><p>Kbely Falcons were founded with a vision to create a place where children and adults can dedicate themselves to basketball on both professional and recreational levels.</p><h3>Our Values</h3><ul><li>Teamwork</li><li>Respect</li><li>Perseverance</li><li>Joy</li></ul>',
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
                        'headline' => 'Hledáme právě tebe!',
                        'subheadline' => 'Nábory do všech věkových kategorií probíhají po celý rok.',
                        'variant' => 'minimal',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Jak se stát Sokolem?</h2><p>Stačí přijít na kterýkoliv trénink své věkové kategorie a vyzkoušet si to. První tři tréninky jsou zdarma!</p><p>Vezmi si s sebou jen sálovou obuv, kraťasy, tričko a láhev s pitím.</p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Máš dotaz k náboru?',
                        'text' => 'Neváhej nám napsat nebo zavolat. Rádi ti vše vysvětlíme.',
                        'button_text' => 'Kontaktovat',
                        'button_url' => '/kontakt',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => 'We are looking for YOU!',
                        'subheadline' => 'Recruitment for all age categories takes place throughout the year.',
                        'variant' => 'minimal',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>How to become a Falcon?</h2><p>Just come to any training of your age category and try it out. The first three trainings are free!</p><p>Bring only indoor shoes, shorts, a T-shirt and a water bottle.</p>',
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'title' => 'Do you have a question about recruitment?',
                        'text' => 'Do not hesitate to write or call us. We will be happy to explain everything to you.',
                        'button_text' => 'Contact Us',
                        'button_url' => '/kontakt',
                    ]
                ],
            ],
        ];
    }
}
