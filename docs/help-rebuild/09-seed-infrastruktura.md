# Infrastruktura pro seedování help systému

Tento dokument popisuje technické řešení pro naplňování help systému daty. Systém je navržen tak, aby byl odolný vůči uživatelským změnám v produkci a zároveň umožňoval snadnou aktualizaci obsahu z Gitu.

## 1. Souborová struktura

Seedery a obsah jsou umístěny v `database/seeders/Help/`:

- `HelpSeeder.php`: Hlavní orchestrátor.
- `HelpCategorySeeder.php`: Správa kategorií.
- `HelpArticleSeeder.php`: Správa článků a doplňkového obsahu (FAQ, Quick Actions).
- `content/`: Adresář s Markdown soubory obsahu.
    - `{locale}/{category_slug}/{article_slug}.md`

## 2. Klíčové mechanismy

### 2.1 Bezpečný Upsert (Trait `SeedsHelpContent`)
Všechny hlavní entity jsou vkládány pomocí metody `upsertHelpItem`, která:
1. Vyhledá záznam podle stabilního **slugu**.
2. Vypočítá **MD5 hash** z aktuálních dat (včetně překladů a metadat).
3. Pokud záznam existuje:
    - Zkontroluje příznak `is_customized`. Pokud je `true`, seeder záznam přeskočí (ochrana ručních úprav admina).
    - Pokud se hash v DB shoduje s novým hashem, seeder přeskočí `update` (optimalizace).
    - Pokud se hash liší, aktualizuje data a uloží nový hash.

### 2.2 Lokalizace
Používáme standardní formát pro `spatie/laravel-translatable`. V seederu jsou překlady definovány jako pole:
```php
'title' => [
    'cs' => 'Název',
    'en' => 'Title',
]
```

### 2.3 Markdown obsah
Obsah článku není definován přímo v PHP poli seederu, ale je načítán ze souborů v `database/seeders/Help/content/`. To umožňuje:
- Pohodlné psaní v Markdownu s náhledem v IDE.
- Čistou historii změn v Gitu.
- Snadnou spolupráci s copywritery.

## 3. Jak přidat nový obsah

### 3.1 Přidání kategorie
1. Otevřete `database/seeders/Help/HelpCategorySeeder.php`.
2. Přidejte nový prvek do pole `$categories` s metadaty a překlady.
3. Spusťte seeder.

### 3.2 Přidání článku
1. Vytvořte Markdown soubory:
    - `database/seeders/Help/content/cs/{category}/{slug}.md`
    - `database/seeders/Help/content/en/{category}/{slug}.md`
2. Otevřete `database/seeders/Help/HelpArticleSeeder.php`.
3. Přidejte článek do pole `$articles`:
    - Definujte `category_slug`.
    - Definujte metadata (role, ikona, pořadí).
    - Přidejte FAQ a Quick Actions podle potřeby.
4. Spusťte seeder.

## 4. Příkazy pro spuštění

Spuštění celého help systému:
```bash
php artisan db:seed --class=Database\\Seeders\\Help\\HelpSeeder
```

Spuštění pouze kategorií nebo článků:
```bash
php artisan db:seed --class=Database\\Seeders\\Help\\HelpCategorySeeder
php artisan db:seed --class=Database\\Seeders\\Help\\HelpArticleSeeder
```

## 5. Údržba a aktualizace

Pokud v produkci provedete opravu v Markdown souboru, stačí znovu spustit seeder. Pokud admin článek v administraci neupravil (tj. `is_customized` je `false`), změna se okamžitě projeví.

Pokud potřebujete **vynutit** aktualizaci i u změněných článků (např. při kritické chybě), můžete v seederu dočasně nastavit parametr `$force = true` v metodě `upsertHelpItem`.
