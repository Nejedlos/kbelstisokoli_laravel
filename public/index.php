<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Nouzové hlášení chyb před bootem Laravelu (pre-boot)
(function () {
    try {
        if (class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
        }
    } catch (\Throwable $e) {
        // Ignorovat chyby při načítání .env
    }

    $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
    $errorRecipient = $_ENV['ERROR_REPORT_EMAIL'] ?? getenv('ERROR_REPORT_EMAIL') ?: null;
    if ($env !== 'production' || !$errorRecipient) {
        return; // povoleno pouze na produkci a pokud je nastaven příjemce
    }

    $send = function (string $subject, string $body) use ($errorRecipient) {
        try {
            $host = $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?? null;
            $port = (int)($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?? 25);
            $user = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?? null;
            $pass = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?? null;
            $enc = $_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?? null;
            $from = $_ENV['ERROR_REPORT_SENDER'] ?? getenv('ERROR_REPORT_SENDER') ?? ($user ?: 'noreply@localhost');

            if (!$host || !$user || !$pass) {
                error_log('Pre-boot mail not sent: missing SMTP credentials');
                return;
            }

            $params = [];
            if (!empty($enc)) {
                $params[] = 'encryption=' . $enc;
            }
            $dsn = sprintf(
                'smtp://%s:%s@%s:%d%s',
                rawurlencode((string) $user),
                rawurlencode((string) $pass),
                (string) $host,
                $port,
                $params ? ('?' . implode('&', $params)) : ''
            );

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);
            $email = (new \Symfony\Component\Mime\Email())
                ->from($from)
                ->to($errorRecipient)
                ->subject($subject)
                ->text($body);

            $mailer->send($email);
        } catch (\Throwable $e) {
            error_log('Pre-boot error email failed: ' . $e->getMessage());
        }
    };

    set_exception_handler(function ($e) use ($send) {
        if (!$e instanceof \Throwable) {
            return;
        }
        $server = [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
        $subject = sprintf('[PreBoot][%s] %s (%s:%s)', $_ENV['APP_ENV'] ?? 'production', get_class($e), $e->getFile(), $e->getLine());
        $body = "Message: {$e->getMessage()}\n\nTrace:\n" . $e->getTraceAsString() . "\n\nServer:\n" . print_r($server, true);
        $send($subject, $body);
    });

    register_shutdown_function(function () use ($send) {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $server = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];
            $subject = sprintf('[PreBoot][FATAL] %s (%s:%s)', $error['message'] ?? 'Fatal error', $error['file'] ?? 'unknown', $error['line'] ?? '');
            $body = "Error:\n" . print_r($error, true) . "\n\nServer:\n" . print_r($server, true);
            $send($subject, $body);
        }
    });
})();

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

