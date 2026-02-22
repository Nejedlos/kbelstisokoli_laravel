# Správa ikon ve Filamentu

Tento dokument popisuje systém správy ikon v administraci, který využívá balíček **Blade Icons** a **Font Awesome**.

## 1. Architektura systému
Systém je navržen tak, aby byl robustní, snadno udržovatelný a podporoval automatické fallbacky.

- **`App\Support\Icons\AppIcon` (Enum):** Centrální seznam klíčů ikon používaných v aplikaci. Hodnota (value) odpovídá názvu ikony ve Font Awesome (bez prefixu).
- **`App\Support\FilamentIcon` (Helper):** Hlavní třída pro získávání názvů ikon. Zajišťuje:
    - Normalizaci názvů (podtržítka na pomlčky).
    - Automatické fallbacky (pokud není aktivní FA Pro, přepne styl z `fal-` na `fas-`).
    - Validaci prefixů.
- **`App\Support\IconHelper` (Zastaralý):** Wrapper pro `FilamentIcon` zachovaný pro zpětnou kompatibilitu.

## 2. Použití ve Filamentu
Ve Filamentu (Resources, Pages, Widgets) vždy používejte helper pro získání ikony.

### Správné použití (přes Enum):
```php
use App\Support\Icons\AppIcon;
use App\Support\FilamentIcon;

// V Resource
public static function getNavigationIcon(): ?string
{
    return FilamentIcon::get(AppIcon::USERS);
}

// V Actions / Formu
->icon(FilamentIcon::get(AppIcon::EDIT))
```

### Použití se specifickým stylem:
```php
FilamentIcon::get(AppIcon::USERS, 'far') // Vynutí Regular styl
FilamentIcon::solid(AppIcon::USERS)      // Alias pro fas-users
```

## 3. Font Awesome Pro vs Free
Aplikace je připravena na Font Awesome Pro, ale standardně běží v režimu **Free**.

- **Free režim:** Všechny požadavky na `fal-` (Light), `fad-` (Duotone) nebo `fat-` (Thin) jsou automaticky přesměrovány na `fas-` (Solid). To zabraňuje chybě `SvgNotFound`.
- **Pro režim:** Aktivován v `.env` pomocí `FONTAWESOME_PRO=true`. Vyžaduje synchronizaci SVG souborů pomocí `php artisan blade-fontawesome:sync-icons --pro`.

## 4. Blade Icons Naming Convention
Blade Icons vyžadují názvy s pomlčkami a prefixem setu:
- `fas-user` (Solid)
- `far-user` (Regular)
- `fab-facebook` (Brands)
- `fal-user` (Light - vyžaduje Pro)

**Nikdy nepoužívejte podtržítka** (např. `fal_user`) přímo ve Filamentu. Náš helper `FilamentIcon` je automaticky opraví, ale je lepší psát kód správně.

## 5. Vlastní SVG ikony
Pokud potřebujete přidat vlastní SVG ikonu:
1. Vložte SVG do `resources/svg/icon-name.svg`.
2. V `config/blade-icons.php` (pokud existuje) se ujistěte, že je cesta registrovaná.
3. Používejte jako `FilamentIcon::get('app-icon-name')`.

## 6. Diagnostika (Icons Doctor)
Pro kontrolu, zda jsou všechny ikony v aplikaci správně nastaveny a dostupné, použijte Artisan příkaz:

```bash
php artisan app:icons:doctor
```

Tento příkaz:
- Ověří dostupnost všech ikon definovaných v `AppIcon` enumu.
- Zkontroluje nastavení prostředí (Pro vs Free).
- Vyhledá problematické řetězce (zastaralé prefixy, podtržítka) v kódu administrace.

## 7. Nasazení na produkci
Při nasazování na produkční server je nutné zajistit, aby byly ikony správně synchronizovány (pokud používáte Pro verzi) a zacachovány pro maximální výkon.

V projektu je pro tento účel připraven jeden souhrnný příkaz:

```bash
php artisan app:icons:sync
```

Tento příkaz automaticky:
1. Zkontroluje, zda je aktivní `FONTAWESOME_PRO`.
2. Pokud ano, spustí `blade-fontawesome:sync-icons --pro` (kopíruje SVG z `node_modules`).
3. Vyčistí starou cache (`icons:clear`).
4. Vygeneruje novou cache (`icons:cache`).

**Důležité:** Před spuštěním tohoto příkazu na produkci musí proběhnout `npm install`, aby byly ikony dostupné v `node_modules`.

**Upozornění ke Gitu:** Adresář `resources/icons/blade-fontawesome` je v souboru `.gitignore` a není součástí repozitáře. To zabraňuje nahrávání tisíců SVG souborů do Gitu. Synchronizace na produkci probíhá právě pomocí výše uvedeného příkazu `app:icons:sync`.

## 8. Běžné chyby a řešení

### Svg by name '...' not found
**Příčina:** Pokus o použití ikony, která neexistuje v dostupných setech (často `fal-` bez Pro verze).
**Řešení:** Používejte `FilamentIcon::get()`, který zajistí fallback na dostupný styl (Solid).

### Ikona se nezobrazuje (prázdné místo)
**Příčina:** Pravděpodobně chybí cache nebo nebyl spuštěn build assetů.
**Řešení:** 
```bash
php artisan icons:clear
php artisan icons:cache
php artisan optimize:clear
```

## 9. Budoucí upgrade na Font Awesome Pro
1. Nainstalujte Pro balíček přes Composer nebo NPM (`npm install @fortawesome/fontawesome-pro`).
2. Nastavte `FONTAWESOME_PRO=true` v `.env`.
3. Spusťte `php artisan app:icons:sync`.
4. Systém automaticky začne používat `fal-` prefixy místo fallbacků.
