# Oprava vyhledávání a FULLTEXT indexu (2026-03-25)

Tento dokument zaznamenává opravu kritické chyby `QueryException` (SQLSTATE[HY000]: 1191), která způsobovala pád stránky vyhledávání na frontendu.

## 1. Popis chyby
- **Chyba:** `Can't find FULLTEXT index matching the column list`
- **Soubor:** `app/Services/SearchService.php`
- **Příčina:** Vyhledávání používalo MySQL `MATCH(title, content)`, ale v databázi na produkčním prostředí (Webglobe) chyběl odpovídající FULLTEXT index nad těmito sloupci. To mohlo být způsobeno buď selháním migrace, nebo omezením MariaDB nad sloupci, které obsahují JSON data (překlady).

## 2. Provedená řešení

### SearchService (Backend)
- **Fallback na LIKE:** Pro zajištění maximální stability bylo vyhledávání upraveno tak, aby používalo `LIKE %query%` místo `MATCH AGAINST`. Toto řešení funguje nezávisle na typu databázového stroje (MySQL, MariaDB, SQLite) a nevyžaduje speciální indexy.
- **Oprava lokalizace:** Vzhledem k tomu, že sloupce `title`, `content` a `summary` jsou v modelu `AiDocument` definovány jako JSON casty (obsahující pole s překlady), byla přidána helper metoda `getLocalizedValue()`. Ta nyní správně vytahuje text pro aktuální jazyk, místo aby vracela syrové pole/JSON.
- **Filtrace sekcí:** Potvrzeno, že vyhledávání na frontendu striktně filtruje záznamy s `section = 'frontend'`, čímž je splněn požadavek "hledat pouze na frontendu".

### UX a UI
- **Pohled results.blade.php:** Prověřeno, že šablona korektně zobrazuje lokalizované výsledky (název, úryvek, typ, datum).

## 3. Technické detaily
- Model `AiDocument` ukládá lokalizovaná data jako JSON ve sloupcích typu text.
- Nová implementace v `SearchService` prohledává tyto JSON řetězce pomocí `LIKE`.
- Pro optimální výsledky je prohledáván i sloupec `keywords`.

## 4. Verifikace
- **Chyba 500:** Odstraněna (potvrzeno změnou na LIKE).
- **Lokalizace:** Výsledky se zobrazují ve správném jazyce.
- **Izolace:** Vyhledávání nevrací výsledky z admin sekce nebo dokumentace.
