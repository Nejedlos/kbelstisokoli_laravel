<?php

/**
 * cron-proxy.php
 *
 * Webglobe CRON -> tento skript (na "hlavní" doméně) -> zavolá scheduler endpoint na subdoméně.
 *
 * Použití v CRONu:
 *   https://TVA-HLAVNI-DOMENA.TLD/cron-proxy.php?token=SEM_DEJ_TOKEN
 *
 * Doporučení:
 * - nastav CRON_PROXY_TOKEN jako env proměnnou (např. v .env / hosting env)
 * - loguje do /tmp (typicky povolené na hostinzích)
 */

// ====== KONFIG ======
const TARGET_URL = 'https://new.kbelstisokoli.cz/system/schedule/6f72f0cdf9f8ce4dbc1860899c94a9ad';
const DEFAULT_TIMEOUT_SECONDS = 60;
const LOG_FILE = '/tmp/cron-proxy-kbelstisokoli.log';

// Token pro ochranu: nastav env CRON_PROXY_TOKEN
$expectedToken = getenv('CRON_PROXY_TOKEN') ?: '';
$providedToken = (string)(isset($_GET['token']) ? $_GET['token'] : '');

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {
        if ($code !== NULL) {
            switch ($code) {
                case 200: $text = 'OK'; break;
                case 403: $text = 'Forbidden'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 502: $text = 'Bad Gateway'; break;
                default: $text = 'Unknown Status'; break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        }
        return (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
    }
}

if (!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if (strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for ($i = strlen($res) - 1; $i >= 0; $i--) {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

if ($expectedToken !== '') {
    if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        http_response_code(403);
        echo "Forbidden\n";
        exit;
    }
} else {
    // Pokud token nenastavíš, skript poběží bez ochrany (nedoporučeno).
}

// Jednoduché logování
function logLine($msg)
{
    $line = sprintf("[%s] %s\n", date('c'), $msg);
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

// ====== VOLÁNÍ SUBDOMÉNY ======
$timeout = (int)(isset($_GET['timeout']) ? $_GET['timeout'] : DEFAULT_TIMEOUT_SECONDS);
if ($timeout < 5) $timeout = 5;
if ($timeout > 180) $timeout = 180;

logLine("START proxy -> " . TARGET_URL);

$ch = curl_init(TARGET_URL);
if ($ch === false) {
    http_response_code(500);
    echo "Failed to init cURL\n";
    logLine("ERROR: curl_init failed");
    exit;
}

curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,   // následuj redirecty
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_USERAGENT      => 'webglobe-cron-proxy/1.0',
    CURLOPT_HEADER         => true,   // chceme oddělit hlavičky + tělo kvůli debug
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
));

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo "Proxy error: cURL failed ($errno): $error\n";
    logLine("ERROR: cURL failed errno=$errno error=$error");
    exit;
}

$headers = substr($response, 0, $headerSize);
$body    = substr($response, $headerSize);

logLine("DONE httpCode=$httpCode bodyLen=" . strlen($body));

// ====== VÝSTUP ======
if ($httpCode >= 200 && $httpCode < 300) {
    http_response_code(200);
    echo "OK (upstream HTTP $httpCode)\n";
} else {
    // ať je v CRON reportu hned vidět, že upstream selhal
    http_response_code(502);
    echo "UPSTREAM_NOT_OK (HTTP $httpCode)\n";
}

echo "\n--- Upstream headers ---\n";
echo trim($headers) . "\n";

echo "\n--- Upstream body (first 4000 chars) ---\n";
echo substr($body, 0, 4000) . "\n";
