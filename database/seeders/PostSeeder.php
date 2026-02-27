<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vyčistíme stávající novinky (jen v rámci seederu, abychom neměli duplicity)
        Post::truncate();

        // Kategorie novinek
        $generalCategory = PostCategory::updateOrCreate(
            ['slug' => 'obecne'],
            [
                'name' => ['cs' => 'Obecné', 'en' => 'General'],
            ]
        );

        $devCategory = PostCategory::updateOrCreate(
            ['slug' => 'vyvoj'],
            [
                'name' => ['cs' => 'Vývoj webu', 'en' => 'Web Development'],
            ]
        );

        $newsData = [
            [
                'date' => '2026-01-06',
                'title' => [
                    'cs' => 'Start projektu: nový web pro Kbelští sokoli (Muži C & E)',
                    'en' => 'Project Start: New Website for Kbely Falcons (Men C & E)',
                ],
                'excerpt' => [
                    'cs' => 'Po novém roce jsme odstartovali plánování a definovali cíl: moderní web pro naše týmy Muži C a Muži E.',
                    'en' => 'After the New Year, we started planning and defined the goal: a modern website for our Men C and Men E teams.',
                ],
                'content' => [
                    'cs' => '<p>Po novém roce jsme odstartovali plánování a definovali cíl: moderní web pro naše týmy Muži C a Muži E, s návazností na širší kbelský basket. Naším záměrem je vytvořit digitální prostor, který bude plně reflektovat potřeby našich hráčů i fanoušků v Letňanech. První fáze zahrnuje definici technologií (Laravel 12, Filament PHP 5) a sběr požadavků na funkčnost, jako je správa týmů a členská sekce.</p>',
                    'en' => '<p>After the New Year, we started planning and defined the goal: a modern website for our Men C and Men E teams, with connections to the wider Kbely basketball community. Our intention is to create a digital space that will fully reflect the needs of our players and fans in Letňany. The first phase includes defining the technologies (Laravel 12, Filament PHP 5) and gathering functional requirements, such as team management and a member section.</p>',
                ],
            ],
            [
                'date' => '2026-01-09',
                'title' => [
                    'cs' => 'Informační architektura a mapa stránek',
                    'en' => 'Information Architecture and Sitemap',
                ],
                'excerpt' => [
                    'cs' => 'Rozkreslili jsme základní strukturu webu a připravili logiku pro pozdější napojení dynamických dat.',
                    'en' => 'We have outlined the basic structure of the website and prepared the logic for later connection of dynamic data.',
                ],
                'content' => [
                    'cs' => '<p>Rozkreslili jsme základní strukturu webu (Úvod, Novinky, Zápasy, Tým, Tréninky, Historie, Kontakt) a připravili logiku pro pozdější napojení dynamických dat. Pečlivě jsme promysleli uživatelskou cestu, aby návštěvník našel nejdůležitější informace (jako je čas příštího zápasu nebo tréninku) na jedno kliknutí. Součástí této fáze byla i definice databázového schématu pro moduly zápasů a soupisek.</p>',
                    'en' => '<p>We have outlined the basic structure of the website (Home, News, Matches, Team, Trainings, History, Contact) and prepared the logic for later connection of dynamic data. We carefully thought through the user journey so that a visitor can find the most important information (such as the time of the next match or practice) in one click. This phase also included defining the database schema for the match modules and rosters.</p>',
                ],
            ],
            [
                'date' => '2026-01-13',
                'title' => [
                    'cs' => 'Branding a barevná paleta',
                    'en' => 'Branding and Color Palette',
                ],
                'excerpt' => [
                    'cs' => 'Vybrali jsme moderní sportovní styl: navy + elektrická modrá + klubová červená.',
                    'en' => 'We chose a modern sports style: navy + electric blue + club red.',
                ],
                'content' => [
                    'cs' => '<p>Vybrali jsme moderní sportovní styl: navy + elektrická modrá + klubová červená, doplněné čistými neutrály pro maximální čitelnost. Barvy vycházejí z tradiční identity Sokolů, ale jsou upraveny pro digitální věk s důrazem na vysoký kontrast a dynamiku. Tato paleta se stane základem pro veškeré vizuály webu i sociálních sítí, čímž zajistíme konzistentní vystupování našich letňanských týmů.</p>',
                    'en' => '<p>We chose a modern sports style: navy + electric blue + club red, complemented by clean neutrals for maximum readability. The colors are based on the traditional Falcons identity but are adjusted for the digital age with an emphasis on high contrast and dynamics. This palette will become the basis for all website visuals and social media, ensuring a consistent appearance for our Letňany teams.</p>',
                ],
            ],
            [
                'date' => '2026-01-16',
                'title' => [
                    'cs' => 'Design systém a UI standardy',
                    'en' => 'Design System and UI Standards',
                ],
                'excerpt' => [
                    'cs' => 'Nastavili jsme jednotný vzhled tlačítek, karet, typografie a rozestupů pro konzistentní zážitek.',
                    'en' => 'We set a uniform look for buttons, cards, typography, and spacing for a consistent experience.',
                ],
                'content' => [
                    'cs' => '<p>Nastavili jsme jednotný vzhled tlačítek, karet, typografie a rozestupů, aby web působil konzistentně na všech stránkách i blocích. Používáme moderní bezpatkové písmo, které je dobře čitelné i na mobilních zařízeních během rychlého sledování výsledků na cestě ze zápasu. Design systém obsahuje i definice pro stavy prvků, jako jsou loadiery a hover efekty, což zvyšuje profesionální dojem z celé aplikace.</p>',
                    'en' => '<p>We set a uniform look for buttons, cards, typography, and spacing so the website looks consistent across all pages and blocks. We use a modern sans-serif font that is easy to read even on mobile devices while quickly checking results on the way from a game. The design system also includes definitions for element states such as loaders and hover effects, which increases the professional impression of the entire application.</p>',
                ],
            ],
            [
                'date' => '2026-01-20',
                'title' => [
                    'cs' => 'Page Builder: skládání stránek z bloků',
                    'en' => 'Page Builder: Building Pages from Blocks',
                ],
                'excerpt' => [
                    'cs' => 'Spustili jsme blokový systém, díky kterému lze stránky skládat bez programování.',
                    'en' => 'We launched a block system that allows pages to be assembled without programming.',
                ],
                'content' => [
                    'cs' => '<p>Spustili jsme blokový systém, díky kterému lze stránky skládat bez programování – jednoduše a bezpečně i pro laiky v administraci. Redaktoři nyní mohou využívat předpřipravené komponenty jako Hero sekce, Výzvy k akci (CTA) nebo Galerie, a vytvářet tak vizuálně atraktivní podstránky během několika minut. Tento modulární přístup nám umožní rychle reagovat na potřeby klubu a flexibilně rozšiřovat obsah webu.</p>',
                    'en' => '<p>We launched a block system that allows pages to be assembled without programming – simply and safely even for laypeople in the administration. Editors can now use pre-prepared components such as Hero sections, Calls to Action (CTA), or Galleries, creating visually attractive subpages in minutes. This modular approach will allow us to react quickly to the club\'s needs and flexibly expand the website\'s content.</p>',
                ],
            ],
            [
                'date' => '2026-01-24',
                'title' => [
                    'cs' => 'Admin sekce: základní správa obsahu a modulů',
                    'en' => 'Admin Section: Basic Content and Module Management',
                ],
                'excerpt' => [
                    'cs' => 'Připravili jsme administrační část pro správu stránek a menu pro dlouhodobou udržitelnost.',
                    'en' => 'We prepared the administrative part for managing pages and menus for long-term sustainability.',
                ],
                'content' => [
                    'cs' => '<p>Připravili jsme administrační část pro správu stránek, menu a základních modulů, aby šel web dlouhodobě udržovat bez zásahů do kódu. Využili jsme sílu frameworku Filament PHP k vytvoření přehledného rozhraní, které zahrnuje i auditní logy změn. Správci tak mají plnou kontrolu nad strukturou navigace, nahráváním dokumentů i publikací aktualit, což výrazně šetří čas při běžné údržbě webu.</p>',
                    'en' => '<p>We prepared the administrative part for managing pages, menus, and basic modules so the website can be maintained long-term without code intervention. We used the power of the Filament PHP framework to create a clear interface that also includes audit logs of changes. Administrators thus have full control over the navigation structure, uploading documents, and publishing news, which significantly saves time during routine website maintenance.</p>',
                ],
            ],
            [
                'date' => '2026-01-28',
                'title' => [
                    'cs' => 'CZ/EN: příprava na bilingvní web',
                    'en' => 'CZ/EN: Preparing for a Bilingual Website',
                ],
                'excerpt' => [
                    'cs' => 'Zavedli jsme podporu češtiny a angličtiny pro správu textů a obsahových bloků v obou jazycích.',
                    'en' => 'We introduced support for Czech and English for managing texts and content blocks in both languages.',
                ],
                'content' => [
                    'cs' => '<p>Zavedli jsme podporu češtiny a angličtiny tak, aby šly spravovat texty a obsahové bloky v obou jazycích. Vzhledem k mezinárodnímu charakteru basketbalové komunity v Praze považujeme dvojjazyčnost za klíčovou. Systém nyní automaticky detekuje preferovaný jazyk prohlížeče a nabízí uživateli možnost přepnutí v navigaci. Lokalizace se týká nejen statických textů, ale i dynamického obsahu v databázi díky balíčku laravel-translatable.</p>',
                    'en' => '<p>We introduced support for Czech and English so that texts and content blocks can be managed in both languages. Given the international nature of the basketball community in Prague, we consider bilingualism to be key. The system now automatically detects the preferred browser language and offers the user the option to switch in the navigation. Localization applies not only to static texts but also to dynamic content in the database thanks to the laravel-translatable package.</p>',
                ],
            ],
            [
                'date' => '2026-02-02',
                'title' => [
                    'cs' => 'Členská sekce: přihlášení a přístupové role',
                    'en' => 'Member Section: Login and Access Roles',
                ],
                'excerpt' => [
                    'cs' => 'Rozběhli jsme chráněnou část pro hráče a trenéry s přesným nastavením oprávnění.',
                    'en' => 'We launched a protected section for players and coaches with precise permission settings.',
                ],
                'content' => [
                    'cs' => '<p>Rozběhli jsme chráněnou část pro hráče a trenéry – tak, aby každý viděl přesně to, co potřebuje, a nic navíc. Členská sekce využívá bezpečné autentizační mechanismy a umožňuje hráčům přístup k interním informacím, ke kterým se běžný návštěvník nedostane. Trenéři mají k dispozici rozšířené nástroje pro správu týmu, zatímco členové mohou spravovat svůj profil a sledovat svou historii v klubu.</p>',
                    'en' => '<p>We launched a protected section for players and coaches – so that everyone sees exactly what they need and nothing more. The member section uses secure authentication mechanisms and allows players access to internal information that a regular visitor cannot reach. Coaches have extended team management tools at their disposal, while members can manage their profiles and track their history in the club.</p>',
                ],
            ],
            [
                'date' => '2026-02-06',
                'title' => [
                    'cs' => 'Docházka a RSVP: účast na akce',
                    'en' => 'Attendance and RSVP: Event Participation',
                ],
                'excerpt' => [
                    'cs' => 'Přidali jsme systém potvrzování účasti na tréninky, zápasy i klubové akce.',
                    'en' => 'We added a system for confirming attendance at practices, matches, and club events.',
                ],
                'content' => [
                    'cs' => '<p>Přidali jsme systém potvrzování účasti (confirmed/declined/pending/maybe), který se dá později napojit na reálné kalendáře a eventy. Hráči nyní mohou snadno označit svou přítomnost na nadcházejícím tréninku nebo zápasu přímo ze svého mobilu. Trenéři díky tomu získávají okamžitý přehled o soupisce pro daný den, což výrazně usnadňuje plánování tréninkových jednotek a logistiku zápasů v Pražském přeboru.</p>',
                    'en' => '<p>We added an attendance confirmation system (confirmed/declined/pending/maybe), which can later be connected to real calendars and events. Players can now easily mark their presence at an upcoming practice or match directly from their mobile. Thanks to this, coaches get an immediate overview of the roster for the given day, which significantly simplifies the planning of training sessions and match logistics in the Prague Championship.</p>',
                ],
            ],
            [
                'date' => '2026-02-10',
                'title' => [
                    'cs' => 'Statistiky: struktura pro tabulky a budoucí importy',
                    'en' => 'Statistics: Structure for Tables and Future Imports',
                ],
                'excerpt' => [
                    'cs' => 'Připravili jsme základ pro týmové i hráčské statistiky a soutěžní tabulky.',
                    'en' => 'We prepared the basis for team and player statistics and competition tables.',
                ],
                'content' => [
                    'cs' => '<p>Připravili jsme základ pro týmové i hráčské statistiky a soutěžní tabulky – ruční zadávání i budoucí automatický import. Datový model je navržen tak, aby dokázal pojmout širokou škálu metrik od bodů a doskoků až po procentuální úspěšnost střelby. Tyto informace budou klíčové pro budování historické paměti klubu a pro motivaci hráčů v rámci týmů C i E, kteří budou moci sledovat své osobní rekordy napříč sezónami.</p>',
                    'en' => '<p>We prepared the basis for team and player statistics and competition tables – manual entry and future automatic import. The data model is designed to accommodate a wide range of metrics from points and rebounds to shooting percentage success. This information will be key to building the club\'s historical memory and for motivating players within teams C and E, who will be able to track their personal records across seasons.</p>',
                ],
            ],
            [
                'date' => '2026-02-14',
                'title' => [
                    'cs' => 'Média a galerie: základ pro fotky a dokumenty',
                    'en' => 'Media and Gallery: Basis for Photos and Documents',
                ],
                'excerpt' => [
                    'cs' => 'Zavedli jsme centrální správu obrázků a galerií pro jednotný vizuální standard.',
                    'en' => 'We introduced central management of images and galleries for a uniform visual standard.',
                ],
                'content' => [
                    'cs' => '<p>Zavedli jsme centrální správu obrázků a galerií, aby šly používat napříč bloky a stránkami a web měl pořád stejný vizuální standard. Systém automaticky generuje náhledy v různých velikostech, což zajišťuje rychlé načítání stránek bez ztráty kvality. Správci mohou snadno nahrávat fotografie ze zápasů v Letňanech a třídit je do alb, která jsou následně prezentována fanouškům v moderním gridovém rozložení s funkcí lightboxu.</p>',
                    'en' => '<p>We introduced central management of images and galleries so they can be used across blocks and pages while maintaining the same visual standard. The system automatically generates thumbnails in different sizes, ensuring fast page loading without loss of quality. Administrators can easily upload photos from matches in Letňany and sort them into albums, which are then presented to fans in a modern grid layout with lightbox functionality.</p>',
                ],
            ],
            [
                'date' => '2026-02-18',
                'title' => [
                    'cs' => 'SEO vrstva: metadata, OpenGraph a strukturovaná data',
                    'en' => 'SEO Layer: Metadata, OpenGraph, and Structured Data',
                ],
                'excerpt' => [
                    'cs' => 'Nastavili jsme SEO tagy tak, aby byl web dobře sdílitelný a připravený pro vyhledávače.',
                    'en' => 'We set SEO tags so that the website is easily shareable and prepared for search engines.',
                ],
                'content' => [
                    'cs' => '<p>Nastavili jsme title/description, OG tagy a základní strukturovaná data tak, aby web byl dobře sdílitelný a připravený pro vyhledávače. Každá stránka i novinka má nyní unikátní metadata, která pomáhají robotům Google a Seznamu správně indexovat náš obsah. OpenGraph tagy pak zajišťují, že při sdílení odkazu na Facebooku nebo X se zobrazí atraktivní náhledový obrázek s jasným titulkem, což zvyšuje proklik na web z externích zdrojů.</p>',
                    'en' => '<p>We set title/description, OG tags, and basic structured data so that the website is easily shareable and prepared for search engines. Every page and news item now has unique metadata that helps Google and Seznam robots correctly index our content. OpenGraph tags then ensure that when a link is shared on Facebook or X, an attractive preview image with a clear title is displayed, increasing click-through rates from external sources.</p>',
                ],
            ],
            [
                'date' => '2026-02-22',
                'title' => [
                    'cs' => 'Formuláře: kontakt a nábor (lead workflow)',
                    'en' => 'Forms: Contact and Recruitment (Lead Workflow)',
                ],
                'excerpt' => [
                    'cs' => 'Přidali jsme veřejné formuláře a admin přehled pro zpracování zpráv od zájemců.',
                    'en' => 'We added public forms and an admin overview for processing messages from interested parties.',
                ],
                'content' => [
                    'cs' => '<p>Přidali jsme veřejné formuláře a admin přehled pro zpracování zpráv, včetně ochrany proti spamu a připravených notifikací. Nový náborový formulář umožňuje zájemcům o hru v týmech C nebo E zaslat své údaje přímo vedení týmu. Všechny požadavky jsou ukládány do centrální databáze, kde je trenéři mohou spravovat, měnit jejich stav a odpovídat na ně, čímž zajišťujeme, že žádný potenciální talent nezapadne v e-mailové schránce.</p>',
                    'en' => '<p>We added public forms and an admin overview for processing messages, including spam protection and prepared notifications. The new recruitment form allows those interested in playing for teams C or E to send their data directly to the team leadership. All requests are stored in a central database where coaches can manage them, change their status, and reply, ensuring no potential talent gets lost in an inbox.</p>',
                ],
            ],
            [
                'date' => '2026-02-26',
                'title' => [
                    'cs' => 'Testy a provozní stabilita: ověření rolí a přístupů',
                    'en' => 'Tests and Operational Stability: Verifying Roles and Access',
                ],
                'excerpt' => [
                    'cs' => 'Prošli jsme klíčové scénáře a doladili redirecty pro spolehlivý provoz.',
                    'en' => 'We went through key scenarios and fine-tuned redirects for reliable operation.',
                ],
                'content' => [
                    'cs' => '<p>Prošli jsme klíčové scénáře (guest/member/admin), doladili redirecty a připravili kontrolní kroky pro spolehlivý provoz. V rámci testování jsme se zaměřili na bezpečnostní aspekty členské sekce a ověřili, že všechny formuláře správně ukládají data i při nekorektním vyplnění. Tato fáze "vychytávání much" je nezbytná pro zajištění bezproblémového spuštění a dobrého prvního dojmu u všech uživatelů webu.</p>',
                    'en' => '<p>We went through key scenarios (guest/member/admin), fine-tuned redirects, and prepared control steps for reliable operation. During testing, we focused on the security aspects of the member section and verified that all forms correctly save data even when incorrectly filled. This "bug-hunting" phase is essential to ensure a smooth launch and a good first impression for all website users.</p>',
                ],
            ],
            [
                'date' => '2026-03-03',
                'title' => [
                    'cs' => 'Dokončení první verze: moderní homepage a připravený obsah',
                    'en' => 'Completion of Version 1: Modern Homepage and Ready Content',
                ],
                'excerpt' => [
                    'cs' => 'Uzavřeli jsme první veřejnou verzi webu s moderní úvodní stránkou zaměřenou na Muži C & E.',
                    'en' => 'We have closed the first public version of the website with a modern homepage focused on Men C & E.',
                ],
                'content' => [
                    'cs' => '<p>Uzavřeli jsme první veřejnou verzi webu s moderní úvodní stránkou, jasným zaměřením na Muži C & E a přímým propojením na hlavní kbelský basket. Web je nyní plně funkční a připraven k ostrému provozu. Návštěvníci zde najdou vše od historie klubu až po aktuální kontakty. Tímto milníkem sice končí hlavní vývojová fáze, ale náš digitální domov budeme i nadále rozvíjet o nové funkce a čerstvý obsah ze života Sokolů v Letňanech.</p>',
                    'en' => '<p>We have closed the first public version of the website with a modern homepage, a clear focus on Men C & E, and a direct connection to the main Kbely basketball. The website is now fully functional and ready for live operation. Visitors will find everything from the club\'s history to current contacts. While this milestone ends the main development phase, we will continue to develop our digital home with new features and fresh content from the life of the Falcons in Letňany.</p>',
                ],
            ],
        ];

        foreach ($newsData as $item) {
            $publishDate = \Illuminate\Support\Carbon::parse($item['date'])->setHour(10)->setMinute(0);

            Post::updateOrCreate(
                ['slug' => Str::slug($item['title']['cs'])],
                [
                    'category_id' => $devCategory->id,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'status' => 'published',
                    'is_visible' => true,
                    'publish_at' => $publishDate,
                    'created_at' => $publishDate,
                ]
            );
        }
    }
}
