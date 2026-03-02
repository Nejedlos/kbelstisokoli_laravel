<?php $dir_name = isset($dir_name) ? $dir_name : null; ?>
<?php $dir = isset($dir) ? $dir : null; ?>
<?php $NODE_VERSION = isset($NODE_VERSION) ? $NODE_VERSION : null; ?>
<?php $PATH = isset($PATH) ? $PATH : null; ?>
<?php $NPM_BIN_PATH = isset($NPM_BIN_PATH) ? $NPM_BIN_PATH : null; ?>
<?php $N_VER = isset($N_VER) ? $N_VER : null; ?>
<?php $NODE_BIN_PATH = isset($NODE_BIN_PATH) ? $NODE_BIN_PATH : null; ?>
<?php $VER = isset($VER) ? $VER : null; ?>
<?php $n = isset($n) ? $n : null; ?>
<?php $maintenance = isset($maintenance) ? $maintenance : null; ?>
<?php $replacement = isset($replacement) ? $replacement : null; ?>
<?php $app = isset($app) ? $app : null; ?>
<?php $APP_BASE = isset($APP_BASE) ? $APP_BASE : null; ?>
<?php $c = isset($c) ? $c : null; ?>
<?php $f = isset($f) ? $f : null; ?>
<?php $files = isset($files) ? $files : null; ?>
<?php $dest = isset($dest) ? $dest : null; ?>
<?php $public = isset($public) ? $public : null; ?>
<?php $argv = isset($argv) ? $argv : null; ?>
<?php $base = isset($base) ? $base : null; ?>
<?php $DEST = isset($DEST) ? $DEST : null; ?>
<?php $COMPOSER_BIN = isset($COMPOSER_BIN) ? $COMPOSER_BIN : null; ?>
<?php $line = isset($line) ? $line : null; ?>
<?php $safeValue = isset($safeValue) ? $safeValue : null; ?>
<?php $found = isset($found) ? $found : null; ?>
<?php $value = isset($value) ? $value : null; ?>
<?php $key = isset($key) ? $key : null; ?>
<?php $vars = isset($vars) ? $vars : null; ?>
<?php $lines = isset($lines) ? $lines : null; ?>
<?php $envFile = isset($envFile) ? $envFile : null; ?>
<?php $PHP_VERSION = isset($PHP_VERSION) ? $PHP_VERSION : null; ?>
<?php $target_public = isset($target_public) ? $target_public : null; ?>
<?php $fontawesome_token = isset($fontawesome_token) ? $fontawesome_token : null; ?>
<?php $noai = isset($noai) ? $noai : null; ?>
<?php $usersync = isset($usersync) ? $usersync : null; ?>
<?php $freshseed = isset($freshseed) ? $freshseed : null; ?>
<?php $public_path = isset($public_path) ? $public_path : null; ?>
<?php $public_path_b64 = isset($public_path_b64) ? $public_path_b64 : null; ?>
<?php $db_prefix = isset($db_prefix) ? $db_prefix : null; ?>
<?php $db_prefix_b64 = isset($db_prefix_b64) ? $db_prefix_b64 : null; ?>
<?php $db_password = isset($db_password) ? $db_password : null; ?>
<?php $db_password_b64 = isset($db_password_b64) ? $db_password_b64 : null; ?>
<?php $db_username = isset($db_username) ? $db_username : null; ?>
<?php $db_username_b64 = isset($db_username_b64) ? $db_username_b64 : null; ?>
<?php $db_database = isset($db_database) ? $db_database : null; ?>
<?php $db_database_b64 = isset($db_database_b64) ? $db_database_b64 : null; ?>
<?php $db_port = isset($db_port) ? $db_port : null; ?>
<?php $db_port_b64 = isset($db_port_b64) ? $db_port_b64 : null; ?>
<?php $db_host = isset($db_host) ? $db_host : null; ?>
<?php $db_host_b64 = isset($db_host_b64) ? $db_host_b64 : null; ?>
<?php $db_connection = isset($db_connection) ? $db_connection : null; ?>
<?php $db_connection_b64 = isset($db_connection_b64) ? $db_connection_b64 : null; ?>
<?php $npm = isset($npm) ? $npm : null; ?>
<?php $node = isset($node) ? $node : null; ?>
<?php $php = isset($php) ? $php : null; ?>
<?php $path = isset($path) ? $path : null; ?>
<?php $token = isset($token) ? $token : null; ?>
<?php $repository = isset($repository) ? $repository : null; ?>
<?php $port = isset($port) ? $port : null; ?>
<?php $host = isset($host) ? $host : null; ?>
<?php $user = isset($user) ? $user : null; ?>
<?php $__container->servers(['web' => $user . '@' . $host . ($port ? ' -p ' . $port : '') . ' -o StrictHostKeyChecking=no']); ?>

