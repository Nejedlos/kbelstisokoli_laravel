# Ikony ve Filament administraci

Tento dokument popisuje, jak správně používat ikony ve Filament administraci projektu Kbelští sokoli.

## 1. Architektura ikon

V projektu používáme balíček **owenvoke/blade-fontawesome**, který integruje Font Awesome ikony přímo do **Blade Icons**. Filament nativně podporuje Blade Icons, což nám umožňuje používat ikony bez vkládání přímého HTML (`<i>` tagů).

### Klíčové zásady:
- **NEPOUŽÍVEJTE** `new HtmlString('<i class="..."></i>')` v navigaci ani v akcích.
- **NEPOUŽÍVEJTE** podtržítka v názvech ikon (např. `fal_users`).
- **POUŽÍVEJTE** centrální třídu `App\Support\FilamentIcon` a její konstanty.
- **POUŽÍVEJTE** formát Blade Icons s pomlčkami (např. `fas-users`).

## 2. Centrální správa (`FilamentIcon`)

Všechny ikony jsou definovány v třídě `App\Support\FilamentIcon`. Tato třída zajišťuje:
1. **Konzistenci:** Celá aplikace používá stejné ikony pro stejné akce/moduly.
2. **Robustnost:** Automaticky řeší fallbacky a normalizaci názvů.
3. **Udržovatelnost:** Pokud se rozhodneme změnit ikonu pro "Uživatele", změníme ji na jednom místě.

### Příklad použití v Resource:

```php
use App\Support\FilamentIcon;

public static function getNavigationIcon(): ?string
{
    return FilamentIcon::get(FilamentIcon::USERS);
}
```

### Příklad použití v Akcích/Schématech:

```php
use App\Support\FilamentIcon;

Action::make('edit')
    ->icon(FilamentIcon::get(FilamentIcon::EDIT))
```

## 3. Mapování ikon (`config/app-icons.php`)

Pro pokročilé mapování a možnost globální výměny ikon (např. přechod z Font Awesome na Heroicons) existuje konfigurační soubor `config/app-icons.php`.

## 4. Font Awesome Pro vs Free

Aktuálně projekt využívá:
- **Webfont:** Font Awesome 7 Pro (Light) pro frontend a obecné UI.
- **SVG (Blade Icons):** Font Awesome Free (Solid/Regular) pro administraci. Aby SVG ikony v administraci (např. v sidebaru a taby) fungovaly spolehlivě, musí být jejich názvy v konstantách `FilamentIcon` dostupné v této bezplatné sadě. Pokud použijete název, který je pouze v Pro verzi (např. `shield-check`), administrace vyhodí chybu `SvgNotFound`.

**Důležité pravidlo:** V `App\Support\FilamentIcon.php` vždy definujte konstanty s názvy ikon, které existují ve verzi Font Awesome Free Solid. Tím zajistíte funkční SVG fallback. Webfont verze ikon budou i nadále moci používat Light styl tam, kde je to podporováno.

**Proč `fal-` nefunguje v administraci?**
Bez explicitního nahrání SVG souborů z Pro verze do složky `resources/svg` nebo syncu s KITem nemá Blade Icons přístup k Light variantám. Naše Support třída `FilamentIcon` automaticky přepíná styl na `fas-` (Solid), pokud je vyžadován styl, který není dostupný.

### Jak v budoucnu zapnout Pro verze SVG:
1. Stáhněte SVG ikony z Font Awesome Pro.
2. Umístěte je do `resources/svg/fontawesome/light`.
3. Zaregistrujte nový set v `config/blade-icons.php`.
4. Nastavte `app.fontawesome_pro` na `true` v `.env`.

## 5. Diagnostika (`php artisan app:icons:doctor`)

Pokud narazíte na problém s ikonami (např. bílá stránka nebo `SvgNotFound` chyba), spusťte tento příkaz:

```bash
php artisan app:icons:doctor
```

Tento příkaz provede automatickou kontrolu:
- Instalaci balíčku `owenvoke/blade-fontawesome`.
- **Existenci všech SVG ikon** definovaných v `FilamentIcon` konstantách proti nainstalovaným sadám.
- Detekci zakázaného přímého vkládání HTML (`<i class="...">`) do PHP kódu administrace.
- Formát názvů ikon (pomlčky vs. podtržítka).

## 6. Časté chyby (Troubleshooting)

- **Error: `Svg by name '...' from set 'default' not found`**
  - Příčina: Filament se snaží najít ikonu v defaultním setu (Heroicons), protože ji nerozpoznal jako Font Awesome.
  - Řešení: Ujistěte se, že název začíná prefixem `fas-`, `far-` nebo `fab-`. Použijte `FilamentIcon::get()`.

- **Ikona se nezobrazuje (prázdné místo)**
  - Příčina: Ikona s tímto názvem neexistuje ve Font Awesome Free setu.
  - Řešení: Zkontrolujte název na [fontawesome.com](https://fontawesome.com/search?m=free).
