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
- **Interaktivita:** Kompletně přepracovaný 3D basketbalový míč s realistickou texturou a stínováním.
    - **Vizuální věrnost:** Míč je složen ze dvou vrstev. Spodní vrstva (základní barva, "pebble" textura a basketbalové linky) rotuje pomocí `animate-spin-slow`. Horní vrstva (statické sférické stínování a odlesk) zůstává na místě, což vytváří přesvědčivý 3D efekt koule osvětlené z jednoho bodu.
    - **Aura:** Kolem míče je přidána pulzující červená aura (`animate-aura`), která doplňuje dynamiku prvku a zvyšuje jeho viditelnost.
    - **Detaily:** Přidán SVG pattern `ballPebbles` pro simulaci hrubého koženého povrchu a radial-gradient `ballVolume` pro hloubku.
- **Responzivita:** Stránka je plně optimalizována pro mobilní zařízení. Hlavní nadpis "TIME-OUT!" využívá zjemněnou responzivní stupnici a na nejmenších displejích (xs) dynamickou velikost `12vw`, aby se na mobilech nelámal na dva řádky. Štítek "LAKUJEME PALUBOVKU!" je stabilně ukotven k pravé straně textového bloku nadpisu pomocí vlastní CSS třídy `.label-pos-custom` (kvůli eliminaci problémů s JIT kompilací Tailwindu). Na mobilu je jeho pozice nastavena na `translateX(-80px)`, aby byl v bezpečné zóně, zatímco na desktopu je vysunut více doprava (`translateX(40px)` až `80px`) pro dynamičtější vzhled. Celý layout je vertikálně kompaktnější, aby se na desktopových obrazovkách zobrazoval bez nutnosti scrollování. Podnadpis `h2` je na mobilech mírně zvětšen (`text-2xl`) pro lepší hierarchii.
- **Typografie (Moderní sportovní styl):**
    - Hlavní titulky (`h1`) využívají kombinaci `font-display` (Oswald), `font-black`, `italic`, `uppercase` and `tracking-tighter`.
    - Textový obsah (`h2`, `p`) využívá hlavní bezpatkové písmo projektu (`font-sans` / Instrument Sans).
    - Pro doprovodný text (`p`) je na mobilech snížen proklad na `tracking-[0.2em]` pro lepší čitelnost delších vět, zatímco na desktopu zůstává výrazný `tracking-[0.56em]`.
    - Menší štítky a pomocné texty jsou sjednoceny do stylu: `text-xs`, `font-black`, `uppercase` s výrazným prokladem (typicky `tracking-[0.6em]`). Text v patičce ("Waiting for the buzzer") používá na mobilech snížený proklad `tracking-[0.3em]` pro lepší čitelnost v jednom řádku, zatímco na desktopu si zachovává úderný `tracking-[0.8em]`.
    - **Patička:** Na mobilních zařízeních se prvky v patičce (animovaná tečka a text "Waiting for the buzzer") zobrazují pod sebou a text je omezen na dva řádky pro lepší vizuální stabilitu. Copyright text je dynamický a zobrazuje rozsah od roku 2026 (`2026{{ date('Y') > 2026 ? ' - ' . date('Y') : '' }}`).
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
