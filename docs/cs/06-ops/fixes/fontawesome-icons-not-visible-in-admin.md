# Font Awesome ikony nejsou vidět v administraci (Filament)

Tento fix řeší problém, kdy se v administraci (včetně AI vyhledávání a dalších prvků s `<i class="fa-light ...">`) nezobrazovaly žádné ikony Font Awesome.

## Příčina
V admin assetu `resources/css/filament-admin.css` nebyl importován Font Awesome CSS, takže se na stránkách Filamentu nenačítaly webfonty FA. Na frontendu byly ikony funkční díky importu v `resources/css/app.css`, ale admin používal samostatný entrypoint bez tohoto importu.

## Řešení
Do `resources/css/filament-admin.css` byl přidán import lokální (vendored) verze Font Awesome Pro:

```css
@import "tailwindcss";

/* Font Awesome Pro - Lokální (Vendored) pro admin panel */
@import "../vendor/fontawesome/css/all.min.css";
```

Admin panel již tento soubor načítá globálně přes `AdminPanelProvider` pomocí render hooku `panels::head.end`:

```php
->renderHook('panels::head.end', fn (): string => Blade::render(
    "<style>{!! app(\\App\\Services\\BrandingService::class)->getCssVariables() !!}</style>\n                 @vite(['resources/css/filament-admin.css'])"
))
```

## Postup po změně
1. Spusťte build assetů, aby se aktualizoval manifest a přibalily se webfonty FA:
   - `npm run build`
2. Ověřte ve Filament adminu, že se ikony v AI vyhledávání a v jeho dropdownu vykreslují.
3. Případně vygenerujte snapshot HTML podle `docs/08-manualy-a-ostatni/01-renderovani-html.md` a zkontrolujte přítomnost FA tříd a linků na assety s hashem.

## Poznámky
- V repozitáři jsou lokálně vendornuté webfonty v `resources/vendor/fontawesome/webfonts`, které Vite detekuje a publikuje (viz `public/build/manifest.json`).
- V celém projektu držíme variantu `fa-light` dle Guidelines.
