# Přechod uploadů na public disk (public_path)

## Shrnutí
Byla zavedena jednotná a robustní strategie pro ukládání souborů (uploadů) přímo do veřejné složky aplikace. To zajišťuje, že nahrané soubory jsou okamžitě dostupné přes `asset()` na frontendu jak v lokálním prostředí, tak na produkčním hostingu Webglobe s vlastní cestou k public adresáři.

## Provedené změny
- Přidán nový disk `public_path` v `config/filesystems.php`, který míří na `public_path()` a používá `APP_URL` bez suffixu `/storage`.
- Nastaven výchozí upload disk na `public_path` v `config/filesystems.php` (`'uploads.disk'`).
- Nastaven výchozí disk pro Spatie Media Library na `MEDIA_DISK` → `UPLOADS_DISK` → fallback `public_path` (`config/media-library.php`).
- Přidán helper `web_asset($path)` v `app/Helpers/media.php` pro bezpečné generování veřejných URL (zohledňuje nové umístění i zpětnou kompatibilitu se starými soubory v `storage/`).
- Aktualizovány Blade šablony a poskytovatelé tak, aby používali `web_asset()` místo pevného `asset('storage/...')`.
- `FileUpload` komponenty (Branding) přepnuty na `->disk(config('filesystems.uploads.disk'))`.
- Upravena `.env`, `.env.production` a `.env.example` – `UPLOADS_DISK=public_path` a přidána `MEDIA_DISK=public_path`.

## Co je potřeba po deploy
1. `php artisan optimize:clear`
2. `php artisan config:clear`
3. `php artisan migrate --force` (bezpečné, idempotentní)
4. (Volitelné) `php artisan cache:clear` a `php artisan view:clear`

Pokud nasazujete přes Envoy, stačí spustit `php artisan envoy run deploy` nebo `envoy run sync` – skripty již obsahují čištění cache.

## Poznámky ke zpětné kompatibilitě
- `web_asset()` nejprve hledá soubor v `public/`, a pokud není nalezen, zkusí `public/storage/...` (pro staré uploady se symlinkem). Díky tomu fungují i existující cesty uložené v DB bez migrací dat.
- Tam, kde se generuje URL k logu/OG obrázkům/ikonám, se nyní používá `web_asset()`.

## Konfigurace prostředí
- Lokál: `.env`
  - `UPLOADS_DISK=public_path`
  - `MEDIA_DISK=public_path`
- Produkce: `.env.production` (případně `public/.env` dle Envoy)
  - `UPLOADS_DISK=public_path`
  - `MEDIA_DISK=public_path`
  - `APP_PUBLIC_PATH="/absolutni/cesta/k/verejne/slozce"` (na Webglobe nastavena)

## Dotčené soubory (výběr)
- `config/filesystems.php`
- `config/media-library.php`
- `app/Helpers/media.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Services/SeoService.php`
- `app/Filament/Pages/BrandingSettings.php`
- Blade: `resources/views/components/{header,footer,auth-header}.blade.php`, `resources/views/public/matches/show.blade.php`, `resources/views/public/blocks/cards_grid.blade.php`, `resources/views/filament/widgets/contact-admin-widget.blade.php`, `resources/views/member/contact/{admin,coach}.blade.php`, `resources/views/vendor/mail/html/header.blade.php`

## Důvody
- Jasná dostupnost souborů přes veřejnou URL bez nutnosti `/storage` prefixu.
- Jednotné chování mezi lokálním vývojem a produkcí s custom `public_path`.
- Menší riziko zmatků při kombinaci více disků a symlinků.
