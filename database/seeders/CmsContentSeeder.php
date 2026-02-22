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
            'contact_email' => 'info@kbelstisokoli.cz',
            'contact_phone' => '+420 123 456 789',
            'contact_address' => 'Toužimská 700, Praha 9 - Kbely',
            'social_facebook' => 'https://facebook.com/kbelstisokoli',
            'social_instagram' => 'https://instagram.com/kbelstisokoli',
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
                    'title' => ['cs' => 'Kbelští sokoli | Basketbalový klub Praha Kbely', 'en' => 'Kbely Falcons | Basketball Club Prague Kbely'],
                    'description' => ['cs' => 'Oficiální stránky basketbalového klubu Kbelští sokoli. Přidejte se k nám!', 'en' => 'Official website of the Kbely Falcons basketball club. Join us!'],
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
    }

    protected function getHomeBlocks(): array
    {
        return [
            'cs' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => "Srdce basketbalu\nv pražských Kbelích",
                        'subheadline' => 'Budujeme silnou komunitu, rozvíjíme talenty a sdílíme radost z každého koše. Přidejte se k Sokolům!',
                        'cta_label' => 'Chci začít hrát',
                        'cta_url' => '/nabor',
                        'variant' => 'standard',
                        'alignment' => 'left',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'stats_cards',
                    'data' => [
                        'variant' => 'dark',
                        'stats' => [
                            ['label' => 'Aktivních členů', 'value' => '250+', 'icon' => 'users'],
                            ['label' => 'Soutěžních týmů', 'value' => '12', 'icon' => 'user-group'],
                            ['label' => 'Kvalifikovaných trenérů', 'value' => '15', 'icon' => 'graduation-cap'],
                            ['label' => 'Let tradice', 'value' => '10+', 'icon' => 'calendar-star'],
                        ]
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Proč hrát za Sokoly?',
                        'subtitle' => 'Naše pilíře',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Rodinná atmosféra',
                                'description' => 'Nejsme jen klub, jsme komunita. Podporujeme se na hřišti i mimo něj. U nás najdete přátele na celý život.',
                                'icon' => 'house-heart',
                            ],
                            [
                                'title' => 'Špičkoví trenéři',
                                'description' => 'Naši trenéři mají licenci a srdce pro hru. Zaměřujeme se na individuální rozvoj i týmovou chemii.',
                                'icon' => 'whistle',
                            ],
                            [
                                'title' => 'Moderní zázemí',
                                'description' => 'Trénujeme v moderní hale v Kbelích s veškerým vybavením, které mladý basketbalista potřebuje.',
                                'icon' => 'court-basketball',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'primary',
                        'title' => "Staň se součástí\nnašeho týmu!",
                        'text' => 'Pořádáme nábory pro děti od 6 let i pro zkušené hráče. První tři tréninky jsou na zkoušku zdarma.',
                        'button_text' => 'Více o náboru',
                        'button_url' => '/nabor',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'style' => 'default',
                        'content' => '<h2>O našem klubu</h2><p>Basketbalový klub <strong>Kbelští sokoli</strong> byl založen s cílem přivést basketbal do pražských Kbel a okolí. Od té doby jsme se rozrostli v jeden z nejvýznamnějších sportovních oddílů v městské části.</p><p>Věnujeme se všem věkovým kategoriím – od přípravek (U9) až po týmy dospělých. Naše filozofie stojí na třech pilířích: <strong>Sportovní růst</strong>, <strong>Charakter</strong> a <strong>Zábava</strong>.</p>',
                    ]
                ],
                [
                    'type' => 'news_listing',
                    'data' => [
                        'title' => 'Co je u nás nového?',
                        'subtitle' => 'Aktuality z klubu',
                        'limit' => 3,
                        'layout' => 'grid',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => "The Heart of Basketball\nin Prague's Kbely",
                        'subheadline' => 'We build a strong community, develop talents, and share the joy of every basket. Join the Falcons!',
                        'cta_label' => 'Start playing',
                        'cta_url' => '/nabor',
                        'variant' => 'standard',
                        'alignment' => 'left',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'stats_cards',
                    'data' => [
                        'variant' => 'dark',
                        'stats' => [
                            ['label' => 'Active Members', 'value' => '250+', 'icon' => 'users'],
                            ['label' => 'Competitive Teams', 'value' => '12', 'icon' => 'user-group'],
                            ['label' => 'Qualified Coaches', 'value' => '15', 'icon' => 'graduation-cap'],
                            ['label' => 'Years of Tradition', 'value' => '10+', 'icon' => 'calendar-star'],
                        ]
                    ]
                ],
                [
                    'type' => 'cards_grid',
                    'data' => [
                        'title' => 'Why play for Falcons?',
                        'subtitle' => 'Our pillars',
                        'columns' => 3,
                        'cards' => [
                            [
                                'title' => 'Family Atmosphere',
                                'description' => "We are not just a club, we are a community. We support each other on and off the court. You'll find friends for life here.",
                                'icon' => 'house-heart',
                            ],
                            [
                                'title' => 'Top Coaches',
                                'description' => 'Our coaches have a license and a heart for the game. We focus on individual development and team chemistry.',
                                'icon' => 'whistle',
                            ],
                            [
                                'title' => 'Modern Facilities',
                                'description' => 'We train in a modern hall in Kbely with all the equipment a young basketball player needs.',
                                'icon' => 'court-basketball',
                            ],
                        ]
                    ]
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'style' => 'primary',
                        'title' => "Become part\nof our team!",
                        'text' => 'We organize recruitments for children from 6 years old and for experienced players. The first three trainings are for free trial.',
                        'button_text' => 'More about recruitment',
                        'button_url' => '/nabor',
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'style' => 'default',
                        'content' => '<h2>About our club</h2><p>Basketball club <strong>Kbely Falcons</strong> was founded with the goal of bringing basketball to Prague\'s Kbely and surrounding areas. Since then, we have grown into one of the most important sports clubs in the district.</p><p>We cater to all age categories – from preparation classes (U9) to adult teams. Our philosophy stands on three pillars: <strong>Sports Growth</strong>, <strong>Character</strong>, and <strong>Fun</strong>.</p>',
                    ]
                ],
                [
                    'type' => 'news_listing',
                    'data' => [
                        'title' => 'What\'s new?',
                        'subtitle' => 'Club news',
                        'limit' => 3,
                        'layout' => 'grid',
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
