<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Absolutní základní cesta k aplikaci (pro lokál relativně)
$APP_BASE = realpath(__DIR__.'/..');

// Nouzové hlášení chyb před bootem Laravelu (pre-boot)
(function () use ($APP_BASE) {
    try {
        $envPath = file_exists($APP_BASE.'/.env') ? $APP_BASE : (file_exists($APP_BASE.'/public/.env') ? $APP_BASE.'/public' : null);
        if ($envPath && class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
        }
    } catch (\Throwable $e) {
        // Ignorovat chyby při načítání .env
    }

    $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
    $debug = ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false') === 'true';
    $errorRecipient = $_ENV['ERROR_REPORT_EMAIL'] ?? getenv('ERROR_REPORT_EMAIL') ?: null;

    // Pokud nejsme na produkci nebo máme zapnutý debug, zobrazujeme chyby
    if ($env !== 'production' || $debug) {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    if (! $errorRecipient || ($env !== 'production' && ! $debug)) {
        // Pokud nemáme kam posílat reporty, nebo nejsme v módu pro hlášení, končíme SMTP logiku
        if ($env === 'production' && ! $debug) {
            // Na produkci bez debugu a bez mailu raději chyby skryjeme (pokud nebyly povoleny výše)
            ini_set('display_errors', '0');
        }

        return;
    }

    $send = function (string $subject, string $body) use ($errorRecipient) {
        try {
            $host = $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?? null;
            $port = (int) ($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?? 25);
            $user = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?? null;
            $pass = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?? null;
            $enc = $_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?? null;
            $from = $_ENV['ERROR_REPORT_SENDER'] ?? getenv('ERROR_REPORT_SENDER') ?? ($user ?: 'noreply@localhost');

            if (! $host || ! $user || ! $pass) {
                return;
            }

            $params = [];
            if (! empty($enc)) {
                $params[] = 'encryption='.$enc;
            }
            $dsn = sprintf(
                'smtp://%s:%s@%s:%d%s',
                rawurlencode((string) $user),
                rawurlencode((string) $pass),
                (string) $host,
                $port,
                $params ? ('?'.implode('&', $params)) : ''
            );

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);
            $email = (new \Symfony\Component\Mime\Email)
                ->from($from)
                ->to($errorRecipient)
                ->subject($subject)
                ->text($body);

            $mailer->send($email);
        } catch (\Throwable $e) {
            error_log('Pre-boot error email failed: '.$e->getMessage());
        }
    };

    set_exception_handler(function ($e) use ($send, $env, $debug) {
        if (! $e instanceof \Throwable) {
            return;
        }

        // Pokud máme zobrazovat chyby, vypíšeme je i do výstupu
        if ($env !== 'production' || $debug) {
            echo '<h1>Pre-boot Exception</h1>';
            echo '<p><strong>'.get_class($e)."</strong>: {$e->getMessage()}</p>";
            echo "<p>File: {$e->getFile()}:{$e->getLine()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        }

        $server = [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
        $subject = sprintf('Chyba spuštění | %s | %s (%s:%s)', strtoupper($_ENV['APP_ENV'] ?? 'production'), get_class($e), $e->getFile(), $e->getLine());
        $body = "Message: {$e->getMessage()}\n\nTrace:\n".$e->getTraceAsString()."\n\nServer:\n".print_r($server, true);
        $send($subject, $body);
    });

    register_shutdown_function(function () use ($send, $env, $debug) {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            if ($env !== 'production' || $debug) {
                echo '<h1>Pre-boot Fatal Error</h1>';
                echo '<pre>'.print_r($error, true).'</pre>';
            }

            $server = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];
            $subject = sprintf('Kritická chyba spuštění | %s (%s:%s)', $error['message'] ?? 'Fatal error', $error['file'] ?? 'unknown', $error['line'] ?? '');
            $body = "Error:\n".print_r($error, true)."\n\nServer:\n".print_r($server, true);
            $send($subject, $body);
        }
    });
})();

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP_BASE.'/vendor/autoload.php';

define('LARAVEL_PUBLIC_PATH', __DIR__);

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP_BASE.'/bootstrap/app.php';

// Nastavíme public path na adresář, kde se nachází tento index.php (pro jistotu i explicitně)
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
