### Změny a optimalizace: Homepage, odkazy, patička a výkon (únor 2026)

#### Cíle
- Opravit/ujasnit URL strukturu týmů (`/tymy` + redirect z `/tym`).
- Opravit náborové odkazy (externě `https://www.basketkbely.cz/zacnihrat`, interně `/nabor`).
- Opravit patičku (layout badge + reálný odkaz na GDPR).
- Zrychlit homepage (hero video a obrázky).
- Doplňit SEO meta a očistit JSON-LD.
- Zajistit, aby při přeseedování vznikla nová stránka (GDPR).

---

### Co bylo upraveno v kódu

1) Public routy
- Přidána hlavní route přehledu týmů (plural):
  - `GET /tymy` → `Public\TeamController@index` (route name: `public.teams.index`).
- Zpětná kompatibilita:
  - `GET /tym` → 301 redirect na `/tymy`.
- Nábor:
  - `GET /nabor` řeší generická CMS stránka (existuje v seederu `CmsContentSeeder`).
  - `POST /nabor` zůstává pro odeslání formuláře.

2) Navigace
- `config/navigation.php`: položka týmů nyní ukazuje na `public.teams.index`.

3) Homepage – hero video a obrázky
- **Strategie "Image-First, Video-Later"**:
  - Hero sekce nyní prioritně načítá statický obrázek (`<x-picture>` s `fetchpriority="high"` a `loading="eager"`).
  - Video je odloženo až po události `window.load`.
  - Po načtení videa a detekci připravenosti (`canplaythrough`) se video plynule zobrazí (transition opacity) přes statický obrázek.
  - Tímto je dosaženo bleskového LCP (Largest Contentful Paint), zatímco uživatelský zážitek s videem zůstává zachován.
- **Odstraněno**: Okamžité načítání videa a IntersectionObserver (nahrazeno spolehlivějším odloženým načítáním pro maximální výkon).

4) Obrázky (globálně)
- Vytvořena nová **Blade komponenta `<x-picture>`**, která sjednocuje práci s obrázky v celém projektu.
- **Robustní Fallback systém**:
  1. Zkusí se WebP verze (`.webp`).
  2. Pokud neexistuje, zkusí se JPG/JPEG verze (`.jpg`, `.jpeg`).
  3. Pokud ani ta neexistuje, použije se globální fallback: `assets/img/home/basketball-court-detail.webp` (resp. `.jpg`).
- **Automatická detekce mobilních verzí**: Komponenta automaticky hledá soubor s příponou `-mobile` (např. `hero-mobile.webp`), pokud existuje, použije ho pro mobilní zobrazení.
- Použito v blocích `hero`, `cards_grid` a `image`.
- Doplněno: `loading="lazy"`, `decoding="async"` (pokud není u Hero nastaveno jinak).

5) Patička
- Badge („Praha‑Kbely • basketbal • komunita“) zarovnána pomocí `inline-flex items-center`, tečka je vlevo a vertikálně vystředěná, zachován `animate-ping`.
- Odkaz „Ochrana soukromí“ již není `#`, ale míří na `/gdpr`.

6) SEO/structured data
- Přidány meta: `og:locale`, `twitter:image:alt`.
- `SeoService`: přidána metoda `resolveOgLocale()` a rekurzivní čištění JSON‑LD (`cleanSchema()`), které odstraňuje `null`/prázdné hodnoty.

7) Seeder pro GDPR
- Nový seeder `GdprPageSeeder` vytváří/publicuje stránku `/gdpr` se základním obsahem (CS/EN) a SEO titulkem/popisem.
- Registrován v `GlobalSeeder::SEEDERS`.

8) Testy
- `tests/Feature/HomepagePublicRoutesTest.php` pokrývá:
  - `/` vrací 200
  - `/tymy` vrací 200
  - `/tym` → 301 na `/tymy`
  - `/nabor` vrací 200 (interní CMS stránka)
  - `/gdpr` vrací 200

---

### Co upravit v obsahu (CMS) – krok za krokem
Následující položky jsou spravovány obsahem (DB). Pokud byly dříve nasazené se starými URL, po přegenerování/úpravách prosím zkontrolujte:

1) Homepage → blok „Hero“
- Tlačítko „Chci začít s basketem“ (CS) / „I want to start playing“ (EN):
  - URL musí být `https://www.basketkbely.cz/zacnihrat`
  - V seederu je to již opraveno, ale pokud běžíte na existující DB, změňte v adminu obsahově.

