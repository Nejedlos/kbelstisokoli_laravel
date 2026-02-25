# Dokumentace úpravy Homepage (Feb 2026)

Tento dokument shrnuje změny provedené na hlavní stránce projektu Kbelští sokoli v rámci úkolu pro redesign a aktualizaci obsahu.

## 1. Provedené změny v layoutu a blocích
Pro dosažení moderního, sportovního a "clean sexy" vzhledu byly upraveny následující Blade šablony v `resources/views/public/blocks/`:

- **Hero block (`hero.blade.php`):**
    - Přidána podpora pro **eyebrow text** (overline) nad hlavním nadpisem.
    - Rozšířena podpora pro **více CTA tlačítek** (primární, sekundární, terciární textový link).
    - Přidán **mikrotext** pod tlačítky pro doplňující informace.
    - Přidána možnost vložit obrázek na pozadí přímo přes URL (pole `image_url`), nejen přes MediaAsset ID.
    - Vylepšen vizuální styl (animace, přechody).

- **Cards Grid block (`cards_grid.blade.php`):**
    - Přidána podpora pro **obrázky v kartách** (pole `image_url`).
    - Přidána podpora pro **badge** (štítek) v rohu obrázku (např. pro název soutěže).
    - Přidána podpora pro **sekundární link** v patičce karty.
    - Vylepšen design karet (overflow-hidden, hover efekty, lepší typografie).

- **CTA block (`cta.blade.php`):**
    - Přidána podpora pro **sekundární tlačítko**.
    - Přidána možnost nastavení **zarovnání textu a tlačítek** (vlevo, na střed, vpravo).
    - Vylepšen vizuální styl s využitím brandových mesh gradientů a ikon.

## 2. Obsahová strategie
Homepage byla rozdělena do logických celků, které jasně odlišují tento web (primárně pro Muže C a Muže E) od hlavního webu celého oddílu:

1. **HERO:** Zaměření na komunitu a týmy C + E.
2. **Positioning:** Jasné vysvětlení, že tento web patří týmům C a E, s odkazem na nábor mládeže na hlavní web.
3. **Naše týmy:** Dvě výrazné karty pro Muže C a Muži E s faktickými údaji (trenéři, soutěže).
4. **Klubová tradice:** Prezentace vazby na TJ Sokol Kbely Basketbal.
5. **Náborový most (Bridge):** Výrazné CTA pro zájemce o basketbal (zejména děti/rodiče) vedoucí na hlavní web.
6. **Navigační rozcestník:** Co uživatel na tomto webu najde (zápasy, info, aktuality, členská sekce).
7. **Aktuality:** Příprava na dynamický výpis novinek.
8. **Závěrečné CTA:** Výzva k akci pro fanoušky i nové hráče.

## 3. Lokalizace (CZ/EN)
Celý obsah homepage je připraven bilingvně (čeština i angličtina) v rámci `CmsContentSeeder.php`. 
- **CZ verze:** Kompletní copy dle zadání.
- **EN verze:** Kvalitní překlad zachovávající smysl a hierarchii (This website vs. Main club website).

## 4. SEO a Metadata
Byla nastavena specifická SEO metadata pro homepage:
- **Title (CZ):** Kbelští sokoli – Muži C a Muži E | Basket Kbely Praha
- **Meta Description (CZ):** Web týmů Kbelští sokoli Muži C a Muži E v rámci TJ Sokol Kbely Basketbal. Zápasy, týmové informace, aktuality a odkazy na nábor a hlavní kbelský basket.
- **Title/Description (EN):** Odpovídající anglické varianty.

## 5. Správa obrázků a assety
V kódu jsou použity následující cesty k obrázkům (očekávané v `public/assets/img/home/`):
- `home-hero-basketball-team.jpg` (Hero pozadí)
- `team-muzi-c.jpg` (Karta Muži C)
- `team-muzi-e.jpg` (Karta Muži E)
- `kbely-basket-community.jpg` (Sekce tradice)
- `kids-youth-basket-training.jpg` (Náborový bridge)
- `basketball-court-detail.jpg` (Atmosféra/detaily)

**Fallbacky:** 
- Hero má gradientní fallback (`hero-gradient`), pokud obrázek chybí.
- Karty se zobrazí bez obrázku (pouze text/ikona), pokud `image_url` není definován.

## 6. Technická realizace
Změny byly zavedeny pomocí:
1. Úpravy Blade komponent v `resources/views/public/blocks/`.
2. Aktualizace metody `getHomeBlocks()` a `seedPages()` v `database/seeders/CmsContentSeeder.php`.
3. Spuštění seederu: `php artisan db:seed --class=CmsContentSeeder`.

Obsah je plně editovatelný z administrace Filamentu přes Builder v sekci "Stránky".

## 7. Doporučené commity
- `feat: update homepage blocks to support modern sporting design`
- `feat: implement new homepage content strategy and CZ/EN copy`
- `fix: add SEO metadata for homepage`
- `docs: document homepage redesign and content strategy`
