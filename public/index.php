<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Absolutní základní cesta k aplikaci (pro lokál relativně)
$APP_BASE = realpath(__DIR__.'/..');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP_BASE.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP_BASE.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