2) Patička → sekce „Týmy a klub“
- Položka „Nábor/Začni hrát“ směřuje externě na `https://www.basketkbely.cz/zacnihrat`.
- Odkaz „Ochrana soukromí“ je `/gdpr` (interní stránka). Obsah lze upravit v adminu (Page: `gdpr`).

3) Interní stránka `/nabor`
- Existuje jako CMS stránka (obsah pro Muži C/E). Pokud obsahově nechcete interní náborový landing, můžete dočasně:
  - aktualizovat tlačítka na homepage/footeru tak, aby směřovala externě na `zacnihrat` (mládež/oddíl),
  - nebo obsah `/nabor` přizpůsobit pro dospělé (kontakty, tréninky, podmínky).

---

### Jak pracovat s velkým videem a obrázky (praktický návod)

Hero video (doporučení):
1) Video soubory umístěte do `public/assets/video/`:
   - Desktop (WebM - primární): `public/assets/video/hero.webm` (lepší komprese).
   - Desktop (MP4 - fallback): `public/assets/video/hero.mp4` (pro starší prohlížeče).
2) Poster obrázky umístěte do `public/assets/img/home/`:
   - `home-hero-basketball-team.webp` (desktop, hlavní volba pro moderní prohlížeče)
   - `home-hero-basketball-team.png` (desktop, fallback pro starší prohlížeče)
   - `home-hero-mobile.webp` (mobilní, lehký, automaticky preferován na malých displejích)
   - `home-hero-mobile.png` (mobilní fallback)
3) Systém automaticky upřednostňuje WebP, pokud existuje ve stejné složce se stejným názvem (jen jinou příponou). Pokud měníte názvy, upravte je v adminu v obsahu (blok „Hero“ → `image_url`). Pro Hero stačí v adminu zadat cestu k desktopové verzi, mobilní se odvodí automaticky (konvence `-mobile`).
4) Optimalizace:
   - Zajistěte krátkou smyčku, nižší framerate (např. 24 fps), klidnější scény.
   - Mobile-first: na mobilech se obecně video nenačítá (zobrazuje se poster) – to je nyní default.

Obrázky (globálně):
- Vkládejte velké obrázky přes Media knihovnu (model `MediaAsset`) a používejte varianty (např. `getUrl('large')`).
- V šablonách jsou doplněny `loading="lazy"` a `decoding="async"` – zachovejte.
- Pokud víte reálné rozměry, doplňte `width`/`height` pro lepší CLS.
- Doporučené formáty: primárně WebP (fallback JPG/PNG dle potřeby), poměr stran 16:9/3:2 dle bloku.

Build pipeline (Vite) – kdy spouštět `npm run build`:
- Pokud měníte jen soubory v `public/assets/...`, build není nutný.
- Pokud měníte CSS/JS entrypointy v `resources` (např. přidáte nové soubory a voláte `@vite`), spusťte `npm run build`, aby se aktualizoval manifest (jinak `ViteException`).

---

### Ověřené routy (stav po úpravách)

| URL         | Existuje | Typ           | Poznámka                     |
|-------------|----------|---------------|------------------------------|
| `/`         | ano      | page          | homepage                     |
| `/tymy`     | ano      | page          | přehled týmů                 |
| `/tym`      | ano      | redirect 301  | trvalé přesměrování na `/tymy` |
| `/nabor`    | ano      | page          | interní CMS stránka          |
| `/gdpr`     | ano      | page          | interní CMS stránka          |

---

### Použité příkazy
- Spuštění seederů (bez interakce):
```
php artisan db:seed --class=Database\Seeders\GlobalSeeder -n
```
- Běh jen CMS seederů (rychleji pro test/dev):
```
php artisan db:seed --class=Database\Seeders\CmsContentSeeder -n
php artisan db:seed --class=Database\Seeders\GdprPageSeeder -n
```
- Testy:
```
php -d detect_unicode=0 ./vendor/bin/phpunit --filter=HomepagePublicRoutesTest
```

---

### Dopad na výkon (očekávání)
- Hero video se nenačítá na mobilech a na desktopu až ve chvíli, kdy je hero ve viewportu → výrazně menší data při prvním vykreslení, lepší LCP.
- Obrázky pod hero jsou lazy + async decoding → menší blokování hlavního vlákna a redukce přenášených dat.

---

### Doporučení na další kroky
- Přidat jednoduchou admin stránku (Filament) pro správu veřejných odkazů (hlavní klub, nábor, sociální sítě), aby se odkazy neudržovaly v seederech.
- Zvážit `webm` variantu videa (menší datová náročnost) a více rozměrových variant posterů (`<picture>` + `srcset`).
- CDN pro statická aktiva (`public/assets/...`) – s HTTP/3 a obrázkovými transformacemi.
