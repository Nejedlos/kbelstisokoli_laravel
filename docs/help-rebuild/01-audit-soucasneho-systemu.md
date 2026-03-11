# Technický audit současného systému nápovědy

Tento dokument detailně mapuje aktuální stav systému nápovědy v projektu Kbelští sokoli a slouží jako podklad pro migraci do databázového řešení.

## 1. Přehled současné architektury
Současný systém je postaven jako **dynamický prohlížeč Markdown souborů** integrovaný přímo do Filament PHP administrace.

- **Storage**: Soubory typu `.md` v adresáři `docs/help/`.
- **Struktura**: Adresáře představují kategorie, soubory v nich pak jednotlivé články.
- **Metadata**: Názvy kategorií a popisy se dynamicky čerpají z překladových souborů, `README.md` souborů nebo jsou odvozovány z názvů složek/souborů.
- **Navigace**: Realizována jako vlastní Filament stránka s Livewire parametry v URL (`cat`, `file`, `q`).
- **Lokalizace**: Oddělené adresářové stromy pro každý jazyk (`cs`, `en`).

## 2. Mapa souborů a tříd

| Soubor / Třída | Role v systému |
| :--- | :--- |
| `App\Services\HelpService` | Jádro systému – skenování FS, parsování Markdownu, fulltextové vyhledávání, extrakce metadat (názvy, ikony, barvy). |
| `App\Filament\Pages\Help` | Filament stránka – router a kontroler nápovědy. Spravuje stav zobrazení (kategorie vs. článek) a vyhledávání. |
| `resources/views/filament/pages/help.blade.php` | Blade/Livewire šablona – zajišťuje kompletní vizuální renderování nápovědy (seznam kategorií, seznam článků, detail článku, vyhledávání). |
| `docs/help/cs/**` | Obsah nápovědy v češtině. |
| `docs/help/en/**` | Obsah nápovědy v angličtině (aktuálně téměř prázdné). |
| `lang/cs/admin.php` | Překlady názvů kategorií a popisů (klíče `help.categories.*`). |
| `app/Providers/Filament/AdminPanelProvider.php` | Registrace nápovědy v rámci Filament panelu (automatická přes `discoverPages`). |

## 3. Popis toku dat

1. **Načtení kategorií**:
   - `HelpService->getTree()` skenuje adresářovou strukturu.
   - Pro každou složku hledá název v `lang/{locale}/admin.php`. Pokud chybí, hledá H1 v `README.md`, jinak formátuje název složky.
   - Ikony a barvy jsou přiřazovány v kódu `HelpService` pomocí `match` na základě názvu složky.

2. **Zobrazení článku**:
   - `Help->getFile()` volá `HelpService->getFileContent()`.
   - Obsah souboru se parsuje pomocí `Str::markdown()`.
   - První řádek H1 je použit jako titulek článku.

3. **Vyhledávání**:
   - `HelpService->search()` prochází všechny `.md` soubory v aktuálním jazyce.
   - Provádí `Str::contains` nad celým obsahem souborů.
   - Generuje náhledy (excerpts) se zvýrazněním hledaného textu.

## 4. Slabá místa a omezení

- **Závislost na souborovém systému**: Skenování a vyhledávání při každém requestu je neefektivní.
- **Nepraktické řazení**: Nutnost prefixovat soubory a složky čísly (např. `01-sportovni-agenda`) pro vynucení pořadí.
- **Inkonzistence**: Existence duplicitních nebo nejednotných názvů složek (např. `01-sportovni-agenda` vs `02-sportovni-agenda`).
- **Lokalizace**: Synchronizace mezi jazykovými stromy je manuální a náchylná k chybám (chybějící soubory v jedné verzi).
- **Závislost na vnějších souborech**: Silná vazba na `lang/cs/admin.php` a fyzickou strukturu disků.
- **Chybějící ACL**: Nápověda je dostupná všem přihlášeným uživatelům bez ohledu na jejich role.
- **Správa médií**: Vkládání obrázků do Markdownu je aktuálně komplikované (neexistuje dedikované úložiště přístupné z editoru).

## 5. Co bude nahrazeno vs. zachováno

### Nahradit / Odstranit:
- `App\Services\HelpService`: Logika skenování FS bude nahrazena DB query.
- `docs/help/`: Obsah bude migrován do DB tabulek (kategorie a články).
- Číslování prefixů v názvech (bude nahrazeno `sort_order` v DB).
- `README.md` v kategoriích (bude nahrazeno popisem kategorie v DB).

### Zachovat:
- **UI Šablona**: Design v `help.blade.php` je velmi kvalitní (Tailwind, Font Awesome 7 Pro, moderní vzhled) a může být zachován s úpravami pro odběr dat z DB.
- **Markdown**: Formátování obsahu zůstane Markdownem, ale bude uloženo v DB (např. s editorem typu EasyMDE nebo Filament Markdown editor).
- **Lokalizace**: Myšlenka translatability zůstane, ale bude implementována pomocí JSON polí (Spatie Translatable).
- **Styly**: CSS utility (Tailwind Typography/prose) zůstávají beze změny.

## 6. Rizika migrace

- **Slugy a URL**: Aktuální slugy článků jsou cesty k souborům (např. `docs/help/cs/02-sportovni-agenda/01-tymy.md`). Pokud se změní, staré odkazy (např. z prohlížeče) přestanou fungovat.
- **Vnitřní odkazy**: Pokud články nápovědy obsahují odkazy na jiné `.md` soubory (relativní cesty), tyto se v DB rozbijí a bude nutné je přeformátovat.
- **Breadcrumbs**: Aktuálně se v některých souborech breadcrumbs píšou ručně do obsahu, což je v DB redundantní (budou generovány automaticky z hierarchie).
- **Fallbacky**: Systém aktuálně při chybějícím překladu padá do češtiny. V DB řešení musí být fallback korektně ošetřen.

## 7. Doporučení pro další krok

1. **Návrh DB schématu**:
   - Tabulka `help_categories` (název, slug, popis, ikona, barva, sort_order, parent_id).
   - Tabulka `help_articles` (category_id, titul, slug, obsah, sort_order, metadata).
   - Obě tabulky s podporou pro překlady (Spatie Translatable).
2. **Vytvoření migračního scriptu**: Jednorázový import ze stávajících `.md` souborů do DB pro zachování obsahu.
3. **Refaktoring HelpService**: Přepsání na metody pracující s Eloquent modely při zachování rozhraní pro UI.
4. **Implementace administrace**: Vytvoření Filament Resource pro správu kategorií a článků v DB.
