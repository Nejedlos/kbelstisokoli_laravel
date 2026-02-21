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
Ikony lze používat standardním způsobem pomocí tříd, např.:

```html
<i class="fa-solid fa-house"></i>
<i class="fa-duotone fa-user"></i>
```

Díky verzi Pro jsou k dispozici všechny styly:
- Solid (`fa-solid`)
- Regular (`fa-regular`)
- Light (`fa-light`)
- Thin (`fa-thin`)
- Duotone (`fa-duotone`)
- Brands (`fa-brands`)
- Sharp styly (pokud jsou součástí verze 7)
