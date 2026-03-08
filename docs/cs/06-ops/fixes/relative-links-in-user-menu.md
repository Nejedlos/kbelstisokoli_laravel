# Oprava relativních odkazů v uživatelském menu

## Problém
Odkazy v uživatelském menu (dropdown v hlavičce webu) na administraci a členskou sekci se generovaly jako relativní cesty (bez úvodního lomítka nebo absolutní URL). To způsobovalo, že při kliknutí na odkaz z podstránky (např. `/novinky/detail/1`) se cesta přičítala k aktuální URL (např. `/novinky/detail/admin`), což vedlo k chybě 404.

## Příčina
V komponentě `resources/views/components/header.blade.php` byly použity tyto zápisy:
- Administrace: `href="{{ config('filament.panels.admin.path', 'admin') }}"` -> vracelo pouze `admin`.
- Členská sekce: `href="{{ route('member.dashboard') }}"` -> ačkoliv `route()` by měl být absolutní, v některých konfiguracích prostředí mohl způsobovat potíže.

## Řešení
Všechny problematické odkazy byly obaleny helperem `url()`, který zajišťuje generování absolutní cesty od kořene webu.

### Provedené změny v `resources/views/components/header.blade.php`:
1. Odkaz na členskou sekci změněn na:
   ```html
   href="{{ url('/clenska-sekce/dashboard') }}"
   ```
2. Odkaz na administraci změněn na:
   ```html
   href="{{ url(config('filament.panels.admin.path', 'admin')) }}"
   ```

## Verifikace
Po úpravě odkazy vždy směřují na správnou absolutní adresu (např. `https://domena.cz/admin`), bez ohledu na to, na jaké podstránce se uživatel právě nachází.
