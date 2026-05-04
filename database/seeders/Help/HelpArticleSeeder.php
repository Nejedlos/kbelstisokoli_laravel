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
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'pool-fotografii',
                    'sort_order' => 15,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'editor', 'coach'],
                    'search_keywords' => ['pool', 'fotografie', 'fotky', 'import', 'ai', 'popisky', 'media'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Pool fotografií (Hromadná správa)',
                        'en' => 'Photo Pool (Bulk Management)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak hromadně nahrávat a spravovat klubové fotografie s využitím AI.',
                        'en' => 'How to bulk upload and manage club photos using AI.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Efektivní správa vizuálního obsahu a hromadné nahrávání k událostem.',
                            'audience_summary' => 'Redaktoři, administrátoři a trenéři spravující média.',
                            'short_intro' => 'Pool fotografií je centrálním místem pro veškerý klubový vizuální obsah.',
                        ],
                        'en' => [
                            'purpose' => 'Efficient management of visual content and bulk uploading to events.',
                            'audience_summary' => 'Editors, administrators, and coaches managing media.',
                            'short_intro' => 'The Photo Pool is the central hub for all club visual content.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Proč se při nahrávání objevuje basketbalový míč?',
                            'en' => 'Why does a basketball animation appear during upload?',
                        ],
                        'answer' => [
                            'cs' => 'Jde o ukazatel hromadného importu. Okno v tuto chvíli nezavírejte, probíhá optimalizace fotek.',
                            'en' => 'It is a bulk import indicator. Do not close the window at this time; photos are being optimized.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Otevřít Pool fotografií',
                            'en' => 'Open Photo Pool',
                        ],
                        'url' => '/admin/photo-pools',
                        'icon' => 'fa-light fa-images',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'klubove-souteze',
                    'sort_order' => 15,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['soutez', 'turnaj', 'cbf', 'rocnik', 'liga', 'competition'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Klubové soutěže (Turnaje a ligy)',
                        'en' => 'Club Competitions (Tournaments and Leagues)',
                    ],
                    'excerpt' => [
                        'cs' => 'Rozdíl mezi vlastními turnaji a oficiálními ČBF soutěžemi.',
                        'en' => 'Difference between local tournaments and official ČBF competitions.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Správa soutěžních celků a jejich zobrazení na webu.',
                            'audience_summary' => 'Administrátoři a šéftrenéři.',
                            'short_intro' => 'Tato sekce definuje, ve kterých ligách a turnajích naše týmy hrají.',
                        ],
                        'en' => [
                            'purpose' => 'Management of competitive entities and their display on the website.',
                            'audience_summary' => 'Administrators and head coaches.',
                            'short_intro' => 'This section defines the leagues and tournaments our teams play in.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak propojím soutěž s ČBF?',
                            'en' => 'How do I link a competition with ČBF?',
                        ],
                        'answer' => [
                            'cs' => 'Nastavte typ soutěže na „cbf“ a vyplňte externí ID z portálu federace.',
                            'en' => 'Set the competition type to "cbf" and fill in the external ID from the federation portal.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Spravovat soutěže',
                            'en' => 'Manage competitions',
                        ],
                        'url' => '/admin/club-competitions',
                        'icon' => 'fa-light fa-trophy',
                    ],
                ],
            ],
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'vstup-do-kabiny',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['login', 'prihlaseni', 'kabina', 'vstup', 'heslo', 'username', 'email'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Vstup do kabiny (Přihlášení)',
                        'en' => 'Entry to the Locker Room (Login)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak se bezpečně přihlásit do systému klubu.',
                        'en' => 'How to securely log in to the club system.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění bezpečného přístupu oprávněných osob do systému.',
                            'audience_summary' => 'Všichni registrovaní členové a zástupci klubu.',
                            'short_intro' => 'Vítejte v digitální kabině Kbelští sokoli. Zde začíná vaše cesta systémem.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring secure access for authorized persons to the system.',
                            'audience_summary' => 'All registered members and club representatives.',
                            'short_intro' => 'Welcome to the Kbelští sokoli digital locker room. Your journey starts here.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co znamená hláška „Špatná nahrávka!“?',
                            'en' => 'What does the message "Špatná nahrávka!" mean?',
                        ],
                        'answer' => [
                            'cs' => 'Zadali jste špatné heslo nebo e-mail. Zkontrolujte překlepy a CapsLock.',
                            'en' => 'You entered an incorrect password or e-mail. Check for typos and CapsLock.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Přejít na přihlášení',
                            'en' => 'Go to login',
                        ],
                        'url' => '/admin/login',
                        'icon' => 'fa-light fa-right-to-bracket',
                    ],
                ],
            ],
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'zapomenute-heslo',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['heslo', 'password', 'obnova', 'reset', 'zapomenute', 'nahrávka'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Zapomenutá nahrávka (Obnova hesla)',
                        'en' => 'Forgotten Pass (Password Reset)',
                    ],
                    'excerpt' => [
                        'cs' => 'Postup pro obnovu přístupu k účtu, pokud jste zapomněli heslo.',
                        'en' => 'Procedure for recovering account access if you have forgotten your password.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Samoobslužná obnova přístupu k účtu.',
                            'audience_summary' => 'Uživatelé, kteří ztratili přístupové údaje.',
                            'short_intro' => 'Zapomněli jste heslo? Žádný problém, nahrávku (obnovu) vám pošleme na e-mail.',
                        ],
                        'en' => [
                            'purpose' => 'Self-service account access recovery.',
                            'audience_summary' => 'Users who have lost their access credentials.',
                            'short_intro' => 'Forgotten your password? No problem, we\'ll send you a reset "pass" to your e-mail.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak dlouho platí odkaz v e-mailu?',
                            'en' => 'How long is the link in the e-mail valid?',
                        ],
                        'answer' => [
                            'cs' => 'Odkaz pro obnovu platí 60 minut. Poté je třeba požádat o nový.',
                            'en' => 'The recovery link is valid for 60 minutes. After that, you must request a new one.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Požádat o nové heslo',
                            'en' => 'Request new password',
                        ],
                        'url' => '/admin/password-reset/request',
                        'icon' => 'fa-light fa-key',
                    ],
                ],
            ],
            [
                'category_slug' => 'clenska-sekce',
                'data' => [
                    'slug' => 'muj-profil',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['profil', 'udaje', 'email', 'telefon', 'foto', 'avatar', 'nastaveni'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Můj profil (Členská sekce)',
                        'en' => 'My Profile (Member Section)',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa osobních údajů, kontaktních informací a profilové fotografie.',
                        'en' => 'Management of personal data, contact information, and profile photo.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Udržování aktuálních dat o členech klubu.',
                            'audience_summary' => 'Všichni členové klubu a trenéři.',
                            'short_intro' => 'Váš profil je vaše vizitka v systému Sokoli.',
                        ],
                        'en' => [
                            'purpose' => 'Maintaining up-to-date club member data.',
                            'audience_summary' => 'All club members and coaches.',
                            'short_intro' => 'Your profile is your business card in the Sokoli system.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Proč nemůžu změnit rodné číslo?',
                            'en' => 'Why can\'t I change my personal ID number?',
                        ],
                        'answer' => [
                            'cs' => 'Tento údaj je pevně svázán s matrikou ČBF a může jej měnit pouze administrátor.',
                            'en' => 'This information is fixed to the ČBF register and can only be changed by an administrator.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Upravit můj profil',
                            'en' => 'Edit my profile',
                        ],
                        'url' => '/member/profile',
                        'icon' => 'fa-light fa-user-gear',
                    ],
                ],
            ],
            [
                'category_slug' => 'clenska-sekce',
                'data' => [
                    'slug' => 'zabezpeceni-uctu',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['2fa', 'zabezpeceni', 'heslo', 'overeni', 'security', 'authenticator', 'změna hesla', 'změnit heslo'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Neprůstřelná obrana (Zabezpečení a 2FA)',
                        'en' => 'Bulletproof Defense (Security and 2FA)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak si nastavit dvoufázové ověření a lépe ochránit svůj účet.',
                        'en' => 'How to set up two-factor authentication and better protect your account.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Maximalizace bezpečnosti uživatelských účtů.',
                            'audience_summary' => 'Doporučeno všem, povinné pro administrátory.',
                            'short_intro' => 'I ta nejlepší nahrávka potřebuje dobrou obranu. Chraňte svůj účet pomocí 2FA.',
                        ],
                        'en' => [
                            'purpose' => 'Maximizing user account security.',
                            'audience_summary' => 'Recommended for all, mandatory for administrators.',
                            'short_intro' => 'Even the best pass needs a good defense. Protect your account with 2FA.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Je 2FA povinné?',
                            'en' => 'Is 2FA mandatory?',
                        ],
                        'answer' => [
                            'cs' => 'Pro administrátory a trenéry ano, pro hráče a rodiče je to silně doporučeno.',
                            'en' => 'For administrators and coaches yes; for players and parents it is highly recommended.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Nastavit 2FA',
                            'en' => 'Set up 2FA',
                        ],
                        'url' => '/member/2fa/setup',
                        'icon' => 'fa-light fa-shield-check',
                    ],
                ],
            ],
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'sokoli-v-mobilu',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['mobil', 'aplikace', 'pwa', 'iphone', 'android', 'plochu', 'install'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Sokoli v mobilu (PWA aplikace)',
                        'en' => 'Sokoli on Mobile (PWA App)',
                    ],
                    'excerpt' => [
                        'cs' => 'Návod, jak si přidat systém na plochu mobilu jako aplikaci.',
                        'en' => 'Guide on how to add the system to your mobile home screen as an app.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zlepšení uživatelského zážitku na mobilních zařízeních.',
                            'audience_summary' => 'Uživatelé přistupující z chytrých telefonů.',
                            'short_intro' => 'Mějte klub neustále po ruce. Nainstalujte si nás na plochu.',
                        ],
                        'en' => [
                            'purpose' => 'Improving the user experience on mobile devices.',
                            'audience_summary' => 'Users accessing from smartphones.',
                            'short_intro' => 'Have the club always at hand. Install us on your home screen.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Musím stahovat aplikaci z App Store?',
                            'en' => 'Do I have to download the app from the App Store?',
                        ],
                        'answer' => [
                            'cs' => 'Ne, stačí v prohlížeči zvolit „Přidat na plochu“. Je to rychlejší a jednodušší.',
                            'en' => 'No, just select "Add to Home Screen" in your browser. It\'s faster and simpler.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'PWA Manifest',
                            'en' => 'PWA Manifest',
                        ],
                        'url' => '/manifest.json',
                        'icon' => 'fa-light fa-mobile-screen',
                    ],
                ],
            ],
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'role-v-systemu',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['role', 'opravneni', 'admin', 'trener', 'rodic', 'hrac', 'permissions'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Kdo je kdo (Role v systému)',
                        'en' => 'Who is Who (System Roles)',
                    ],
                    'excerpt' => [
                        'cs' => 'Přehled uživatelských rolí a jejich oprávnění v systému.',
                        'en' => 'Overview of user roles and their permissions in the system.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Vysvětlení hierarchie a přístupů v systému.',
                            'audience_summary' => 'Všichni uživatelé.',
                            'short_intro' => 'Basketbal je týmová hra a každý má svou roli. I v našem systému.',
                        ],
                        'en' => [
                            'purpose' => 'Explanation of system hierarchy and access.',
                            'audience_summary' => 'All users.',
                            'short_intro' => 'Basketball is a team game and everyone has a role. Even in our system.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Můžu být zároveň trenér i rodič?',
                            'en' => 'Can I be both a coach and a parent?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, systém podporuje více rolí u jednoho uživatelského účtu.',
                            'en' => 'Yes, the system supports multiple roles for a single user account.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Moje role',
                            'en' => 'My roles',
                        ],
                        'url' => '/member/profile',
                        'icon' => 'fa-light fa-users-gear',
                    ],
                ],
            ],
            [
                'category_slug' => 'uvod',
                'data' => [
                    'slug' => 'onboarding-faq',
                    'sort_order' => 70,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['player', 'parent', 'coach', 'admin', 'super_admin'],
                    'search_keywords' => ['faq', 'otazky', 'napoveda', 'pomoc', 'nejčastější', 'help'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'První kroky: Často kladené otázky',
                        'en' => 'First Steps: FAQ',
                    ],
                    'excerpt' => [
                        'cs' => 'Rychlé odpovědi na nejčastější dotazy nových uživatelů.',
                        'en' => 'Quick answers to common questions from new users.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Rychlá pomoc v začátcích.',
                            'audience_summary' => 'Noví členové a uživatelé.',
                            'short_intro' => 'Máte otázku? Máme odpověď. Zde jsou ty nejčastější.',
                        ],
                        'en' => [
                            'purpose' => 'Quick help for beginners.',
                            'audience_summary' => 'New members and users.',
                            'short_intro' => 'Have a question? We have an answer. Here are the most common ones.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Kdo mi založí účet?',
                            'en' => 'Who creates my account?',
                        ],
                        'answer' => [
                            'cs' => 'Účet zakládá administrátor klubu na základě vaší přihlášky.',
                            'en' => 'The account is created by the club administrator based on your application.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Kontaktovat podporu',
                            'en' => 'Contact support',
                        ],
                        'url' => 'mailto:info@basketkbely.cz',
                        'icon' => 'fa-light fa-envelope',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'evidence-uzivatelu',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['uzivatele', 'clenove', 'ghost', 'duplicity', 'filtry', 'users', 'members', 'merging', 'sloučení', 'přidat člena', 'nový člen'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Evidence uživatelů a členů (Uživatel)',
                        'en' => 'User and Member Records (User)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak se orientovat v seznamu členů, používat filtry, hromadné akce a slučovat duplicity.',
                        'en' => 'How to navigate the member list, use filters, bulk actions, and merge duplicates.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Základní správa členské základny, rychlé vyhledávání a pročišťování dat.',
                            'audience_summary' => 'Administrátoři a trenéři se zvýšeným oprávněním.',
                            'short_intro' => 'Tato sekce je srdcem vaší správy. Zde najdete každého člena klubu a můžete provádět hromadné operace.',
                        ],
                        'en' => [
                            'purpose' => 'Basic management of the membership base, quick search, and data cleanup.',
                            'audience_summary' => 'Administrators and coaches with elevated permissions.',
                            'short_intro' => 'This section is the heart of your management. Here you can find every club member and perform bulk operations.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co znamená ikona ducha u uživatele?',
                            'en' => 'What does the ghost icon next to a user mean?',
                        ],
                        'answer' => [
                            'cs' => 'Uživatel se ještě nikdy nepřihlásil. Byl pravděpodobně vytvořen importem nebo ručně administrátorem a čeká na první vstup.',
                            'en' => 'The user has never logged in yet. They were likely created via import or manually by an administrator and are waiting for their first entry.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Jak se zbavím duplicitních záznamů?',
                            'en' => 'How do I get rid of duplicate records?',
                        ],
                        'answer' => [
                            'cs' => 'Použijte akci "Sloučit s..." v řádku uživatele. Vyberte cílový (správný) profil, do kterého se všechna data převedou.',
                            'en' => 'Use the "Merge with..." action in the user row. Select the target (correct) profile into which all data will be transferred.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Zobrazit seznam uživatelů',
                            'en' => 'Show user list',
                        ],
                        'url' => '/admin/users',
                        'icon' => 'fa-light fa-users',
                    ],
                    [
                        'label' => [
                            'cs' => 'Hledat duplicity',
                            'en' => 'Search duplicates',
                        ],
                        'url' => '/admin/users?tableFilters[duplicates][value]=1',
                        'icon' => 'fa-light fa-clone',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'sprava-clena',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['editace', 'profil', 'udaje', 'osobni', 'admin', 'update', 'seznam', 'member', 'reset hesla', 'zapomenuté heslo'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa a editace člena (Hráč)',
                        'en' => 'Member Management and Editing (Player)',
                    ],
                    'excerpt' => [
                        'cs' => 'Detailní popis záložek v profilu člena, osobních údajů, fyzických parametrů a zabezpečení.',
                        'en' => 'Detailed description of tabs in the member profile, personal data, physical parameters, and security.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Detailní správa všech aspektů profilu člena klubu.',
                            'audience_summary' => 'Administrátoři s plným přístupem k osobním datům.',
                            'short_intro' => 'V detailu člena spravujete vše od základních kontaktů přes fyzické parametry až po přístupová práva.',
                        ],
                        'en' => [
                            'purpose' => 'Detailed management of all aspects of the club member profile.',
                            'audience_summary' => 'Administrators with full access to personal data.',
                            'short_intro' => 'In the member detail, you manage everything from basic contacts to physical parameters to access rights.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak aktivuji hráči profil na webu?',
                            'en' => 'How do I activate a player profile on the web?',
                        ],
                        'answer' => [
                            'cs' => 'V záložce „Hráč“ zapněte přepínač „Má aktivní hráčský profil“. Hráč se pak objeví v soupisce svého týmu.',
                            'en' => 'In the "Player" tab, turn on the "Has active player profile" toggle. The player will then appear in their team\'s roster.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'K čemu slouží nouzový kontakt?',
                            'en' => 'What is the emergency contact for?',
                        ],
                        'answer' => [
                            'cs' => 'Slouží trenérům v mobilní aplikaci pro rychlé volání rodičům v případě úrazu. Najdete ho v záložce „Osobní“.',
                            'en' => 'It serves coaches in the mobile app for quick calls to parents in case of injury. You can find it in the "Personal" tab.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Vytvořit nového člena',
                            'en' => 'Create new member',
                        ],
                        'url' => '/admin/users/create',
                        'icon' => 'fa-light fa-user-plus',
                    ],
                    [
                        'label' => [
                            'cs' => 'Můj profil (náhled)',
                            'en' => 'My profile (preview)',
                        ],
                        'url' => '/member/profile',
                        'icon' => 'fa-light fa-id-card',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'rodinne-vazby',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'parent'],
                    'search_keywords' => ['rodic', 'dite', 'vazba', 'propojeni', 'parent', 'child', 'family'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Rodinné vazby (Rodič a dítě)',
                        'en' => 'Family Ties (Parent and Child)',
                    ],
                    'excerpt' => [
                        'cs' => 'Návod na propojení účtů pro přepínání profilů v mobilní aplikaci.',
                        'en' => 'Guide to connecting accounts for profile switching in the mobile app.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zprovoznění rodinných účtů a nouzových kontaktů.',
                            'audience_summary' => 'Administrátoři a rodiče.',
                            'short_intro' => 'Propojte rodinu, ať mohou rodiče omlouvat děti z jedné aplikace.',
                        ],
                        'en' => [
                            'purpose' => 'Activation of family accounts and emergency contacts.',
                            'audience_summary' => 'Administrators and parents.',
                            'short_intro' => 'Connect the family so parents can excuse children from one app.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Může mít dítě více připojených rodičů?',
                            'en' => 'Can a child have multiple connected parents?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, k jednomu profilu dítěte můžete připojit libovolné množství rodičovských účtů.',
                            'en' => 'Yes, you can connect any number of parent accounts to a single child profile.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'hracske-profily',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'coach', 'player'],
                    'search_keywords' => ['hrac', 'stint', 'dres', 'historie', 'cislo', 'pozice', 'player', 'jersey', 'stats', 'statistiky'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Hráčské profily a sportovní historie (Profil)',
                        'en' => 'Player Profiles and History (Profile)',
                    ],
                    'excerpt' => [
                        'cs' => 'Evidence dresů, pozic, historie působení hráče (stinty) a automatické statistiky na webu.',
                        'en' => 'Records of jerseys, positions, player history (stints), and automatic website statistics.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Správa sportovní identity hráče a uchování historických dat o jeho kariéře.',
                            'audience_summary' => 'Administrátoři, trenéři i samotní hráči v členské sekci.',
                            'short_intro' => 'Každý hráč má svůj příběh. Zde spravujete jeho dresy, týmy a sledujete herní růst.',
                        ],
                        'en' => [
                            'purpose' => 'Management of a player\'s sporting identity and preservation of historical career data.',
                            'audience_summary' => 'Administrators, coaches, and players in the member section.',
                            'short_intro' => 'Every player has a story. Here you manage their jerseys, teams, and track their growth.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co je to Sezónní konfigurace (Stint)?',
                            'en' => 'What is a Season Configuration (Stint)?',
                        ],
                        'answer' => [
                            'cs' => 'Záznam spojující hráče s týmem v konkrétní sezóně. Určuje číslo dresu a roli pro daný rok.',
                            'en' => 'A record connecting a player with a team in a specific season. It defines the jersey number and role for that year.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Kde uvidím statistiky hráče?',
                            'en' => 'Where can I see player statistics?',
                        ],
                        'answer' => [
                            'cs' => 'Agregované statistiky se zobrazují na veřejném webu a v detailu hráče v sekci „Výsledky“.',
                            'en' => 'Aggregated statistics are displayed on the public website and in the player detail in the "Results" section.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Správa hráčských profilů',
                            'en' => 'Manage player profiles',
                        ],
                        'url' => '/admin/player-profiles',
                        'icon' => 'fa-light fa-basketball',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'nabory-a-zajemci',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['nabor', 'lead', 'zajemce', 'prihlaska', 'novacek', 'recruitment'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Nábory a správa zájemců',
                        'en' => 'Recruitment and Lead Management',
                    ],
                    'excerpt' => [
                        'cs' => 'Zpracování přihlášek z webu a postup pro přijetí nováčka.',
                        'en' => 'Processing applications from the web and the procedure for accepting a newcomer.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Přehledné zpracování nových zájemců o basketbal.',
                            'audience_summary' => 'Administrátoři a šéftrenéři.',
                            'short_intro' => 'Nábor je první kontakt zájemce s klubem. Udělejte ho profesionálně.',
                        ],
                        'en' => [
                            'purpose' => 'Clear processing of new basketball applicants.',
                            'audience_summary' => 'Administrators and head coaches.',
                            'short_intro' => 'Recruitment is the applicant\'s first contact with the club. Make it professional.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Vytvoří se člen automaticky po přijetí zájemce?',
                            'en' => 'Is a member created automatically after accepting an applicant?',
                        ],
                        'answer' => [
                            'cs' => 'Ne, člena musíte vytvořit ručně v sekci Uživatelé pro zachování čistoty dat.',
                            'en' => 'No, you must create the member manually in the Users section to maintain data integrity.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Seznam zájemců',
                            'en' => 'Lead list',
                        ],
                        'url' => '/admin/leads',
                        'icon' => 'fa-light fa-address-card',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'role-a-opravneni',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['super_admin'],
                    'search_keywords' => ['role', 'prava', 'opravneni', 'pristup', 'rbac', 'permissions'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Role a oprávnění',
                        'en' => 'Roles and Permissions',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak přidělovat role a co která role v systému umožňuje.',
                        'en' => 'How to assign roles and what each role in the system allows.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Správa přístupových práv k různým modulům administrace.',
                            'audience_summary' => 'Administrátoři a Superadmini.',
                            'short_intro' => 'Práva určují, kdo může spravovat finance a kdo jen docházku.',
                        ],
                        'en' => [
                            'purpose' => 'Management of access rights to various administration modules.',
                            'audience_summary' => 'Administrators and Superadmins.',
                            'short_intro' => 'Rights determine who can manage finances and who just attendance.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Kde přidělím členovi roli Trenéra?',
                            'en' => 'Where do I assign a member the Coach role?',
                        ],
                        'answer' => [
                            'cs' => 'V detailu uživatele na záložce „Zabezpečení“ v poli „Role“.',
                            'en' => 'In the user detail on the "Security" tab in the "Roles" field.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'sprava-tymu',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'coach', 'super_admin'],
                    'search_keywords' => ['tym', 'kategorie', 'trener', 'barva', 'synchronizace', 'cz.basketball'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa týmů a kategorií',
                        'en' => 'Team and Category Management',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak organizovat klub do věkových kategorií a spravovat trenéry.',
                        'en' => 'How to organize the club into age categories and manage coaches.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Organizace sportovní struktury klubu.',
                            'audience_summary' => 'Administrátoři a trenéři.',
                            'short_intro' => 'Týmy jsou základem všeho. Zde určíte, kdo koho trénuje a v jakých barvách hrajeme.',
                        ],
                        'en' => [
                            'purpose' => 'Organization of the club\'s sports structure.',
                            'audience_summary' => 'Administrators and coaches.',
                            'short_intro' => 'Teams are the foundation of everything. Here you determine who coaches whom and what colors we play in.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Seznam týmů',
                            'en' => 'Team list',
                        ],
                        'url' => '/admin/teams',
                        'icon' => 'fa-light fa-people-group',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'soupisky-a-clenstvi',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'coach'],
                    'search_keywords' => ['soupiska', 'hrac', 'clenstvi', 'roster', 'attach', 'detach'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Soupisky a členství v týmu',
                        'en' => 'Rosters and Team Membership',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa seznamu hráčů v týmu a jejich herních statusů.',
                        'en' => 'Management of the player list in the team and their playing statuses.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Přiřazování hráčů k týmům a sezónám.',
                            'audience_summary' => 'Administrátoři a trenéři.',
                            'short_intro' => 'Kdo v týmu jen trénuje a kdo hraje zápasy? Zde spravujete složení týmu.',
                        ],
                        'en' => [
                            'purpose' => 'Assigning players to teams and seasons.',
                            'audience_summary' => 'Administrators and coaches.',
                            'short_intro' => 'Who in the team just trains and who plays matches? Here you manage the team composition.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Může být hráč ve více týmech?',
                            'en' => 'Can a player be in multiple teams?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, hráč může být členem libovolného počtu týmů.',
                            'en' => 'Yes, a player can be a member of any number of teams.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'planovani-sezony',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['sezona', 'rok', 'inicializace', 'start', 'konfigurace', 'preklopeni'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Plánování a start nové sezóny',
                        'en' => 'Planning and Starting a New Season',
                    ],
                    'excerpt' => [
                        'cs' => 'Průvodce vytvořením sezóny a hromadným nastavením členů.',
                        'en' => 'A guide to creating a season and bulk setting up members.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění kontinuity dat mezi sportovními roky.',
                            'audience_summary' => 'Administrátoři klubu.',
                            'short_intro' => 'Start sezóny je největší technická operace v roce. Postupujte podle návodu.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring data continuity between sports years.',
                            'audience_summary' => 'Club administrators.',
                            'short_intro' => 'Starting a season is the biggest technical operation of the year. Follow the instructions.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Správa sezón',
                            'en' => 'Season management',
                        ],
                        'url' => '/admin/seasons',
                        'icon' => 'fa-light fa-calendar-star',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'treninky-a-dochazka',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['trenink', 'dochazka', 'omluva', 'mismatch', 'prezence', 'attendance', 'reporty', 'odmeny'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Tréninky a docházka (Trénink)',
                        'en' => 'Training and Attendance (Training)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak plánovat tréninky, efektivně vést evidenci docházky, řešit omluvy a kontrolovat disciplínu (Mismatches).',
                        'en' => 'How to plan training sessions, effectively keep attendance records, handle excuses, and check discipline (Mismatches).',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Sledování tréninkové morálky, rozvoj hráčů a podklady pro odměny trenérů.',
                            'audience_summary' => 'Trenéři a administrátoři.',
                            'short_intro' => 'Kvalitní docházka je základem úspěchu týmu. Systém vám pomůže hlídat disciplínu hráčů i trenérské hodiny.',
                        ],
                        'en' => [
                            'purpose' => 'Monitoring training morale, player development, and coach reward data.',
                            'audience_summary' => 'Coaches and administrators.',
                            'short_intro' => 'Quality attendance is the foundation of team success. The system helps you monitor player discipline and coaching hours.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co znamená zelené pozadí v tabulce tréninků?',
                            'en' => 'What does the green background in the training table mean?',
                        ],
                        'answer' => [
                            'cs' => 'Zelené řádky označují budoucí tréninky, které teprve proběhnou. Šedé řádky jsou historie.',
                            'en' => 'Green rows mark future trainings that are yet to happen. Grey rows are history.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Může mít trénink více týmů?',
                            'en' => 'Can a training have multiple teams?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, do pole "Týmy" můžete vybrat libovolný počet týmů. Soupiska docházky se automaticky sloučí.',
                            'en' => 'Yes, you can select any number of teams in the "Teams" field. The attendance roster will automatically merge.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Přehled tréninků',
                            'en' => 'Training overview',
                        ],
                        'url' => '/admin/trainings',
                        'icon' => 'fa-light fa-calendar-check',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'zapasy-a-nominace',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['zapas', 'nominace', 'vysledek', 'skore', 'pozvanka', 'match', 'cbf', 'cz.basketball', 'sync', 'rozpis', 'víkend', 'kdy hrajeme'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Zápasy a nominace hráčů (Zápas)',
                        'en' => 'Matches and Player Nominations (Match)',
                    ],
                    'excerpt' => [
                        'cs' => 'Workflow od plánování utkání, přes synchronizaci s ČBF (cz.basketball) až po zápis výsledků.',
                        'en' => 'Workflow from match planning, through synchronization with ČBF (cz.basketball), to result recording.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Organizace soutěžních i přátelských utkání s podporou automatizace.',
                            'audience_summary' => 'Trenéři a administrátoři.',
                            'short_intro' => 'Zápas je vyvrcholením tréninku. Systém se postará o stažení rozpisu, vy se soustřeďte na nominaci.',
                        ],
                        'en' => [
                            'purpose' => 'Organization of competitive and friendly matches with automation support.',
                            'audience_summary' => 'Coaches and administrators.',
                            'short_intro' => 'A match is the culmination of training. The system takes care of downloading the schedule; you focus on the nomination.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak funguje synchronizace s ČBF?',
                            'en' => 'How does synchronization with ČBF work?',
                        ],
                        'answer' => [
                            'cs' => 'Systém se několikrát denně ptá na web cz.basketball a stahuje nové termíny i výsledky. Poznáte to podle ikony cloudu.',
                            'en' => 'Several times a day, the system checks the cz.basketball website and downloads new dates and results. You can recognize this by the cloud icon.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Co jsou to Mismatches u zápasů?',
                            'en' => 'What are Mismatches in matches?',
                        ],
                        'answer' => [
                            'cs' => 'Označují rozdíl mezi tím, kdo potvrdil účast v aplikaci a kdo byl skutečně trenérem označen jako přítomný.',
                            'en' => 'They indicate the difference between who confirmed participation in the app and who was actually marked as present by the coach.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Rozpis zápasů',
                            'en' => 'Match schedule',
                        ],
                        'url' => '/admin/basketball-matches',
                        'icon' => 'fa-light fa-basketball-hoop',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'souperi',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'coach'],
                    'search_keywords' => ['souper', 'klub', 'logo', 'mesto', 'slouceni', 'opponent'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Adresář soupeřů',
                        'en' => 'Opponent Directory',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa klubů, se kterými hrajeme, a řešení duplicit.',
                        'en' => 'Management of clubs we play against and duplicate resolution.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Evidence externích týmů a klubů.',
                            'audience_summary' => 'Administrátoři a trenéři.',
                            'short_intro' => 'Mějte v soupeřích pořádek. Duplicity po importu snadno vyřešíte zde.',
                        ],
                        'en' => [
                            'purpose' => 'Evidence of external teams and clubs.',
                            'audience_summary' => 'Administrators and coaches.',
                            'short_intro' => 'Keep your opponents organized. Easily resolve duplicates after import here.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Správa soupeřů',
                            'en' => 'Opponent management',
                        ],
                        'url' => '/admin/opponents',
                        'icon' => 'fa-light fa-shield-halved',
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'sportovni-udaje-hrace',
                    'sort_order' => 70,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'coach', 'player', 'parent'],
                    'search_keywords' => ['dres', 'cislo', 'pozice', 'licence', 'vyska', 'vaha', 'medical'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Sportovní údaje hráče',
                        'en' => 'Player Sports Data',
                    ],
                    'excerpt' => [
                        'cs' => 'Evidence dresů, licencí, zdravotních a fyzických údajů.',
                        'en' => 'Recording of jerseys, licenses, medical and physical data.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Detailní sportovní karta člena klubu.',
                            'audience_summary' => 'Trenéři, administrátoři a členové.',
                            'short_intro' => 'Dresy, výška, váha a lékařské prohlídky na jednom místě.',
                        ],
                        'en' => [
                            'purpose' => 'Detailed sports card of a club member.',
                            'audience_summary' => 'Coaches, administrators, and members.',
                            'short_intro' => 'Jerseys, height, weight, and medical check-ups in one place.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'omlouvani-z-akci',
                    'sort_order' => 80,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent'],
                    'search_keywords' => ['omluva', 'kalendar', 'absence', 'duvod', 'deadline', 'trénink', 'zápas', 'syn', 'dcera', 'dítě', 'omluvit'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Omlouvání z akcí',
                        'en' => 'Excusing from Events',
                    ],
                    'excerpt' => [
                        'cs' => 'Návod pro členy, jak se správně omluvit z tréninku.',
                        'en' => 'Instructions for members on how to correctly excuse themselves from training.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Komunikace neúčasti mezi členem a trenérem.',
                            'audience_summary' => 'Hráči a rodiče.',
                            'short_intro' => 'Nemůžete na trénink? Dejte to vědět včas přes aplikaci.',
                        ],
                        'en' => [
                            'purpose' => 'Communication of absence between a member and a coach.',
                            'audience_summary' => 'Players and parents.',
                            'short_intro' => 'Can\'t make it to training? Let us know in time via the app.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Do kdy se mohu omluvit?',
                            'en' => 'Until when can I excuse myself?',
                        ],
                        'answer' => [
                            'cs' => 'Standardně 24 hodin před akcí. Poté už jen přímo trenérovi.',
                            'en' => 'Standardly 24 hours before the event. After that, only directly to the coach.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'klubove-akce',
                    'sort_order' => 90,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'coach', 'player', 'parent'],
                    'search_keywords' => ['akce', 'event', 'soustředění', 'kemp', 'brigáda', 'schůzka', 'rsvp', 'přihláška'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Klubové akce (Event)',
                        'en' => 'Club Events (Event)',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa mimořádných událostí jako soustředění, kempy, schůzky a společenské akce.',
                        'en' => 'Management of special events such as retreats, camps, meetings, and social events.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Organizace nevšedních klubových aktivit a sběr přihlášek.',
                            'audience_summary' => 'Všichni členové klubu a organizátoři.',
                            'short_intro' => 'Klub není jen o tréninku. Organizujte kempy, brigády nebo večírky s plnou kontrolou nad účastí.',
                        ],
                        'en' => [
                            'purpose' => 'Organization of unusual club activities and collection of applications.',
                            'audience_summary' => 'All club members and organizers.',
                            'short_intro' => 'The club is not just about training. Organize camps, volunteer events, or parties with full control over participation.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak zapnu přihlašování na akci?',
                            'en' => 'How do I enable registration for an event?',
                        ],
                        'answer' => [
                            'cs' => 'Při editaci akce zaškrtněte pole „Povolit docházku (RSVP)“. Hráčům se pak v aplikaci objeví tlačítka Ano/Ne.',
                            'en' => 'When editing an event, check the "Enable attendance (RSVP)" field. Players will then see Yes/No buttons in the app.',
                        ],
                    ],
                    [
                        'question' => [
                            'cs' => 'Uvidí akci i neregistrovaní lidé?',
                            'en' => 'Will unregistered people see the event too?',
                        ],
                        'answer' => [
                            'cs' => 'Pokud zaškrtnete „Veřejné“, akce se zobrazí v kalendáři na hlavním webu klubu.',
                            'en' => 'If you check "Public", the event will be displayed in the calendar on the club\'s main website.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Přehled klubových akcí',
                            'en' => 'Club events overview',
                        ],
                        'url' => '/admin/club-events',
                        'icon' => 'fa-light fa-calendar-star',
                    ],
                ],
            ],
            // BATCH 04: FINANCE
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'financni-system-prehled',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'coach', 'parent', 'player'],
                    'search_keywords' => ['finance', 'predpis', 'platba', 'alokace', 'dluh', 'prehled'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Finanční systém klubu',
                        'en' => 'Club Finance System',
                    ],
                    'excerpt' => [
                        'cs' => 'Základní přehled o tom, jak v klubu fungují platby a předpisy.',
                        'en' => 'Basic overview of how payments and charges work in the club.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Vysvětlení finanční logiky klubu.',
                            'audience_summary' => 'Všichni členové a správci.',
                            'short_intro' => 'Jak u nás funguje koloběh peněz od předpisu po zaplacení.',
                        ],
                        'en' => [
                            'purpose' => 'Explanation of the club\'s financial logic.',
                            'audience_summary' => 'All members and administrators.',
                            'short_intro' => 'How the cycle of money works from charge to payment.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'sprava-tarifu',
                    'sort_order' => 20,
                    'is_published' => true,
                    'audience_roles' => ['admin'],
                    'search_keywords' => ['tarif', 'cenik', 'prispevky', 'castka', 'jednotka'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa finančních tarifů',
                        'en' => 'Financial Tariffs Management',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak nastavit ceník pro příspěvky a další poplatky.',
                        'en' => 'How to set up the price list for fees and other charges.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Nastavení číselníku cen.',
                            'audience_summary' => 'Administrátoři.',
                            'short_intro' => 'Tarify šetří čas při generování hromadných dluhů.',
                        ],
                        'en' => [
                            'purpose' => 'Setting up the price directory.',
                            'audience_summary' => 'Administrators.',
                            'short_intro' => 'Tariffs save time when generating mass debts.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'predpisy-plateb',
                    'sort_order' => 30,
                    'is_published' => true,
                    'audience_roles' => ['admin', 'coach'],
                    'search_keywords' => ['predpis', 'dluh', 'splatnost', 'vs', 'variabilni', 'clenske'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Předpisy plateb (dluhy členů)',
                        'en' => 'Finance Charges (Member Debts)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak zadat členovi povinnost zaplatit členské příspěvky.',
                        'en' => 'How to enter a member\'s obligation to pay membership fees.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Evidence dluhů a splatnosti.',
                            'audience_summary' => 'Hospodáři a trenéři.',
                            'short_intro' => 'Předpis je základ pro to, aby člen věděl, co má zaplatit.',
                        ],
                        'en' => [
                            'purpose' => 'Records of debts and due dates.',
                            'audience_summary' => 'Treasurers and coaches.',
                            'short_intro' => 'A charge is the basis for a member to know what to pay.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'evidence-plateb',
                    'sort_order' => 40,
                    'is_published' => true,
                    'audience_roles' => ['admin'],
                    'search_keywords' => ['platba', 'prijem', 'banka', 'prevod', 'hotovost'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Evidence a příjem plateb',
                        'en' => 'Evidence and Receipt of Payments',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak zapsat přijatou platbu z banky nebo v hotovosti.',
                        'en' => 'How to record a received payment from a bank or in cash.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Evidence příchozích peněz.',
                            'audience_summary' => 'Hospodáři.',
                            'short_intro' => 'Zapsání platby je první krok k jejímu spárování s dluhem.',
                        ],
                        'en' => [
                            'purpose' => 'Evidence of incoming money.',
                            'audience_summary' => 'Treasurers.',
                            'short_intro' => 'Recording a payment is the first step to matching it with a debt.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'parovani-plateb',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin'],
                    'search_keywords' => ['parovani', 'alokace', 'prirazeni', 'propojeni'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Párování plateb (Alokace)',
                        'en' => 'Payment Matching (Allocations)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak propojit přijatou platbu s konkrétním předpisem.',
                        'en' => 'How to link a received payment with a specific charge.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Propojení plateb a dluhů.',
                            'audience_summary' => 'Hospodáři.',
                            'short_intro' => 'Alokací peněz se dluh v systému označí jako zaplacený.',
                        ],
                        'en' => [
                            'purpose' => 'Linking payments and debts.',
                            'audience_summary' => 'Treasurers.',
                            'short_intro' => 'By allocating money, the debt is marked as paid in the system.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'clenska-sekce',
                'data' => [
                    'slug' => 'moje-platby',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['parent', 'player'],
                    'search_keywords' => ['vyuctovani', 'moje', 'ucet', 'bankovni', 'cislo', 'zaplatit', 'platba', 'qr kód', 'variabilní symbol', 'příspěvky'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Moje platby a vyúčtování',
                        'en' => 'My Payments and Invoicing',
                    ],
                    'excerpt' => [
                        'cs' => 'Návod pro členy – jak zaplatit a kde najít platební údaje.',
                        'en' => 'Instructions for members – how to pay and where to find payment details.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Samoobsluha plateb.',
                            'audience_summary' => 'Členové a rodiče.',
                            'short_intro' => 'Mějte přehled o svých příspěvcích pod kontrolou.',
                        ],
                        'en' => [
                            'purpose' => 'Payment self-service.',
                            'audience_summary' => 'Members and parents.',
                            'short_intro' => 'Keep track of your contributions under control.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Moje finance',
                            'en' => 'My finances',
                        ],
                        'url' => '/member/finance',
                        'icon' => 'fa-light fa-wallet',
                    ],
                ],
            ],
            [
                'category_slug' => 'clenska-sekce',
                'data' => [
                    'slug' => 'moje-dochazka',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['player', 'parent', 'coach'],
                    'search_keywords' => ['omluva', 'dochazka', 'nepritomnost', 'trenink', 'zapas', 'omluvit', 'syn', 'dcera', 'dítě', 'nemoc'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Moje docházka a omluvy',
                        'en' => 'My Attendance and Excuses',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak se omluvit z tréninku nebo zápasu a sledovat svou herní účast.',
                        'en' => 'How to excuse yourself from a training or a match and track your participation.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Informování trenéra o nepřítomnosti.',
                            'audience_summary' => 'Hráči a rodiče.',
                            'short_intro' => 'Včasná omluva je základem týmové spolupráce.',
                        ],
                        'en' => [
                            'purpose' => 'Informing the coach about absence.',
                            'audience_summary' => 'Players and parents.',
                            'short_intro' => 'Timely excuse is the foundation of team cooperation.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Do kdy se musím omluvit?',
                            'en' => 'When is the deadline to excuse myself?',
                        ],
                        'answer' => [
                            'cs' => 'Zpravidla 24 hodin před akcí, ale záleží na dohodě v týmu.',
                            'en' => 'Usually 24 hours before the event, but it depends on the team agreement.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Můj program',
                            'en' => 'My program',
                        ],
                        'url' => '/member/attendance',
                        'icon' => 'fa-light fa-calendar-days',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'aktuality-blog',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'editor', 'super_admin'],
                    'search_keywords' => ['clanek', 'aktuality', 'blog', 'novinka', 'post', 'clanky', 'zprava'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Aktuality a klubové novinky',
                        'en' => 'News and Club Updates',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak psát a publikovat články na klubový web.',
                        'en' => 'How to write and publish articles on the club website.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Informování veřejnosti a členů o dění v klubu.',
                            'audience_summary' => 'Redaktoři webu a administrátoři.',
                            'short_intro' => 'Kvalitní obsah je vizitkou klubu.',
                        ],
                        'en' => [
                            'purpose' => 'Informing the public and members about club events.',
                            'audience_summary' => 'Website editors and administrators.',
                            'short_intro' => 'Quality content is the club\'s business card.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Všechny aktuality',
                            'en' => 'All news',
                        ],
                        'url' => '/admin/posts',
                        'icon' => 'fa-light fa-newspaper',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'media-galerie',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'editor', 'super_admin'],
                    'search_keywords' => ['fotky', 'galerie', 'album', 'media', 'obrazky', 'upload'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Galerie a fotoalba',
                        'en' => 'Gallery and Photo Albums',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa fotografií a vizuálního obsahu z akcí.',
                        'en' => 'Management of photos and visual content from events.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Ukládání a prezentace vizuální historie klubu.',
                            'audience_summary' => 'Redaktoři a fotografové klubu.',
                            'short_intro' => 'Fotka vydá za tisíc slov, zejména u mládeže.',
                        ],
                        'en' => [
                            'purpose' => 'Storage and presentation of the club\'s visual history.',
                            'audience_summary' => 'Club editors and photographers.',
                            'short_intro' => 'A photo is worth a thousand words, especially for youth.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Spravovat galerie',
                            'en' => 'Manage galleries',
                        ],
                        'url' => '/admin/galleries',
                        'icon' => 'fa-light fa-images',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'sponzori-partneri',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['partner', 'sponzor', 'logo', 'reklama', 'podpora'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Partneři a sponzoři klubu',
                        'en' => 'Club Partners and Sponsors',
                    ],
                    'excerpt' => [
                        'cs' => 'Správa log a odkazů našich partnerů v patičce a na webu.',
                        'en' => 'Management of logos and links of our partners in the footer and on the website.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zviditelnění podporovatelů klubu.',
                            'audience_summary' => 'Administrátoři klubu.',
                            'short_intro' => 'Díky partnerům můžeme basketbal dělat na takové úrovni.',
                        ],
                        'en' => [
                            'purpose' => 'Increasing visibility of club supporters.',
                            'audience_summary' => 'Club administrators.',
                            'short_intro' => 'Thanks to our partners, we can play basketball at such a level.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Seznam partnerů',
                            'en' => 'List of partners',
                        ],
                        'url' => '/admin/partners',
                        'icon' => 'fa-light fa-handshake',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'staticke-stranky',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['menu', 'navigace', 'web', 'odkazy', 'stranky'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Webové menu a navigace',
                        'en' => 'Website Menu and Navigation',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak spravovat strukturu a odkazy na hlavním webu.',
                        'en' => 'How to manage the structure and links on the main website.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění přehledné navigace pro návštěvníky.',
                            'audience_summary' => 'Administrátoři s právem k nastavení systému.',
                            'short_intro' => 'Dobré menu je jako dobře rozehraný útok – vede k cíli.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring clear navigation for visitors.',
                            'audience_summary' => 'Administrators with system setup rights.',
                            'short_intro' => 'A good menu is like a well-played attack – it leads to the goal.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Nastavit menu',
                            'en' => 'Set menu',
                        ],
                        'url' => '/admin/menus',
                        'icon' => 'fa-light fa-list-tree',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'bannery',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['banner', 'oznameni', 'upozorneni', 'zprava', 'vyzva'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Bannery a rychlá oznámení',
                        'en' => 'Banners and Quick Announcements',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak zobrazit důležité zprávy všem uživatelům webu.',
                        'en' => 'How to display important messages to all website users.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Okamžité informování o důležitých změnách.',
                            'audience_summary' => 'Administrátoři klubu.',
                            'short_intro' => 'Potřebujete, aby si něčeho všimli všichni? Použijte banner.',
                        ],
                        'en' => [
                            'purpose' => 'Immediate information about important changes.',
                            'audience_summary' => 'Club administrators.',
                            'short_intro' => 'Need everyone to notice something? Use a banner.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Správa oznámení',
                            'en' => 'Manage announcements',
                        ],
                        'url' => '/admin/announcements',
                        'icon' => 'fa-light fa-bullhorn',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'seo-redakce',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'editor', 'super_admin'],
                    'search_keywords' => ['seo', 'google', 'vyhledavani', 'optimalizace', 'metatagy'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'SEO standardy pro redaktory',
                        'en' => 'SEO Standards for Editors',
                    ],
                    'excerpt' => [
                        'cs' => 'Metodika psaní článků pro dobrou dohledatelnost na internetu.',
                        'en' => 'Methodology for writing articles for good discoverability on the internet.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zvýšení organické návštěvnosti webu.',
                            'audience_summary' => 'Redaktoři a tvůrci obsahu.',
                            'short_intro' => 'Napište článek, který lidé skutečně najdou.',
                        ],
                        'en' => [
                            'purpose' => 'Increasing organic website traffic.',
                            'audience_summary' => 'Editors and content creators.',
                            'short_intro' => 'Write an article that people will actually find.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'system-sezony',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['sezona', 'rok', 'preklopeni', 'inicializace', 'start'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa sezón a překlopení dat',
                        'en' => 'Season Management and Data Rollover',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak připravit systém na nový sportovní rok.',
                        'en' => 'How to prepare the system for a new sports year.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Meziroční správa kontinuity dat.',
                            'audience_summary' => 'Hlavní administrátoři.',
                            'short_intro' => 'Každá sezóna má svůj začátek a konec.',
                        ],
                        'en' => [
                            'purpose' => 'Year-to-year data continuity management.',
                            'audience_summary' => 'Main administrators.',
                            'short_intro' => 'Every season has its beginning and its end.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Spravovat sezóny',
                            'en' => 'Manage seasons',
                        ],
                        'url' => '/admin/seasons',
                        'icon' => 'fa-light fa-calendar-days',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'api-audit',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['audit', 'log', 'historie', 'zmeny', 'kdo', 'kdy'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Historie změn a Audit logy',
                        'en' => 'Change History and Audit Logs',
                    ],
                    'excerpt' => [
                        'cs' => 'Sledování všech důležitých akcí a změn v systému.',
                        'en' => 'Tracking of all important actions and changes in the system.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Dohledatelnost akcí uživatelů.',
                            'audience_summary' => 'Bezpečnostní a hlavní administrátoři.',
                            'short_intro' => 'Vše je zaznamenáno pro vaši kontrolu.',
                        ],
                        'en' => [
                            'purpose' => 'Traceability of user actions.',
                            'audience_summary' => 'Security and main administrators.',
                            'short_intro' => 'Everything is recorded for your check.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Audit logy',
                            'en' => 'Audit logs',
                        ],
                        'url' => '/admin/audit-logs',
                        'icon' => 'fa-light fa-eye',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'scenar-nova-sezona',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['scenar', 'navod', 'sezona', 'zari', 'start', 'checklist'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Checklist: Start nové sezóny',
                        'en' => 'Checklist: Start of a New Season',
                    ],
                    'excerpt' => [
                        'cs' => 'Komplexní průvodce pro spuštění nového sportovního roku.',
                        'en' => 'Comprehensive guide for starting a new sports year.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Metodické vedení při kritické operaci.',
                            'audience_summary' => 'Administrátoři klubu.',
                            'short_intro' => 'Krok za krokem k úspěšnému startu září.',
                        ],
                        'en' => [
                            'purpose' => 'Methodical guidance during a critical operation.',
                            'audience_summary' => 'Club administrators.',
                            'short_intro' => 'Step by step to a successful start in September.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'scenar-nabor',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'coach', 'super_admin'],
                    'search_keywords' => ['nabor', 'novy', 'hrac', 'integrace', 'onboarding'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Scénář: Nábor a integrace nového hráče',
                        'en' => 'Scenario: Recruitment and Integration of a New Player',
                    ],
                    'excerpt' => [
                        'cs' => 'Cesta od prvního zájmu po zařazení do kabiny.',
                        'en' => 'The journey from initial interest to inclusion in the locker room.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění kompletního onboardingu člena.',
                            'audience_summary' => 'Administrátoři a trenéři.',
                            'short_intro' => 'Od leadu k platícímu členovi.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring a complete member onboarding.',
                            'audience_summary' => 'Administrators and coaches.',
                            'short_intro' => 'From a lead to a paying member.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'scenar-odchod',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['odchod', 'konec', 'clenstvi', 'ukonceni', 'vystoupeni'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Scénář: Ukončení členství (Exit process)',
                        'en' => 'Scenario: Termination of Membership (Exit Process)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak správně uzavřít profil odcházejícího člena.',
                        'en' => 'How to correctly close the profile of a departing member.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Korektní ukončení vztahu se členem.',
                            'audience_summary' => 'Administrátoři.',
                            'short_intro' => 'I loučení musí mít svá pravidla.',
                        ],
                        'en' => [
                            'purpose' => 'Correct termination of relationship with a member.',
                            'audience_summary' => 'Administrators.',
                            'short_intro' => 'Even goodbyes must have rules.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'branding-emaily',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['super_admin'],
                    'search_keywords' => ['vzhled', 'barvy', 'branding', 'emaily', 'sablony', 'loga'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Branding a e-mailové šablony',
                        'en' => 'Branding and E-mail Templates',
                    ],
                    'excerpt' => [
                        'cs' => 'Přizpůsobení vizuální identity administrace a komunikace.',
                        'en' => 'Customization of the visual identity of the administration and communication.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Konzistence klubové identity.',
                            'audience_summary' => 'Techničtí správci.',
                            'short_intro' => 'Sokoli musí vypadat skvěle i v e-mailech.',
                        ],
                        'en' => [
                            'purpose' => 'Consistency of club identity.',
                            'audience_summary' => 'Technical administrators.',
                            'short_intro' => 'Sokoli must look great in e-mails as well.',
                        ],
                    ],
                ],
            ],
            [
                'category_slug' => 'sport',
                'data' => [
                    'slug' => 'sprava-tymu',
                    'sort_order' => 10,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['tym', 'soupiska', 'roster', 'logo', 'branding', 'barvy', 'stint', 'hrac'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa týmu a soupisek (Excesivní)',
                        'en' => 'Team and Roster Management (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Kompletní manuál pro vedení týmu, jeho branding a sportovní soupisku.',
                        'en' => 'A complete manual for team leadership, its branding, and sports roster.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění sportovní integrity a vizuální prezentace týmu.',
                            'audience_summary' => 'Trenéři, administrátoři a šéftrenéři.',
                            'short_intro' => 'Tým je základní stavební jednotkou našeho klubu. Zde spravujete jeho duši i tvář.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring sports integrity and visual presentation of the team.',
                            'audience_summary' => 'Coaches, administrators, and head coaches.',
                            'short_intro' => 'A team is the fundamental building block of our club. You manage its soul and face here.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Kde se zobrazuje týmové logo?',
                            'en' => 'Where is the team logo displayed?',
                        ],
                        'answer' => [
                            'cs' => 'Na webu v rozpisu zápasů, na profilu týmu a v členské sekci hráčů daného týmu.',
                            'en' => 'On the website in match schedules, on the team profile, and in the member section of players from that team.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Otevřít seznam týmů',
                            'en' => 'Open team list',
                        ],
                        'url' => '/admin/teams',
                        'icon' => 'fa-light fa-users-gear',
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'evidence-plateb',
                    'sort_order' => 40,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['platba', 'banka', 'import', 'prijem', 'uctovani', 'transakce', 'cash'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Evidence a příjem plateb (Excesivní)',
                        'en' => 'Evidence and Receipt of Payments (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak hromadně importovat výpisy z banky a ručně zadávat platby.',
                        'en' => 'How to bulk import bank statements and manually enter payments.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Správa klubové pokladny a bankovního toku peněz.',
                            'audience_summary' => 'Hospodáři a administrátoři s přístupem k financím.',
                            'short_intro' => 'Peníze jsou krevním oběhem klubu. Mějte o nich dokonalý přehled.',
                        ],
                        'en' => [
                            'purpose' => 'Management of the club treasury and bank cash flow.',
                            'audience_summary' => 'Treasurers and administrators with access to finances.',
                            'short_intro' => 'Money is the lifeblood of the club. Maintain perfect oversight.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak často nahrávat bankovní výpisy?',
                            'en' => 'How often should I upload bank statements?',
                        ],
                        'answer' => [
                            'cs' => 'Doporučujeme aspoň jednou týdně, aby měli členové v profilu aktuální saldo.',
                            'en' => 'We recommend at least once a week so that members have an up-to-date balance in their profile.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Importovat z banky',
                            'en' => 'Import from bank',
                        ],
                        'url' => '/admin/finance-payments',
                        'icon' => 'fa-light fa-file-import',
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'predpisy-plateb',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['predpis', 'dluh', 'tarif', 'generovani', 'členské', 'vs', 'payment'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Předpisy plateb a tarify (Excesivní)',
                        'en' => 'Payment Charges and Tariffs (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak definovat dluhy členů a hromadně generovat variabilní symboly.',
                        'en' => 'How to define member debts and bulk generate variable symbols.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Definice finančních závazků členů vůči klubu.',
                            'audience_summary' => 'Administrátoři, trenéři a hospodáři.',
                            'short_intro' => 'Vystavení předpisu je prvním krokem k tomu, aby hráč mohl zaplatit.',
                        ],
                        'en' => [
                            'purpose' => 'Defining members\' financial obligations to the club.',
                            'audience_summary' => 'Administrators, coaches, and treasurers.',
                            'short_intro' => 'Issuing a charge is the first step for a player to be able to pay.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co je to tarif?',
                            'en' => 'What is a tariff?',
                        ],
                        'answer' => [
                            'cs' => 'Tarif je šablona částky (např. Členský příspěvek), kterou pak hromadně vystavíte mnoha hráčům naráz.',
                            'en' => 'A tariff is an amount template (e.g., Membership Fee) that you then issue in bulk to many players at once.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Spravovat tarify',
                            'en' => 'Manage tariffs',
                        ],
                        'url' => '/admin/financial-tariffs',
                        'icon' => 'fa-light fa-tags',
                    ],
                ],
            ],
            [
                'category_slug' => 'finance',
                'data' => [
                    'slug' => 'parovani-plateb',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['parovani', 'alokace', 'vs', 'variabilni', 'preplatek', 'matching', 'allocation'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Alokace a párování (Excesivní)',
                        'en' => 'Allocation and Matching (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak technicky propojit přijaté peníze s vystavenými dluhy.',
                        'en' => 'How to technically link received money with issued debts.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění správného vyrovnání pohledávek a závazků.',
                            'audience_summary' => 'Hospodáři a hlavní administrátoři.',
                            'short_intro' => 'Alokace je most mezi bankou a evidencí dluhů v KS.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring correct settlement of receivables and payables.',
                            'audience_summary' => 'Treasurers and head administrators.',
                            'short_intro' => 'Allocation is the bridge between the bank and the debt records in KS.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co když hráč pošle více peněz bez VS?',
                            'en' => 'What if a player sends more money without a VS?',
                        ],
                        'answer' => [
                            'cs' => 'Platba zůstane v nespárovaných. Musíte ji ručně přiřadit a zbytek nechat jako nealokovaný přeplatek.',
                            'en' => 'The payment will remain in the unpaired section. You must manually assign it and leave the remainder as an unallocated overpayment.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Ruční alokace',
                            'en' => 'Manual allocation',
                        ],
                        'url' => '/admin/finance-payments',
                        'icon' => 'fa-light fa-link',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'scenar-nova-sezona',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['restart', 'sezona', 'migrace', 'převod', 'archiv', 'obnova', 'season', 'renewal'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Restart sezóny a migrace (Excesivní)',
                        'en' => 'Season Restart and Migration (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Kritický průvodce přechodem klubu do nového soutěžního roku.',
                        'en' => 'A critical guide to the club\'s transition into a new competitive year.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Bezpečný přechod dat a členů do nového období.',
                            'audience_summary' => 'Pouze super-administrátoři a hlavní vedení klubu.',
                            'short_intro' => 'Konec sezóny není konec světa. Je to čas na velký reset a nový začátek.',
                        ],
                        'en' => [
                            'purpose' => 'Secure transition of data and members into a new period.',
                            'audience_summary' => 'Only super-administrators and head club management.',
                            'short_intro' => 'The end of the season is not the end of the world. It is time for a big reset and a new beginning.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Co se stane se starými daty?',
                            'en' => 'What happens to old data?',
                        ],
                        'answer' => [
                            'cs' => 'Nic se nemaže. Stará sezóna se jen archivuje a na webu se stane neaktivní.',
                            'en' => 'Nothing is deleted. The old season is simply archived and becomes inactive on the website.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Otevřít Restart sezóny',
                            'en' => 'Open Season Restart',
                        ],
                        'url' => '/admin/season-renewal',
                        'icon' => 'fa-light fa-arrows-rotate',
                    ],
                ],
            ],
            [
                'category_slug' => 'lide',
                'data' => [
                    'slug' => 'nabory-a-zajemci',
                    'sort_order' => 50,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'coach'],
                    'search_keywords' => ['lead', 'nabor', 'zajemce', 'novacek', 'prihlaska', 'rodic', 'prijmova'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Správa náborů (Leady)',
                        'en' => 'Recruitment Management (Leads)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak zpracovávat nové zájemce z webu a konvertovat je na členy.',
                        'en' => 'How to process new prospects from the web and convert them into members.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Evidence budoucích členů klubu (lead management).',
                            'audience_summary' => 'Náboroví manažeři a administrátoři.',
                            'short_intro' => 'Každý velký hráč začíná jako zájemce. Zde spravujete první kontakt.',
                        ],
                        'en' => [
                            'purpose' => 'Records of future club members (lead management).',
                            'audience_summary' => 'Recruitment managers and administrators.',
                            'short_intro' => 'Every great player starts as a prospect. You manage the first contact here.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Proč tu není tlačítko „Převést na člena“?',
                            'en' => 'Why is there no "Convert to Member" button?',
                        ],
                        'answer' => [
                            'cs' => 'Z důvodu čistoty dat. Profil člena vyžaduje mnohem více údajů než úvodní lead.',
                            'en' => 'For data integrity reasons. A member profile requires much more data than the initial lead.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Otevřít nábory',
                            'en' => 'Open recruitments',
                        ],
                        'url' => '/admin/leads',
                        'icon' => 'fa-light fa-user-plus',
                    ],
                ],
            ],
            [
                'category_slug' => 'obsah',
                'data' => [
                    'slug' => 'sponzori-partneri',
                    'sort_order' => 30,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin', 'editor'],
                    'search_keywords' => ['partner', 'sponzor', 'logo', 'priorita', 'zobrazeni', 'footer', 'homepage'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Partneři a sponzoři (Excesivní)',
                        'en' => 'Partners and Sponsors (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak prezentovat podporovatele klubu na webu a u zápasů.',
                        'en' => 'How to showcase club supporters on the website and at matches.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění viditelnosti partnerů a plnění smluvních závazků.',
                            'audience_summary' => 'Administrátoři a redaktoři webu.',
                            'short_intro' => 'Kluboví partneři jsou klíčem k naší stabilitě. Prezentujte je důstojně.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring partner visibility and fulfillment of contractual obligations.',
                            'audience_summary' => 'Administrators and website editors.',
                            'short_intro' => 'Club partners are the key to our stability. Present them with dignity.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Můžu nastavit partnera utkání?',
                            'en' => 'Can I set a partner for a match?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, zaškrtněte „Zápasové odznaky“ u partnera. Pak se bude moci zobrazit u detailu zápasu.',
                            'en' => 'Yes, check "Match Badges" for the partner. Then they can be displayed in the match details.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Spravovat partnery',
                            'en' => 'Manage partners',
                        ],
                        'url' => '/admin/partners',
                        'icon' => 'fa-light fa-handshake',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'branding-emaily',
                    'sort_order' => 20,
                    'is_published' => true,
                    'is_featured' => true,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['branding', 'nastaveni', 'barvy', 'loga', 'identita', 'banka', 'seo', 'haly', 'změna loga', 'logo klubu', 'favicon'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Branding a nastavení klubu (Excesivní)',
                        'en' => 'Branding and Club Settings (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Centrální konfigurace vizuální identity, bankovních údajů a SEO.',
                        'en' => 'Central configuration of visual identity, bank details, and SEO.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Jednotná správa značky a globálních technických údajů.',
                            'audience_summary' => 'Vedení klubu a hlavní administrátoři.',
                            'short_intro' => 'Identita KS je definována zde. Každá změna se projeví napříč celým systémem.',
                        ],
                        'en' => [
                            'purpose' => 'Unified management of the brand and global technical data.',
                            'audience_summary' => 'Club management and head administrators.',
                            'short_intro' => 'The KS identity is defined here. Every change is reflected across the entire system.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Jak změním číslo účtu pro příspěvky?',
                            'en' => 'How do I change the account number for fees?',
                        ],
                        'answer' => [
                            'cs' => 'Přímo zde v záložce Ekonomické údaje. Nový účet se okamžitě propíše členům.',
                            'en' => 'Right here in the Financial Data tab. The new account will be immediately visible to members.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Nastavení brandingu',
                            'en' => 'Branding settings',
                        ],
                        'url' => '/admin/branding-settings',
                        'icon' => 'fa-light fa-palette',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'api-audit',
                    'sort_order' => 60,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['api', 'cbf', 'sync', 'parovani', 'statistiky', 'data', 'mismatch', 'mapping'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Externí data a párování (Excesivní)',
                        'en' => 'External Data and Mapping (Excessive)',
                    ],
                    'excerpt' => [
                        'cs' => 'Jak propojit KS se světem (ČBF) a spravovat externí statistiky.',
                        'en' => 'How to link KS with the world (ČBF) and manage external statistics.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění toku automatizovaných sportovních dat.',
                            'audience_summary' => 'Techničtí administrátoři a šéftrenéři.',
                            'short_intro' => 'Statistiky z cz.basketball se k nám dostávají přes tento digitální most.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring the flow of automated sports data.',
                            'audience_summary' => 'Technical administrators and head coaches.',
                            'short_intro' => 'Statistics from cz.basketball reach us across this digital bridge.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Hráči se nenačítají body z víkendu?',
                            'en' => 'Player\'s points from the weekend aren\'t loading?',
                        ],
                        'answer' => [
                            'cs' => 'Zkontrolujte, zda má v „Párování entit“ správně nastaveno external_id z ČBF.',
                            'en' => 'Check if their external_id from ČBF is correctly set in "Entity Mappings."',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Párování entit',
                            'en' => 'Entity mapping',
                        ],
                        'url' => '/admin/external-entity-mappings',
                        'icon' => 'fa-light fa-code-merge',
                    ],
                ],
            ],
            [
                'category_slug' => 'system',
                'data' => [
                    'slug' => 'planovane-ulohy-cron',
                    'sort_order' => 100,
                    'is_published' => true,
                    'is_featured' => false,
                    'audience_roles' => ['admin', 'super_admin'],
                    'search_keywords' => ['cron', 'ulohy', 'planovani', 'sync', 'logy', 'run', 'automatizace'],
                ],
                'translations' => [
                    'title' => [
                        'cs' => 'Plánované úlohy (Cron)',
                        'en' => 'Scheduled Tasks (Cron)',
                    ],
                    'excerpt' => [
                        'cs' => 'Dohlížení na automatické procesy systému a jejich ruční spouštění.',
                        'en' => 'Monitoring automated system processes and running them manually.',
                    ],
                    'metadata' => [
                        'cs' => [
                            'purpose' => 'Zajištění běhu periodických operací (sync plateb, dat).',
                            'audience_summary' => 'Hlavní administrátoři a technická správa.',
                            'short_intro' => 'Zde vidíte, zda se všechny automatické synchronizace točí tak, jak mají.',
                        ],
                        'en' => [
                            'purpose' => 'Ensuring the execution of periodic operations (payment sync, data sync).',
                            'audience_summary' => 'Head administrators and technical management.',
                            'short_intro' => 'Here you can see if all automatic synchronizations are running as they should.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => [
                            'cs' => 'Můžu spustit synchronizaci hned?',
                            'en' => 'Can I run a synchronization right now?',
                        ],
                        'answer' => [
                            'cs' => 'Ano, použijte akci „Spustit nyní (Run Now)“ v seznamu úloh.',
                            'en' => 'Yes, use the "Run Now" action in the task list.',
                        ],
                    ],
                ],
                'quick_actions' => [
                    [
                        'label' => [
                            'cs' => 'Seznam úloh',
                            'en' => 'Task list',
                        ],
                        'url' => '/admin/cron-tasks',
                        'icon' => 'fa-light fa-clock-rotate-left',
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
                } else {
                    // Fallback pokud soubor neexistuje
                    $translations['content'][$locale] = "Content for article `{$article['data']['slug']}` ({$locale}) is not yet available.";
                }

                // Automatické doplnění sekce do metadat (pro filtrování v HelpQueryService)
                $section = in_array($article['category_slug'], ['uvod', 'clenska-sekce']) ? 'both' : 'admin';
                $translations['metadata'][$locale]['section'] = $section;
            }

            // Upsert článku
            $helpArticle = $this->upsertHelpItem(HelpArticle::class, $articleData, $translations, true);

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
