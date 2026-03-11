# Finální model a seed strategie help systému

Tento dokument uzavírá architektonickou fázi a definuje přesná pravidla pro implementaci datového modelu, lokalizace a systému seedování nápovědy v projektu Kbelští sokoli.

## 1. Finální rozhodnutí o datovém modelu

Na základě technického auditu a navrženého schématu bylo rozhodnuto o následující struktuře:

- **Relační model:** Použijeme 5 samostatných tabulek (viz [04-databazove-schema.md](04-databazove-schema.md)). Toto řešení poskytuje nejlepší rovnováhu mezi flexibilitou (Filament Relation Managers) a čistotou dat (normalizace).
- **Lokalizace:** V souladu s projekčními standardy využijeme balíček `spatie/laravel-translatable`. Všechna překládaná pole budou v databázi typu `json`.
- **Rychlé akce a FAQ:** Budou uloženy v samostatných tabulkách (`help_quick_actions`, `help_faqs`) s vazbou na článek. To umožní budoucí globální vyhledávání napříč všemi FAQ nebo akcemi.
- **Související články:** Realizováno přes pivot tabulku `help_article_related` (vztah M:N), což umožňuje obousměrné nebo jednostranné doporučování obsahu.

---

## 2. Strategie seedování (Seed Strategy)

Seedování je kritickou částí systému, protože nápověda musí být verzovatelná v Gitu, ale zároveň editovatelná adminem v produkci.

### 2.1 Identifikace a stabilní klíče
- **Stabilní klíč:** Pro párování záznamů v seederu se stávajícími záznamy v DB budeme používat **`slug`**. Sluhy musí být unikátní a stabilní napříč verzemi.
- **Idempotence:** Seedery budou používat metodu `updateOrCreate(['slug' => $slug], [...])`.

### 2.2 Ochrana uživatelských úprav
- **Příznak `is_customized`:** Každý záznam v kategorii a článku má tento boolean příznak (výchozí `false`).
- **Trigger:** Jakmile admin v administraci Filament článek nebo kategorii upraví, aplikace automaticky nastaví `is_customized = true`.
- **Chování seederu:**
    - Pokud `is_customized == false`: Seeder aktualizuje data z Gitu/souborů (včetně `source_hash`).
    - Pokud `is_customized == true`: Seeder záznam **přeskočí** a nevypíše chybu.
    - **Vynucení (`--force`):** Seeder bude mít volitelný parametr pro přepsání i upravených záznamů (pro kritické opravy).

### 2.3 Detekce změn (Source Hash)
- **`source_hash`:** Seeder při uložení vypočítá MD5 hash obsahu (Markdown + metadata).
- **Optimalizace:** Při dalším spuštění seederu se nejprve porovná hash souboru na disku s hashem v DB. Pokud jsou shodné, operace `update` se přeskočí (šetří se výkon a zápis do DB).

---

## 3. Typy obsahu a konflikty

### 3.1 System Seed Content
- Obsah definovaný vývojáři v `database/seeders/help/*.md` nebo v PHP polích seederů.
- Je považován za "Master copy" do okamžiku ruční editace v adminu.

### 3.2 Custom Admin Content
- Články vytvořené adminem přímo v administraci (nemají odpovídající soubor v seederu).
- Tyto záznamy seeder nikdy nesmaže (nepoužíváme `truncate()` nebo synchronizaci typu "smazat vše, co není v seederu").

### 3.3 Konflikty
- Pokud vznikne v seederu nový článek se slugem, který už admin vytvořil ručně, seeder ho díky `updateOrCreate` "adoptuje" jako system content (pokud admin neupravil slug).

---

## 4. Technické specifikace (Naming & Namespaces)

### 4.1 Migrace
- **Název:** `database/migrations/YYYY_MM_DD_HHMMSS_create_help_tables.php`
- **Obsah:** Jedna migrace definující všech 5 tabulek pro čistý start.

### 4.2 Modely (`App\Models`)
- `HelpCategory`
- `HelpArticle`
- `HelpQuickAction`
- `HelpFaq`

### 4.3 Seedery (`Database\Seeders\Help`)
- `HelpSystemSeeder`: Hlavní orchestrátor (volá ostatní).
- `HelpCategorySeeder`: Definice struktury (stromu).
- `HelpArticleSeeder`: Import obsahu z Markdown souborů.

### 4.4 Cesty k obsahu
- Markdown soubory budou umístěny v `database/seeders/help/content/{locale}/{category_slug}/{article_slug}.md`.
- Metadata k článkům (role, ikony, akce) budou definována v JSON nebo PHP poli v `HelpArticleSeeder`.

---

## 5. Doporučení pro implementaci

1.  **Helper pro hashování:** Vytvořit metodu v `HelpService` nebo seederu pro generování `source_hash` z pole dat.
2.  **Filament Hook:** V `HelpResource` využít hook `beforeSave` pro automatické nastavení `is_customized = true`.
3.  **Media Library:** Pokud bude potřeba v nápovědě obrázky, využijeme `Spatie Media Library` napojenou na `HelpArticle`.

---
*Tento dokument je finálním podkladem pro zahájení vývojových prací.*
