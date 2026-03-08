### SEO optimalizace (Letňany) a rozšíření administrace brandingu (únor 2026)

Tato aktualizace se zaměřuje na posílení lokálního SEO pro klíčové slovo **Letňany** a zpřístupnění důležitých globálních odkazů v administraci.

#### Hlavní změny:

1.  **Rozšíření administrace (BrandingSettings):**
    *   Do Filamentu (stránka Branding Settings) byla přidána nová sekce **Důležité odkazy**.
    *   Správci nyní mohou přímo z administrace měnit **URL hlavního klubu** (`main_club_url`) a **URL náboru mládeže** (`recruitment_url`).
    *   Tyto hodnoty byly dříve pevně v kódu nebo seederu, nyní jsou plně pod kontrolou uživatele v databázi.

2.  **SEO optimalizace pro "Letňany":**
    *   **Homepage:** Titulek (Title) byl změněn na `Basketbal Letňany & Kbely – Týmy C & E | Kbelští sokoli` pro lepší indexaci při hledání basketbalu v Letňanech.
    *   **Homepage Meta:** Popis (Description) nyní explicitně zmiňuje hraní v Letňanech.
    *   **Náborová stránka:** Titulek byl aktualizován na `Nábor basketbal Letňany – Muži C & E | Kbelští sokoli` a popis byl upraven tak, aby zdůrazňoval tréninky v RumcajsAreně.
    *   **Konzistence:** Všechny texty v seederu byly prověřeny a opraveny tak, aby neuváděly nábor "do Kbel", pokud se týká dospělých týmů C & E hrajících v Letňanech.

3.  **Technické detaily:**
    *   Aktualizován `CmsContentSeeder.php` s novými SEO daty.
    *   Aktualizována `BrandingSettings.php` (Filament Page) a příslušné překladové soubory (`lang/cs/admin/branding-settings.php`, `lang/en/admin/branding-settings.php`).
    *   Spuštěn produkční build assetů pro zajištění správného zobrazení.

#### Použité příkazy:
```bash
php artisan db:seed --class=CmsContentSeeder -n
npm run build
```
