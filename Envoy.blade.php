@servers(['web' => $user . '@' . $host . ($port ? ' -p ' . $port : '') . ' -o StrictHostKeyChecking=no'])

@setup
    $repository = $repository ?? 'https://' . $token . '@github.com/Nejedlos/kbelstisokoli_laravel.git';
    $path = $path ?? '/www/kbelstisokoli';
    $php = $php ?? 'php';
    $node = $node ?? 'node';
    $npm = $npm ?? 'npm';
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

    if [ ! -z "{{ $db_database ?? '' }}" ]; then
        sed -i "s|DB_CONNECTION=.*|DB_CONNECTION={{ $db_connection ?? 'mysql' }}|" .env
        sed -i "s|DB_HOST=.*|DB_HOST={{ $db_host ?? '127.0.0.1' }}|" .env
        sed -i "s|DB_PORT=.*|DB_PORT={{ $db_port ?? '3306' }}|" .env
        sed -i "s|DB_DATABASE=.*|DB_DATABASE={{ $db_database }}|" .env
        sed -i "s|DB_USERNAME=.*|DB_USERNAME={{ $db_username }}|" .env
        sed -i "s|DB_PASSWORD=.*|DB_PASSWORD={{ $db_password }}|" .env
        if [ ! -z "{{ $db_prefix ?? '' }}" ]; then
            if grep -q "^DB_PREFIX=" .env; then
                sed -i "s|DB_PREFIX=.*|DB_PREFIX={{ $db_prefix }}|" .env
            else
                echo "DB_PREFIX={{ $db_prefix }}" >> .env
            fi
        fi
        echo "✅ Database configured in .env."
    fi

    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env

    if [ ! -z "{{ $public_path ?? '' }}" ]; then
        if grep -q "^APP_PUBLIC_PATH=" .env; then
            sed -i "s|APP_PUBLIC_PATH=.*|APP_PUBLIC_PATH={{ $public_path }}|" .env
        else
            echo "APP_PUBLIC_PATH={{ $public_path }}" >> .env
        fi
    fi

    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generating APP_KEY..."
        {{ $php }} artisan key:generate --no-interaction
    fi

    echo "Running composer install..."
    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Configuring custom public path: {{ $public_path }}"
        if [ ! -d "{{ $public_path }}" ]; then
            mkdir -p "{{ $public_path }}"
        fi

        echo "Syncing public directory to {{ $public_path }}..."
        cp -rt "{{ $public_path }}" public/*

        echo "Patching index.php in {{ $public_path }}..."
        # Update paths in index.php to point to the functional path using robust regex
        {{ $php }} -r '
            $path = "{{ $public_path }}/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            // 1. Fix autoload.php reference
            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\"\']\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            // 2. Fix bootstrap/app.php and ensure usePublicPath(__DIR__)
            // First clean up any existing usePublicPath call to avoid duplicates
            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\"\']\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            // 3. Fix maintenance mode path
            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\"\']\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
        echo "✅ index.php patched."
    fi

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        NODE_BIN_PATH=$(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
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

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    echo "Running database migrations..."
    {{ $php }} artisan migrate --force

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync

    echo "Optimizing application..."
    {{ $php }} artisan optimize

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

    if [ ! -z "{{ $public_path ?? '' }}" ]; then
        if grep -q "^APP_PUBLIC_PATH=" .env; then
            sed -i "s|APP_PUBLIC_PATH=.*|APP_PUBLIC_PATH={{ $public_path }}|" .env
        else
            echo "APP_PUBLIC_PATH={{ $public_path }}" >> .env
        fi
    fi

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is synced: {{ $public_path }}"
        cp -rt "{{ $public_path }}" public/*

        echo "Patching index.php in {{ $public_path }}..."
        {{ $php }} -r '
            $path = "{{ $public_path }}/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\"\']\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\"\']\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\"\']\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
    fi

    echo "Installing NPM dependencies..."
    mkdir -p .node_bin

    # Symlink node (using absolute path to avoid circularity)
    if [[ "{{ $node }}" == /* ]]; then
        NODE_BIN_PATH="{{ $node }}"
    else
        NODE_BIN_PATH=$(which -a "{{ $node }}" | grep -v "{{ $path }}/.node_bin" | head -n1)
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

    {{ $php }} artisan app:icons:sync
    {{ $php }} artisan optimize

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

    if [ ! -z "{{ $db_database ?? '' }}" ]; then
        sed -i "s|DB_CONNECTION=.*|DB_CONNECTION={{ $db_connection ?? 'mysql' }}|" .env
        sed -i "s|DB_HOST=.*|DB_HOST={{ $db_host ?? '127.0.0.1' }}|" .env
        sed -i "s|DB_PORT=.*|DB_PORT={{ $db_port ?? '3306' }}|" .env
        sed -i "s|DB_DATABASE=.*|DB_DATABASE={{ $db_database }}|" .env
        sed -i "s|DB_USERNAME=.*|DB_USERNAME={{ $db_username }}|" .env
        sed -i "s|DB_PASSWORD=.*|DB_PASSWORD={{ $db_password }}|" .env
        if [ ! -z "{{ $db_prefix ?? '' }}" ]; then
            if grep -q "^DB_PREFIX=" .env; then
                sed -i "s|DB_PREFIX=.*|DB_PREFIX={{ $db_prefix }}|" .env
            else
                echo "DB_PREFIX={{ $db_prefix }}" >> .env
            fi
        fi
        echo "✅ Database configured in .env."
    fi

    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env

    if [ ! -z "{{ $public_path ?? '' }}" ]; then
        if grep -q "^APP_PUBLIC_PATH=" .env; then
            sed -i "s|APP_PUBLIC_PATH=.*|APP_PUBLIC_PATH={{ $public_path }}|" .env
        else
            echo "APP_PUBLIC_PATH={{ $public_path }}" >> .env
        fi
    fi

    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generating APP_KEY..."
        {{ $php }} artisan key:generate --no-interaction
    fi

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is synced: {{ $public_path }}"
        if [ ! -d "{{ $public_path }}" ]; then
            mkdir -p "{{ $public_path }}"
        fi
        cp -rt "{{ $public_path }}" public/*

        echo "Patching index.php in {{ $public_path }}..."
        {{ $php }} -r '
            $path = "{{ $public_path }}/index.php";
            if (!file_exists($path)) { exit(0); }
            $content = file_get_contents($path);
            $base = "{{ $path }}";

            $content = preg_replace(
                "/require\s+[^;]+vendor\/autoload\.php[\"\']\s*;/",
                "require \"$base/vendor/autoload.php\";",
                $content
            );

            $content = preg_replace("/\\\$app->usePublicPath\(.*?\);\\s*/", "", $content);

            $content = preg_replace(
                "/(\\\$app\s*=\s*)?require_once\s+[^;]+bootstrap\/app\.php[\"\']\s*;/",
                "\$app = require_once \"$base/bootstrap/app.php\";\n            \$app->usePublicPath(__DIR__);",
                $content
            );

            $content = preg_replace(
                "/file_exists\(\s*\\\$maintenance\s*=\s*[^;]+storage\/framework\/maintenance\.php[\"\']\s*\)/",
                "file_exists(\$maintenance = \"$base/storage/framework/maintenance.php\")",
                $content
            );

            file_put_contents($path, $content);
        '
        echo "✅ index.php patched."
    fi

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php bootstrap/cache/packages.php

    echo "Running database migrations..."
    {{ $php }} artisan migrate --force

    echo "Syncing icons..."
    {{ $php }} artisan app:icons:sync

    echo "Optimizing application..."
    {{ $php }} artisan optimize

    echo "✅ Sync finished successfully!"
@endtask

@task('status', ['on' => 'web'])
    cd {{ $path }}
    {{ $php }} artisan --version
    git log -1
@endtask