<?php
    $repository = isset($repository) ? $repository : 'https://' . $token . '@github.com/Nejedlos/kbelstisokoli_laravel.git';
    $path = isset($path) ? $path : '/www/kbelstisokoli';
    $php = isset($php) ? $php : 'php';
    $node = isset($node) ? $node : 'node';
    $npm = isset($npm) ? $npm : 'npm';

    $db_connection_b64 = base64_encode(isset($db_connection) ? $db_connection : 'mysql');
    $db_host_b64 = base64_encode(isset($db_host) ? $db_host : '127.0.0.1');
    $db_port_b64 = base64_encode(isset($db_port) ? $db_port : '3306');
    $db_database_b64 = base64_encode(isset($db_database) ? $db_database : '');
    $db_username_b64 = base64_encode(isset($db_username) ? $db_username : '');
    $db_password_b64 = base64_encode(isset($db_password) ? $db_password : '');
    $db_prefix_b64 = base64_encode(isset($db_prefix) ? $db_prefix : '');
    $public_path_b64 = base64_encode(isset($public_path) ? $public_path : '');
    $freshseed = isset($freshseed) ? $freshseed : false;
    $usersync = isset($usersync) ? $usersync : false;
    $noai = isset($noai) ? $noai : false;
    $fontawesome_token = isset($fontawesome_token) ? $fontawesome_token : '';
    $target_public = isset($public_path) ? $public_path : ($path . '/public');
?>

