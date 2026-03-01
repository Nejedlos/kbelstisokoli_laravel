# Konfigurace externího veřejného adresáře (Public Root)

Tento dokument popisuje nastavení projektu pro provoz s veřejným adresářem (Document Root), který se nachází mimo hlavní adresář aplikace. Toto nastavení je typické pro hosting Webglobe, kde subdomény míří do specifických složek.

## Architektura na produkci

- **Kód aplikace:** `/home/html/kbelstisokoli.cz/public_html/secret`
- **Veřejný adresář:** `/home/html/kbelstisokoli.cz/public_html/subdomains/new`

## Konfigurace prostředí (.env)

Pro aktivaci tohoto režimu jsou v `.env` (nebo v `.env.production`) klíčové tyto proměnné:

```env
# Mód veřejné cesty: 'default' (místní /public) nebo 'external' (vzdálená složka)
PUBLIC_PATH_MODE=external

# Absolutní cesta k veřejnému adresáři na serveru
PROD_PUBLIC_PATH="/home/html/kbelstisokoli.cz/public_html/subdomains/new"

# Původní proměnná (ponechána pro kompatibilitu s jinými skripty)
APP_PUBLIC_PATH="/home/html/kbelstisokoli.cz/public_html/subdomains/new"
```

## Jak to funguje

1.  **Laravel bootstrap (`bootstrap/app.php`):**
    - Pokud je `PUBLIC_PATH_MODE=external`, Laravel přenastaví svou `public_path()` pomocí `$app->usePublicPath()` a bindem do kontejneru (`path.public`).
    - To zajistí, že funkce `public_path()`, disk `public_path` ve filesystému i helper `@vite()` budou mířit do externí složky.

2.  **Vite build (`vite.config.js`):**
    - Při sestavování assetů (`npm run build`) Vite detekuje `PUBLIC_PATH_MODE=external`.
    - Pokud je aktivní, nastaví `publicDirectory` na `../subdomains/new`.
    - Výsledný build se pak uloží do `/subdomains/new/build`.

3.  **Vstupní bod (`index.php`):**
    - V externí složce `/subdomains/new` musí být `index.php`, který správně načte aplikaci ze složky `/secret`.

## Soubory pro externí složku

Tyto soubory by měly být umístěny v `/home/html/kbelstisokoli.cz/public_html/subdomains/new/`:

### index.php

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Cesta k aplikaci
$APP_BASE = __DIR__ . '/../../secret';

// Maintenance mode
if (file_exists($maintenance = $APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require $APP_BASE.'/vendor/autoload.php';

// Bootstrap
$app = require_once $APP_BASE.'/bootstrap/app.php';

// Spuštění
$app->handleRequest(Request::capture());
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

## Build příkaz

Na produkci spouštějte build standardně:
```bash
npm run build
```
Vite se postará o doručení souborů do správné složky na základě `.env` souboru.
