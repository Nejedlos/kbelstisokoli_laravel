<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class GdprPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::updateOrCreate(
            ['slug' => 'gdpr'],
            [
                'title' => [
                    'cs' => 'Ochrana soukromí (GDPR)',
                    'en' => 'Privacy Policy (GDPR)',
                ],
                'content' => [
                    'cs' => [
                        [
                            'type' => 'hero',
                            'data' => [
                                'variant' => 'standard',
                                'headline' => 'Hrajeme fair-play i s vašimi údaji',
                                'subheadline' => 'Ochrana soukromí u Sokolů není žádná věda, ale základní pravidlo hry. Vaše data u nás nejsou v ofsajdu.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'overlay' => true,
                                'alignment' => 'left',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h2>Pravidla naší hry</h2><p>V našich mužských týmech si na nic nehrajeme. Web používáme k tomu, abychom se domluvili na trénink, věděli, kdo přijde na zápas, a mohli se pochlubit výsledky. Žádný agresivní marketing, žádné otravné reklamy ani prodej vašich dat do cizích týmů. Prostě čistý basket.</p>',
                            ],
                        ],
                        [
                            'type' => 'cards_grid',
                            'data' => [
                                'title' => 'Soupiska údajů, které hlídáme',
                                'subtitle' => 'Co o vás potřebujeme vědět, aby hra plynula hladce.',
                                'columns' => 2,
                                'cards' => [
                                    [
                                        'title' => 'Identifikace',
                                        'description' => 'Jméno a příjmení. Abychom věděli, komu patří dres a kdo dal ten vítězný koš.',
                                        'icon' => 'user',
                                    ],
                                    [
                                        'title' => 'Komunikace',
                                        'description' => 'E-mail a telefon. Pro rychlou přihrávku s informací o změně tréninku nebo srazu na zápas.',
                                        'icon' => 'phone',
                                    ],
                                    [
                                        'title' => 'Statistiky',
                                        'description' => 'Docházka a herní data. Základní taktika pro trenéra, abychom věděli, kdo je ve formě.',
                                        'icon' => 'chart-column',
                                    ],
                                    [
                                        'title' => 'Vzpomínky',
                                        'description' => 'Fotografie a videa. Momentky z palubovky, které zveřejňujeme v aktualitách pro radost fanoušků.',
                                        'icon' => 'camera',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'cta',
                            'data' => [
                                'style' => 'outline',
                                'alignment' => 'left',
                                'title' => 'Máte právo na "přezkum rozhodčího"',
                                'text' => 'Kdykoliv můžete chtít vědět, co o vás v tabulkách máme, opravit chybnou nahrávku nebo se úplně odhlásit ze hry. Stačí písknout na náš e-mail.',
                                'button_text' => 'Napsat vedoucímu',
                                'button_url' => '/kontakt',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h3>Právní informace a správce údajů</h3><p>Oficiálním správcem vašich osobních údajů pro naše mužské týmy je:</p><p><strong>Tomáš Spanilý</strong> – vedoucí týmu<br>Kovářská 17, Praha 9<br>Mobil: +420 602 285 447<br>Fax: +420 266 315 868<br>E-mail: <a href="javascript:void(0)" data-protected-email="c3BhbmlseUBrZWVwNjkuY3o=">spanily [zavináč] keep69.cz</a></p><h3>Pověřenec pro GDPR</h3><p><strong>Michal Nejedlý</strong><br>E-mail: <a href="javascript:void(0)" data-protected-email="bmVqZWRseW1pQGdtYWlsLmNvbQ==">nejedlymi [zavináč] gmail.com</a></p><p>Podrobná pravidla celého oddílu najdete v <a href="https://www.basketkbely.cz/" target="_blank">oficiálním dokumentu na hlavním webu klubu</a>.</p><h3>Osobní údaje</h3><p>Zpracováváme pouze údaje nezbytné pro fungování týmu, organizaci zápasů a tréninků (jméno, e-mail, telefon, docházka). Údaje uchováváme po dobu vašeho aktivního členství v týmu. Máte právo na přístup k údajům, jejich opravu či výmaz.</p><h3>Soubory cookies</h3><p>Tento web používá pouze nezbytné technické cookies pro zajištění funkčnosti (např. přihlášení do členské sekce). Nepoužíváme žádné sledovací ani marketingové cookies třetích stran.</p>',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'type' => 'hero',
                            'data' => [
                                'variant' => 'standard',
                                'headline' => 'We play fair with your data',
                                'subheadline' => 'Privacy at the Falcons is simple – we treat your data with the same respect as our teammates. No fouls, just fair play.',
                                'image_url' => 'assets/img/home/basketball-court-detail.jpg',
                                'overlay' => true,
                                'alignment' => 'left',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h2>Our Rules of the Game</h2><p>In our men\'s teams, we keep things real. We use this website to organize practices, track match attendance, and share our results. No aggressive marketing, no annoying ads, and definitely no selling your data to other teams. Just pure basketball.</p>',
                            ],
                        ],
                        [
                            'type' => 'cards_grid',
                            'data' => [
                                'title' => 'Data Roster',
                                'subtitle' => 'What we need to know about you to keep the game flowing.',
                                'columns' => 2,
                                'cards' => [
                                    [
                                        'title' => 'Identification',
                                        'description' => 'Name and surname. So we know who\'s on the roster and who scored the winning point.',
                                        'icon' => 'user',
                                    ],
                                    [
                                        'title' => 'Communication',
                                        'description' => 'E-mail and phone. For quick assists regarding training changes or match meetings.',
                                        'icon' => 'phone',
                                    ],
                                    [
                                        'title' => 'Statistics',
                                        'description' => 'Attendance and game data. Basic tactics for the coach to know who\'s ready to play.',
                                        'icon' => 'chart-column',
                                    ],
                                    [
                                        'title' => 'Memories',
                                        'description' => 'Photos and videos. Snapshots from the court published for our fans\' enjoyment.',
                                        'icon' => 'camera',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'cta',
                            'data' => [
                                'style' => 'outline',
                                'alignment' => 'left',
                                'title' => 'You have the right to a "referee review"',
                                'text' => 'At any time, you can ask what\'s in our stats about you, fix a bad pass (edit data), or call a timeout (delete data). Just blow the whistle and email us.',
                                'button_text' => 'Contact Team Leader',
                                'button_url' => '/kontakt',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h3>Legal Information and Data Controller</h3><p>The official controller of your personal data for our men\'s teams is:</p><p><strong>Tomáš Spanilý</strong> – Team Leader<br>Kovářská 17, Prague 9<br>Mobile: +420 602 285 447<br>Fax: +420 266 315 868<br>E-mail: <a href="javascript:void(0)" data-protected-email="c3BhbmlseUBrZWVwNjkuY3o=">spanily [at] keep69.cz</a></p><h3>GDPR Officer</h3><p><strong>Michal Nejedlý</strong><br>E-mail: <a href="javascript:void(0)" data-protected-email="bmVqZWRseW1pQGdtYWlsLmNvbQ==">nejedlymi [at] gmail.com</a></p><p>You can find the detailed rules of the entire club in the <a href="https://www.basketkbely.cz/" target="_blank">official document on the main club website</a>.</p><h3>Personal Data</h3><p>We only process data necessary for team operations, match organization, and training (name, email, phone, attendance). We keep the data for the duration of your active team membership. You have the right to access, correct, or delete your data.</p><h3>Cookie Policy</h3><p>This website uses only essential technical cookies to ensure functionality (e.g., logging into the member section). We do not use any third-party tracking or marketing cookies.</p>',
                            ],
                        ],
                    ],
                ],
                'status' => 'published',
                'is_visible' => true,
            ]
        );

        // Základní SEO
        $page->seo()->updateOrCreate(
            ['seoable_id' => $page->id, 'seoable_type' => Page::class],
            [
                'title' => [
                    'cs' => 'Ochrana soukromí (GDPR) | Kbelští sokoli',
                    'en' => 'Privacy Policy (GDPR) | Kbely Falcons',
                ],
                'description' => [
                    'cs' => 'Zásady ochrany soukromí tohoto webu a našich mužských týmů.',
                    'en' => 'Privacy policy for this website and our men\'s teams.',
                ],
            ]
        );
    }
}
