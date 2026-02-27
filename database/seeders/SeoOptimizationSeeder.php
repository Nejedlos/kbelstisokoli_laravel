<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Team;
use Illuminate\Database\Seeder;

class SeoOptimizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPageSeo();
        $this->seedTeamSeo();
    }

    protected function seedPageSeo(): void
    {
        $pages = [
            'home' => [
                'title' => [
                    'cs' => 'Basketbal Letňany – Týmy Muži C a E',
                    'en' => 'Basketball Letnany – Teams Men C and E',
                ],
                'description' => [
                    'cs' => 'Oficiální web basketbalových týmů TJ Sokol Kbely C a E. Hrajeme v Letňanech (RumcajsArena). Přidejte se k naší basketbalové komunitě v Praze 9.',
                    'en' => 'Official website of TJ Sokol Kbely C and E basketball teams. We play in Letňany (RumcajsArena). Join our basketball community in Prague 9.',
                ],
                'keywords' => [
                    'cs' => 'basketbal Letňany, Sokol Kbely basketbal, muži C, muži E, basketbal Praha 9, RumcajsArena, Pražský přebor basketbal, basketbalová komunita',
                    'en' => 'basketball Letnany, Sokol Kbely basketball, men C, men E, basketball Prague 9, RumcajsArena, Prague basketball league, basketball community',
                ],
            ],
            'o-klubu' => [
                'title' => [
                    'cs' => 'O nás – Basketbal v Letňanech',
                    'en' => 'About Us – Basketball in Letnany',
                ],
                'description' => [
                    'cs' => 'Poznejte historii a vizi našich mužských týmů. C a E jsou srdcem kbelstského basketbalu se základnou v Letňanech.',
                    'en' => 'Discover the history and vision of our men\'s teams. C and E are the heart of Kbely basketball based in Letňany.',
                ],
                'keywords' => [
                    'cs' => 'historie Sokol Kbely, basketbalový klub Praha, TJ Sokol Kbely, basketbal Letňany, mužský basketbal',
                    'en' => 'Sokol Kbely history, basketball club Prague, TJ Sokol Kbely, basketball Letnany, men\'s basketball',
                ],
            ],
            'nabor' => [
                'title' => [
                    'cs' => 'Nábor hráčů – Přidej se k Sokolům C nebo E',
                    'en' => 'Join the Kbely Falcons – Recruitment C or E',
                ],
                'description' => [
                    'cs' => 'Hledáme nové posily do našich týmů! Pokud hraješ basket a hledáš dobrou partu v Praze 9, přijď na trénink do RumcajsAreny.',
                    'en' => 'We are looking for new players! If you play basketball and seek a great team in Prague 9, join our practice at RumcajsArena.',
                ],
                'keywords' => [
                    'cs' => 'basketbalový nábor Praha, nábor muži basketbal, hrát basketbal Praha 9, basketbalový tým nábor, Sokol Kbely nábor',
                    'en' => 'basketball recruitment Prague, men\'s basketball recruitment, play basketball Prague 9, basketball team recruitment',
                ],
            ],
            'treninky' => [
                'title' => [
                    'cs' => 'Tréninky v RumcajsAreně Letňany',
                    'en' => 'Practices at RumcajsArena Letnany',
                ],
                'description' => [
                    'cs' => 'Kdy a kde trénujeme? Kompletní rozpis tréninkových hodin pro týmy C a E v naší hale v Letňanech (Třinecká 650).',
                    'en' => 'When and where do we practice? Complete schedule for teams C and E at our gym in Letňany (Třinecká 650).',
                ],
                'keywords' => [
                    'cs' => 'tréninky basketbalu Praha, RumcajsArena rozpis, basketbal Letňany trénink, Třinecká 650 basketbal',
                    'en' => 'basketball practices Prague, RumcajsArena schedule, basketball Letnany practice',
                ],
            ],
            'zapasy' => [
                'title' => [
                    'cs' => 'Zápasy a výsledky – Sledujte nás v akci',
                    'en' => 'Matches and Results – Watch Us in Action',
                ],
                'description' => [
                    'cs' => 'Výsledky, tabulky a nadcházející zápasy týmů Sokol Kbely C v Pražském přeboru a týmu E ve 3. třídě.',
                    'en' => 'Results, standings, and upcoming matches for Sokol Kbely C in the Prague Championship and team E in the 3rd class.',
                ],
                'keywords' => [
                    'cs' => 'výsledky basketbalu Praha, Pražský přebor basketbal tabulka, zápasy Sokol Kbely, basketbalové utkání Praha',
                    'en' => 'Prague basketball results, Prague basketball league standings, Kbely matches',
                ],
            ],
            'tymy' => [
                'title' => [
                    'cs' => 'Naše týmy – Muži C a Muži E',
                    'en' => 'Our Teams – Men C and Men E',
                ],
                'description' => [
                    'cs' => 'Soupisky, profily hráčů a trenérů našich hlavních týmů. Poznejte sestavu Mužů C a Mužů E.',
                    'en' => 'Rosters, player and coach profiles of our main teams. Meet the Men C and Men E squads.',
                ],
                'keywords' => [
                    'cs' => 'soupiska basketbal, basketbalový tým muži, Sokol Kbely soupiska, basketbalisti Praha 9',
                    'en' => 'basketball roster, men\'s basketball team, Kbely roster, basketball players Prague 9',
                ],
            ],
            'kontakt' => [
                'title' => [
                    'cs' => 'Kontakt na vedení týmů C a E',
                    'en' => 'Contact Team Leaders C and E',
                ],
                'description' => [
                    'cs' => 'Máte dotaz k náboru nebo přátelskému utkání? Kontaktujte vedení našich letňanských týmů přímo přes web.',
                    'en' => 'Have a question about recruitment or a friendly match? Contact our Letňany teams leadership directly via the web.',
                ],
                'keywords' => [
                    'cs' => 'kontakt basketbal Kbely, Tomáš Spanilý basketbal, RumcajsArena kontakt, basketbal Praha 9 kontakt',
                    'en' => 'Kbely basketball contact, RumcajsArena contact, basketball Prague 9 contact',
                ],
            ],
            'historie' => [
                'title' => [
                    'cs' => 'Historie Sokolů – Naše cesta od roku 2004',
                    'en' => 'History – Our Journey Since 2004',
                ],
                'description' => [
                    'cs' => 'Jak vznikly týmy C a E? Projděte si milníky naší basketbalové party a naše úspěchy v pražských soutěžích.',
                    'en' => 'How did teams C and E start? Explore the milestones of our basketball group and our successes in Prague competitions.',
                ],
                'keywords' => [
                    'cs' => 'historie basketbalu Kbely, basketbalová historie Praha, milníky Sokol Kbely',
                    'en' => 'Kbely basketball history, Prague basketball history, Kbely milestones',
                ],
            ],
            'gdpr' => [
                'title' => [
                    'cs' => 'Ochrana soukromí (GDPR) | Kbelští sokoli',
                    'en' => 'Privacy Policy (GDPR) | Kbely Falcons',
                ],
                'description' => [
                    'cs' => 'Jak nakládáme s vašimi údaji v rámci našich týmů. Hrajeme fair-play na palubovce i v soukromí.',
                    'en' => 'How we handle your data within our teams. We play fair on the court and in privacy.',
                ],
                'keywords' => [
                    'cs' => 'GDPR basketbal, ochrana údajů Sokol Kbely, soukromí sportovní klub',
                    'en' => 'GDPR basketball, data protection Kbely, sports club privacy',
                ],
            ],
            'hledat' => [
                'title' => [
                    'cs' => 'Vyhledávání | Kbelští sokoli',
                    'en' => 'Search | Kbely Falcons',
                ],
                'description' => [
                    'cs' => 'Najděte informace o zápasech, hráčích nebo novinkách z našich basketbalových týmů C a E.',
                    'en' => 'Find information about matches, players, or news from our basketball teams C and E.',
                ],
                'keywords' => [
                    'cs' => 'hledat basketbal, vyhledávání Sokol Kbely',
                    'en' => 'search basketball, Kbely search',
                ],
            ],
            'join' => [
                'title' => [
                    'cs' => 'Chci hrát! – Přihláška do týmu Sokol Kbely',
                    'en' => 'Join Us! – Team Application Form',
                ],
                'description' => [
                    'cs' => 'Vyplň krátký dotazník a my se ti ozveme. Nábor do týmů C a E probíhá celoročně v Letňanech.',
                    'en' => 'Fill out a short questionnaire and we\'ll get back to you. Recruitment for teams C and E is open year-round in Letňany.',
                ],
                'keywords' => [
                    'cs' => 'přihláška basketbal, náborový formulář basketbal, registrace hráče Praha',
                    'en' => 'basketball application, basketball recruitment form, player registration Prague',
                ],
            ],
        ];

        foreach ($pages as $slug => $seoData) {
            $page = Page::where('slug', $slug)->first();
            if ($page) {
                $page->seo()->updateOrCreate(
                    ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                    $seoData
                );
            }
        }
    }

    protected function seedTeamSeo(): void
    {
        $teams = [
            'muzi-c' => [
                'title' => [
                    'cs' => 'Muži C – Pražský přebor B Letňany',
                    'en' => 'Men C – Prague Championship B Letnany',
                ],
                'description' => [
                    'cs' => 'Tým Muži C hraje Pražský přebor B. Domácí zápasy v Letňanech. Sledujte soupisku, tabulku a rozpis zápasů "céčka".',
                    'en' => 'The Men C team competes in the Prague Championship B. Home games in Letňany. Follow the "C" roster, standings, and schedule.',
                ],
                'keywords' => [
                    'cs' => 'muži C basketbal, Sokol Kbely C, Pražský přebor B, basketbal Letňany muži',
                    'en' => 'men C basketball, Sokol Kbely C, Prague Championship B, basketball Letnany men',
                ],
            ],
            'muzi-e' => [
                'title' => [
                    'cs' => 'Muži E – 3. třída B Letňany',
                    'en' => 'Men E – 3rd Class B Letnany',
                ],
                'description' => [
                    'cs' => 'Tým Muži E hraje 3. třídu B v RumcajsAreně. Naše "éčko" je skvělá parta pro ty, co milují soutěžní basketbal.',
                    'en' => 'The Men E team plays in the 3rd Class B at RumcajsArena. Our "E" team is a great group for those who love competitive basketball.',
                ],
                'keywords' => [
                    'cs' => 'muži E basketbal, Sokol Kbely E, 3. třída B basketbal, RumcajsArena basketbal',
                    'en' => 'men E basketball, Sokol Kbely E, 3rd class B basketball, RumcajsArena basketball',
                ],
            ],
        ];

        foreach ($teams as $slug => $seoData) {
            $team = Team::where('slug', $slug)->first();
            if ($team) {
                $team->seo()->updateOrCreate(
                    ['seoable_id' => $team->id, 'seoable_type' => Team::class],
                    $seoData
                );
            }
        }
    }
}
