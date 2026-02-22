# Ikony ve Filament administraci

Tento dokument popisuje, jak správně používat ikony ve Filament administraci projektu Kbelští sokoli.

## 1. Architektura ikon

V projektu používáme balíček **owenvoke/blade-fontawesome**, který integruje Font Awesome ikony přímo do **Blade Icons**. Filament nativně podporuje Blade Icons, což nám umožňuje používat ikony bez vkládání přímého HTML (`<i>` tagů).

### Klíčové zásady:
- **NEPOUŽÍVEJTE** `new HtmlString('<i class="..."></i>')` v navigaci ani v akcích.
- **NEPOUŽÍVEJTE** podtržítka v názvech ikon (např. `fal_users`).
- **POUŽÍVEJTE** centrální třídu `App\Support\IconHelper` a její konstanty.
- **POUŽÍVEJTE** formát Blade Icons s pomlčkami (např. `fas-users`).

## 2. Centrální správa (`IconHelper`)

Všechny ikony jsou definovány v třídě `App\Support\IconHelper`. Tato třída zajišťuje:
1. **Konzistenci:** Celá aplikace používá stejné ikony pro stejné akce/moduly.
2. **Robustnost:** Automaticky řeší fallbacky a normalizaci názvů.
3. **Udržovatelnost:** Pokud se rozhodneme změnit ikonu pro "Uživatele", změníme ji na jednom místě.

### Příklad použití v Resource:

```php
use App\Support\IconHelper;

public static function getNavigationIcon(): ?string
{
    return IconHelper::get(IconHelper::USERS);
}
```

### Příklad použití v Akcích/Schématech:

```php
use App\Support\IconHelper;

Action::make('edit')
    ->icon(IconHelper::get(IconHelper::EDIT))
```

## 3. Registrace aliasů (Filament Icon Aliases)

Aby ikony v administraci vypadaly moderně (Light styl) a zároveň nezpůsobovaly problémy s renderováním, používáme systém **Icon Aliases**.

1. **Registrace:** Všechny ikony se automaticky registrují v `AppServiceProvider::boot()` pod prefixem `app::fal-`.
2. **Hodnota:** Každý alias odkazuje na `HtmlString` s tagem `<i class="fa-light ..."></i>`.
3. **Použití:** Metoda `IconHelper::get()` vrací název aliasu (string). Filament pak při renderování tento alias automaticky vyřeší.

Tento přístup je nejrobustnější, protože:
1. **Zamezuje chybám:** String aliasu neobsahuje lomítka, takže Filament negeneruje nechtěné `<img>` tagy v sidebaru.
2. **Univerzálnost:** Funguje ve všech komponentách Filamentu (navigace, akce, taby, sekce).
3. **Bezpečnost:** Zamezuje chybám `SvgNotFound`, protože Blade Icons se nepokouší hledat SVG pro alias, který Filament vyřeší na `HtmlString`.

### Přímý render HTML (`IconHelper::render()`)

Pokud potřebujete vložit ikonu do vlastního HTML řetězce (např. v `Placeholder` komponentě), použijte metodu `render()`:

```php
new HtmlString('<div>' . IconHelper::render(IconHelper::CIRCLE_CHECK) . ' Aktivní</div>')
```

## 4. Font Awesome Pro vs Free

Aktuálně projekt využívá:
- **Webfont:** Font Awesome 7 Pro (Light) pro frontend a celou administraci.
- **SVG (Blade Icons):** Font Awesome Free (Solid/Regular) slouží jako technický základ pro ověřování existence ikon, ale v UI administrace (Filament) preferujeme **Light** variantu přes webfont.

**Jak funguje Light styl v administraci?**
Aby ikony v administraci vypadaly moderně (Light styl), používáme výše zmíněný systém aliasů. Metoda `IconHelper::get()` vrací název aliasu, který Filament vyřeší na `HtmlString`.

Tento přístup je nejrobustnější, protože:
1. **Univerzálnost:** Funguje ve všech komponentách Filamentu (navigace, akce, taby, sekce), i v těch, které měly problémy s přímým vkládáním HTML.
2. **Bezpečnost:** Zamezuje chybám `SvgNotFound`, protože Filament najde registrovaný alias a nevyužívá Blade Icons pro vyhledávání souboru.
3. **Kvalita:** Využívá plný potenciál Font Awesome Pro webfontu.

Pokud potřebujete skutečný `HtmlString` objekt (např. pro vložení do jiného řetězce), použijte `IconHelper::render()`.

**Důležité pravidlo:** V `App\Support\IconHelper.php` vždy definujte konstanty s názvy ikon, které existují ve verzi Font Awesome Free Solid. Tím zajistíte, že diagnostický nástroj bude moci ikonu ověřit (protože ten testuje existenci SVG fallbacku).

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
- **Existenci všech SVG ikon** definovaných v `IconHelper` konstantách proti nainstalovaným sadám.
- **Správnou registraci aliasů** ve Filamentu.
- Formát názvů ikon (pomlčky vs. podtržítka).

## 6. Časté chyby (Troubleshooting)

- **Error: `Svg by name '...' from set 'default' not found`**
  - Příčina: Filament se snaží najít ikonu v defaultním setu (Heroicons), protože ji nerozpoznal jako Font Awesome.
  - Řešení: Ujistěte se, že název začíná prefixem `fas-`, `far-` nebo `fab-`. Použijte `FilamentIcon::get()`.

- **Ikona se nezobrazuje (prázdné místo)**
  - Příčina: Ikona s tímto názvem neexistuje ve Font Awesome Free setu.
  - Řešení: Zkontrolujte název na [fontawesome.com](https://fontawesome.com/search?m=free).
