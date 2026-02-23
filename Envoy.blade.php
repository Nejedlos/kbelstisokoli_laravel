@servers(['web' => $user . '@' . $host])

@setup
    $repository = 'https://' . $token . '@github.com/Nejedlos/kbelstisokoli_laravel.git';
    $path = $path ?? '/www/kbelstisokoli';
@endsetup

@task('setup', ['on' => 'web'])
    echo "🚀 Starting setup on {{ $host }}..."

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
        git pull origin main
    fi

    echo "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader

    if [ ! -f ".env" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
        php artisan key:generate

        if [ ! -z "{{ $db_host ?? '' }}" ]; then
            sed -i "s/DB_HOST=.*/DB_HOST={{ $db_host }}/" .env
            sed -i "s/DB_DATABASE=.*/DB_DATABASE={{ $db_database }}/" .env
            sed -i "s/DB_USERNAME=.*/DB_USERNAME={{ $db_username }}/" .env
            sed -i "s/DB_PASSWORD=.*/DB_PASSWORD={{ $db_password }}/" .env
        fi

        echo "✅ .env configured."
    fi

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Configuring custom public path: {{ $public_path }}"
        if [ -d "{{ $public_path }}" ] && [ ! -L "{{ $public_path }}" ]; then
             echo "Backing up existing public directory..."
             mv "{{ $public_path }}" "{{ $public_path }}_backup_$(date +%Y%m%d_%H%M%S)"
        fi
        ln -sfn "{{ $path }}/public" "{{ $public_path }}"
        echo "✅ Symlink created: {{ $public_path }} -> {{ $path }}/public"
    fi

    echo "Installing NPM dependencies..."
    npm install

    echo "Building assets..."
    npm run build

    echo "Running database migrations..."
    php artisan migrate --force

    echo "Syncing icons..."
    php artisan app:icons:sync

    echo "Optimizing application..."
    php artisan optimize

    echo "✅ Setup finished successfully!"
@endtask

@task('deploy', ['on' => 'web'])
    echo "🚀 Deploying to {{ $host }}..."
    cd {{ $path }}

    git pull origin main

    composer install --no-interaction --prefer-dist --optimize-autoloader
    php artisan migrate --force

    if [ ! -z "{{ $public_path ?? '' }}" ] && [ "{{ $public_path }}" != "{{ $path }}/public" ]; then
        echo "Ensuring custom public path symlink: {{ $public_path }}"
        ln -sfn "{{ $path }}/public" "{{ $public_path }}"
    fi

    npm install
    npm run build
    php artisan app:icons:sync
    php artisan optimize

    echo "✅ Deployment finished successfully!"
@endtask

@task('status', ['on' => 'web'])
    cd {{ $path }}
    php artisan --version
    git log -1
@endtask
