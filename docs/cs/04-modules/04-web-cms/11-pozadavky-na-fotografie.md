# Seznam požadovaných fotografií

Tento dokument slouží jako podklad pro stažení a přípravu fotografií z fotobanky nebo AI pro veřejný web Kbelští sokoli.

## 1. Technické parametry a standardy

Pro zachování rychlosti a kvality webu dodržujeme následující pravidla:

- **Formáty:** Každá fotografie musí být nahrána ve dvou formátech: `.webp` (primární) a `.jpg` (fallback).
- **Mobilní varianty:** Pro Hero sekce a velké obrázky se doporučuje nahrát i mobilní verzi s příponou `-mobile` (např. `hero-news-mobile.webp`). Komponenta `x-picture` ji automaticky rozpozná a použije na malých displejích.
- **Kvalita:** WebP s kompresí cca 80 %, JPG optimalizované pro web (cca 70-80 %).
- **Rozměry:** 
    - **Hero sekce:** Šířka 1920px (Desktop), 800px (Mobile).
    - **Obsahové obrázky / Karty:** Šířka 1200px.
- **Implementace:** V Blade šablonách používáme komponentu `<x-picture src="assets/img/cesta/soubor.jpg" ... />`, která se postará o automatické přepnutí na `.webp` a mobilní verzi, pokud existují.

## 2. Přehled požadovaných fotografií

### 2.1 Homepage a obecné sekce

| Umístění | Popis (Co má na fotce být) | Doporučený Prompt pro fotobanku/AI | Cílová cesta a název (bez přípony) |
| :--- | :--- | :--- | :--- |
| **Homepage Hero** | Tým basketbalistů v hale, dynamická atmosféra, kbelské barvy (červená/modrá). | Cinematic shot of a basketball team in a gym, action atmosphere, dramatic lighting, professional basketball court. | `assets/img/home/home-hero` |
| **Homepage Hero Mobile** | Detail basketbalového míče nebo koše, vertikální formát pro mobilní telefony. | Vertical shot of a basketball hoop with net, dramatic sunset lighting, sports photography, high contrast. | `assets/img/home/home-hero-mobile` |
| **Karta Muži C** | Hráči týmu C v akci nebo týmové foto. | Basketball players in red and white jerseys playing a match, intense action, professional look. | `assets/img/home/team-muzi-c` |
| **Karta Muži E** | Hráči týmu E nebo momentka ze zápasu/tréninku. | Basketball players in blue and white jerseys, smiling, team spirit, amateur league match. | `assets/img/home/team-muzi-e` |
| **Karta Nábor (Home)** | Detail basketbalové palubovky nebo sítě. | Close-up of a basketball net after a score, motion blur of the net, gym background. | `assets/img/home/basketball-court-detail` |
| **Karta Mládež (Home)** | Děti nebo mládež při tréninku basketbalu. | Group of kids (boys and girls) in basketball uniforms training with balls, happy faces, indoor gym. | `assets/img/home/kids-youth-basket-training` |

### 2.2 Hlavičky sekcí (Hero Headers)

Tyto obrázky se používají v horní části stránek přes celou šířku.

