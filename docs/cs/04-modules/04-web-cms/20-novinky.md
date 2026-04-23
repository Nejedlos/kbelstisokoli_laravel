# Modul Novinky (Basketball News)

Tento dokument popisuje modul novinek, který slouží k informování členů a fanoušků o dění v klubu i ve světě basketbalu.

## Přehled
Modul je realizován pomocí modelu `Post`, který podporuje:
- Bilingvnost (čeština, angličtina) pomocí `spatie/laravel-translatable`.
- Kategorie (`PostCategory`).
- SEO metadata.
- Plánování publikace (`publish_at`).
- Náhledové obrázky přes `spatie/laravel-medialibrary`.

## Správa obsahu
Novinky se spravují v administraci Filament pod sekcí **Web CMS -> Novinky**.

### Datové pole
- **Titul (Title):** Název článku (překládaný).
- **Slug:** Automaticky generovaná URL identifikátor.
- **Perex (Excerpt):** Krátký úvodní text (překládaný).
- **Obsah (Content):** Plný text článku s podporou HTML (překládaný).
- **Status:** Draft / Published.
- **Datum publikace:** Kdy se má článek zobrazit na webu.

## Seeding a aktualizace dat
V dubnu 2026 proběhla transformace obsahu novinek. Původní technické "vývojářské" novinky byly nahrazeny reálným basketbalovým obsahem z prostředí českého basketbalu (`cz.basketball`).

Pro aktualizaci obsahu na produkci bez nutnosti manuálního přepisování byl vytvořen dedikovaný seeder:
`database/seeders/BasketballNewsSeeder.php`

### Spuštění aktualizace (Local)
```bash
php artisan db:seed --class=BasketballNewsSeeder
```

### Spuštění aktualizace (Production)
```bash
ssh user@host "cd /path/to/app && php artisan db:seed --class=BasketballNewsSeeder --force"
```
*Poznámka: Tento příkaz vymaže (truncate) stávající novinky a nahradí je novou sadou reálných basketbalových zpráv.*

## Frontend zobrazení
Novinky jsou zobrazeny:
1. Na homepage v bloku `news_listing`.
2. Na samostatné stránce `/novinky` (NewsController).
3. V detailu článku `/novinky/{slug}`.

Layout využívá komponentu `<x-news-card />` pro jednotný vizuální styl.
