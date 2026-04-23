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

        $matchCategory = PostCategory::updateOrCreate(
            ['slug' => 'zápasy'],
            [
                'name' => ['cs' => 'Zápasy', 'en' => 'Matches'],
            ]
        );

        $newsData = [
            [
                'date' => '2026-04-22',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'A-tým ovládl derby proti Čakovicím!',
                    'en' => 'A-team dominated the derby against Čakovice!',
                ],
                'excerpt' => [
                    'cs' => 'V zaplněné kbelské hale naši muži nedali soupeři šanci a zvítězili přesvědčivě 84:62.',
                    'en' => 'In a packed Kbely arena, our men left no chance for the opponent and won convincingly 84:62.',
                ],
                'content' => [
                    'cs' => '<p>Kbelská sportovní hala zažila bouřlivou atmosféru. V tradičním derby proti Čakovicím předvedli naši muži koncentrovaný výkon od první minuty. Skvělá obrana a rychlé protiútoky dělaly soupeři značné problémy. Nejlepším střelcem utkání byl s 22 body kapitán týmu. Děkujeme všem fanouškům za fantastickou podporu!</p>',
                    'en' => '<p>The Kbely sports hall experienced a stormy atmosphere. In the traditional derby against Čakovice, our men showed a concentrated performance from the first minute. Great defense and fast breaks caused significant problems for the opponent. The top scorer of the match was the team captain with 22 points. Thanks to all fans for the fantastic support!</p>',
                ],
            ],
            [
                'date' => '2026-04-20',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'U17 vezou cenný skalp z palubovky soupeře',
                    'en' => 'U17 take a valuable scalp from the opponent\'s court',
                ],
                'excerpt' => [
                    'cs' => 'Naši kadeti vybojovali v dramatické koncovce vítězství o jediný koš na horké půdě v Brandýse.',
                    'en' => 'Our cadets fought out a victory by a single basket in a dramatic ending on the hot court in Brandýs.',
                ],
                'content' => [
                    'cs' => '<p>Zápas jako na houpačce! Naši kluci z kategorie U17 ukázali v Brandýse neuvěřitelnou bojovnost. Přestože v polovině třetí čtvrtiny prohrávali o 12 bodů, dokázali skóre otočit. Rozhodující koš padl 5 sekund před koncem utkání. Tato výhra nás posouvá na průběžné třetí místo v tabulce.</p>',
                    'en' => '<p>A roller-coaster match! Our U17 boys showed incredible fighting spirit in Brandýs. Although they were losing by 12 points in the middle of the third quarter, they managed to turn the score around. The decisive basket was scored 5 seconds before the end of the match. This win moves us to the temporary third place in the standings.</p>',
                ],
            ],
            [
                'date' => '2026-04-18',
                'category_id' => $generalCategory->id,
                'title' => [
                    'cs' => 'Hledáme nové posily! Nábor dětí ročníků 2015–2017',
                    'en' => 'Looking for new reinforcements! Recruitment of children born 2015–2017',
                ],
                'excerpt' => [
                    'cs' => 'Chceš se stát součástí kbelské basketbalové rodiny? Přijď na náš náborový trénink!',
                    'en' => 'Do you want to become part of the Kbely basketball family? Come to our recruitment training!',
                ],
                'content' => [
                    'cs' => '<p>Basketbalový klub Kbelští sokoli otevírá své brány novým talentům. Hledáme kluky i holky ročníků 2015 až 2017, kteří mají chuť se hýbat, učit se novým věcem a najít si partu skvělých kamarádů. Tréninky probíhají pod vedením zkušených trenérů každé úterý a čtvrtek v naší hale.</p>',
                    'en' => '<p>The Kbelští sokoli basketball club opens its doors to new talents. We are looking for boys and girls born between 2015 and 2017 who want to move, learn new things, and find a group of great friends. Training sessions take place under the guidance of experienced coaches every Tuesday and Thursday in our hall.</p>',
                ],
            ],
            [
                'date' => '2026-04-15',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'B-tým potvrdil roli favorita a upevnil si 2. místo',
                    'en' => 'B-team confirmed favorite role and strengthened 2nd place',
                ],
                'excerpt' => [
                    'cs' => 'Rezerva Sokolů si poradila s hostujícím celkem a nadále útočí na čelo soutěže.',
                    'en' => 'The Falcons\' reserve dealt with the visiting team and continues to attack the top of the competition.',
                ],
                'content' => [
                    'cs' => '<p>Naše "béčko" pokračuje ve vítězné vlně. V domácím utkání proti nepříjemnému soupeři z Neratovic dominovalo zejména v podkošovém prostoru. Trenér mohl v druhé polovině zápasu prostřídat celou lavičku a dát šanci i mladším hráčům, kteří se rozhodně neztratili.</p>',
                    'en' => '<p>Our "B-team" continues its winning streak. In the home match against a tough opponent from Neratovice, they dominated especially in the paint. The coach was able to rotate the entire bench in the second half of the match and give a chance to younger players, who certainly didn\'t get lost.</p>',
                ],
            ],
            [
                'date' => '2026-04-12',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'Minižáci U11 na svém prvním velkém turnaji',
                    'en' => 'Mini-basketball players U11 at their first big tournament',
                ],
                'excerpt' => [
                    'cs' => 'Naši nejmladší reprezentanti si užili víkend plný basketbalu v Lounech.',
                    'en' => 'Our youngest representatives enjoyed a weekend full of basketball in Louny.',
                ],
                'content' => [
                    'cs' => '<p>Kategorie U11 vyrazila na svůj první mimopražský turnaj. I když o výsledky šlo až v druhé řadě, naši malí sokolíci ukázali, že se nebojí žádného soupeře. Odvážejí si nejen cenné zkušenosti, ale hlavně spoustu zážitků a radost z prvních vstřelených košů v soutěžních zápasech.</p>',
                    'en' => '<p>The U11 category set off for its first tournament outside Prague. Although results were secondary, our little Falcons showed that they are not afraid of any opponent. They take away not only valuable experience but especially many memories and the joy of the first baskets scored in competitive matches.</p>',
                ],
            ],
            [
                'date' => '2025-05-01',
                'category_id' => $generalCategory->id,
                'title' => [
                    'cs' => 'Spouštíme nový web Kbelští sokoli!',
                    'en' => 'Launching the new Kbelští sokoli website!',
                ],
                'excerpt' => [
                    'cs' => 'Vítejte na našem novém webu, který přináší moderní design a lepší přehled o dění v klubu.',
                    'en' => 'Welcome to our new website, featuring a modern design and a better overview of club activities.',
                ],
                'content' => [
                    'cs' => '<p>S radostí vám oznamujeme, že jsme dnes spustili zcela nové webové stránky našeho klubu Kbelští sokoli. Naším cílem bylo vytvořit přehledné a moderní místo, kde fanoušci i členové najdou vše potřebné na jednom místě.</p><p>Co nového na webu najdete?</p><ul><li>Aktuální výsledky a tabulky všech našich týmů.</li><li>Přehledný kalendář nadcházejících zápasů a akcí.</li><li>Jednoduchou správu členských profilů a příspěvků.</li><li>Pravidelné novinky z klubového života i světa basketbalu.</li></ul><p>Doufáme, že se vám nový web bude líbit a stane se vaším hlavním zdrojem informací o našich sokolech. Sportu zdar a basketbalu zvláště!</p>',
                    'en' => '<p>We are excited to announce that we have launched the brand new website of our club, Kbelští sokoli. Our goal was to create a clear and modern space where fans and members can find everything they need in one place.</p><p>What\'s new on the website?</p><ul><li>Up-to-date results and tables for all our teams.</li><li>A clear calendar of upcoming matches and events.</li><li>Simple management of member profiles and fees.</li><li>Regular news from club life and the world of basketball.</li></ul><p>We hope you enjoy the new website and that it becomes your primary source of information about our falcons. Go Falcons!</p>',
                ],
            ],
        ];

        foreach ($newsData as $item) {
            $publishDate = \Illuminate\Support\Carbon::parse($item['date'])->setHour(10)->setMinute(0);

            Post::updateOrCreate(
                ['slug' => Str::slug($item['title']['cs'])],
                [
                    'category_id' => $item['category_id'] ?? $generalCategory->id,
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
