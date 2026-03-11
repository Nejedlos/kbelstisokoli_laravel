# Cleanup starého systému nápovědy (v1)

Tento dokument shrnuje kroky provedené pro odstranění zastaralé markdown-based architektury nápovědy a její plné nahrazení novým databázovým systémem (v2).

## 1. Verifikace funkčnosti nového systému (v2)
Nový systém plně nahrazuje a rozšiřuje původní funkcionalitu:
- **Procházení kategorií:** Realizováno přes `HelpCategory` model (místo skenování adresářů).
- **Detail článku:** Realizováno přes `HelpArticle` model s podporou Markdownu v databázi (místo čtení `.md` souborů).
- **Vyhledávání:** Implementována robustní služba `HelpSearchService` s vážením výsledků (titulek vs. obsah).
- **Nové funkce:**
    - Role/Publikum (filtrace obsahu podle oprávnění uživatele).
    - FAQ (často kladené dotazy u článků).
    - Rychlé akce (odkazy na akce v systému).
    - Breadcrumbs (vlastní hierarchická navigace).
    - Relace mezi články.
    - Featured články na úvodní stránce.

## 2. Seznam odstraněných komponent
Následující komponenty byly identifikovány jako zbytné a odstraněny:
- **Služba:** `App\Services\HelpService` (v1 logic).
- **Šablona:** `resources/views/filament/pages/help-v1.blade.php`.
- **Konfigurace:** `config/help.php` (v2 je nyní jediná a výchozí verze).
- **Kód:** Odstraněny všechny větve `if (config('help.version') === 'v1')` v `App\Filament\Pages\Help.php`.
- **View:** `resources/views/filament/pages/help-v2.blade.php` byl přejmenován na standardní `help.blade.php`.

## 3. Archivace dat
Původní markdown soubory nebyly smazány, ale přesunuty do archivní složky pro případnou budoucí referenci nebo potřebu dodatečného importu:
- **Původní cesta:** `database/seeders/Help/content/`
- **Archivní cesta:** `database/seeders/Help/archive_markdown_v1/`

## 4. Technický dluh a stabilizace
- Odstraněním `HelpService` (v1) se zjednodušilo načítání souborového systému při každém requestu na nápovědu.
- Kód stránky `Help` je nyní čistší a soustředí se pouze na v2 logiku.
- Sjednocení rozhraní nápovědy přes standardní Eloquent modely umožňuje budoucí využití Filament Resources pro správu obsahu přímo z administrace.

---
*Datum: 2026-03-11*
*Stav: Dokončeno*
