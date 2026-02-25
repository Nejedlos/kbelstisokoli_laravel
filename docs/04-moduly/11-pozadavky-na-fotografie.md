# Seznam požadovaných fotografií

Tento dokument slouží jako podklad pro stažení a přípravu fotografií z fotobanky pro veřejný web Kbelští sokoli.

## 1. Technické parametry
- **Formáty:** Každá fotografie musí být nahrána ve dvou formátech: `.webp` (primární) a `.jpg` (fallback).
- **Umístění:** Soubory nahrajte do adresáře `public/assets/img/...` dle tabulky níže.
- **Kvalita:** WebP s kompresí cca 80 %, JPG optimalizované pro web.
- **Rozměry:** Pro Hero sekce doporučeno šířka 1920px, pro obsahové obrázky 1200px.

## 2. Přehled fotografií

| Umístění | Popis (Co má na fotce být) | Doporučený Prompt pro fotobanku/AI | Cílová cesta a název (bez přípony) |
| :--- | :--- | :--- | :--- |
| **Homepage Hero** | Tým basketbalistů v hale, dynamická atmosféra, kbelské barvy (červená/modrá). | Cinematic shot of a basketball team in a gym, action atmosphere, dramatic lighting, professional basketball court. | `assets/img/home/home-hero` |
| **Homepage Hero Mobile** | Detail basketbalového míče nebo koše, vertikální formát pro mobilní telefony. | Vertical shot of a basketball hoop with net, dramatic sunset lighting, sports photography, high contrast. | `assets/img/home/home-hero-mobile` |
| **Homepage - Karta Muži C** | Hráči týmu C v akci nebo týmové foto. | Basketball players in red and white jerseys playing a match, intense action, professional look. | `assets/img/home/team-muzi-c` |
| **Homepage - Karta Muži E** | Hráči týmu E nebo momentka ze zápasu/tréninku. | Basketball players in blue and white jerseys, smiling, team spirit, amateur league match. | `assets/img/home/team-muzi-e` |
| **Homepage - Karta Nábor C/E** | Detail basketbalové palubovky nebo sítě. | Close-up of a basketball net after a score, motion blur of the net, gym background. | `assets/img/home/basketball-court-detail` |
| **Homepage - Karta Mládež** | Děti nebo mládež při tréninku basketbalu. | Group of kids (boys and girls) in basketball uniforms training with balls, happy faces, indoor gym. | `assets/img/home/kids-youth-basket-training` |
| **Přehled týmů** | Detail palubovky s míčem v popředí, rozmazané pozadí (bokeh). | Close-up of a professional basketball on a wooden parquet floor, gym interior blurred in background, high quality photography. | `assets/img/teams/teams-header` |
| **Muži C (Detail)** | Dynamická akce ze zápasu, smeč nebo blok, soutěžní napětí. | Action shot of a basketball player performing a layup or dunk, intense competition vibe, motion blur, sharp focus on player. | `assets/img/teams/muzi-c-header` |
| **Muži E (Detail)** | Skupina hráčů radujících se po koši, přátelská a komunitní atmosféra. | Group of basketball players high-fiving and celebrating on court, friendly atmosphere, community sport vibe, smiling faces. | `assets/img/teams/muzi-e-header` |
| **Nábor (Hlavička)** | Prázdná basketbalová hala připravená na trénink, ranní světlo procházející okny. | Empty indoor basketball court, morning light through windows, dust particles, clean wooden floor, ready for training. | `assets/img/recruitment/recruitment-header` |
| **Nábor (Obsah)** | Basketbalové boty, láhev s vodou a ručník položené na lavičce nebo u hřiště. | Basketball shoes, water bottle and a towel on a wooden bench next to a basketball court, sports gear equipment. | `assets/img/recruitment/recruitment-content` |

## 3. Implementace v kódu
V šablonách je použit následující vzor pro zajištění podpory WebP s JPG fallbackem:

```html
<picture>
    <source srcset="{{ asset('assets/img/cesta/soubor.webp') }}" type="image/webp">
    <img src="{{ asset('assets/img/cesta/soubor.jpg') }}" alt="...">
</picture>
```

V případě komponent, které přijímají pouze jednu URL (např. `x-page-header`), je cesta nastavena na `.jpg` a komponenta (nebo CSS) se stará o zbytek, případně byla upravena pro podporu obou formátů.
