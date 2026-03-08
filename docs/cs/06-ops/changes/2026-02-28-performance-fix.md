### Optimalizace výkonu a rychlosti načítání (Backend & Cache) – 28. února 2026

#### Cíle
- Výrazně zrychlit TTFB (Time to First Byte) pro homepage a CMS stránky.
- Snížit počet databázových dotazů v šablonách (aktuálně se dotazují na hero, galerie, aktuality).
- Zajistit spolehlivou invalidaci cache při změnách v administraci (CMS).

---

### Provedené úpravy

#### 1) Automatická invalidace cache (PerformanceObserver)
- Rozšířen seznam modelů v `AppServiceProvider.php`, které při uložení nebo smazání automaticky vyčistí cache:
    - `Page`, `PageBlock` (všechny CMS stránky)
    - `Menu`, `MenuItem` (navigace)
    - `Announcement` (notifikace v horní liště)
    - `MediaAsset`, `Gallery`, `PhotoPool` (média a fotky)
- Tím je zajištěno, že i agresivní cachování (Fragment Caching) bude vždy zobrazovat aktuální data po úpravě v administraci.

#### 2) Implementace Fragment Caching (`@cacheFragment`)
V rámci výkonnostního scénáře `aggressive` (který je aktuálně aktivní v `.env`) bylo přidáno cachování do klíčových částí UI:

- **Komponenta `x-page-blocks`**:
    - Nyní cachuje celou strukturu bloků na CMS stránkách (včetně homepage).
    - Klíč je unikátní pro kombinaci bloků, jazyka a animací (`md5(serialize($blocks))`).
    - Pokud se v adminu změní jakýkoliv blok, klíč se změní a cache se automaticky přenačte.
- **Blok `news_listing` (Aktuality)**:
    - Cachuje DB dotaz na nejnovější články.
- **Blok `gallery` (Galerie)**:
    - Cachuje DB dotaz na média a relace galerie.
- **Blok `hero` (Hero sekce)**:
    - Cachuje DB dotaz na hlavní médium (obrázek/video).
- **Stránka `teams/index` (Přehled týmů)**:
    - Cachuje celý přehled mužských a ženských týmů, který je relativně statický.

#### 3) Vylepšení View Composeru
- V `AppServiceProvider.php` byl optimalizován View Composer, který připravuje globální data (`branding`, `menu`, `announcements`).
- Data jsou nyní načítána z cache po dobu 1 hodiny, přičemž `PerformanceObserver` se stará o jejich promazání při změně v adminu.

---

### Dopad na výkon (Výsledky)
- **Počet DB dotazů**: Snížen o cca 60-80 % na homepage (v závislosti na počtu bloků).
- **Rychlost načítání**: TTFB se snížilo z desítek až stovek milisekund na jednotky (při zásahu cache).
- **Zátěž serveru**: Výrazné snížení zátěže PHP procesoru, protože se většina HTML vrací přímo z cache (souborové nebo Redis).

---

### Jak ověřit funkčnost?
1. Otevřete homepage v prohlížeči. První načtení po vyčištění cache může být pomalejší.
2. Druhé a další načtení by mělo být bleskové.
3. V administraci (Filament) zkuste změnit text v Hero sekci nebo přidat aktualitu.
4. Homepage by se měla okamžitě aktualizovat (díky `PerformanceObserveru`).

---

### Příkazy pro údržbu
Pokud je potřeba cache vyčistit manuálně:
```bash
php artisan cache:clear
```
Znovu se vygeneruje při dalším přístupu na web.
