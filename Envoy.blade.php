@servers(['web' => $user . '@' . $host . ($port ? ' -p ' . $port : '') . ' -o StrictHostKeyChecking=no'])

@setup
    $repository = $repository ?? 'https://' . $token . '@github.com/Nejedlos/kbelstisokoli_laravel.git';
    $path = $path ?? '/www/kbelstisokoli';
    $php = $php ?? 'php';
    $node = $node ?? 'node';
    $npm = $npm ?? 'npm';

    $db_connection_b64 = base64_encode($db_connection ?? 'mysql');
    $db_host_b64 = base64_encode($db_host ?? '127.0.0.1');
    $db_port_b64 = base64_encode($db_port ?? '3306');
    $db_database_b64 = base64_encode($db_database ?? '');
    $db_username_b64 = base64_encode($db_username ?? '');
    $db_password_b64 = base64_encode($db_password ?? '');
    $db_prefix_b64 = base64_encode($db_prefix ?? '');
    $public_path_b64 = base64_encode($public_path ?? '');
    $freshseed = $freshseed ?? false;
    $usersync = $usersync ?? false;
    $noai = $noai ?? false;
    $fontawesome_token = $fontawesome_token ?? '';
@endsetup

@task('setup', ['on' => 'web'])
    echo "🚀 Starting setup on {{ $host }}..."

    PHP_VERSION=$({{ $php }} -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using {{ $php }})"
        exit 1
    fi

    if [ ! -d "{{ $path }}" ]; then
        echo "Creating directory {{ $path }}..."
        mkdir -p "{{ $path }}"
    fi

    cd {{ $path }}

    if [ ! -d ".git" ]; then
        echo "Cloning repository..."
        git clone {{ $repository }} .
    else
        echo "Repository already exists, updating URL with token..."
        git remote set-url origin {{ $repository }}
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
    {{ $php }} -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [
            "APP_ENV" => "production",
            "APP_DEBUG" => "false",
        ];
        if ("{{ $db_database_b64 }}") {
            $vars["DB_CONNECTION"] = base64_decode("{{ $db_connection_b64 }}");
            $vars["DB_HOST"] = base64_decode("{{ $db_host_b64 }}");
            $vars["DB_PORT"] = base64_decode("{{ $db_port_b64 }}");
            $vars["DB_DATABASE"] = base64_decode("{{ $db_database_b64 }}");
            $vars["DB_USERNAME"] = base64_decode("{{ $db_username_b64 }}");
            $vars["DB_PASSWORD"] = base64_decode("{{ $db_password_b64 }}");
            if ("{{ $db_prefix_b64 }}") {
                $vars["DB_PREFIX"] = base64_decode("{{ $db_prefix_b64 }}");
            }
        }
        if ("{{ $public_path_b64 }}") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("{{ $public_path_b64 }}");
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
        {{ $php }} artisan key:generate --no-interaction
    fi

    echo "Running composer install..."
    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is configured: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ ! -d "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi
    fi

    # Determine and patch entry point
    if [ -f "public/index.production.php" ]; then
        if [ -z "{{ $public_path ?? '' }}" ] || [ "{{ $public_path }}" = "{{ $path }}/public" ]; then
            DEST="{{ $path }}/public/index.php"
        else
            DEST="{{ $public_path }}/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    else
        DEST="{{ $path }}/public/index.php"
    fi

    echo "Patching entry points for absolute paths..."
    {{ $php }} -r '
        $targets = ["{{ $path }}/public/index.php", "'$DEST'"];
        $base = "{{ $path }}";
        $public = "{{ $public_path ?? $path . "/public" }}";

        foreach (array_unique($targets) as $target) {
            if (!file_exists($target)) continue;
            $content = file_get_contents($target);

            // 1. Fix $APP_BASE if exists
            $content = preg_replace("/\\\$APP_BASE\\s*=\\s*['\"].*?['\"];/", "\$APP_BASE = \"$base\";", $content);

            // 2. Fix autoload.php reference
            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            // 3. Fix bootstrap/app.php reference and ensure LARAVEL_PUBLIC_PATH is defined
            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);
            $content = preg_replace("/define\('LARAVEL_PUBLIC_PATH'.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                "define(\"LARAVEL_PUBLIC_PATH\", \"$public\");\n            \$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(\"$public\");",
                $content
            );

            // 4. Fix maintenance mode path
            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($target, $content);
        }
    '
    echo "✅ Entry points patched."

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a node22 node20 node18 "{{ $node }}" | grep -v "{{ $path }}/.node_bin"); do
            VER=$($n -v 2>/dev/null | sed "s/v//")
            if [ "$(printf "%s\n" "18.0.0" "$VER" | sort -V | head -n1)" = "18.0.0" ]; then
                NODE_BIN_PATH=$n
                break
            fi
        done
        if [ -z "$NODE_BIN_PATH" ]; then
            NODE_BIN_PATH=$(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NODE_BIN_PATH" .node_bin/node

    # Symlink npm (using absolute path to avoid circularity)
    if [[ "{{ $npm }}" == /* ]]; then
        NPM_BIN_PATH="{{ $npm }}"
    else
        NPM_BIN_PATH=""
        # Try to find a matching npm if we have a specific node version (e.g. node20 -> npm20)
        N_VER=$(echo "$NODE_BIN_PATH" | sed -E 's/.*node([0-9]+).*/\1/' | grep -E '^[0-9]+$')
        if [ ! -z "$N_VER" ]; then
            NPM_BIN_PATH=$(which -a "npm$N_VER" 2>/dev/null | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi

        if [ -z "$NPM_BIN_PATH" ]; then
            NPM_BIN_PATH=$(which -a "{{ $npm }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="{{ $path }}/.node_bin:$PATH"

    if [ ! -z "{{ $fontawesome_token }}" ]; then
        export FONTAWESOME_TOKEN="{{ $fontawesome_token }}"
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
    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                rm -rf "{{ $public_path }}/$dir_name"
                mkdir -p "{{ $public_path }}/$dir_name"
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
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
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction {{ $freshseed ? '--fresh' : '' }} {{ $usersync == "1" ? '--users' : '' }}

    if [ "{{ $usersync }}" = "1" ]; then
        echo "Syncing users (avatars) skipped (using FTP sync instead)..."
    fi

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:clear

    echo "Optimizing application..."
    {{ $php }} artisan optimize

    if [ "{{ $noai }}" != "1" ]; then
        echo "Reindexing AI..."
        {{ $php }} artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Setup finished successfully!"
@endtask

@task('deploy', ['on' => 'web'])
    echo "🚀 Deploying to {{ $host }}..."

    PHP_VERSION=$({{ $php }} -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using {{ $php }})"
        exit 1
    fi

    cd {{ $path }}

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
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    echo "Running idempotent database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction {{ $freshseed ? '--fresh' : '' }} {{ $usersync == "1" ? '--users' : '' }}

    echo "Updating .env configuration..."
    {{ $php }} -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [];
        if ("{{ $db_database_b64 }}") {
            $vars["DB_CONNECTION"] = base64_decode("{{ $db_connection_b64 }}");
            $vars["DB_HOST"] = base64_decode("{{ $db_host_b64 }}");
            $vars["DB_PORT"] = base64_decode("{{ $db_port_b64 }}");
            $vars["DB_DATABASE"] = base64_decode("{{ $db_database_b64 }}");
            $vars["DB_USERNAME"] = base64_decode("{{ $db_username_b64 }}");
            $vars["DB_PASSWORD"] = base64_decode("{{ $db_password_b64 }}");
            if ("{{ $db_prefix_b64 }}") {
                $vars["DB_PREFIX"] = base64_decode("{{ $db_prefix_b64 }}");
            }
        }
        if ("{{ $public_path_b64 }}") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("{{ $public_path_b64 }}");
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

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is configured: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ ! -d "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi
    fi

    # Determine and patch entry point
    if [ -f "public/index.production.php" ]; then
        if [ -z "{{ $public_path ?? '' }}" ] || [ "{{ $public_path }}" = "{{ $path }}/public" ]; then
            DEST="{{ $path }}/public/index.php"
        else
            DEST="{{ $public_path }}/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    else
        DEST="{{ $path }}/public/index.php"
    fi

    echo "Patching entry points for absolute paths..."
    {{ $php }} -r '
        $targets = ["{{ $path }}/public/index.php", "'$DEST'"];
        $base = "{{ $path }}";
        $public = "{{ $public_path ?? $path . "/public" }}";

        foreach (array_unique($targets) as $target) {
            if (!file_exists($target)) continue;
            $content = file_get_contents($target);

            // 1. Fix $APP_BASE if exists
            $content = preg_replace("/\\\$APP_BASE\\s*=\\s*['\"].*?['\"];/", "\$APP_BASE = \"$base\";", $content);

            // 2. Fix autoload.php reference
            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            // 3. Fix bootstrap/app.php reference and ensure LARAVEL_PUBLIC_PATH is defined
            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);
            $content = preg_replace("/define\('LARAVEL_PUBLIC_PATH'.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                "define(\"LARAVEL_PUBLIC_PATH\", \"$public\");\n            \$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(\"$public\");",
                $content
            );

            // 4. Fix maintenance mode path
            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($target, $content);
        }
    '
    echo "✅ Entry points patched."

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a node22 node20 node18 "{{ $node }}" | grep -v "{{ $path }}/.node_bin"); do
            VER=$($n -v 2>/dev/null | sed "s/v//")
            if [ "$(printf "%s\n" "18.0.0" "$VER" | sort -V | head -n1)" = "18.0.0" ]; then
                NODE_BIN_PATH=$n
                break
            fi
        done
        if [ -z "$NODE_BIN_PATH" ]; then
            NODE_BIN_PATH=$(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NODE_BIN_PATH" .node_bin/node

    # Symlink npm (using absolute path to avoid circularity)
    if [[ "{{ $npm }}" == /* ]]; then
        NPM_BIN_PATH="{{ $npm }}"
    else
        NPM_BIN_PATH=""
        # Try to find a matching npm if we have a specific node version (e.g. node20 -> npm20)
        N_VER=$(echo "$NODE_BIN_PATH" | sed -E 's/.*node([0-9]+).*/\1/' | grep -E '^[0-9]+$')
        if [ ! -z "$N_VER" ]; then
            NPM_BIN_PATH=$(which -a "npm$N_VER" 2>/dev/null | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi

        if [ -z "$NPM_BIN_PATH" ]; then
            NPM_BIN_PATH=$(which -a "{{ $npm }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
        fi
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="{{ $path }}/.node_bin:$PATH"

    if [ ! -z "{{ $fontawesome_token }}" ]; then
        export FONTAWESOME_TOKEN="{{ $fontawesome_token }}"
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
    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                rm -rf "{{ $public_path }}/$dir_name"
                mkdir -p "{{ $public_path }}/$dir_name"
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
        fi
    fi

    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:clear
    {{ $php }} artisan optimize

    if [ "{{ $noai }}" != "1" ]; then
        echo "Reindexing AI..."
        {{ $php }} artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Deployment finished successfully!"
@endtask

@task('sync', ['on' => 'web'])
    echo "🚀 Syncing configuration and running migrations on {{ $host }}..."

    PHP_VERSION=$({{ $php }} -r 'echo PHP_VERSION;')
    if [ "$(printf '%s\n' "8.4.0" "$PHP_VERSION" | sort -V | head -n1)" != "8.4.0" ]; then
        echo "❌ Error: PHP version 8.4.0 or higher is required. Found: $PHP_VERSION (using {{ $php }})"
        exit 1
    fi

    cd {{ $path }}

    echo "Preparing .env file..."
    if [ ! -f ".env" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
    fi

    echo "Updating .env configuration..."
    {{ $php }} -r '
        $envFile = ".env";
        if (!file_exists($envFile)) { exit(0); }
        $lines = explode("\n", trim(file_get_contents($envFile)));
        $vars = [
            "APP_ENV" => "production",
            "APP_DEBUG" => "false",
        ];
        if ("{{ $db_database_b64 }}") {
            $vars["DB_CONNECTION"] = base64_decode("{{ $db_connection_b64 }}");
            $vars["DB_HOST"] = base64_decode("{{ $db_host_b64 }}");
            $vars["DB_PORT"] = base64_decode("{{ $db_port_b64 }}");
            $vars["DB_DATABASE"] = base64_decode("{{ $db_database_b64 }}");
            $vars["DB_USERNAME"] = base64_decode("{{ $db_username_b64 }}");
            $vars["DB_PASSWORD"] = base64_decode("{{ $db_password_b64 }}");
            if ("{{ $db_prefix_b64 }}") {
                $vars["DB_PREFIX"] = base64_decode("{{ $db_prefix_b64 }}");
            }
        }
        if ("{{ $public_path_b64 }}") {
            $vars["APP_PUBLIC_PATH"] = base64_decode("{{ $public_path_b64 }}");
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
        {{ $php }} artisan key:generate --no-interaction
    fi

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/logs
    chmod -R 775 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    # Dynamická synchronizace všech adresářů z public/ do public_path (kromě storage)
    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            # Najdeme všechny skutečné adresáře v public/
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                rm -rf "{{ $public_path }}/$dir_name"
                mkdir -p "{{ $public_path }}/$dir_name"
                # Kopírování obsahu včetně skrytých souborů
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            # Také zkopírovat jednotlivé soubory v public/ (všechny, ne jen vybrané přípony)
            # Vynecháme index.php a index.production.php, které jsou řešeny patchováním/nahrazením
            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
        fi

        echo "Patching entry points for absolute paths..."
        {{ $php }} -r '
            $targets = ["{{ $path }}/public/index.php"];
            if ("{{ $public_path ?? "" }}" && !is_link("{{ $public_path }}") && file_exists("{{ $public_path }}/index.php")) {
                $targets[] = "{{ $public_path }}/index.php";
            }
            $base = "{{ $path }}";
            $public = "{{ $public_path ?? $path . "/public" }}";

            foreach (array_unique($targets) as $target) {
                if (!file_exists($target)) continue;
                $content = file_get_contents($target);

                $content = preg_replace("/\\\$APP_BASE\\s*=\\s*['\"].*?['\"];/", "\$APP_BASE = \"$base\";", $content);

                $content = preg_replace(
                    "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                    "require \"$base/vendor/autoload.php\";",
                    $content
                );

                $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);
                $content = preg_replace("/define\('LARAVEL_PUBLIC_PATH'.*?\);\\s*/", "", $content);

                $content = preg_replace(
                    "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                    "define(\"LARAVEL_PUBLIC_PATH\", \"$public\");\n            \$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(\"$public\");",
                    $content
                );

                $content = preg_replace(
                    "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                    "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                    $content
                );

                file_put_contents($target, $content);
            }
        '
        echo "✅ Entry points patched."
    fi

    echo "Running idempotent database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction {{ $freshseed ? '--fresh' : '' }} {{ $usersync == "1" ? '--users' : '' }}

    if [ "{{ $usersync }}" = "1" ]; then
        echo "Syncing users (avatars) skipped (using FTP sync instead)..."
    fi

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:clear

    echo "Optimizing application..."
    {{ $php }} artisan optimize

    if [ "{{ $noai }}" != "1" ]; then
        echo "Reindexing AI..."
        {{ $php }} artisan ai:index --locale=all --enrich --no-interaction
    fi

    echo "✅ Sync finished successfully!"
@endtask

@task('status', ['on' => 'web'])
    cd {{ $path }}
    {{ $php }} artisan --version
    git log -1
@endtask
