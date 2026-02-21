# Veřejný frontend (Public Frontend)

Tato dokumentace popisuje strukturu a vizuální prvky veřejné části webu Kbelští sokoli.

## Stránka "V přípravě" (Under Construction)

Stránka se zobrazuje automaticky, pokud není publikován žádný obsah, nebo ji lze manuálně aktivovat v administraci (Branding Settings).

### Vizuální prvky
- **Téma:** Basketbalové hřiště s taktickými prvky (X a O).
- **Pozadí a hloubka:**
    - Odstraněna rušivá tečkovaná mřížka a nahrazena systémem tří velkých atmosférických gradientů (primární barva nahoře, doplňková modrá v rozích).
    - Taktické prvky (X, O, hřiště) jsou zmenšeny a posunuty blíže ke středu obrazovky (shlukovány do skupin), aby byly lépe viditelné na mobilních zařízeních a různých poměrech stran.
    - Upraveno vrstvení (z-index): aura záře je nyní v jedné vrstvě s pozadím, což zabraňuje nechtěnému překrývání pohyblivých prvků.
    - Šum (grainy overlay) je implementován jako pseudoelement `body::before` s nízkou opacitou (2 %), což dodává pocit "materiálu" bez vizuálního šumu.
- **Tlačítka:** Hlavní akce (CTA) využívají plnou brandovou barvu s výrazným stínem pro maximální viditelnost.
- **Interaktivita:** Animovaný basketbalový míč s CSS září a hover efekty.
- **Responzivita:** Stránka je plně optimalizována pro mobilní zařízení. Hlavní nadpis "TIME-OUT!" využívá zjemněnou responzivní stupnici (6xl až 9xl) a zvětšený kontejner (`max-w-7xl`), aby se na žádném zařízení neořezával.
- **Typografie (Moderní sportovní styl):**
    - Hlavní titulky (`h1`) využívají kombinaci `font-display` (Oswald), `font-black`, `italic`, `uppercase` a `tracking-tighter`.
    - Textový obsah (`h2`, `p`) využívá hlavní bezpatkové písmo projektu (`font-sans` / Instrument Sans) s extrémně výrazným prokladem (pro `h2` `tracking-[0.2em]`, pro `p` `tracking-[0.4em]`).
    - Hlavní podnadpis (`h2`) si zachovává tučný a kurzivní styl (`font-black`, `italic`, `uppercase`).
    - Doprovodný text (`p`) je menší, bez kurzívy (`non-italic`) a s velmi rozvolněným řádkováním (`leading-[1.6]`), což zajišťuje vzdušnost a moderní technický vzhled i při velkém množství textu při zachování sportovního charakteru.
    - Menší štítky a pomocné texty (např. "Status", "Pro trenéry", "Sledujte nás") jsou sjednoceny do stylu: `text-xs`, `font-black`, `uppercase` s velmi výrazným prokladem (`tracking-[0.6em]`) pro prémiový a technický vzhled.
- **Záře (Glow):** Využívá se velká, vycentrovaná červená záře v pozadí (pod taktickými prvky), která dodává scéně hloubku a barvu. Nadpis "TIME-OUT!" má navíc přidán dostatečný padding a `overflow-visible`, aby nedocházelo k ořezávání zkoseného písma (italic) na okrajích, což je u `bg-clip-text` častý problém.

### Technické detaily
- **Šablona:** `resources/views/public/under-construction.blade.php`.
- **Barvy:** Dynamicky přebírá barvy z `BrandingService`. Využívá CSS proměnné (např. `--color-primary`, `--color-primary-rgb`).
- **Písmo:** Využívá Google Fonts (Instrument Sans, Oswald, Patrick Hand – s podporou latin-ext pro českou diakritiku).

## Branding Integrace

Barvy a základní informace (sociální sítě, kontakt) jsou spravovány přes `App\Services\BrandingService`. Ten poskytuje:
- Pole s nastavením pro Blade šablony.
- Vygenerovaný blok CSS proměnných pro `:root`.
- RGB varianty barev pro podporu opacity v CSS.
