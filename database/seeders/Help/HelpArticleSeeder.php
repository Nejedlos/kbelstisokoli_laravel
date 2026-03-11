<?php

namespace Database\Seeders\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Traits\SeedsHelpContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HelpArticleSeeder extends Seeder
{
    use SeedsHelpContent;

    /**
     * Seed articles.
     *
     * @return void
     */
    public function run(): void
    {
        $articles = [
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'prvni-kroky',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin'],
                    'search_keywords' => ['login', 'profil', 'prihlaseni', 'heslo', 'sokol'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'První kroky v systému',
                        'en' => 'First steps in the system',
                    ],
                    'excerpt' => [
                        'cs' => 'Základní informace pro nové členy klubu.',
                        'en' => 'Basic information for new club members.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Základní orientace v systému pro nové členy.',
                            'audience_summary' => 'Všichni uživatelé systému.',
                            'short_intro' => 'Základní informace pro nové členy klubu.',
                        ],
                        'en' => [
                            'purpose' => 'Basic orientation in the system for new members.',
                            'audience_summary' => 'All system users.',
                            'short_intro' => 'Basic information for new club members.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co dělat, když mi nepřišel aktivační e-mail?',
                            'en' => 'What if I didn\'t receive the activation email?',
                        ],
                        'answer' => [
                            'cs' => 'Zkontrolujte složku se spamem nebo kontaktujte správce.',
                            'en' => 'Check your spam folder or contact the administrator.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Upravit můj profil',
                            'en' => 'Edit my profile',
                        ],
                        'url' => '/admin/users/profile',
                        'icon' => 'fa-light fa-user-pen',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'sprava-dochazky',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['coach', 'admin'],
                    'search_keywords' => ['dochazka', 'trenink', 'omluvenky', 'absence', 'attendance'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa docházky',
                        'en' => 'Attendance Management',
                    ],
                    'excerpt' => [
                        'cs' => 'Návod pro trenéry, jak efektivně vést a vyhodnocovat docházku na tréninky a zápasy.',
                        'en' => 'Guide for coaches on how to effectively manage and evaluate attendance for practices and matches.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Evidence účasti členů na sportovních aktivitách klubu.',
                            'audience_summary' => 'Primárně pro trenéry a vedoucí týmů.',
                            'short_intro' => 'Návod pro trenéry, jak efektivně vést a vyhodnocovat docházku.',
                        ],
                        'en' => [
                            'purpose' => 'Evidence of member participation in club sporting activities.',
                            'audience_summary' => 'Primarily for coaches and team leaders.',
                            'short_intro' => 'Guide for coaches on how to effectively manage and evaluate attendance.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Dá se docházka zpětně editovat?',
                            'en' => 'Can attendance be edited retroactively?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, trenér může docházku upravit kdykoliv během probíhající sezóny.',
                            'en' => 'Yes, the coach can edit attendance at any time during the ongoing season.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Vidí rodiče docházku svých dětí?',
                            'en' => 'Can parents see their children\'s attendance?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, v členské sekci mají rodiče i hráči k dispozici grafický přehled docházky.',
                            'en' => 'Yes, in the member section, both parents and players have a graphical attendance overview available.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Zapsat dnešní docházku',
                            'en' => 'Record today\'s attendance',
                        ],
                        'url' => '/admin/trainings', // Příklad URL
                        'icon' => 'fa-light fa-calendar-check',
                    ],
                    [
                        'label' => [
                            'cs' => 'Exportovat docházku',
                            'en' => 'Export attendance',
                        ],
                        'url' => '/admin/attendance/export',
                        'icon' => 'fa-light fa-file-pdf',
                    ],
                ],
            ],
        ];

        foreach ($articles as $article) {
            $category = HelpCategory::where('slug', $article['category_slug'])->first();
            if (!$category) {
                continue;
            }

            $articleData = array_merge($article['data'], ['category_id' => $category->id]);
            $translations = $article['translations'];

            // Načtení obsahu z Markdown souborů
            foreach (['cs', 'en'] as $locale) {
                $path = base_path("database/seeders/Help/content/{$locale}/{$article['category_slug']}/{$article['data']['slug']}.md");
                if (File::exists($path)) {
                    $translations['content'][$locale] = File::get($path);
                }
            }

            // Upsert článku
            $helpArticle = $this->upsertHelpItem(HelpArticle::class, $articleData, $translations);

            // Synchronizace FAQ (pokud není článek customizován)
            if (!$helpArticle->is_customized) {
                $this->syncFaqs($helpArticle, $article['faqs'] ?? []);
                $this->syncQuickActions($helpArticle, $article['quick_actions'] ?? []);
            }
        }
    }

    /**
     * Synchronize FAQs for an article.
     */
    protected function syncFaqs(HelpArticle $article, array $faqs): void
    {
        $article->faqs()->delete();

        foreach ($faqs as $index => $faq) {
            $article->faqs()->create([
                'sort_order' => $index,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ]);
        }
    }

    /**
     * Synchronize Quick Actions for an article.
     */
    protected function syncQuickActions(HelpArticle $article, array $actions): void
    {
        $article->quickActions()->delete();

        foreach ($actions as $index => $action) {
            $article->quickActions()->create([
                'url' => $action['url'],
                'icon' => $action['icon'],
                'sort_order' => $index,
                'label' => $action['label'],
            ]);
        }
    }
}
