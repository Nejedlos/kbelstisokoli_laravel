# Databázové schéma nového help systému

Tento dokument definuje normalizované databázové schéma pro nový systém nápovědy v projektu Kbelští sokoli. Návrh klade důraz na rozšiřitelnost, multijazyčnost a stabilitu při seedování obsahu.

## 1. Architektonická rozhodnutí

- **Lokalizace:** V souladu s [projekčními guidelines](../../guidelines.md) používáme `spatie/laravel-translatable`. Překládaná pole jsou typu `json`.
- **Struktura vs. Obsah:** Rychlé akce (Quick Actions) a Časté dotazy (FAQ) mají vlastní tabulky. To umožňuje jejich snadnou správu přes Filament Relation Managery a budoucí rozšiřování o další metadata (ikony, barvy, typy odkazů).
- **Správa rolí:** Cílové skupiny (Audience) jsou uloženy jako `json` pole názvů rolí (vazba na `spatie/laravel-permission`). Toto řešení je dostatečně flexibilní pro interní nápovědu a nevyžaduje složité M:N vazby na tabulku rolí.
- **Ochrana seedů:** Sloupec `is_customized` (boolean) indikuje, zda byl záznam ručně upraven v administraci. Seedery budou tento příznak respektovat, aby nedošlo k přepsání uživatelských úprav.
- **Verzování:** Sloupec `source_hash` slouží k detekci změn v seedovacích souborech (např. při změně obsahu v `.md` souboru na disku).

---

## 2. Definice tabulek

### 2.1 Tabulka: `help_categories` (Kategorie/Sekce)
Účel: Definice hlavních tematických celků nápovědy.

| Sloupec | Typ | Vlastnosti | Popis |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | `unsigned`, `PK`, `auto_increment` | Primární klíč. |
| `parent_id` | `bigint` | `unsigned`, `nullable`, `FK` | Odkaz na nadřazenou kategorii (pro podsekce). |
| `name` | `json` | `not null` | Translatable: Název kategorie. |
| `slug` | `string` | `unique`, `index`, `not null` | Unikátní identifikátor pro URL (např. `sport`). |
| `description` | `json` | `nullable` | Translatable: Krátký popis kategorie. |
| `icon` | `string` | `nullable` | Font Awesome třída (např. `fa-light fa-users`). |
| `color` | `string` | `nullable` | Tailwind barva (např. `sky`, `orange`). |
| `sort_order` | `integer` | `default(0)`, `not null` | Pořadí zobrazení. |
| `is_active` | `boolean` | `default(true)`, `not null` | Viditelnost celé kategorie. |
| `is_featured` | `boolean` | `default(false)`, `not null` | Zobrazení na hlavní straně help centra. |
| `is_customized` | `boolean` | `default(false)`, `not null` | Ochrana před přepsáním seederem. |
| `source_hash` | `string` | `nullable` | Hash obsahu ze seederu pro detekci změn. |
| `created_at` | `timestamp` | `nullable` | Čas vytvoření. |
| `updated_at` | `timestamp` | `nullable` | Čas poslední úpravy. |

**Indexy:**
- `UNIQUE INDEX(slug)`
- `INDEX(parent_id)`
- `INDEX(sort_order)`

**Cizí klíče:**
- `parent_id` -> `help_categories(id)` ON DELETE `SET NULL` (zachováme články i po smazání kategorie).

---

### 2.2 Tabulka: `help_articles` (Články)
Účel: Hlavní obsahová jednotka nápovědy.

| Sloupec | Typ | Vlastnosti | Popis |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | `unsigned`, `PK`, `auto_increment` | Primární klíč. |
| `category_id` | `bigint` | `unsigned`, `FK`, `not null` | Vazba na kategorii. |
| `title` | `json` | `not null` | Translatable: Titulek článku. |
| `slug` | `string` | `unique`, `index`, `not null` | Unikátní identifikátor (např. `sprava-tymu`). |
| `content` | `json` | `not null` | Translatable: Hlavní tělo článku (Markdown). |
| `excerpt` | `json` | `nullable` | Translatable: Krátký výtah pro vyhledávání. |
| `search_keywords` | `json` | `nullable` | Translatable: Synonyma a klíčová slova. |
| `audience_roles` | `json` | `nullable` | Pole názvů rolí (např. `["coach", "admin"]`). |
| `sort_order` | `integer` | `default(0)`, `not null` | Pořadí v rámci kategorie. |
| `is_published` | `boolean` | `default(false)`, `not null` | Stav publikace. |
| `is_featured` | `boolean` | `default(false)`, `not null` | Pripíchnutý článek (např. v kategorii). |
| `is_customized` | `boolean` | `default(false)`, `not null` | Ochrana před přepsáním seederem. |
| `published_at` | `timestamp` | `nullable` | Datum publikace. |
| `source_hash` | `string` | `nullable` | Hash obsahu ze seederu. |
| `metadata` | `json` | `nullable` | Doplňková data (např. `last_verified_by`). |
| `created_at` | `timestamp` | `nullable` | |
| `updated_at` | `timestamp` | `nullable` | |

