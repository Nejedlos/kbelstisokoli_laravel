# Cílová architektura nového help systému

Tento dokument definuje technický návrh, datový model a UX strategii pro novou generaci systému nápovědy v projektu Kbelští sokoli.

## 1. Celková architektura

Systém přechází z Markdown souborů na plně databázové řešení s podporou seedování a pokročilé správy v administraci.

### Komponenty systému:
- **Eloquent Modely**: `HelpCategory`, `HelpArticle`, `HelpQuickAction`, `HelpFaq`.
- **Service Layer (`App\Services\HelpService`)**: Rozhraní pro získávání nápovědy, fulltextové vyhledávání (pomocí `whereJsonContains` nebo dedikované search tabulky) a caching.
- **Filament Administrace**:
    - `HelpResource`: CRUD pro správu nápovědy (kategorie a články).
    - `HelpRelationManagers`: Pro správu FAQ, Quick Actions a souvisejících článků přímo v detailu článku.
- **Filament UI (`App\Filament\Pages\Help`)**: Přepracovaná klientská stránka pro zobrazení nápovědy s využitím nových DB modelů.
- **Cache vrstva**: Využití `Cache::tags(['help'])` pro bleskové načítání stromu kategorií a obsahu.

---

## 2. Datový model

Modely využívají `Spatie\Translatable\HasTranslations` pro multijazyčnost (JSON sloupce v DB).

### HelpCategory (Kategorie)
- `id` (int, PK)
- `parent_id` (int, null, FK na `help_categories`) - pro hierarchickou strukturu.
- `name` (json: cs, en) - název kategorie.
- `slug` (string) - unikátní identifikátor v URL.
- `description` (json: cs, en) - krátký popis kategorie.
- `icon` (string) - kód Font Awesome ikony (např. `fa-light fa-basketball`).
- `color` (string) - barva pro UI (např. `orange`, `blue`).
- `sort_order` (int) - pro ruční řazení.
- `is_active` (boolean) - možnost dočasně skrýt celou sekci.

### HelpArticle (Článek)
- `id` (int, PK)
- `category_id` (int, FK na `help_categories`)
- `title` (json: cs, en) - titulek článku.
- `slug` (string) - unikátní identifikátor v URL.
- `content` (json: cs, en) - hlavní obsah v Markdown formátu.
- `excerpt` (json: cs, en) - krátký výtah pro vyhledávání a náhledy.
- `search_keywords` (json: cs, en) - skrytá klíčová slova pro lepší indexaci.
- `audience_roles` (json) - pole stringů (názvy rolí ze Spatie Permission), komu se článek zobrazuje.
- `sort_order` (int)
- `is_published` (boolean)
- `published_at` (datetime)
- `metadata` (json) - např. `author`, `last_verified_at`.

### HelpQuickAction (Rychlé akce)
- `id` (int, PK)
- `article_id` (int, FK na `help_articles`)
- `label` (json: cs, en) - text tlačítka.
- `url` (string) - odkaz na konkrétní sekci administrace (např. `/admin/teams`).
- `icon` (string)
- `sort_order` (int)

### HelpFaq (Časté dotazy v článku)
- `id` (int, PK)
- `article_id` (int, FK na `help_articles`)
- `question` (json: cs, en)
- `answer` (json: cs, en)
- `sort_order` (int)

### RelatedArticles (Pivot tabulka)
- `article_id` (FK)
- `related_id` (FK na `help_articles`)

---

## 3. UX Architektura

UI bude vycházet ze stávajícího moderního designu, ale bude rozšířeno o nové prvky.

### Landing Page (Hlavní strana nápovědy)
- **Top Bar**: Robustní fulltextové vyhledávání s našeptávačem.
- **Hero Sekce**: Přivítání a nejčastější hledané termíny.
- **Kategorie**: Grid karet s ikonami, barvami a počtem článků.
- **Nejčastější akce**: Blok s Quick Actions napříč celou nápovědou (např. "Jak přidat člena?").

### Detail Článku
- **Breadcrumbs**: Automaticky generované z hierarchie kategorií.
- **Sticky Sidebar (Levý)**: Navigace po kategoriích a článcích (Tree view).
- **Obsah (Střed)**: Renderovaný Markdown s podporou pro kopírování kódů a zvýraznění upozornění.
- **On-this-page (Pravý Sidebar)**: Kotvy generované z H2 a H3 nadpisů v textu.
- **Audience Info**: Malý badge "Určeno pro: Trenéry" na základě `audience_roles`.
- **Quick Actions**: Blok tlačítek vedoucích přímo do příslušných sekcí systému.
- **FAQ Sekce**: Akordeon s doplňujícími dotazy.
- **Related Articles**: Doporučení na další četbu.

---

## 4. Způsob seedování a správa obsahu

Obsah bude spravován kombinací Seederů (pro vývoj/standardní nápovědu) a UI (pro specifické klubové manuály).

### Verziovatelné seedy
- Seedery budou rozděleny na `HelpStructureSeeder` (kategorie) a `HelpContentSeeder` (články).
- **Idempotence**: Použití `updateOrCreate(['slug' => '...'], [...])`.
- **Markdown v souborech**: Pro zachování pohodlí psaní v IDE mohou seedery načítat obsah z `.md` souborů (např. `database/seeders/help/cs/tymy.md`), ale pouze při iniciálním seedu nebo vynuceném updatu.

### Správa v DB
- Jakmile admin upraví článek přes UI, nastaví se příznak `is_customized = true`.
- Seedery budou respektovat tento příznak a nebudou přepisovat uživatelsky upravený obsah (pokud nebude použit `--force`).

---

## 5. Plán migrace a technické kroky

1. **Vytvoření migrací**: Definice tabulek popsaných výše.
2. **Vytvoření Eloquent modelů**: Včetně traitů `HasTranslations` a relací.
3. **Migrační script**: Jednorázový Artisan command, který projde `docs/help/cs` a `en` a naplní DB tabulky (zachování slugů na základě názvů souborů).
4. **Implementace HelpResource**: Pro pohodlnou editaci v administraci.
5. **Refaktoring HelpService**:
    - Původní metody `getTree()`, `getFileContent()` budou přepsány na Eloquent Query.
    - Přidání `search()` metody využívající DB vyhledávání (Searchable trait nebo Scout).
6. **Aktualizace Help Page**: Přepojení Blade šablony na data z `HelpService` pracujícího s DB.
7. **Odstranění starého systému**: Smazání `docs/help/` (po ověření migrace) a zjednodušení `HelpService`.

## 6. Rizika a řešení

- **Ztráta vnitřních odkazů**: Bude vyřešeno regexem při migraci, který převede linky typu `01-tymy.md` na routu nápovědy se slugem.
- **Výkon vyhledávání**: Při větším množství článků bude implementován jednoduchý index nebo Laravel Scout s databázovým driverem.
- **Synchronizace překladů**: Filament formulář bude zobrazovat obě jazykové verze vedle sebe pro snadnou kontrolu.
