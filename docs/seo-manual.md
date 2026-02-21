# SEO a Metadata vrstva

Tento dokument popisuje architekturu a způsob správy SEO metadat pro veřejný web basketbalového klubu Kbelští sokoli.

## 1. Architektura
SEO systém je postaven na centralizované službě `App\Services\SeoService`, která zodpovídá za generování všech relevantních metadat pro public frontend.

### Centrální vrstva (SeoService)
Služba shromažďuje data z několika zdrojů v následujícím pořadí (fallback chain):
1. **Model-specific metadata:** Data vyplněná editorem u konkrétní stránky, aktuality nebo galerie (polymorfní vztah `seo`).
2. **Model properties:** Pokud chybí SEO metadata, služba se pokusí data odvodit z obsahu modelu (např. `title` stránky, `excerpt` aktuality, `featured_image`).
3. **Global settings:** Pokud nejsou data u modelu ani v jeho vlastnostech, použijí se globální výchozí hodnoty z **Branding Settings**.
4. **Site defaults:** Poslední pojistka v kódu nebo konfiguraci (např. název klubu).

## 2. Správa v administraci
SEO metadata lze spravovat na třech úrovních:

### A. Globální nastavení (Branding Settings)
V sekci **Nastavení > Branding a vzhled** lze nastavit:
- **Přípona titulku (Title Suffix):** Např. ` | Kbelští sokoli`. Přidává se za název každé stránky.
- **Výchozí meta popis:** Fallback pro stránky bez vlastního popisu.
- **Výchozí OG obrázek:** Obrázek pro sociální sítě (doporučeno 1200x630px).
- **Globální Robots:** Nastavení `index` a `follow` pro celý web.

### B. Stránky, Aktuality a Galerie
Každý z těchto modulů obsahuje záložku **SEO**, kde může editor ovlivnit:
- **SEO Titulek:** Přepíše výchozí název stránky v prohlížeči.
- **SEO Popis:** Krátký text pro výsledky vyhledávání (Google).
- **Kanonická URL:** Pro řešení duplicitního obsahu.
- **Index/Follow:** Možnost zakázat indexaci konkrétní stránky.
- **Open Graph & Twitter:** Specifický titulek, popis a obrázek pro sociální sítě.
- **Twitter Card:** Výběr mezi malým a velkým náhledem.
- **Strukturovaná data:** Možnost vložit vlastní JSON-LD (pro pokročilé uživatele).

## 3. Veřejný frontend (Public Render)
Metadata jsou automaticky vkládána do hlavičky `<head>` v `layouts/public.blade.php`.

### Generovaná metadata:
- `<title>`
- `meta name="description"`
- `link rel="canonical"`
- `meta name="robots"`
- **Open Graph (Facebook, LinkedIn):** `og:title`, `og:description`, `og:type`, `og:url`, `og:image`, `og:site_name`.
- **Twitter / X:** `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`.
- **Structured Data (JSON-LD):** Automaticky generované `SportsOrganization` (klub), `WebSite` a pro aktuality `NewsArticle`.

## 4. Indexace a sitemap
Systém automaticky generuje technické soubory pro vyhledávače:

- **Sitemap.xml:** Dostupná na `/sitemap.xml`. Zahrnuje všechny publikované a viditelné stránky, aktuality a galerie.
- **Robots.txt:** Dostupný na `/robots.txt`. Respektuje nastavení z Branding Settings a zakazuje indexaci `/admin/` a `/member/` sekcí.

### Automatická pravidla indexace:
- Veřejné stránky jsou standardně `index, follow`.
- Obsah ve stavu **Koncept (Draft)** je automaticky `noindex, follow`.
- Stránky v sekcích `/admin` a `/member` jsou vždy `noindex, nofollow`.

## 5. Favicon
Web používá standardní sadu ikon umístěných v `public/` složce. V layoutu jsou správně nalinkovány:
- `favicon.ico`
- `favicon-32x32.png`, `favicon-16x16.png`
- `apple-touch-icon.png`
- `site.webmanifest`