| Stránka / Sekce | Popis (Vizuální motiv) | Prompt | Cesta a název |
| :--- | :--- | :--- | :--- |
| **Novinky** | Hráč odpočívající po zápase, sportovní noviny nebo pozadí se zprávami. | Basketball player taking a break, sports news background, professional sports photography. | `assets/img/hero/hero-news` |
| **Zápasy** | Interiér profesionální basketbalové arény, zápasová atmosféra. | Professional basketball arena interior, match day atmosphere, wide angle. | `assets/img/hero/hero-matches` |
| **Týmy (Přehled)** | Skupina hráčů v kruhu (huddle), týmový duch. | Basketball team huddle, group spirit, sports team photo. | `assets/img/hero/hero-teams` |
| **Tréninky** | Míč na palubovce během tréninku, tréninkové kužely. | Basketball training session, ball on court, drills session. | `assets/img/hero/hero-trainings` |
| **Galerie** | Detail sportovní fotografie v akci, fotoaparát u hřiště. | Sports photography action, camera on court sideline, bokeh effect. | `assets/img/hero/hero-gallery` |
| **Historie** | Starý retro basketbalový míč, sportovní dědictví. | Vintage basketball, old retro sports heritage, nostalgia, sepia tint. | `assets/img/hero/hero-history` |
| **Kontakt** | Vchod do sportovního areálu nebo moderní kancelář. | Basketball stadium entrance, sports office, communication hub. | `assets/img/hero/hero-contact` |
| **Vyhledávání** | Abstraktní sportovní data, informační centrum. | Sports data search, professional information hub, basketball info graphic. | `assets/img/hero/hero-search` |
| **Detail zápasu** | Akce ze zápasu zblízka, světelná tabule v pozadí. | Basketball match action close-up, arena scoreboard blurred background. | `assets/img/hero/hero-match-detail` |

### 2.3 Nábor a formuláře

| Stránka | Popis | Prompt | Cesta a název |
| :--- | :--- | :--- | :--- |
| **Nábor (Hlavička)** | Prázdná basketbalová hala připravená na trénink, ranní světlo. | Empty indoor basketball court, morning light through windows, clean wooden floor. | `assets/img/recruitment/recruitment-header` |
| **Nábor (Obsah)** | Basketbalové vybavení (boty, láhev) na lavičce u hřiště. | Basketball shoes, water bottle and a towel on a wooden bench next to a court. | `assets/img/recruitment/recruitment-content` |
| **Přidej se (Join)** | Hráč připravující se na zápas, odhodlání, obouvání bot. | Basketball player putting on shoes, getting ready for the game, intense focus. | `assets/img/hero/hero-join` |

### 2.4 Detaily týmů

Každý tým má svůj vlastní Hero obrázek v hlavičce detailu.

| Tým | Popis | Prompt | Cesta a název |
| :--- | :--- | :--- | :--- |
| **Muži A** | Vrcholový basketbal, 2. liga, vysoká intenzita. | Top level basketball game action, pro league vibe, intense competition. | `assets/img/teams/muzi-a-header` |
| **Muži B** | Zkušení hráči v akci, Pražský přebor. | Experienced basketball players during a match, city league atmosphere. | `assets/img/teams/muzi-b-header` |
| **Muži C** | Dynamická akce ze zápasu, smeč nebo blok, soutěžní napětí. | Action shot of a basketball player performing a layup or dunk, motion blur. | `assets/img/teams/muzi-c-header` |
| **Muži E** | Skupina hráčů radujících se po koši, přátelská atmosféra. | Group of basketball players high-fiving and celebrating, community sport vibe. | `assets/img/teams/muzi-e-header` |

## 3. Dynamický obsah

Následující obrázky jsou spravovány v administraci a nahrávány uživateli přes Media Library:
- **Featured Image u Novinek:** Nahrává se v detailu příspěvku (Post).
- **Cover Image Galerie:** Nahrává se v detailu galerie.
- **Fotografie v Galeriích:** Nahrávají se hromadně do konkrétní galerie.
- **Loga partnerů:** Nahrávají se v sekci Partneři.

## 4. Vyhledávání ve fotogaleriích

Při výběru fotek z fotobank (např. Adobe Stock, Unsplash, Pixabay) doporučujeme:
1. Používat anglické výrazy uvedené v tabulkách v sloupci **Prompt**.
2. Hledat "Editorial" nebo "Action sports" fotky pro autentický vzhled.
3. Preferovat barvy blízké klubu (červená, modrá, bílá, černá/šedá).
4. Vybírat fotky s "Negative space" (volným prostorem), kde může být umístěn text hlavičky (typicky na středu nebo na straně).
