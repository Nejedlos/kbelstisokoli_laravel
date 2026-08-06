<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// --- EARLY EXIT FAST CACHE (Redis) ---
// Pouze pro GET požadavky hostů na veřejných stránkách.
// Bypasuje celý Laravel bootstrap a vendor autoloading.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' 
    && !isset($_COOKIE['kbelsti-sokoli-session'])
    && !isset($_SERVER['HTTP_X_PRIME_CACHE'])
    && !isset($_GET['screenshot_user_id'])
) {
    $uri_path = ltrim(explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0], '/');
    if ($uri_path === '') $uri_path = '/';
    
    // Vyloučení administrace a členské sekce
    if (!str_starts_with($uri_path, 'admin') && !str_starts_with($uri_path, 'clenska-sekce')) {
        $queryParams = $_GET;
        ksort($queryParams);
        $serializedParams = serialize($queryParams);
        
        $redis = new Redis();
        if (@$redis->connect('c-redis', 6379, 0.05)) {
            $redis->select(1);
            $prefix = 'kbelsti-sokoli-database-kbelsti-sokoli-cache-full_page_';
            
            // Zkusíme oba jazyky (výchozí CS je první)
            foreach (['cs', 'en'] as $l) {
                $key = $prefix . md5($uri_path . '_' . $serializedParams . '_' . $l);
                $cached = $redis->get($key);
                if ($cached) {
                    $data = unserialize($cached);
                    if (isset($data['content'])) {
                        header('Content-Type: ' . ($data['type'] ?? 'text/html; charset=UTF-8'));
                        header('X-Page-Cache: fast-hit');
                        header('X-Fast-Cache-Locale: ' . $l);
                        header('Cache-Control: public, max-age=300, stale-while-revalidate=600');
                        echo $data['content'];
                        exit;
                    }
                }
            }
        }
    }
}
// --- KONEC EARLY EXIT ---

