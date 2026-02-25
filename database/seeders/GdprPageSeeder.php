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
                                'variant' => 'minimal',
                                'headline' => 'Ochrana soukromí',
                                'subheadline' => 'Jak pracujeme s osobními údaji v rámci týmů Muži C a Muži E',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h2>Základní informace</h2><p>Tato stránka stručně popisuje zásady ochrany soukromí tohoto webu a týmů Muži C a Muži E. Podrobná klubová pravidla a kontakty pověřence naleznete na hlavním webu TJ Sokol Kbely Basketbal.</p><h3>Správce údajů</h3><p>TJ Sokol Kbely Basketbal, Praha 9 – Kbely</p><h3>Účel</h3><ul><li>Členství a správa týmů</li><li>Komunikace a organizace zápasů a tréninků</li></ul><p>Dotazy můžete směřovat přes <a href="/kontakt">kontaktní stránku</a>.</p>',
                            ],
                        ],
                    ],
                    'en' => [
                        [
                            'type' => 'hero',
                            'data' => [
                                'variant' => 'minimal',
                                'headline' => 'Privacy Policy',
                                'subheadline' => 'How we process personal data within Men C & Men E teams',
                            ],
                        ],
                        [
                            'type' => 'rich_text',
                            'data' => [
                                'content' => '<h2>Basics</h2><p>This page briefly describes privacy rules for this website and the Men C & Men E teams. Detailed club rules and DPO contacts can be found on the main club website.</p><h3>Controller</h3><p>TJ Sokol Kbely Basketbal, Prague 9 – Kbely</p><h3>Purpose</h3><ul><li>Membership and team management</li><li>Communication and scheduling</li></ul><p>Questions? Use our <a href="/kontakt">contact page</a>.</p>',
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
                    'cs' => 'Zásady ochrany soukromí tohoto webu a týmů Muži C a Muži E.',
                    'en' => 'Privacy policy for this website and the Men C & Men E teams.',
                ],
            ]
        );
    }
}
