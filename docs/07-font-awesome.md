# Font Awesome Pro

V projektu je nainstalována knihovna **Font Awesome 7 Pro**.

## Instalace a konfigurace
Pro instalaci je vyžadován licenční token, který je uložen v souboru `.env` pod klíčem `FONTAWESOME_TOKEN`.

### .npmrc
Pro správné fungování `npm install` je v kořenovém adresáři vytvořen soubor `.npmrc`, který konfiguruje přístup k soukromému registru Font Awesome:

```
@fortawesome:registry=https://npm.fontawesome.com/
//npm.fontawesome.com/:_authToken=${FONTAWESOME_TOKEN}
```

## Použití v CSS
Knihovna je importována v hlavním CSS souboru `resources/css/app.css`:

```css
@import "@fortawesome/fontawesome-pro/css/all.css";
```

## Použití v šablonách
Ikony vkládáme výhradně ve stylu **Light** (např. pomocí tříd `fa-light` nebo `fa-duotone fa-light`):

```html
<i class="fa-light fa-house"></i>
<i class="fa-duotone fa-light fa-user"></i>
```

## Použití v administraci (Filament)

Ve Filamentu používáme systém aliasů registrovaných v `AppServiceProvider.php` přes `FilamentIcon::register()`. Tento přístup zamezuje chybám `SvgNotFound` (ze strany Blade Icons) a zároveň brání Filamentu v chybném renderování HTML ikon jako obrázků (tag `<img>`) v sidebaru.

1. **Registrace aliasů (`AppServiceProvider.php`):**
   Všechny ikony pro administraci registrujeme v metodě `boot()` pod prefixem `fal_` (Font Awesome Light).

   ```php
   use Filament\Support\Facades\FilamentIcon;
   use Illuminate\Support\HtmlString;

   FilamentIcon::register([
       'fal_users' => new HtmlString('<i class="fa-light fa-users fa-fw"></i>'),
       'fal_basketball' => new HtmlString('<i class="fa-light fa-basketball fa-fw"></i>'),
       // ... další ikony
   ]);
   ```

2. **Sidebar (Navigace):** V Resource nebo Page vracíme v metodě `getNavigationIcon()` pouze název aliasu jako prostý řetězec.

   *Příklad v Resource:*
   ```php
   public static function getNavigationIcon(): ?string {
       return 'fal_users';
   }
   ```

3. **Ostatní komponenty (Actions, Tabs, Sections):** Používáme `HtmlString` přímo v metodě `icon()`, pokud to komponenta podporuje, nebo opět registrovaný alias:
   ```php
   Action::make('edit')->icon('fal_users')
   // nebo
   Action::make('edit')->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-pencil"></i>'))
   ```

Díky verzi Pro jsou k dispozici všechny styly, ale v tomto projektu používáme pouze:
- Light (`fa-light`)
- Duotone Light (`fa-duotone fa-light`)
- Brands (`fa-brands`) - pouze pokud neexistuje varianta light.
- Sharp styly (pokud jsou součástí verze 7 a v light variantě)
