# Oprava ViewException ve vyhledávání (2026-03-25)

Tento dokument zaznamenává opravu kritické chyby typu `ViewException`, která způsobovala pád stránky s výsledky vyhledávání na frontendu.

## 1. Popis chyby (ViewException)
- **Chyba:** `array_merge(): Argument #2 must be of type array, Illuminate\Support\Collection given`
- **Soubor:** `resources/views/components/page-header.blade.php`
- **Příčina:** Komponenta `x-page-header` v metodě `array_merge()` pro breadcrumbs očekávala pole, ale `SearchController` (přes `BreadcrumbService`) jí předával kolekci objektů (`Illuminate\Support\Collection`).

## 2. Řešení v komponentě `page-header`
- **Úprava:** Přidána detekce typu proměnné `$breadcrumbs`.
- **Logika:** 
    - Pokud je `$breadcrumbs` kolekce, je převedena na pole, kde klíčem je `title` a hodnotou `url`.
    - Pokud je `$breadcrumbs` pole, je ponecháno beze změny (zpětná kompatibilita pro ostatní stránky jako Aktuality, Zápasy atd.).
- **Bonus:** Pevně zapsaný řetězec `'Úvod'` byl nahrazen lokalizovaným klíčem `__('general.home')`.

## 3. Vylepšení UX vyhledávání
- **Ošetření prázdného dotazu:** Upraven nadpis `page-header`, aby nezobrazoval prázdnou dvojtečku, pokud není zadán žádný dotaz.
- **Minimální délka (3 znaky):** 
    - Do pohledu `public.search.results` přidán blok pro zobrazení `empty-state` informujícího o příliš krátkém dotazu.
    - Přidány nové lokalizační řetězce `min_length_title` a `min_length_text` do `lang/cs/search.php` a `lang/en/search.php`.
- **Layout:** Přidán `min-h-[400px]` na kontejner výsledků, aby prázdné stavy nepůsobily "useknutě".

## 4. Verifikace
- **Aktuality:** Breadcrumbs fungují (předávají pole).
- **Vyhledávání:** Breadcrumbs fungují (předávají kolekci, která je nyní korektně transformována).
- **Lokalizace:** Podporována čeština i angličtina.
