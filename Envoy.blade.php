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

    if [ ! -z "{{ $public_path ?? '' }}" ]; then
        echo "Ensuring custom public path is linked: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ -d "{{ $public_path }}" ]; then
            echo "Moving existing public files from {{ $public_path }} back to {{ $path }}/public..."
            # Prevent moving public files if they are already there (e.g., if paths are same)
            if [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
                cp -rn "{{ $public_path }}"/* public/ 2>/dev/null || true
                rm -rf "{{ $public_path }}"
            fi
        fi

        if [ ! -L "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi

        echo "Patching index.php in project root for absolute paths..."
        {{ $php }} -r '
            $path = "{{ $path }}/public/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            // 1. Fix autoload.php reference
            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            // 2. Fix bootstrap/app.php reference and ensure usePublicPath(__DIR__)
            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            // 3. Fix maintenance mode path
            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
        echo "✅ index.php patched with absolute paths."
    fi

    # Use custom production index if present
    if [ -f "public/index.production.php" ]; then
        echo "Using index.production.php as production index..."
        if [ -z "{{ $public_path ?? '' }}" ] || [ "{{ $public_path }}" = "{{ $path }}/public" ]; then
            DEST="{{ $path }}/public/index.php"
        else
            DEST="{{ $public_path }}/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    fi

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin"); do
            VER=$($n -v | sed "s/v//")
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
        NPM_BIN_PATH=$(which -a "{{ $npm }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="{{ $path }}/.node_bin:$PATH"

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

        # Zajištění, aby build byl v subdoméně, ale i pro PHP dostupné v public_path()
        if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ] && [ ! -L "{{ $public_path }}" ]; then
            echo "Copying build to custom public path: {{ $public_path }}/build"
            mkdir -p "{{ $public_path }}/build"
            cp -r public/build/* "{{ $public_path }}/build/"
        fi

        echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    echo "Running database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan view:clear

    echo "Optimizing application..."
    {{ $php }} artisan optimize

    echo "Reindexing AI..."
    {{ $php }} artisan ai:index --no-interaction

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

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction

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
        echo "Ensuring custom public path is linked: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ -d "{{ $public_path }}" ]; then
            echo "Moving existing public files from {{ $public_path }} back to {{ $path }}/public..."
            cp -rn "{{ $public_path }}"/* public/ 2>/dev/null || true
            rm -rf "{{ $public_path }}"
        fi

        if [ ! -L "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi

        echo "Patching index.php in project root for absolute paths..."
        {{ $php }} -r '
            $path = "{{ $path }}/public/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
    fi

    # Use custom production index if present
    if [ -f "public/index.production.php" ]; then
        echo "Using index.production.php as production index..."
        if [ -z "{{ $public_path ?? '' }}" ] || [ "{{ $public_path }}" = "{{ $path }}/public" ]; then
            DEST="{{ $path }}/public/index.php"
        else
            DEST="{{ $public_path }}/index.php"
        fi
        cp public/index.production.php "$DEST"
        echo "✅ index.php replaced by index.production.php"
    fi

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        # Prefer v18+ versions if found
        NODE_BIN_PATH=""
        for n in $(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin"); do
            VER=$($n -v | sed "s/v//")
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
        NPM_BIN_PATH=$(which -a "{{ $npm }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
    fi
    ln -sf "$NPM_BIN_PATH" .node_bin/npm

    export PATH="{{ $path }}/.node_bin:$PATH"

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

    # Zajištění, aby build byl v subdoméně
    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ] && [ ! -L "{{ $public_path }}" ]; then
        echo "Copying build to custom public path: {{ $public_path }}/build"
        mkdir -p "{{ $public_path }}/build"
        cp -r public/build/* "{{ $public_path }}/build/"
    fi

    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan view:clear
    {{ $php }} artisan optimize

    echo "Reindexing AI..."
    {{ $php }} artisan ai:index --no-interaction

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

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is linked: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ -d "{{ $public_path }}" ]; then
            echo "Moving existing public files from {{ $public_path }} back to {{ $path }}/public..."
            cp -rn "{{ $public_path }}"/* public/ 2>/dev/null || true
            rm -rf "{{ $public_path }}"
        fi

        if [ ! -L "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi

        if [ ! -L "{{ $public_path }}" ]; then
            echo "Copying local assets (build) to custom public path: {{ $public_path }}"
            mkdir -p "{{ $public_path }}/build"
            cp -rf public/build/* "{{ $public_path }}/build/"
        fi

        echo "Patching index.php in project root for absolute paths..."
        {{ $php }} -r '
            $path = "{{ $path }}/public/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\x22\x27]\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\x22\x27]\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\x22\x27]\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
        echo "✅ index.php patched."
    fi

    echo "Running database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running database seeding..."
    {{ $php }} artisan app:seed --force --no-interaction

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan filament:clear-cached-components
    {{ $php }} artisan view:clear

    echo "Optimizing application..."
    {{ $php }} artisan optimize

    echo "Reindexing AI..."
    {{ $php }} artisan ai:index --no-interaction

    echo "✅ Sync finished successfully!"
@endtask

@task('status', ['on' => 'web'])
    cd {{ $path }}
    {{ $php }} artisan --version
    git log -1
@endtask
