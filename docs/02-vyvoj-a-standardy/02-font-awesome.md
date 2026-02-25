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

Ve Filamentu používáme systém **Icon Aliases** registrovaných v `AppServiceProvider.php` přes `FilamentIcon::register()`. Tento přístup zamezuje chybám v renderování a zajišťuje správné zobrazení Light ikon.

1. **Centrální správa (`IconHelper.php`):** Veškeré názvy ikon definujeme jako konstanty v této třídě. Metoda `get()` vrací název aliasu (např. `app::fal-users`).
2. **Registrace aliasů:** Všechny ikony se automaticky registrují v `AppServiceProvider::boot()` a odkazují na `HtmlString` s příslušným tagem `<i>`.
3. **Použití:**
   ```php
   // V Resource (pro navigaci)
   public static function getNavigationIcon(): ?string {
       return IconHelper::get(IconHelper::USERS);
   }

   // V ostatních komponentách (Actions, Tabs, Sections)
   Action::make('edit')->icon(IconHelper::get(IconHelper::EDIT))

   // Ve vlastním HTML
   new HtmlString('<span>' . IconHelper::render(IconHelper::USERS) . '</span>')
   ```

Díky verzi Pro jsou k dispozici všechny styly, ale v tomto projektu používáme pouze:
- Light (`fa-light`)
- Duotone Light (`fa-duotone fa-light`)
- Brands (`fa-brands`) - pouze pokud neexistuje varianta light.
- Sharp styly (pokud jsou součástí verze 7 a v light variantě)
