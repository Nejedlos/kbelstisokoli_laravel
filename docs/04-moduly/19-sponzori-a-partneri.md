# Sponzoři a partneři

Tento modul zajišťuje správu a zobrazení partnerů a sponzorů týmu. Umožňuje definovat různé typy partnerů a určovat, kde se mají na webu zobrazovat.

## 1. Správa partnerů v administraci

Partneři se spravují v sekci **Partneři** v hlavním navigačním menu administrace.

### Pole partnera:
- **Název partnera:** Interní název pro administraci.
- **Slug:** Automaticky generovaný identifikátor pro URL.
- **Typ partnera:** 
    - *Hlavní partner* (success badge)
    - *Generální partner* (info badge)
    - *Partner* (gray badge)
    - *Mediální partner* (warning badge)
- **Webová stránka:** Odkaz na web partnera.
- **Loga:** Relativní cesty k souborům v adresáři `public/` (např. `assets/img/partners/logo.png`). Podporuje PNG i WebP (s automatickým fallbackem na frontendu).
- **Texty a překlady:** Možnost definovat štítek (např. "Hlavní partner týmu") a popis pro CZ i EN verzi.
- **Umístění:** Checkboxy pro zapnutí/vypnutí zobrazení v konkrétních sekcích (Homepage, Footer, Zápasy, Kontakt, Nábor).
- **Stav a řazení:** Možnost partnera deaktivovat nebo změnit jeho pořadí. Zvýraznění (Featured) dává partnerovi prioritu při řazení.

## 2. Globální nastavení zobrazení

V sekci **Branding** (v části Nástroje správce) lze pod záložkou **Sponzoři a partneři** ovlivnit globální chování:
- Celkové vypnutí/zapnutí systému partnerů.
- Vypnutí konkrétních sekcí (strip pod Hero, patička atd.).
- Nastavení rozměrů log (šířka na desktopu/mobilu, maximální výška).
- Volba stylu sekce (např. logo se štítkem).

## 3. Umístění na frontendu

### Homepage (Partner Strip)
Elegantní vodorovný pruh umístěný přímo pod Hero sekcí. Zobrazuje logo a štítek hlavních partnerů. Působí prémiově a sportovně bez narušení čistoty designu.

### Patička (Footer)
Sekce nad copyright řádkem, která obsahuje decentní loga všech aktivních partnerů s poděkováním za podporu.

### Detail zápasu (Match Badge)
V sidebaru u detailu zápasu se zobrazuje blok "Partner zápasu" pro zvýšení viditelnosti sponzorů u sportovního obsahu.

### Stránky Kontakt / Nábor
Na konci těchto stránek je umístěna sekce se všemi relevantními partnery, doplněná o doprovodný text.

## 4. Technické informace

- **Model:** `App\Models\Partner`
- **Služba:** `App\Services\PartnerService` (zajišťuje filtrování partnerů podle nastavení a sekcí).
- **Komponenta:** `resources/views/components/partner-strip.blade.php`.
- **Lokalizace:** `lang/cs/partners.php` a `lang/en/partners.php`.

Při přidávání nového loga doporučujeme nahrát soubor do `public/assets/img/partners/` ve formátu WebP (pro výkon) a PNG (pro kompatibilitu) a cesty zadat do administrace.
