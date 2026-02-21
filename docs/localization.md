# Lokalizace (Bilingvnost)

Tento dokument popisuje systém bilingvnosti (CZ/EN) implementovaný v projektu Kbelští sokoli.

## 1. Architektura
Projekt je nastaven jako plně dvojjazyčný. Výchozím jazykem je čeština (`cs`), sekundárním angličtina (`en`).

### Komponenty:
- **Middleware:** `SetLocaleMiddleware` – Stará se o nastavení jazyka aplikace na základě session nebo URL parametru `?lang=xx`.
- **Modely:** `spatie/laravel-translatable` – Umožňuje ukládat překlady přímo v JSON sloupcích databáze.
- **Administrace:** `bezhansalleh/filament-language-switch` – Přidává přepínač jazyků do horní lišty Filamentu.

## 2. Překlady v databázi (Modely)
Pokud model obsahuje pole, která mají být bilingvní (např. titulek, obsah), postupujte následovně:

1. **Migrace:** Sloupec musí být typu `json`.
   ```php
   $table->json('title');
   ```
2. **Model:** Použijte trait `HasTranslations`.
   ```php
   use Spatie\Translatable\HasTranslations;

   class News extends Model {
       use HasTranslations;

       public $translatable = ['title', 'content'];
   }
   ```

## 3. Překlady v UI (Blade)
Používejte standardní Laravel funkce `__()` nebo `@lang`.
- Soubory překladů jsou v `lang/cs.json`, `lang/en.json` nebo v adresářích `lang/cs/` a `lang/en/`.

## 4. Přepínání jazyka
Na frontendu lze jazyk změnit přidáním parametru do URL:
- `example.com/?lang=en` -> přepne do angličtiny.
- `example.com/?lang=cs` -> přepne do češtiny.
Volba se uloží do session a zůstane aktivní pro další požadavky.

UI obsahuje moderní přepínač jazyků (CZ | EN) v hlavičce webu a na stránce údržby.

## 5. Implementované změny (21. 2. 2026)
- **Migrace:** Všechna textová pole vyžadující lokalizaci byla převedena na typ `json` v migraci `2026_02_21_205203_make_tables_translatable.php`.
- **Modely:** Modely (`Post`, `Page`, `Setting`, `Announcement`, atd.) nyní používají trait `HasTranslations`.
- **Under Construction:** Stránka údržby je plně lokalizovaná a obsahuje vlastní stylový přepínač jazyků.
- **Frontend Header:** Hlavní hlavička webu byla rozšířena o přepínač jazyků.
- **BrandingService:** Služba nyní vrací lokalizované výchozí hodnoty a správně cachuje nastavení pro každý jazyk.
