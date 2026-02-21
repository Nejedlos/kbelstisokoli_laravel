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

Díky verzi Pro jsou k dispozici všechny styly, ale v tomto projektu používáme pouze:
- Light (`fa-light`)
- Duotone Light (`fa-duotone fa-light`)
- Brands (`fa-brands`) - pouze pokud neexistuje varianta light.
- Sharp styly (pokud jsou součástí verze 7 a v light variantě)
