<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'Europe/Prague',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'cs'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'cs'),

    'supported_locales' => ['cs', 'en'],

    'fontawesome_pro' => env('FONTAWESOME_PRO', false),

    'faker_locale' => env('APP_FAKER_LOCALE', 'cs_CZ'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'public_path' => env('APP_PUBLIC_PATH'),

    'prod_php_binary' => env('PROD_PHP_BINARY'),
    'local_php_binary' => env('LOCAL_PHP_BINARY'),
    'prod_node_binary' => env('PROD_NODE_BINARY', 'node'),
    'prod_npm_binary' => env('PROD_NPM_BINARY', 'npm'),
    'prod_host' => env('PROD_HOST'),
    'prod_port' => env('PROD_PORT', '22'),
    'prod_user' => env('PROD_USER'),
    'prod_path' => env('PROD_PATH'),
    'prod_public_path' => env('PROD_PUBLIC_PATH'),
    'prod_git_token' => env('PROD_GIT_TOKEN'),
    'prod_db_connection' => env('PROD_DB_CONNECTION', 'mysql'),
    'prod_db_host' => env('PROD_DB_HOST', '127.0.0.1'),
    'prod_db_port' => env('PROD_DB_PORT', '3306'),
    'prod_db_database' => env('PROD_DB_DATABASE'),
    'prod_db_username' => env('PROD_DB_USERNAME'),
    'prod_db_password' => env('PROD_DB_PASSWORD'),
    'prod_db_prefix' => env('PROD_DB_PREFIX', 'new_'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
