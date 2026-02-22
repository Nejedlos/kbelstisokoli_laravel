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
                        'headline' => 'Budoucnost basketbalu v Kbelích',
                        'subheadline' => 'Přidejte se k rodině Sokolů. Rozvíjíme talenty, stavíme na týmovém duchu.',
                        'cta_label' => 'Chci se přidat',
                        'cta_url' => '/nabor',
                        'variant' => 'centered',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'stats_cards',
                    'data' => [
                        'stats' => [
                            ['name' => 'Členů', 'value' => '250+'],
                            ['name' => 'Týmů', 'value' => '12'],
                            ['name' => 'Trenérů', 'value' => '15'],
                            ['name' => 'Let tradice', 'value' => '10+'],
                        ]
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Vítejte v basketbalovém klubu Kbelští sokoli</h2><p>Jsme dynamicky se rozvíjející klub v srdci Prahy 9. Naším cílem není jen sportovní výkon, ale především radost z pohybu a výchova mladých sportovců v duchu fair play.</p>',
                    ]
                ],
            ],
            'en' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'headline' => 'Future of Basketball in Kbely',
                        'subheadline' => 'Join the Falcons family. We develop talents, we build on team spirit.',
                        'cta_label' => 'Join us',
                        'cta_url' => '/nabor',
                        'variant' => 'centered',
                        'overlay' => true,
                    ]
                ],
                [
                    'type' => 'stats_cards',
                    'data' => [
                        'stats' => [
                            ['name' => 'Members', 'value' => '250+'],
                            ['name' => 'Teams', 'value' => '12'],
                            ['name' => 'Coaches', 'value' => '15'],
                            ['name' => 'Years of tradition', 'value' => '10+'],
                        ]
                    ]
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => '<h2>Welcome to Kbely Falcons Basketball Club</h2><p>We are a dynamically growing club in the heart of Prague 9. Our goal is not only sports performance, but primarily the joy of movement and education of young athletes in the spirit of fair play.</p>',
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
                    'type' => 'call_to_action',
                    'data' => [
                        'headline' => 'Máš dotaz k náboru?',
                        'text' => 'Neváhej nám napsat nebo zavolat. Rádi ti vše vysvětlíme.',
                        'button_label' => 'Kontaktovat',
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
                    'type' => 'call_to_action',
                    'data' => [
                        'headline' => 'Do you have a question about recruitment?',
                        'text' => 'Do not hesitate to write or call us. We will be happy to explain everything to you.',
                        'button_label' => 'Contact Us',
                        'button_url' => '/kontakt',
                    ]
                ],
            ],
        ];
    }
}