$APP_BASE = '/home/html/kbelstisokoli.cz/public_html/secret';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP_BASE.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Nouzové hlášení chyb před bootem Laravelu (pre-boot)
(function () use ($APP_BASE) {
    // Definice cesty k logu pro pre-boot
    $preBootLog = is_writable($APP_BASE . '/storage/logs') ? $APP_BASE . '/storage/logs/pre-boot.log' : sys_get_temp_dir() . '/kbelstisokoli-pre-boot.log';

    /**
     * Jednoduchý logger, který funguje i před startem Laravelu
     */
    $GLOBALS['__PRE_LOGS'] = [];
    $preLog = function (string $message, array $context = []) {
        $microtime = microtime(true);
        $timestamp = date('Y-m-d H:i:s') . '.' . sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $pid = getmypid();
        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $GLOBALS['__PRE_LOGS'][] = [
            'time' => $microtime,
            'log' => sprintf("[%s] [%s] %s: %s %s\n", $timestamp, $pid, $uri, $message, $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : '')
        ];
    };

    // Breadcrumb systém pro detekci zacyklení
    if (!isset($GLOBALS['__BREADCRUMBS'])) {
        $GLOBALS['__BREADCRUMBS'] = [];
    }

    $addBreadcrumb = function (string $label, array $data = []) use (&$preLog) {
        $safeData = [];
        foreach ($data as $key => $value) {
            $safeData[$key] = is_scalar($value) || $value === null ? $value : gettype($value);
        }

        $breadcrumb = ['label' => $label, 'data' => $safeData, 'time' => microtime(true)];
        $GLOBALS['__BREADCRUMBS'][] = $breadcrumb;

        if (count($GLOBALS['__BREADCRUMBS']) > 200) {
            $GLOBALS['__BREADCRUMBS'] = array_slice($GLOBALS['__BREADCRUMBS'], -200);
        }

        // Pokud máme podezření na zacyklení (příliš mnoho stejných labelů za sebou), zalogujeme to
        $labels = array_column($GLOBALS['__BREADCRUMBS'], 'label');
        $counts = array_count_values(array_slice($labels, -20)); // Sledujeme posledních 20
        if (isset($counts[$label]) && $counts[$label] > 10) {
            $preLog("POTENTIAL RECURSION DETECTED in '$label'", ['counts' => $counts, 'last_breadcrumbs' => array_slice($GLOBALS['__BREADCRUMBS'], -10)]);
        }
    };

    // Globální helpery (vždy dostupné přes include, aby byly v globálním scope i pro CLI)
    if (!function_exists('pre_log')) {
        function pre_log(string $message, array $context = []) {
            $microtime = microtime(true);
            $timestamp = date('Y-m-d H:i:s') . '.' . sprintf('%03d', ($microtime - floor($microtime)) * 1000);
            $pid = getmypid();
            $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
            $GLOBALS['__PRE_LOGS'][] = [
                'time' => $microtime,
                'log' => sprintf("[%s] [%s] %s: %s %s\n", $timestamp, $pid, $uri, $message, $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : '')
            ];
        }
    }

    if (!function_exists('add_breadcrumb')) {
        function add_breadcrumb(string $label, array $data = []) {
            if (!isset($GLOBALS['__BREADCRUMBS'])) {
                $GLOBALS['__BREADCRUMBS'] = [];
            }

            $safeData = [];
            foreach ($data as $key => $value) {
                $safeData[$key] = is_scalar($value) || $value === null ? $value : gettype($value);
            }

            $breadcrumb = ['label' => $label, 'data' => $safeData, 'time' => microtime(true)];
            $GLOBALS['__BREADCRUMBS'][] = $breadcrumb;

            if (count($GLOBALS['__BREADCRUMBS']) > 200) {
                $GLOBALS['__BREADCRUMBS'] = array_slice($GLOBALS['__BREADCRUMBS'], -200);
            }

            // Pokud máme podezření na zacyklení (příliš mnoho stejných labelů za sebou), zalogujeme to
            $labels = array_column($GLOBALS['__BREADCRUMBS'], 'label');
            $counts = array_count_values(array_slice($labels, -20)); // Sledujeme posledních 20
            if (isset($counts[$label]) && $counts[$label] > 10) {
                pre_log("POTENTIAL RECURSION DETECTED in '$label'", ['counts' => $counts, 'last_breadcrumbs' => array_slice($GLOBALS['__BREADCRUMBS'], -10)]);
            }
        }
    }

    $GLOBALS['APP_BASE'] = $APP_BASE;

    try {
        $envPath = file_exists($APP_BASE.'/.env') ? $APP_BASE : (file_exists($APP_BASE.'/public/.env') ? $APP_BASE.'/public' : null);
        if ($envPath && class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
        }
    } catch (\Throwable $e) {
        $preLog("Error loading .env", ['error' => $e->getMessage()]);
    }

    $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
    $debug = ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false') === 'true';
    $errorRecipient = $_ENV['ERROR_REPORT_EMAIL'] ?? getenv('ERROR_REPORT_EMAIL') ?: null;

    $preLog("Request started", ['env' => $env, 'debug' => $debug, 'memory' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB']);

    // Pokud nejsme na produkci nebo máme zapnutý debug, zobrazujeme chyby
    if ($env !== 'production' || $debug) {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    $send = function (string $subject, string $body) use ($errorRecipient, $preLog) {
        try {
            $host = $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?? null;
            $port = (int) ($_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?? 25);
            $user = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?? null;
            $pass = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?? null;
            $enc = $_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?? null;
            $from = $_ENV['ERROR_REPORT_SENDER'] ?? getenv('ERROR_REPORT_SENDER') ?? ($user ?: 'noreply@localhost');

            if (! $host || ! $user || ! $pass || ! $errorRecipient) {
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

            if (class_exists(\Symfony\Component\Mailer\Transport::class)) {
                $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
                $mailer = new \Symfony\Component\Mailer\Mailer($transport);
                $email = (new \Symfony\Component\Mime\Email)
                    ->from($from)
                    ->to($errorRecipient)
                    ->subject($subject)
                    ->text($body);

                $mailer->send($email);
                $preLog("Error email sent to $errorRecipient");
            } else {
                $preLog("Cannot send email: Mailer classes not found yet (too early?)");
            }
        } catch (\Throwable $e) {
            $preLog('Pre-boot error email failed: '.$e->getMessage());
        }
    };

    set_exception_handler(function ($e) use ($send, $env, $debug, $preLog) {
        if (! $e instanceof \Throwable) {
            return;
        }

        $preLog("Uncaught exception: " . get_class($e), [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'breadcrumbs' => array_slice($GLOBALS['__BREADCRUMBS'] ?? [], -10)
        ]);

        // Pokud máme zobrazovat chyby, vypíšeme je i do výstupu
        if ($env !== 'production' || $debug) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo '<h1>Pre-boot Exception</h1>';
            echo '<p><strong>'.get_class($e)."</strong>: {$e->getMessage()}</p>";
            echo "<p>File: {$e->getFile()}:{$e->getLine()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
            if (!empty($GLOBALS['__BREADCRUMBS'])) {
                echo "<h3>Last Breadcrumbs:</h3><pre>" . json_encode(array_slice($GLOBALS['__BREADCRUMBS'], -15), JSON_PRETTY_PRINT) . "</pre>";
            }
        }

        $server = [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
        ];
        $subject = sprintf('Chyba spuštění | %s | %s (%s:%s)', strtoupper($_ENV['APP_ENV'] ?? 'production'), get_class($e), $e->getFile(), $e->getLine());
        $body = "Message: {$e->getMessage()}\n\nTrace:\n".$e->getTraceAsString()."\n\nBreadcrumbs:\n".json_encode(array_slice($GLOBALS['__BREADCRUMBS'] ?? [], -20), JSON_PRETTY_PRINT)."\n\nServer:\n".print_r($server, true);
        $send($subject, $body);
    });

    register_shutdown_function(function () use ($send, $env, $debug, $preLog, $APP_BASE) {
        $error = error_get_last();
        $isFatal = $error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : (isset($GLOBALS['__PRE_LOGS'][0]['time']) ? $GLOBALS['__PRE_LOGS'][0]['time'] : microtime(true));
        $totalTime = (microtime(true) - $startTime) * 1000;

        // Zapíšeme logy pouze pokud: nastala fatální chyba, nebo request trval déle než 500ms
        if ($isFatal || $totalTime > 500 || $debug) {
            $preBootLog = is_writable($APP_BASE . '/storage/logs') ? $APP_BASE . '/storage/logs/pre-boot.log' : sys_get_temp_dir() . '/kbelstisokoli-pre-boot.log';
            $allLogs = array_column($GLOBALS['__PRE_LOGS'] ?? [], 'log');
            if ($allLogs) {
                @file_put_contents($preBootLog, implode('', $allLogs), FILE_APPEND);
            }
        }

        if ($isFatal) {

            $isMemoryIssue = (strpos($error['message'], 'Allowed memory size') !== false);

            $preLog("Fatal error captured", [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'breadcrumbs' => array_slice($GLOBALS['__BREADCRUMBS'] ?? [], -15)
            ]);

            if ($env !== 'production' || $debug) {
                if (!headers_sent()) {
                    header('HTTP/1.1 500 Internal Server Error');
                }
                echo '<h1>Pre-boot Fatal Error</h1>';
                echo '<pre>'.print_r($error, true).'</pre>';
                echo '<h3>Memory Usage: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB</h3>';
                if (!empty($GLOBALS['__BREADCRUMBS'])) {
                    echo "<h3>Last Breadcrumbs (Path to crash):</h3><pre>" . json_encode(array_slice($GLOBALS['__BREADCRUMBS'], -20), JSON_PRETTY_PRINT) . "</pre>";
                }
            }

            $server = [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
                'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            ];
            $subject = sprintf('Kritická chyba spuštění | %s (%s:%s)', $error['message'] ?? 'Fatal error', $error['file'] ?? 'unknown', $error['line'] ?? '');
            $body = "Error:\n".print_r($error, true)."\n\nMemory: ".round(memory_get_usage(true) / 1024 / 1024, 2)." MB\n\nBreadcrumbs:\n".json_encode(array_slice($GLOBALS['__BREADCRUMBS'] ?? [], -30), JSON_PRETTY_PRINT)."\n\nServer:\n".print_r($server, true);
            $send($subject, $body);
        }
    });
})();

// Register the Composer autoloader...
require $APP_BASE.'/vendor/autoload.php';

define('LARAVEL_PUBLIC_PATH', __DIR__);

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP_BASE.'/bootstrap/app.php';

// Nastavíme public path na adresář, kde se nachází tento index.php (pro jistotu i explicitně)
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
