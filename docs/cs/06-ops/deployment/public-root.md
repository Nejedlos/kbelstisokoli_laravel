# Konfigurace externího veřejného adresáře (Public Root)

Tento dokument popisuje nastavení projektu pro provoz s veřejným adresářem (Document Root), který se nachází mimo hlavní adresář aplikace. Toto nastavení je typické pro hosting Webglobe, kde subdomény míří do specifických složek.

## Architektura na produkci

- **Sdílené produkční prostředí a storage:** `/home/html/kbelstisokoli.cz/public_html/secret`
- **Releasy:** `/home/html/kbelstisokoli.cz/public_html/secret/deploy/releases/<git-sha>`
- **Aktivní release:** `/home/html/kbelstisokoli.cz/public_html/secret/deploy/current`
- **Veřejný adresář:** `/home/html/kbelstisokoli.cz/public_html/www`

## Konfigurace prostředí (.env)

Pro aktivaci tohoto režimu jsou v `.env` na serveru klíčové tyto proměnné:

```env
# Mód veřejné cesty: 'default' (místní /public) nebo 'external' (vzdálená složka)
PUBLIC_PATH_MODE=external

# Absolutní cesta k veřejnému adresáři na serveru
PROD_PUBLIC_PATH="/home/html/kbelstisokoli.cz/public_html/www"

# Původní proměnná (ponechána pro kompatibilitu s jinými skripty)
APP_PUBLIC_PATH="/home/html/kbelstisokoli.cz/public_html/www"
```

## Jak to funguje

1.  **Laravel bootstrap (`bootstrap/app.php`):**
    - Pokud je `PUBLIC_PATH_MODE=external`, Laravel přenastaví svou `public_path()` pomocí `$app->usePublicPath()` a bindem do kontejneru (`path.public`).
    - To zajistí, že funkce `public_path()`, disk `public_path` ve filesystému i helper `@vite()` budou mířit do externí složky.

2.  **Vite build (`vite.config.js`):**
    - Při sestavování assetů (`npm run build`) Vite builduje do standardní složky `public/build`.
    - GitHub Actions vloží build do release archivu. `/www/build` je symlink přes `secret/deploy/current`, takže se PHP i assety přepnou na stejný commit.
    - Starší hashované soubory se zachovají v následujícím releasu, aby rozečtená stránka během přepnutí neztratila asset.

3.  **Vstupní bod (`index.php`):**
    - V externí složce `/www` je stabilní `index.php`, který přes `realpath()` načte výhradně release pod `secret/deploy/releases` vybraný symlinkem `current`.
    - Odpověď obsahuje `X-App-Release` s aktivním Git SHA. Stejný SHA je součástí namespace rychlé Redis cache.

## Soubory pro externí složku

Tyto soubory by měly být umístěny v `/home/html/kbelstisokoli.cz/public_html/www/`:

### index.php

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Zkrácený příklad; úplná verze je v public/index.production.php.
$deployRoot = '/home/html/kbelstisokoli.cz/public_html/secret/deploy';
$APP_BASE = realpath($deployRoot.'/current');

if ($APP_BASE === false || ! str_starts_with($APP_BASE.'/', $deployRoot.'/releases/')) {
    http_response_code(503);
    exit('Application release is unavailable.');
}

// Maintenance mode
if (file_exists($maintenance = $APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require $APP_BASE.'/vendor/autoload.php';

// Bootstrap
(require_once $APP_BASE.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

### .htaccess (standardní Laravel)

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Lokální vývoj (Localhost)

Na lokálním prostředí ponechte `PUBLIC_PATH_MODE=default` (nebo proměnnou vůbec nedefinujte). Projekt se bude chovat jako standardní Laravel aplikace a bude používat složku `public/` uvnitř projektu.

## Build

Produkční build spouští pouze GitHub Actions na Node.js 22. Na Webglobe se Node.js ani NPM pro běžné nasazení nepoužívá.
