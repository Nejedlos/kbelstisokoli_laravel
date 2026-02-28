# Vyhledávání a AI indexace (Kbelští sokoli)

Tento dokument popisuje systém vyhledávání, který je rozdělen na standardní databázové vyhledávání a AI vyhledávání (GPT), se striktní separací sekcí.

## 1. Architektura sekcí
Projekt rozlišuje tři základní sekce pro vyhledávání:
- **FRONTEND**: Veřejný web, články, CMS stránky.
- **MEMBER**: Členská sekce pro hráče a rodiče (vyžaduje přihlášení).
- **ADMIN**: Administrační rozhraní Filament.

**Bezpečnostní pravidlo:** Vyhledávání v dané sekci nikdy nesmí vrátit výsledky z jiné sekce.

## 2. Datový model
### `ai_documents`
Hlavní tabulka pro indexovaný obsah.
- `section`: ENUM('frontend', 'member', 'admin') - základní filtr.
- `content`: Plaintext obsah stránky (očištěný od HTML).
- `content_hash`: SHA256 hash plaintextu pro detekci změn.
- `is_active`: Indikátor, zda stránka stále existuje.
- `fulltext`: MySQL FULLTEXT index nad `title` a `content`.

### `ai_chunks`
Fragmenty textu pro sémantické vyhledávání.
- Text je rozdělen na menší části (cca 1000 znaků), které se posílají AI jako kontext.

## 3. Indexace (Reindex)
Indexace probíhá pomocí Artisan příkazu:
```bash
php artisan ai:index {--locale=cs} {--section=frontend} {--fresh} {--enrich} {--no-ai}
```

### Parametry:
- `--locale=all`: Reindexace všech jazyků (`cs`, `en`).
- `--section=admin`: Reindexace pouze konkrétní sekce (`frontend`, `member`, `admin`).
- `--fresh`: Smazání existujícího indexu před začátkem (v rámci dané sekce/jazyka).
- `--enrich`: Obohatí dokumenty o AI shrnutí a dotazy (pomalé, volá OpenAI).
- `--no-ai`: Přeskočí generování chunků (fragmentů) pro AI vyhledávání (vhodné pro rychlou aktualizaci pouze DB fulltextu).

### Proces indexace:
1. **Document Providers**: `AiIndexService` prochází definované zdroje (Filament, routy členské sekce, CMS).
2. **Plaintext Extraction**: HTML je očištěno o šum (navigace, footery, skripty) pomocí `TextExtractionService`.
3. **Inkrementální update**: Pokud se hash obsahu nezměnil, dokument se pouze označí jako aktivní. Pokud se změnil, aktualizuje se a přegenerují se chunky.
4. **Cleanup**: Dokumenty, které nebyly během reindexace nalezeny, jsou odstraněny (`is_active = false`).

## 4. Vyhledávání
### Standardní (DB)
Využívá `SearchService` a FULLTEXT indexy v MySQL. Vždy filtruje podle `section` a `locale`.
```php
$results = $searchService->search($query, section: 'member');
```

### AI vyhledávání (GPT)
Využívá `AiSearchService`.
1. Vyhledá relevantní chunky v DB pro danou sekci.
2. Sestaví kontext pro GPT-4o-mini.
3. GPT odpoví na dotaz a přiloží relevantní URL adresy pouze z poskytnutého kontextu.

## 5. Vývoj a testování
Při přidávání nového obsahu k indexaci upravte metody v `AiIndexService`:
- `indexFrontend`
- `indexMemberSection`
- `indexFilament`

Testy vyhledávání se nacházejí v `tests/Feature/SearchServiceTest.php`.
