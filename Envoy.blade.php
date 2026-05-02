@servers(['web' => $user . '@' . $host . ($port ? ' -p ' . $port : '') . ' -o StrictHostKeyChecking=no'])

@setup
    if (isset($repository)) { $v_repository = $repository; } else { $v_repository = 'https://' . $token . '@github.com/Nejedlos/kbelstisokoli_laravel.git'; }
    if (isset($path)) { $v_path = $path; } else { $v_path = '/www/kbelstisokoli'; }
    if (isset($php)) { $v_php = $php; } else { $v_php = 'php'; }
    if (isset($node)) { $v_node = $node; } else { $v_node = 'node'; }
    if (isset($npm)) { $v_npm = $npm; } else { $v_npm = 'npm'; }

    if (isset($db_connection)) { $v_conn = $db_connection; } else { $v_conn = 'mysql'; }
    $db_connection_b64 = base64_encode($v_conn);
    if (isset($db_host)) { $v_host = $db_host; } else { $v_host = '127.0.0.1'; }
    $db_host_b64 = base64_encode($v_host);
    if (isset($db_port)) { $v_port = $db_port; } else { $v_port = '3306'; }
    $db_port_b64 = base64_encode($v_port);
    if (isset($db_database)) { $v_db = $db_database; } else { $v_db = ''; }
    $db_database_b64 = base64_encode($v_db);
    if (isset($db_username)) { $v_user = $db_username; } else { $v_user = ''; }
    $db_username_b64 = base64_encode($v_user);
    if (isset($db_password)) { $v_pass = $db_password; } else { $v_pass = ''; }
    $db_password_b64 = base64_encode($v_pass);
    if (isset($db_prefix)) { $v_pref = $db_prefix; } else { $v_pref = ''; }
    $db_prefix_b64 = base64_encode($v_pref);

    if (isset($db_version)) { $v_dbver = $db_version; } else { $v_dbver = ''; }
    $db_version_b64 = base64_encode($v_dbver);
    if (isset($db_mariadb)) { $v_dbmaria = $db_mariadb; } else { $v_dbmaria = ''; }
    $db_mariadb_b64 = base64_encode($v_dbmaria);

    if (isset($public_path)) { $v_pub = $public_path; } else { $v_pub = ''; }
    $public_path_b64 = base64_encode($v_pub);

    if (isset($freshseed) && $freshseed) { $v_freshseed_opt = '--freshseed'; } else { $v_freshseed_opt = ''; }
    if (isset($usersync) && $usersync == "1") { $v_usersync_opt = '--usersync'; } else { $v_usersync_opt = ''; }
    if (isset($stats) && $stats == "1") { $v_stats_opt = '--stats'; } else { $v_stats_opt = ''; }
    if (isset($noai)) { $v_noai = $noai; } else { $v_noai = false; }
    if (isset($fontawesome_token)) { $v_fontawesome_token = $fontawesome_token; } else { $v_fontawesome_token = ''; }
    if (isset($public_path)) { $target_public = $public_path; } else { $target_public = $v_path . '/public'; }
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

            if ("{{ $db_version_b64 }}") {
                $vars["DB_VERSION"] = base64_decode("{{ $db_version_b64 }}");
            }
            if ("{{ $db_mariadb_b64 }}") {
                $vars["DB_MARIADB"] = base64_decode("{{ $db_mariadb_b64 }}");
            }

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

    echo "Running composer install..."
    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    rm -f bootstrap/cache/*.php
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    if ! grep -q "APP_KEY=base64" .env; then
        echo "Generating APP_KEY..."
        {{ $php }} artisan key:generate --no-interaction
    fi

    if [ ! -z "{{ isset($public_path) ? $public_path : '' }}" ] && [ "{{ isset($public_path) ? $public_path : '' }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path is configured: {{ $public_path }}"
        if [ ! -L "{{ $public_path }}" ] && [ ! -d "{{ $public_path }}" ]; then
            ln -sf "{{ $path }}/public" "{{ $public_path }}"
            echo "✅ Created symlink from {{ $path }}/public to {{ $public_path }}"
        fi
    fi

    # Determine and patch entry point
    if [ -f "public/index.production.php" ]; then
        if [ -z "{{ isset($public_path) ? $public_path : '' }}" ] || [ "{{ isset($public_path) ? $public_path : '' }}" = "{{ $path }}/public" ]; then
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
    echo "PD9waHAKJGJhc2UgPSAkYXJndlsxXTsKJHB1YmxpYyA9ICRhcmd2WzJdOwokZGVzdCA9ICIiOyBpZiAoaXNzZXQoJGFyZ3ZbM10pKSB7ICRkZXN0ID0gJGFyZ3ZbM107IH0KJGZpbGVzID0gYXJyYXlfdW5pcXVlKGFycmF5X2ZpbHRlcihbJGJhc2UgLiAiL3B1YmxpYy9pbmRleC5waHAiLCAkZGVzdF0pKTsKZm9yZWFjaCAoJGZpbGVzIGFzICRmKSB7CiAgICBpZiAoIWZpbGVfZXhpc3RzKCRmKSkgY29udGludWU7CiAgICAkYyA9IGZpbGVfZ2V0X2NvbnRlbnRzKCRmKTsKICAgICRjID0gcHJlZ19yZXBsYWNlKCIvXFxcJEFQUF9CQVNFXFxzKj1cXHMqLio/Oy8iLCAiXCRBUFBfQkFTRSA9IFwiIiAuICRiYXNlIC4gIlwiOyIsICRjKTsKICAgICRjID0gcHJlZ19yZXBsYWNlKCIvcmVxdWlyZVxccysuKj92ZW5kb3JcL2F1dG9sb2FkXFwucGhwLio/Oy8iLCAicmVxdWlyZSBcIiIgLiAkYmFzZSAuICIvdmVuZG9yL2F1dG9sb2FkLnBocFwiOyIsICRjKTsKICAgICRjID0gcHJlZ19yZXBsYWNlKCIvXFxcJGFwcC0+dXNlUHVibGljUGF0aFxcKC4qP1xcKTtcXHMqLyIsICIiLCAkYyk7CiAgICAkYyA9IHByZWdfcmVwbGFjZSgiL2RlZmluZVxcKFtcIiddTEFSQVZFTF9QVUJMSUNfUEFUSFtcIiddLio/XFwpO1xccyovIiwgIiIsICRjKTsKICAgICRyZXBsYWNlbWVudCA9ICJkZWZpbmUoXCJMQVJBVkVMX1BVQkxJQ19QQVRIXCIsIFwiIiAuICRwdWJsaWMgLiAiXCIpO1xuICAgICAgICAgICAgXCRhcHAgPSByZXF1aXJlX29uY2UgXCIiIC4gJGJhc2UgLiAiL2Jvb3RzdHJhcC9hcHAucGhwXCI7XG4gICAgICAgICAgICBcJGFwcC0+dXNlUHVibGljUGF0aChcIiIgLiAkcHVibGljIC4gIlwiKTsiOwogICAgJGMgPSBwcmVnX3JlcGxhY2UoIi9yZXF1aXJlX29uY2VcXHMrLio/Ym9vdHN0cmFwXC9hcHBcXC5waHAuKj8oW1wiJ118OykvIiwgJHJlcGxhY2VtZW50LCAkYyk7CiAgICAkYyA9IHByZWdfcmVwbGFjZSgiL2ZpbGVfZXhpc3RzXFwoXFxzKlxcXCRtYWludGVuYW5jZVxccyo9XFxzKi4qP3N0b3JhZ2VcL2ZyYW1ld29ya1wvbWFpbnRlbmFuY2VcXC5waHAuKj9cXCkvIiwgImZpbGVfZXhpc3RzKFwkbWFpbnRlbmFuY2UgPSBcIiIgLiAkYmFzZSAuICIvc3RvcmFnZS9mcmFtZXdvcmsvbWFpbnRlbmFuY2UucGhwXCIpIiwgJGMpOwogICAgZmlsZV9wdXRfY29udGVudHMoJGYsICRjKTsKfQo=" | base64 -d > patch_entrypoints.php
    {{ $php }} patch_entrypoints.php "{{ $path }}" "{{ $target_public }}" "$DEST"
    rm patch_entrypoints.php
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

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/app/public
    mkdir -p storage/app/livewire-tmp
    mkdir -p storage/logs
    chmod -R 777 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/*.php
    {{ $php }} artisan config:clear
    {{ $php }} artisan cache:clear

    echo "Running idempotent database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running application synchronization..."
    {{ $php }} artisan app:sync --force --no-interaction {{ $v_freshseed_opt }} {{ $v_usersync_opt }} {{ $v_stats_opt }}

    {{ $php }} artisan filament:optimize
    {{ $php }} artisan livewire:publish --assets --force
    {{ $php }} artisan livewire:discover
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:cache
    {{ $php }} artisan optimize

    # Zajištění, aby build a assety byly v subdoméně, ale i pro PHP dostupné v public_path()
    if [ ! -z "{{ isset($public_path) ? $public_path : '' }}" ] && [ "{{ isset($public_path) ? $public_path : '' }}" != "{{ $path }}/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                if [ "$dir_name" != "uploads" ]; then
                    rm -rf "{{ $public_path }}/$dir_name"
                fi
                mkdir -p "{{ $public_path }}/$dir_name"
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
        fi
    fi

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

    # Try to find Node 18+ for building assets if needed
    NODE_BIN_PATH=""
    for n in node22 node20 node18 node; do
        if which $n > /dev/null 2>&1; then
            BIN=$(which $n)
            VER=$($BIN -v 2>/dev/null | sed "s/v//")
            if [ "$(printf "%s\n" "18.0.0" "$VER" | sort -V | head -n1)" = "18.0.0" ]; then
                NODE_BIN_PATH=$BIN
                break
            fi
        fi
    done

    if [ ! -z "$NODE_BIN_PATH" ]; then
        echo "Found compatible Node.js: $NODE_BIN_PATH ($($NODE_BIN_PATH -v))"
        mkdir -p .node_bin
        ln -sf "$NODE_BIN_PATH" .node_bin/node
        ln -sf "$(dirname $NODE_BIN_PATH)/npm" .node_bin/npm 2>/dev/null || ln -sf "$(which npm)" .node_bin/npm
        export PATH="{{ $path }}/.node_bin:$PATH"
        if [ ! -z "{{ $fontawesome_token }}" ]; then
            export FONTAWESOME_TOKEN="{{ $fontawesome_token }}"
        fi
        echo "Running npm install and build..."
        npm install --no-save
        npm run build
    else
        echo "⚠️  Compatible Node.js (>=18) not found. Skipping npm build. Use local build and push manifest.json."
    fi

    echo "Ensuring storage and cache directories exist and are writable..."
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/framework/cache/data
    mkdir -p storage/app/public
    mkdir -p storage/app/livewire-tmp
    mkdir -p storage/logs
    chmod -R 777 storage bootstrap/cache || true

    echo "Running composer install..."
    COMPOSER_BIN=$(which composer 2>/dev/null || echo "composer")
    rm -f bootstrap/cache/*.php
    {{ $php }} $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    echo "Cleaning up cache..."
    {{ $php }} artisan config:clear
    {{ $php }} artisan cache:clear

    echo "Running idempotent database migrations..."
    {{ $php }} artisan migrate --force

    echo "Running application synchronization..."
    {{ $php }} artisan app:sync --force --no-interaction {{ $v_freshseed_opt }} {{ $v_usersync_opt }} {{ $v_stats_opt }}

    {{ $php }} artisan filament:optimize
    {{ $php }} artisan livewire:publish --assets --force
    {{ $php }} artisan livewire:discover
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:cache
    {{ $php }} artisan optimize

    # Zajištění, aby build a assety byly v subdoméně, ale i pro PHP dostupné v public_path()
    if [ ! -z "{{ isset($public_path) ? $public_path : '' }}" ] && [ "{{ isset($public_path) ? $public_path : '' }}" != "{{ $path }}/public" ]; then
        # Pokud public_path není symlink (tedy je to fyzický adresář), musíme do něj soubory zkopírovat
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            # Najdeme všechny skutečné adresáře v public/
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                if [ "$dir_name" != "uploads" ]; then
                    rm -rf "{{ $public_path }}/$dir_name"
                fi
                mkdir -p "{{ $public_path }}/$dir_name"
                # Kopírování obsahu včetně skrytých souborů
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            # Také zkopírovat jednotlivé soubory v public/ (všechny, ne jen vybrané přípony)
            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
        fi
    fi

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

            if ("{{ $db_version_b64 }}") {
                $vars["DB_VERSION"] = base64_decode("{{ $db_version_b64 }}");
            }
            if ("{{ $db_mariadb_b64 }}") {
                $vars["DB_MARIADB"] = base64_decode("{{ $db_mariadb_b64 }}");
            }

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
    mkdir -p storage/app/public
    mkdir -p storage/app/livewire-tmp
    mkdir -p storage/logs
    chmod -R 777 storage bootstrap/cache || true

    echo "Cleaning up cache..."
    rm -f bootstrap/cache/*.php
    {{ $php }} artisan config:clear
    {{ $php }} artisan cache:clear

    cd {{ $path }}
    {{ $php }} artisan app:sync --force --no-interaction {{ $v_freshseed_opt }} {{ $v_usersync_opt }} {{ $v_stats_opt }}

    {{ $php }} artisan filament:optimize
    {{ $php }} artisan livewire:publish --assets --force
    {{ $php }} artisan livewire:discover
    {{ $php }} artisan cache:clear
    {{ $php }} artisan view:cache
    {{ $php }} artisan optimize

    # Dynamická synchronizace všech adresářů z public/ do public_path (kromě storage)
    if [ ! -z "{{ isset($public_path) ? $public_path : '' }}" ] && [ "{{ isset($public_path) ? $public_path : '' }}" != "{{ $path }}/public" ]; then
        if [ ! -L "{{ $public_path }}" ]; then
            cd {{ $path }}/public
            # Najdeme všechny skutečné adresáře v public/
            find . -maxdepth 1 -type d ! -name "." ! -name ".." ! -name "storage" | while read dir; do
                dir_name=$(basename "$dir")
                echo "Syncing $dir_name to custom public path: {{ $public_path }}/$dir_name"
                if [ "$dir_name" != "uploads" ]; then
                    rm -rf "{{ $public_path }}/$dir_name"
                fi
                mkdir -p "{{ $public_path }}/$dir_name"
                # Kopírování obsahu včetně skrytých souborů
                cp -rf "$dir_name"/. "{{ $public_path }}/$dir_name/"
            done

            # Také zkopírovat jednotlivé soubory v public/ (všechny, ne jen vybrané přípony)
            # Vynecháme index.php a index.production.php, které jsou řešeny patchováním/nahrazením
            echo "Syncing root files to custom public path..."
            find . -maxdepth 1 -type f ! -name "index.php" ! -name "index.production.php" -exec cp -f {} "{{ $public_path }}/" \;
        fi
    fi

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
