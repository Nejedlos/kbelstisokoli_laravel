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
            'social_facebook' => null,
            'social_instagram' => null,
            'main_club_url' => 'https://www.basketkbely.cz/',
            'recruitment_url' => 'https://www.basketkbely.cz/zacnihrat',
            'maintenance_mode' => 'false',
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
                        'cs' => 'Kbelští sokoli – Muži C a Muži E | Basket Kbely Praha',
                        'en' => 'Kbely Falcons – Men C and Men E | Basketball Kbely Prague'
                    ],
                    'description' => [
                        'cs' => 'Web týmů Kbelští sokoli Muži C a Muži E v rámci TJ Sokol Kbely Basketbal. Zápasy, týmové informace, aktuality a odkazy na nábor a hlavní kbelský basket.',
                        'en' => 'Website of teams Kbely Falcons Men C and Men E within TJ Sokol Kbely Basketball. Matches, team info, news and links to recruitment.'
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
            ['label' => ['cs' => 'Muži C', 'en' => 'Men C'], 'url' => '/tym/muzi-c', 'sort' => 10],
            ['label' => ['cs' => 'Muži E', 'en' => 'Men E'], 'url' => '/tym/muzi-e', 'sort' => 20],
            ['label' => ['cs' => 'Hlavní web TJ Sokol Kbely Basketbal', 'en' => 'Main Club Website'], 'url' => 'https://www.basketkbely.cz/', 'sort' => 30, 'is_external' => true],
            ['label' => ['cs' => 'Nábor/Začni hrát', 'en' => 'Recruitment'], 'url' => 'https://www.basketkbely.cz/zacnihrat', 'sort' => 40, 'is_external' => true],
            ['label' => ['cs' => 'Družstva a mládež', 'en' => 'Teams & Youth'], 'url' => 'https://www.basketkbely.cz/druzstva', 'sort' => 50, 'is_external' => true],
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
                        'cta_label' => 'Naše týmy C a E',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'Chci začít s basketem',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'cta_tertiary_label' => 'Hlavní web TJ Sokol Kbely Basketbal',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Součást oddílu s tradicí basketbalu ve Kbelích a návazností na mládežnické kategorie.',
                        'image_url' => '/assets/img/home/home-hero-basketball-team.jpg',
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
                        'title' => 'Tento web patří týmům Muži C a Muži E',
                        'text' => 'Kbelští sokoli jsou součástí širšího oddílu TJ Sokol Kbely Basketbal. Náš web se zaměřuje hlavně na mužské týmy C a E – jejich informace, zápasy, týmové dění a komunikaci s hráči a fanoušky. Pokud hledáte mládežnické kategorie, přípravku nebo oficiální náborové informace pro děti, pokračujte na hlavní web oddílu. Pokud se chcete přidat k našim mužským týmům, navštivte naši náborovou stránku.',
                        'button_text' => 'Profil týmu Muži C',
                        'button_url' => '/tym/muzi-c',
                        'secondary_button_text' => 'Profil týmu Muži E',
                        'secondary_button_url' => '/tym/muzi-e',
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Naše týmy',
                        'subtitle' => 'Dva mužské týmy, jeden klubový základ a společná chuť hrát basket.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Muži C',
                                'description' => 'Tým Muži C působí v kategorii mužů a v sezóně 2025/2026 je veden v soutěži Pražský přebor B. Na klubovém profilu je uveden trenér Tomáš Spanilý.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tym/muzi-c',
                                'link_label' => 'Detail týmu Muži C',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'NÁBOR C OTEVŘEN • Pražský přebor B',
                            ],
                            [
                                'title' => 'Muži E',
                                'description' => 'Tým Muži E působí v kategorii mužů a v sezóně 2025/26 je veden v soutěži Pražský přebor 3. třída B. Na klubovém profilu je uveden trenér Lubor Viktorin.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tym/muzi-e',
                                'link_label' => 'Detail týmu Muži E',
                                'secondary_link' => '/zapasy',
                                'secondary_link_label' => 'Program / výsledky',
                                'badge' => 'NÁBOR E OTEVŘEN • Pražský přebor 3. třída B',
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
                                'title' => 'Hráči pro Muže C a E',
                                'description' => 'Hledáme parťáky se zkušenostmi, kteří chtějí hrát Pražský přebor. Máme super partu a chuť vyhrávat, ale i si to užít. Stačí se ozvat.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => '/nabor',
                                'link_label' => 'Chci se přidat k C / E',
                                'badge' => 'NÁBOR MUŽI',
                            ],
                            [
                                'title' => 'Děti, mládež a přípravka',
                                'description' => 'Hledáte basketbal pro děti? Kompletní informace o náborech mládeže do všech kategorií najdete na hlavním webu TJ Sokol Kbely Basketbal.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Začni hrát (Mládež)',
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
                                'description' => 'Přehled utkání, výsledků a týmového programu pro Muže C a Muže E (postupně napojené na data).',
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
                        'title' => 'Novinky z týmů C a E',
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
                        'text' => 'Sledujte dění kolem týmů Muži C a Muži E, fanděte s námi a pokud hledáte basket pro děti nebo mládež, navštivte hlavní stránky kbelského basketu.',
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
                        'eyebrow' => 'KBELY FALCONS • MEN C & MEN E',
                        'headline' => "Basketball in Kbely for hearts,\nteam players and community",
                        'subheadline' => 'We are Men C and Men E teams within TJ Sokol Kbely Basketbal. On this website you will find information about our matches, teams and happenings around us – and also a path to the wider world of Kbely basketball.',
                        'cta_label' => 'Our C & E teams',
                        'cta_url' => '/tymy',
                        'cta_secondary_label' => 'I want to start playing',
                        'cta_secondary_url' => 'https://www.basketkbely.cz/zacnihrat',
                        'cta_tertiary_label' => 'Main Club Website',
                        'cta_tertiary_url' => 'https://www.basketkbely.cz/',
                        'microtext' => 'Part of the club with basketball tradition in Kbely and connection to youth categories.',
                        'image_url' => '/assets/img/home/home-hero-basketball-team.jpg',
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
                        'title' => 'This website belongs to Men C and Men E teams',
                        'text' => 'Kbely Falcons are part of the wider TJ Sokol Kbely Basketbal club. Our website focuses mainly on the Men C and E teams – their info, matches and communication. If you are looking for youth categories, please proceed to the main club website.',
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
                        'subtitle' => 'Two men\'s teams, one club base and a shared passion for basketball.',
                        'columns' => 2,
                        'cards' => [
                            [
                                'title' => 'Men C',
                                'description' => 'The Men C team competes in the Prague Championship B. Led by coach Tomáš Spanilý for the 2025/2026 season.',
                                'image_url' => 'assets/img/home/team-muzi-c.jpg',
                                'link' => '/tym/muzi-c',
                                'link_label' => 'Men C Details',
                                'badge' => 'RECRUITMENT OPEN • Prague Championship B',
                            ],
                            [
                                'title' => 'Men E',
                                'description' => 'The Men E team competes in the Prague Championship 3rd Class B. Led by coach Lubor Viktorin for the 2025/26 season.',
                                'image_url' => 'assets/img/home/team-muzi-e.jpg',
                                'link' => '/tym/muzi-e',
                                'link_label' => 'Men E Details',
                                'badge' => 'RECRUITMENT OPEN • Prague Championship 3rd Class B',
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
                                'title' => 'Players for Men C & E',
                                'description' => 'Looking for experienced teammates to join our Prague Championship squads. Great team spirit and competitive drive. Join us!',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'link' => '/nabor',
                                'link_label' => 'Join Men C / E',
                                'badge' => 'MEN RECRUITMENT',
                            ],
                            [
                                'title' => 'Youth & Minibasketball',
                                'description' => 'Looking for basketball for children? Complete info about youth recruitment for all categories can be found on the main club website.',
                                'image_url' => 'assets/img/home/kids-youth-basket-training.jpg',
                                'link' => 'https://www.basketkbely.cz/zacnihrat',
                                'link_label' => 'Start Playing (Youth)',
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
