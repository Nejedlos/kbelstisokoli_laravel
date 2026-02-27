# Redesign a aktualizace Patičky (Únor 2026)

Patička webu byla kompletně přepracována do moderního sportovního stylu, který je vizuálně konzistentní s novou homepage. Důraz byl kladen na jasnou hierarchii, důvěryhodnost a efektivní propojení s hlavním oddílem TJ Sokol Kbely Basketbal.

## Hlavní změny a vylepšení:

### 1. Vizuální identita a layout
- **4-sloupcový layout:** Desktopová verze je rozdělena do čtyř logických celků pro lepší přehlednost.
- **Maximalizovaná responzivita:** 
    - Na tabletech (sm/md) se sloupce řadí do inteligentní 2-sloupcové mřížky s využitím `col-span` pro vyvážení vizuální váhy (branding a kontakty přes celou šířku).
    - Na mobilních zařízeních se sloupce řadí pod sebe s dostatečným odsazením a pohodlnými klikacími plochami (full-width tlačítka).
    - Dekorativní prvky (basketbalový míč) a texty se adaptivně zmenšují pro zachování harmonie na malých displejích.
- **Moderní design:** Tmavé pozadí (navy), akcentní horní linka v brand barvách a subtilní dekorativní prvek (basketbalový míč na pozadí).

### 2. Obsahová struktura
- **Sloupec 1: Brand & Identita:** Jasně komunikuje, že web patří týmům Muži C a Muži E. Obsahuje logo a krátký vysvětlující text.
- **Sloupec 2: Navigace:** Rychlé odkazy na hlavní sekce našeho webu.
- **Sloupec 3: Týmy a klub:** Odkazy na konkrétní mužské týmy a přímé "bridge" odkazy na hlavní web oddílu, nábor a mládež.
- **Sloupec 4: Kontakt & Spojení:** Ověřené kontaktní údaje s ikonami a výraznými CTA tlačítky pro kontaktování týmu nebo hlavního klubu.

### 3. Technická implementace a editovatelnost
- **Dynamická menu:** Patička nyní načítá menu `footer` a `footer_club` přímo z databáze, což umožňuje správcům snadno měnit odkazy bez zásahu do kódu.
- **Branding Integration:** Veškeré texty a URL (včetně externích odkazů na hlavní klub) jsou napojeny na globální nastavení (`BrandingService`).
- **Lokalizace (CZ/EN):** Celá patička je plně bilingvní. Texty jsou spravovány přes překladové soubory `lang/*.json`.
- **Fallbacky:** Implementovány bezpečné fallbacky pro případ chybějících kontaktů nebo menu, aby layout zůstal stabilní.

### 4. Bottom Bar (Spodní lišta)
- Zjednodušený copyright s informací o členství v širším oddílu.
- Rychlé utility odkazy (Kontakt, Členská sekce, Hlavní oddíl).

## Změněné soubory:
- `app/Services/BrandingService.php` (přidána podpora pro externí linky)
- `app/Providers/AppServiceProvider.php` (přidáno načítání menu do view composeru)
- `database/seeders/CmsContentSeeder.php` (aktualizace nastavení a menu)
- `resources/views/components/footer.blade.php` (kompletní redesign Blade šablony)
- `lang/cs.json` a `lang/en.json` (přidány překladové klíče pro patičku)

## Doporučené Commity:
- `feat: add footer club menu and external links support to BrandingService`
- `feat: redesign footer component to modern sports style (4-column layout)`
- `fix: update footer translations and seeders with verified data strategy`