<?php $__container->startTask('setup', ['on' => 'web']); ?>
    echo "🚀 Starting setup on <?php echo $host; ?>..."

    PHP_VERSION=$(<?php echo $php; ?> -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using <?php echo $php; ?>)"
        exit 1
    fi

    if [ ! -d "<?php echo $path; ?>" ]; then
        echo "Creating directory <?php echo $path; ?>..."
        mkdir -p "<?php echo $path; ?>"
    fi

    cd <?php echo $path; ?>


    if [ ! -d ".git" ]; then
        echo "Cloning repository..."
        git clone <?php echo $repository; ?> .
    else
        echo "Repository already exists, updating URL with token..."
        git remote set-url origin <?php echo $repository; ?>

        if [ -f ".git/gc.log" ]; then
            echo "Removing .git/gc.log..."
            rm .git/gc.log
        fi
        git prune
        git fetch origin main
        git reset --hard origin/main
        git clean -df
    fi

    echo "Preparing .env file..."
    if [ ! -f ".env" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
    fi

    echo "Updating .env configuration..."
    <?php echo $php; ?> -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [
            "APP_ENV" => "production",
            "APP_DEBUG" => "false",
        ];
        if ("<?php echo $db_database_b64; ?>") {
            $vars["DB_CONNECTION"] = base64_decode("<?php echo $db_connection_b64; ?>");
            $vars["DB_HOST"] = base64_decode("<?php echo $db_host_b64; ?>");
            $vars["DB_PORT"] = base64_decode("<?php echo $db_port_b64; ?>");
            $vars["DB_DATABASE"] = base64_decode("<?php echo $db_database_b64; ?>");
            $vars["DB_USERNAME"] = base64_decode("<?php echo $db_username_b64; ?>");
            $vars["DB_PASSWORD"] = base64_decode("<?php echo $db_password_b64; ?>");
            if ("<?php echo $db_prefix_b64; ?>") {
                $vars["DB_PREFIX"] = base64_decode("<?php echo $db_prefix_b64; ?>");
            }
        }
        if ("<?php echo $public_path_b64; ?>") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("<?php echo $public_path_b64; ?>");
        }
        foreach ($vars as $key => $value) {
            $found = false;
            $safeValue = str_replace(["\\", "\"", "$"], ["\\\\", "\\\"", "\\$"], $value);
            foreach ($lines as &$line) {
                if (strpos(trim($line), "$key=") === 0) {
                    $line = "$key=\"$safeValue\"";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $lines[] = "$key=\"$safeValue\"";
            }
        }
        file_put_contents($envFile, implode("\n", $lines) . "\n");
    '
    echo "✅ .env updated."

    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generating APP_KEY..."
        <?php echo $php; ?> artisan key:generate --no-interaction
    fi

    echo "Running composer install..."
    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php
    <?php echo $php; ?> $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    if [ ! -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] && [ "<?php echo isset($public_path) ? $public_path : ''; ?>" != "<?php echo $path; ?>/public" ]; then
        echo "Ensuring custom public path is configured: <?php echo $public_path; ?>"
        if [ ! -L "<?php echo $public_path; ?>" ] && [ ! -d "<?php echo $public_path; ?>" ]; then
            ln -sf "<?php echo $path; ?>/public" "<?php echo $public_path; ?>"
            echo "✅ Created symlink from <?php echo $path; ?>/public to <?php echo $public_path; ?>"
        fi
    fi

    # Determine and patch entry point
    if [ -f "public/index.production.php" ]; then
        if [ -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] || [ "<?php echo isset($public_path) ? $public_path : ''; ?>" = "<?php echo $path; ?>/public" ]; then
            DEST="<?php echo $path; ?>/public/index.php"
        else
            DEST="<?php echo $public_path; ?>/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    else
        DEST="<?php echo $path; ?>/public/index.php"
    fi

    echo "Patching entry points for absolute paths..."
    cat << 'EOF' > patch_entrypoints.php
<?php
$base = $argv[1];
$public = $argv[2];
$dest = isset($argv[3]) ? $argv[3] : "";
$files = array_unique(array_filter([$base . "/public/index.php", $dest]));
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $c = preg_replace("/\\\$APP_BASE\\s*=\\s*.*?;/", "\$APP_BASE = \"" . $base . "\";", $c);
    $c = preg_replace("/require\\s+.*?vendor\/autoload\\.php.*?;/", "require \"" . $base . "/vendor/autoload.php\";", $c);
    $c = preg_replace("/\\\$app->usePublicPath\\(.*?\\);\\s*/", "", $c);
    $c = preg_replace("/define\\([\"']LARAVEL_PUBLIC_PATH[\"'].*?\\);\\s*/", "", $c);
    $replacement = "define(\"LARAVEL_PUBLIC_PATH\", \"" . $public . "\");\n            \$app = require_once \"" . $base . "/bootstrap/app.php\";\n            \$app->usePublicPath(\"" . $public . "\");";
    $c = preg_replace("/require_once\\s+.*?bootstrap\/app\\.php.*?[\"'];/", $replacement, $c);
    $c = preg_replace("/file_exists\\(\\s*\\\$maintenance\\s*=\\s*.*?storage\/framework\/maintenance\\.php.*?\\)/", "file_exists(\$maintenance = \"" . $base . "/storage/framework/maintenance.php\")", $c);
    file_put_contents($f, $c);
}
EOF
    <?php echo $php; ?> patch_entrypoints.php "<?php echo $path; ?>" "<?php echo $target_public; ?>" "$DEST"
    rm patch_entrypoints.php
    echo "✅ Entry points patched."

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "<?php echo $node; ?>" == /* ]]; then
        NODE_BIN_PATH="<?php echo $node; ?>"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a node22 node20 node18 "<?php echo $node; ?>" | grep -v "<?php echo $path; ?>/.node_bin"); do
            VER=$($n -v 2>/dev/null | sed "s/v//")
            if [ "$(printf "%s\n" "18.0.0" "$VER" | sort -V | head -n1)" = "18.0.0" ]; then
                NODE_BIN_PATH=$n
                break
            fi
        done
        if [ -z "$NODE_BIN_PATH" ]; then
            NODE_BIN_PATH=$(which -a "<?php echo $node; ?>" | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NODE_BIN_PATH" .node_bin/node

    # Symlink npm (using absolute path to avoid circularity)
    if [[ "<?php echo $npm; ?>" == /* ]]; then
        NPM_BIN_PATH="<?php echo $npm; ?>"
    else
        NPM_BIN_PATH=""
        # Try to find a matching npm if we have a specific node version (e.g. node20 -> npm20)
        N_VER=$(echo "$NODE_BIN_PATH" | sed -E 's/.*node([0-9]+).*/\1/' | grep -E '^[0-9]+$')
        if [ ! -z "$N_VER" ]; then
            NPM_BIN_PATH=$(which -a "npm$N_VER" 2>/dev/null | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi

        if [ -z "$NPM_BIN_PATH" ]; then
            NPM_BIN_PATH=$(which -a "<?php echo $npm; ?>" | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="<?php echo $path; ?>/.node_bin:$PATH"

    if [ ! -z "<?php echo $fontawesome_token; ?>" ]; then
        export FONTAWESOME_TOKEN="<?php echo $fontawesome_token; ?>"
    fi

    # Node.js version check
    NODE_VERSION=$(node -v | sed 's/v//')
    echo "Current Node version: $NODE_VERSION (from $(which node))"

    if [ "$(printf '%s\n' "18.0.0" "$NODE_VERSION" | sort -V | head -n1)" != "18.0.0" ]; then
        echo "❌ Error: Node.js version 18.0.0 or higher is required for Vite 6. Found: $NODE_VERSION"
        echo "Please re-run 'php artisan app:production:setup' to find a suitable Node.js binary."
        exit 1
    fi

    npm install

        echo "Building assets..."
        npm run build

    # Zajištění, aby build a assety byly v subdoméně, ale i pro PHP dostupné v public_path()
    if [ ! -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] && [ "<?php echo isset($public_path) ? $public_path : ''; ?>" != "<?php echo $path; ?>/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "<?php echo $public_path; ?>" ]; then
            cd <?php echo $path; ?>/public
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: <?php echo $public_path; ?>/$dir_name"
                rm -rf "<?php echo $public_path; ?>/$dir_name"
                mkdir -p "<?php echo $public_path; ?>/$dir_name"
                cp -rf "$dir_name"/. "<?php echo $public_path; ?>/$dir_name/"
            done

            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "<?php echo $public_path; ?>/" \;
        fi
    fi

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/logs
    chmod -R 775 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    echo "Running idempotent database migrations..."
    <?php echo $php; ?> artisan migrate --force

    echo "Running database seeding..."
    <?php echo $php; ?> artisan app:seed --force --no-interaction <?php echo $freshseed ? '--fresh' : ''; ?> <?php echo $usersync == "1" ? '--users' : ''; ?>


    if [ "<?php echo $usersync; ?>" = "1" ]; then
        echo "Syncing users (avatars) skipped (using FTP sync instead)..."
    fi

    echo "Syncing icons..."
    <?php echo $php; ?> artisan app:icons:sync
    <?php echo $php; ?> artisan filament:clear-cached-components
    <?php echo $php; ?> artisan cache:clear
    <?php echo $php; ?> artisan view:clear

    echo "Optimizing application..."
    <?php echo $php; ?> artisan optimize

    if [ "<?php echo $noai; ?>" != "1" ]; then
        echo "Reindexing AI..."
        <?php echo $php; ?> artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Setup finished successfully!"
<?php $__container->endTask(); ?>

<?php $__container->startTask('deploy', ['on' => 'web']); ?>
    echo "🚀 Deploying to <?php echo $host; ?>..."

    PHP_VERSION=$(<?php echo $php; ?> -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using <?php echo $php; ?>)"
        exit 1
    fi

    cd <?php echo $path; ?>


    git fetch origin main
    git reset --hard origin/main
    git clean -df
    if [ -f ".git/gc.log" ]; then
        rm .git/gc.log
    fi
    git prune

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/logs
    chmod -R 775 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    <?php echo $php; ?> $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    echo "Running idempotent database migrations..."
    <?php echo $php; ?> artisan migrate --force

    echo "Running database seeding..."
    <?php echo $php; ?> artisan app:seed --force --no-interaction <?php echo $freshseed ? '--fresh' : ''; ?> <?php echo $usersync == "1" ? '--users' : ''; ?>


    echo "Updating .env configuration..."
    <?php echo $php; ?> -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [];
        if ("<?php echo $db_database_b64; ?>") {
            $vars["DB_CONNECTION"] = base64_decode("<?php echo $db_connection_b64; ?>");
            $vars["DB_HOST"] = base64_decode("<?php echo $db_host_b64; ?>");
            $vars["DB_PORT"] = base64_decode("<?php echo $db_port_b64; ?>");
            $vars["DB_DATABASE"] = base64_decode("<?php echo $db_database_b64; ?>");
            $vars["DB_USERNAME"] = base64_decode("<?php echo $db_username_b64; ?>");
            $vars["DB_PASSWORD"] = base64_decode("<?php echo $db_password_b64; ?>");
            if ("<?php echo $db_prefix_b64; ?>") {
                $vars["DB_PREFIX"] = base64_decode("<?php echo $db_prefix_b64; ?>");
            }
        }
        if ("<?php echo $public_path_b64; ?>") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("<?php echo $public_path_b64; ?>");
        }
        foreach ($vars as $key => $value) {
            $found = false;
            $safeValue = str_replace(["\\", "\"", "$"], ["\\\\", "\\\"", "\\$"], $value);
            foreach ($lines as &$line) {
                if (strpos(trim($line), "$key=") === 0) {
                    $line = "$key=\"$safeValue\"";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $lines[] = "$key=\"$safeValue\"";
            }
        }
        file_put_contents($envFile, implode("\n", $lines) . "\n");
    '
    echo "✅ .env updated."

    if [ ! -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] && [ "<?php echo isset($public_path) ? $public_path : ''; ?>" != "<?php echo $path; ?>/public" ]; then
        echo "Ensuring custom public path is configured: <?php echo $public_path; ?>"
        if [ ! -L "<?php echo $public_path; ?>" ] && [ ! -d "<?php echo $public_path; ?>" ]; then
            ln -sf "<?php echo $path; ?>/public" "<?php echo $public_path; ?>"
            echo "✅ Created symlink from <?php echo $path; ?>/public to <?php echo $public_path; ?>"
        fi
    fi

    # Determine and patch entry point
    if [ -f "public/index.production.php" ]; then
        if [ -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] || [ "<?php echo isset($public_path) ? $public_path : ''; ?>" = "<?php echo $path; ?>/public" ]; then
            DEST="<?php echo $path; ?>/public/index.php"
        else
            DEST="<?php echo $public_path; ?>/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    else
        DEST="<?php echo $path; ?>/public/index.php"
    fi

    echo "Patching entry points for absolute paths..."
    cat << 'EOF' > patch_entrypoints.php
<?php
$base = $argv[1];
$public = $argv[2];
$dest = isset($argv[3]) ? $argv[3] : "";
$files = array_unique(array_filter([$base . "/public/index.php", $dest]));
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $c = preg_replace("/\\\$APP_BASE\\s*=\\s*.*?;/", "\$APP_BASE = \"" . $base . "\";", $c);
    $c = preg_replace("/require\\s+.*?vendor\/autoload\\.php.*?;/", "require \"" . $base . "/vendor/autoload.php\";", $c);
    $c = preg_replace("/\\\$app->usePublicPath\\(.*?\\);\\s*/", "", $c);
    $c = preg_replace("/define\\([\"']LARAVEL_PUBLIC_PATH[\"'].*?\\);\\s*/", "", $c);
    $replacement = "define(\"LARAVEL_PUBLIC_PATH\", \"" . $public . "\");\n            \$app = require_once \"" . $base . "/bootstrap/app.php\";\n            \$app->usePublicPath(\"" . $public . "\");";
    $c = preg_replace("/require_once\\s+.*?bootstrap\/app\\.php.*?[\"'];/", $replacement, $c);
    $c = preg_replace("/file_exists\\(\\s*\\\$maintenance\\s*=\\s*.*?storage\/framework\/maintenance\\.php.*?\\)/", "file_exists(\$maintenance = \"" . $base . "/storage/framework/maintenance.php\")", $c);
    file_put_contents($f, $c);
}
EOF
    <?php echo $php; ?> patch_entrypoints.php "<?php echo $path; ?>" "<?php echo $target_public; ?>" "$DEST"
    rm patch_entrypoints.php
    echo "✅ Entry points patched."

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "<?php echo $node; ?>" == /* ]]; then
        NODE_BIN_PATH="<?php echo $node; ?>"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a node22 node20 node18 "<?php echo $node; ?>" | grep -v "<?php echo $path; ?>/.node_bin"); do
            VER=$($n -v 2>/dev/null | sed "s/v//")
            if [ "$(printf "%s\n" "18.0.0" "$VER" | sort -V | head -n1)" = "18.0.0" ]; then
                NODE_BIN_PATH=$n
                break
            fi
        done
        if [ -z "$NODE_BIN_PATH" ]; then
            NODE_BIN_PATH=$(which -a "<?php echo $node; ?>" | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NODE_BIN_PATH" .node_bin/node

    # Symlink npm (using absolute path to avoid circularity)
    if [[ "<?php echo $npm; ?>" == /* ]]; then
        NPM_BIN_PATH="<?php echo $npm; ?>"
    else
        NPM_BIN_PATH=""
        # Try to find a matching npm if we have a specific node version (e.g. node20 -> npm20)
        N_VER=$(echo "$NODE_BIN_PATH" | sed -E 's/.*node([0-9]+).*/\1/' | grep -E '^[0-9]+$')
        if [ ! -z "$N_VER" ]; then
            NPM_BIN_PATH=$(which -a "npm$N_VER" 2>/dev/null | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi

        if [ -z "$NPM_BIN_PATH" ]; then
            NPM_BIN_PATH=$(which -a "<?php echo $npm; ?>" | grep -v "<?php echo $path; ?>/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="<?php echo $path; ?>/.node_bin:$PATH"

    if [ ! -z "<?php echo $fontawesome_token; ?>" ]; then
        export FONTAWESOME_TOKEN="<?php echo $fontawesome_token; ?>"
    fi

    # Node.js version check
    NODE_VERSION=$(node -v | sed 's/v//')
    echo "Current Node version: $NODE_VERSION (from $(which node))"

    if [ "$(printf '%s\n' "18.0.0" "$NODE_VERSION" | sort -V | head -n1)" != "18.0.0" ]; then
        echo "❌ Error: Node.js version 18.0.0 or higher is required for Vite 6. Found: $NODE_VERSION"
        echo "Please re-run 'php artisan app:production:setup' to find a suitable Node.js binary."
        exit 1
    fi

    npm install

    echo "Building assets..."
    npm run build

    # Zajištění, aby build a assety byly v subdoméně
    if [ ! -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] && [ "<?php echo isset($public_path) ? $public_path : ''; ?>" != "<?php echo $path; ?>/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "<?php echo $public_path; ?>" ]; then
            cd <?php echo $path; ?>/public
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: <?php echo $public_path; ?>/$dir_name"
                rm -rf "<?php echo $public_path; ?>/$dir_name"
                mkdir -p "<?php echo $public_path; ?>/$dir_name"
                cp -rf "$dir_name"/. "<?php echo $public_path; ?>/$dir_name/"
            done

            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "<?php echo $public_path; ?>/" \;
        fi
    fi

    <?php echo $php; ?> artisan app:icons:sync
    <?php echo $php; ?> artisan filament:clear-cached-components
    <?php echo $php; ?> artisan cache:clear
    <?php echo $php; ?> artisan view:clear
    <?php echo $php; ?> artisan optimize

    if [ "<?php echo $noai; ?>" != "1" ]; then
        echo "Reindexing AI..."
        <?php echo $php; ?> artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Deployment finished successfully!"
<?php $__container->endTask(); ?>

<?php $__container->startTask('sync', ['on' => 'web']); ?>
    echo "🚀 Syncing configuration and running migrations on <?php echo $host; ?>..."

    PHP_VERSION=$(<?php echo $php; ?> -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using <?php echo $php; ?>)"
        exit 1
    fi

    cd <?php echo $path; ?>


    echo "Preparing .env file..."
    if [ ! -f ".env" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
    fi

    echo "Updating .env configuration..."
    <?php echo $php; ?> -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [
            "APP_ENV" => "production",
            "APP_DEBUG" => "false",
        ];
        if ("<?php echo $db_database_b64; ?>") {
            $vars["DB_CONNECTION"] = base64_decode("<?php echo $db_connection_b64; ?>");
            $vars["DB_HOST"] = base64_decode("<?php echo $db_host_b64; ?>");
            $vars["DB_PORT"] = base64_decode("<?php echo $db_port_b64; ?>");
            $vars["DB_DATABASE"] = base64_decode("<?php echo $db_database_b64; ?>");
            $vars["DB_USERNAME"] = base64_decode("<?php echo $db_username_b64; ?>");
            $vars["DB_PASSWORD"] = base64_decode("<?php echo $db_password_b64; ?>");
            if ("<?php echo $db_prefix_b64; ?>") {
                $vars["DB_PREFIX"] = base64_decode("<?php echo $db_prefix_b64; ?>");
            }
        }
        if ("<?php echo $public_path_b64; ?>") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("<?php echo $public_path_b64; ?>");
        }
        foreach ($vars as $key => $value) {
            $found = false;
            $safeValue = str_replace(["\\", "\"", "$"], ["\\\\", "\\\"", "\\$"], $value);
            foreach ($lines as &$line) {
                if (strpos(trim($line), "$key=") === 0) {
                    $line = "$key=\"$safeValue\"";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $lines[] = "$key=\"$safeValue\"";
            }
        }
        file_put_contents($envFile, implode("\n", $lines) . "\n");
    '
    echo "✅ .env updated."

    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generating APP_KEY..."
        <?php echo $php; ?> artisan key:generate --no-interaction
    fi

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/logs
    chmod -R 775 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    # Dynamická synchronizace všech adresářů z public/ do public_path (kromě storage)
    if [ ! -z "<?php echo isset($public_path) ? $public_path : ''; ?>" ] && [ "<?php echo isset($public_path) ? $public_path : ''; ?>" != "<?php echo $path; ?>/public" ]; then
        if [ ! -L "<?php echo $public_path; ?>" ]; then
            cd <?php echo $path; ?>/public
            # Najdeme všechny skutečné adresáře v public/
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: <?php echo $public_path; ?>/$dir_name"
                rm -rf "<?php echo $public_path; ?>/$dir_name"
                mkdir -p "<?php echo $public_path; ?>/$dir_name"
                # Kopírování obsahu včetně skrytých souborů
                cp -rf "$dir_name"/. "<?php echo $public_path; ?>/$dir_name/"
            done

            # Také zkopírovat jednotlivé soubory v public/ (všechny, ne jen vybrané přípony)
            # Vynecháme index.php a index.production.php, které jsou řešeny patchováním/nahrazením
            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "<?php echo $public_path; ?>/" \;
        fi

        echo "Patching entry points for absolute paths..."
        cat << 'EOF' > patch_entrypoints.php
<?php
$base = $argv[1];
$public = $argv[2];
$dest = isset($argv[3]) ? $argv[3] : "";
$files = array_unique(array_filter([$base . "/public/index.php", $dest]));
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $c = preg_replace("/\\\$APP_BASE\\s*=\\s*.*?;/", "\$APP_BASE = \"" . $base . "\";", $c);
    $c = preg_replace("/require\\s+.*?vendor\/autoload\\.php.*?;/", "require \"" . $base . "/vendor/autoload.php\";", $c);
    $c = preg_replace("/\\\$app->usePublicPath\\(.*?\\);\\s*/", "", $c);
    $c = preg_replace("/define\\([\"']LARAVEL_PUBLIC_PATH[\"'].*?\\);\\s*/", "", $c);
    $replacement = "define(\"LARAVEL_PUBLIC_PATH\", \"" . $public . "\");\n            \$app = require_once \"" . $base . "/bootstrap/app.php\";\n            \$app->usePublicPath(\"" . $public . "\");";
    $c = preg_replace("/require_once\\s+.*?bootstrap\/app\\.php.*?[\"'];/", $replacement, $c);
    $c = preg_replace("/file_exists\\(\\s*\\\$maintenance\\s*=\\s*.*?storage\/framework\/maintenance\\.php.*?\\)/", "file_exists(\$maintenance = \"" . $base . "/storage/framework/maintenance.php\")", $c);
    file_put_contents($f, $c);
}
EOF
        <?php echo $php; ?> patch_entrypoints.php "<?php echo $path; ?>" "<?php echo $target_public; ?>" "<?php echo $target_public; ?>/index.php"
        rm patch_entrypoints.php
        echo "✅ Entry points patched."
    fi

    echo "Running idempotent database migrations..."
    <?php echo $php; ?> artisan migrate --force

    echo "Running database seeding..."
    <?php echo $php; ?> artisan app:seed --force --no-interaction <?php echo $freshseed ? '--fresh' : ''; ?> <?php echo $usersync == "1" ? '--users' : ''; ?>


    if [ "<?php echo $usersync; ?>" = "1" ]; then
        echo "Syncing users (avatars) skipped (using FTP sync instead)..."
    fi

    echo "Syncing icons..."
    <?php echo $php; ?> artisan app:icons:sync
    <?php echo $php; ?> artisan filament:clear-cached-components
    <?php echo $php; ?> artisan cache:clear
    <?php echo $php; ?> artisan view:clear

    echo "Optimizing application..."
    <?php echo $php; ?> artisan optimize

    if [ "<?php echo $noai; ?>" != "1" ]; then
        echo "Reindexing AI..."
        <?php echo $php; ?> artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Sync finished successfully!"
<?php $__container->endTask(); ?>

<?php $__container->startTask('status', ['on' => 'web']); ?>
    cd <?php echo $path; ?>

    <?php echo $php; ?> artisan --version
    git log -1
<?php $__container->endTask(); ?>
