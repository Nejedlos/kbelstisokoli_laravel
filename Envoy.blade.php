@servers(['web' => 'user@your-server-ip'])

@setup
    $repository = 'git@github.com:user/kbelstisokoli.git';
    $path = '/path/to/your/web/root';
@endsetup

@task('deploy', ['on' => 'web'])
    cd {{ $path }}
    git pull origin main
    composer install --no-interaction --prefer-dist --optimize-autoloader
    php artisan migrate --force
    npm install
    npm run build
    php artisan optimize
    php artisan up
@endtask

@task('status', ['on' => 'web'])
    cd {{ $path }}
    php artisan --version
    git log -1
@endtask
