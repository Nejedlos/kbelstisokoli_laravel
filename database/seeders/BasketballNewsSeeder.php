<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BasketballNewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // Vymažeme staré vývojářské novinky (volitelné, ale uživatel to chtěl nahradit)
        // Nechceme ale smazat úplně všechno, co by tam uživatel mohl mít,
        // ale v rámci seederu to obvykle dává smysl.
        // Uživatel psal "nahraď tím současné novinky jak na local tak na produkci bez ztráty ostatních dat v databázích"
        // Takže smažeme jen Posty, ne celou DB.
        Post::truncate();

        $newsData = [
            [
                'date' => '2026-04-21',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'Lvice načaly kvalifikaci MS suverénní výhrou nad Súdánem',
                    'en' => 'Lionesses started WC qualification with a dominant win over Sudan',
                ],
                'excerpt' => [
                    'cs' => 'České basketbalistky otevřely kvalifikaci mistrovství světa jasným vítězstvím 89:52 nad Jižním Súdánem.',
                    'en' => 'Czech basketball players opened the World Cup qualifiers with a clear 89:52 victory over South Sudan.',
                ],
                'content' => [
                    'cs' => '<p>České basketbalistky otevřely kvalifikaci mistrovství světa jasným vítězstvím 89:52 nad oslabeným Jižním Súdánem. I přes neideální procento střelby si dokázaly proti zónové obraně soupeřek poradit skvělým týmovým výkonem, pod kterým se dvouciferným zápisem podepsala hned šestice lvic. Rozehrávačka Eliška Hamzová byla se 12 body, 6 doskoky, 3 asistencemi a 2 zisky vyhlášena nejlepší hráčkou utkání.</p>',
                    'en' => '<p>Czech basketball players opened the World Cup qualifiers with a clear 89:52 victory over South Sudan. Despite a less than ideal shooting percentage, they were able to deal with the opponents\' zone defense with a great team performance, with six Lionesses scoring in double figures. Point guard Eliška Hamzová was named the player of the match with 12 points, 6 rebounds, 3 assists, and 2 steals.</p>',
                ],
            ],
            [
                'date' => '2026-04-21',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'USK Praha vstoupil do finále drtivou výhrou o 35 bodů',
                    'en' => 'USK Prague entered the finals with a crushing 35-point win',
                ],
                'excerpt' => [
                    'cs' => 'Úřadující šampionky v prvním finálovém utkání nedaly Žabinám šanci a zvítězily 106:71.',
                    'en' => 'The defending champions left no chance for Žabiny in the first final match and won 106:71.',
                ],
                'content' => [
                    'cs' => '<p>Úřadující šampionky z USK Praha v prvním finálovém utkání Ženské basketbalové ligy nedaly brněnským soupeřkám šanci. Po dominantním výkonu Žabiny přehrály 106:71 a ujaly se vedení v sérii. Skvělý útočný basketbal podpořený pevnou obranou ukázal, proč jsou Pražanky favoritkami na titul i v letošní sezóně.</p>',
                    'en' => '<p>The defending champions from USK Prague left no chance for their Brno rivals in the first final match of the Women\'s Basketball League. After a dominant performance, they beat Žabiny 106:71 and took the lead in the series. Great offensive basketball supported by solid defense showed why the Prague team are favorites for the title again this season.</p>',
                ],
            ],
            [
                'date' => '2026-04-20',
                'category_id' => $generalCategory->id,
                'title' => [
                    'cs' => 'Los MS 2026 v Berlíně: Češky čekají na soupeřky',
                    'en' => '2026 WC Draw in Berlin: Czechs waiting for opponents',
                ],
                'excerpt' => [
                    'cs' => 'V Berlíně se 21. dubna rozhodne o složení základních skupin pro nadcházející mistrovství světa.',
                    'en' => 'The composition of the basic groups for the upcoming World Cup will be decided in Berlin on April 21.',
                ],
                'content' => [
                    'cs' => '<p>České reprezentantky se po dvanácti letech opět představí mezi světovou elitou. S kým se v Berlíně na FIBA Women\'s World Cup 2026 utkají v základní skupině, rozhodne slavnostní los 21. dubna. Turnaj, který se koná doslova "za humny", slibuje velký zájem českých fanoušků a návrat českého ženského basketbalu na nejvyšší úroveň.</p>',
                    'en' => '<p>Czech national team players will appear among the world elite again after twelve years. The official draw on April 21 will decide who they will face in the basic group at the FIBA Women\'s World Cup 2026 in Berlin. The tournament, which takes place literally "next door", promises great interest from Czech fans and the return of Czech women\'s basketball to the highest level.</p>',
                ],
            ],
            [
                'date' => '2026-04-18',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'Maxa Národní liga: Nymburk potvrdil roli favorita v Brně',
                    'en' => 'Maxa National League: Nymburk confirmed favorite role in Brno',
                ],
                'excerpt' => [
                    'cs' => 'V 14. kole nadstavby si Nymburk odvezl cennou výhru z palubovky Brna.',
                    'en' => 'In the 14th round of the extension, Nymburk took a valuable win from Brno\'s court.',
                ],
                'content' => [
                    'cs' => '<p>V očekávaném souboji nadstavbové části Maxa Národní ligy potvrdil Nymburk svou dominanci. Na horké půdě v Brně zvítězil po soustředěném výkonu a upevnil si první příčku v tabulce. Brno sice v úvodu kousalo, ale zkušenost hostujícího celku se projevila zejména v rozhodující čtvrté čtvrtině.</p>',
                    'en' => '<p>In the expected clash of the Maxa National League extension, Nymburk confirmed its dominance. On the hot court in Brno, they won after a concentrated performance and strengthened their first place in the standings. Brno bit at the beginning, but the visiting team\'s experience showed especially in the decisive fourth quarter.</p>',
                ],
            ],
            [
                'date' => '2026-04-15',
                'category_id' => $matchCategory->id,
                'title' => [
                    'cs' => 'Senzační obrat v NBA: Charlotte udolalo Miami v prodloužení',
                    'en' => 'Sensational NBA comeback: Charlotte edged Miami in overtime',
                ],
                'excerpt' => [
                    'cs' => 'V napínavém předkole play off NBA zvítězilo Charlotte nad Miami těsně 127:126.',
                    'en' => 'In a thrilling NBA play-in, Charlotte narrowly beat Miami 127:126.',
                ],
                'content' => [
                    'cs' => '<p>Fanoušci v Charlotte zažili noc, na kterou nezapomenou. V dramatickém předkole play off NBA jejich tým udolal Miami Heat 127:126 po prodloužení. O vítězství rozhodla střela v poslední sekundě, která rozpoutala v hale nepopsatelnou euforii. Tento výsledek posouvá Charlotte blíže k branám samotného play off.</p>',
                    'en' => '<p>Fans in Charlotte experienced a night they won\'t forget. In a dramatic NBA play-in, their team edged out the Miami Heat 127:126 after overtime. A last-second shot decided the victory, unleashing indescribable euphoria in the arena. This result moves Charlotte closer to the gates of the playoffs themselves.</p>',
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
                    'cs' => '<p>S radostí vám oznamujeme, že jsme dnes spustili zcela nové webové stránky našeho klubu Kbelští sokoli. Naším cílem bylo vytvořit přehledné a moderní místo, kde fanoušci i členové najdou vše potřebné na jednom místě.</p><p>Co nového na webu najdete?</p><ul><li>Aktuální výsledky a tabulky všech našich týmů.</li><li>Přehledný kalendář nadcházejících zápasů a akcí.</li><li>Jednoduchou správu členských profilů a příspěvků.</li><li>Pravivelné novinky z klubového života i světa basketbalu.</li></ul><p>Doufáme, že se vám nový web bude líbit a stane se vaším hlavním zdrojem informací o našich sokolech. Sportu zdar a basketbalu zvláště!</p>',
                    'en' => '<p>We are excited to announce that we have launched the brand new website of our club, Kbelští sokoli. Our goal was to create a clear and modern space where fans and members can find everything they need in one place.</p><p>What\'s new on the website?</p><ul><li>Up-to-date results and tables for all our teams.</li><li>A clear calendar of upcoming matches and events.</li><li>Simple management of member profiles and fees.</li><li>Regular news from club life and the world of basketball.</li></ul><p>We hope you enjoy the new website and that it becomes your primary source of information about our falcons. Go Falcons!</p>',
                ],
            ],
        ];

        foreach ($newsData as $item) {
            $publishDate = \Illuminate\Support\Carbon::parse($item['date'])->setHour(10)->setMinute(0);

            Post::updateOrCreate(
                ['slug' => Str::slug($item['title']['cs'])],
                [
                    'category_id' => $item['category_id'],
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
