# SEO a Metadata

Tato dokumentace popisuje způsob správy SEO metadat v projektu Kbelští sokoli.

## 1. Architektura
SEO je řešeno centrálně pomocí `App\Services\SeoService`. Tento servis je volán z `AppServiceProvider` v rámci `View::composer` pro všechny veřejné layouty.

## 2. Automatické generování metadat
`SeoService` automaticky generuje metadata na základě předaného modelu (Page, Post, Team, Gallery, atd.).

### Titulky (Title)
- Pokud má model přidruženou relaci `seo` (model `SeoMetadata`), použije se titulek odtud.
- Pokud ne, použije se titulek přímo z modelu (`$model->title` nebo `$model->name`).
- Pokud model chybí, servis se pokusí odhadnout titulek podle URL cesty (např. `/novinky` -> "Novinky").
- K titulku se automaticky přidává suffix (např. " | Kbelští sokoli"), pokud již název klubu neobsahuje.

### Popisky (Description)
- Priorita: `seo->description` -> `$model->excerpt` -> `$model->description` -> `$model->content` (oříznuto na 160 znaků).
- Pokud nic z toho neexistuje, použije se globální fallback optimalizovaný pro funnel.

### Klíčová slova (Keywords)
- Klíčová slova se spojují z modelu a globálního nastavení v administraci.
- Pokud nejsou definována žádná, použijí se výchozí (např. "basketbal Kbely, nábor dětí basketbal").

## 3. Správa v administraci
V administraci je k dispozici SEO sekce pro modely, které to podporují. Tato data jsou ukládána do tabulky `seo_metadatas`.

## 4. Obrázky a ALT texty
- Všechny obrázky v hlavičkách stránek (`x-page-header`) automaticky používají titulek stránky jako `alt` text.
- U obrázků v galeriích a novinkách se používá pole `alt_text` z assetu, nebo název (title) daného prvku.

## 5. Strukturovaná data (JSON-LD)
`SeoService` automaticky generuje:
- `SportsOrganization` pro každou stránku.
- `NewsArticle` pro detaily novinek.
- Podporuje manuální override strukturovaných dat v administraci.