**Indexy:**
- `UNIQUE INDEX(slug)`
- `INDEX(category_id)`
- `INDEX(sort_order)`
- `INDEX(is_published)`

**Cizí klíče:**
- `category_id` -> `help_categories(id)` ON DELETE `CASCADE`.

---

### 2.3 Tabulka: `help_quick_actions` (Rychlé akce)
Účel: Tlačítka vedoucí přímo do příslušné části administrace.

| Sloupec | Typ | Vlastnosti | Popis |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | `unsigned`, `PK`, `auto_increment` | |
| `help_article_id` | `bigint` | `unsigned`, `FK`, `not null` | Vazba na článek. |
| `label` | `json` | `not null` | Translatable: Text tlačítka. |
| `url` | `string` | `not null` | Odkaz (např. `/admin/teams`). |
| `icon` | `string` | `nullable` | Ikona tlačítka. |
| `sort_order` | `integer` | `default(0)`, `not null` | |
| `created_at` | `timestamp` | `nullable` | |
| `updated_at` | `timestamp` | `nullable` | |

**Cizí klíče:**
- `help_article_id` -> `help_articles(id)` ON DELETE `CASCADE`.

---

### 2.4 Tabulka: `help_faqs` (Časté dotazy k článku)
Účel: Specifické FAQ sekce v rámci detailu článku.

| Sloupec | Typ | Vlastnosti | Popis |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | `unsigned`, `PK`, `auto_increment` | |
| `help_article_id` | `bigint` | `unsigned`, `FK`, `not null` | Vazba na článek. |
| `question` | `json` | `not null` | Translatable: Otázka. |
| `answer` | `json` | `not null` | Translatable: Odpověď (Markdown). |
| `sort_order` | `integer` | `default(0)`, `not null` | |
| `created_at` | `timestamp` | `nullable` | |
| `updated_at` | `timestamp` | `nullable` | |

**Cizí klíče:**
- `help_article_id` -> `help_articles(id)` ON DELETE `CASCADE`.

---

### 2.5 Tabulka: `help_article_related` (Související články)
Účel: M:N vazba pro doporučování souvisejícího obsahu.

| Sloupec | Typ | Vlastnosti | Popis |
| :--- | :--- | :--- | :--- |
| `article_id` | `bigint` | `unsigned`, `not null`, `FK` | Zdrojový článek. |
| `related_article_id` | `bigint` | `unsigned`, `not null`, `FK` | Cílový související článek. |

**Indexy:**
- `PRIMARY KEY(article_id, related_article_id)`

**Cizí klíče:**
- `article_id` -> `help_articles(id)` ON DELETE `CASCADE`.
- `related_article_id` -> `help_articles(id)` ON DELETE `CASCADE`.

---

## 3. Strategie vyhledávání a indexace

Pro fulltextové vyhledávání v rámci DB budeme využívat:
1.  **Search Keywords:** Pole `search_keywords` v `help_articles` bude primárním zdrojem pro aliasy a synonyma.
2.  **JSON Search:** Laravel/Eloquent podporuje vyhledávání v JSON sloupcích (`whereJsonContains` nebo `->where('content->cs', 'like', '%...%')`).
3.  **Váha výsledků:** Při implementaci vyhledávání v `HelpService` budou mít výsledky váhu v tomto pořadí:
    - Shoda v `title`
    - Shoda v `search_keywords`
    - Shoda v `excerpt`
    - Shoda v `content`

---

## 4. Mazací politika (Delete Policy)

- **Kategorie:** Smazání kategorie nastaví `parent_id` u podkategorií na `NULL` (zachování struktury). Samotné smazání kategorie je však "nebezpečná" operace, která ve Filamentu vyvolá kaskádové smazání článků (`CASCADE`).
- **Články:** Smazání článku automaticky smaže jeho `FAQ`, `Quick Actions` a záznamy v pivot tabulce souvisejících článků.
- **Relace:** Všechny vazby jsou chráněny cizími klíči na úrovni DB.

---

## 5. Metadata a rozšíření

Pole `metadata` v tabulce `help_articles` je určeno pro:
- `last_verified_at`: Datum poslední kontroly aktuálnosti textu.
- `author_id`: ID uživatele, který článek naposledy upravil (pokud nestačí standardní `updated_at`).
- `reading_time`: Odhadovaná doba čtení (vypočtená při uložení).
