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
const DEFAULT_TIMEOUT_SECONDS = 90;
const LOG_FILE = '/tmp/cron-proxy-kbelstisokoli.log';

// Podpora pro CLI argumenty (pokud se volá jako php cron-proxy.php TOKEN)
if (PHP_SAPI === 'cli' && isset($argv[1])) {
    $_GET['token'] = $argv[1];
}

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

// Jednoduché logování
function logLine($msg)
{
    $line = sprintf("[%s] %s\n", date('c'), $msg);
    @file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

// ====== AUTH CHECK ======
if ($expectedToken !== '') {
    if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        http_response_code(403);
        if (PHP_SAPI !== 'cli') {
            echo "<!DOCTYPE html><html><head><title>403 Forbidden</title><style>body{font-family:sans-serif;background:#f4f4f4;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;} .box{background:white;padding:2rem;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);text-align:center;} h1{color:#e53e3e;margin-top:0;} p{color:#4a5568;}</style></head><body><div class='box'><h1>403 Forbidden</h1><p>Neplatný nebo chybějící token pro spuštění CRONu.</p></div></body></html>";
        } else {
            echo "Forbidden: Invalid token.\n";
        }
        exit;
    }
}

// ====== VOLÁNÍ SUBDOMÉNY ======
$timeout = (int)(isset($_GET['timeout']) ? $_GET['timeout'] : DEFAULT_TIMEOUT_SECONDS);
if ($timeout < 5) $timeout = 5;
if ($timeout > 300) $timeout = 300;

$startTime = microtime(true);
logLine("START proxy -> " . TARGET_URL . " from " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'CLI') . " (SAPI: " . PHP_SAPI . ")");

$ch = curl_init(TARGET_URL);
if ($ch === false) {
    http_response_code(500);
    logLine("ERROR: curl_init failed");
    echo "Failed to init cURL\n";
    exit;
}

$verboseLog = '';
if (isset($_GET['debug']) || PHP_SAPI === 'cli' || true) { // Zapneme debug vždy pro diagnostiku, ale vypíšeme jen když bude třeba
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
}

curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,   // následuje redirecty
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_CONNECTTIMEOUT => 20,
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_USERAGENT      => 'webglobe-cron-proxy/2.0 (Kbelsti Sokoli)',
    CURLOPT_HEADER         => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
));

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

if (isset($verbose)) {
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);
}

$executionTime = round(microtime(true) - $startTime, 3);
logLine("DONE httpCode=$httpCode bodyLen=" . ($response ? strlen($response) : 0) . " time={$executionTime}s");

if ($response === false) {
    http_response_code(502);
    $msg = "Proxy error: cURL failed ($errno): $error";
    logLine("ERROR: " . $msg);
}

$headers = $response ? substr($response, 0, $headerSize) : '';
$body    = $response ? substr($response, $headerSize) : '';

// Diagnostika DNS
$host = parse_url(TARGET_URL, PHP_URL_HOST);
$dnsIp = gethostbyname($host);

// ====== VÝSTUP (UI) ======
if (PHP_SAPI === 'cli') {
    echo "=== CRON PROXY REPORT ===\n";
    echo "Target: " . TARGET_URL . "\n";
    echo "Status: " . ($response === false ? "FAILED" : "OK") . " (HTTP $httpCode)\n";
    echo "Time:   {$executionTime}s\n";
    echo "DNS:    $host -> $dnsIp\n";
    if ($response === false) echo "Error:  $error ($errno)\n";
    echo "\n--- Body ---\n";
    echo substr($body, 0, 1000) . "\n";
    exit;
}

// HTML UI pro Web prohlížeč
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cron Proxy Status | Kbelští sokoli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        body { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); min-height: 100vh; color: #f8fafc; font-family: ui-sans-serif, system-ui; }
        pre { background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.85rem; border: 1px solid #1e293b; }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white mb-1">Cron Proxy</h1>
                <p class="text-blue-200">System Pipeline Management</p>
            </div>
            <div class="text-right text-xs text-blue-300">
                <?php echo date('Y-m-d H:i:s'); ?><br>
                PHP <?php echo PHP_VERSION; ?>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass p-4 rounded-xl shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-800 mb-1">Status</p>
                <div class="flex items-center">
                    <?php if ($httpCode >= 200 && $httpCode < 300): ?>
                        <span class="h-3 w-3 rounded-full bg-green-500 mr-2 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                        <span class="text-xl font-bold text-slate-900">SUCCESS</span>
                    <?php else: ?>
                        <span class="h-3 w-3 rounded-full bg-red-500 mr-2 shadow-[0_0_8px_rgba(239,68,68,0.8)]"></span>
                        <span class="text-xl font-bold text-slate-900">FAILED</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-600 mt-1">HTTP Response: <?php echo $httpCode; ?></p>
            </div>
            <div class="glass p-4 rounded-xl shadow-lg text-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-800 mb-1">Execution Time</p>
                <div class="text-2xl font-bold"><?php echo $executionTime; ?> <span class="text-lg font-normal">sec</span></div>
                <p class="text-sm text-slate-600 mt-1">Limit: <?php echo $timeout; ?>s</p>
            </div>
            <div class="glass p-4 rounded-xl shadow-lg text-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-800 mb-1">Upstream</p>
                <div class="text-lg font-bold truncate" title="<?php echo TARGET_URL; ?>"><?php echo parse_url(TARGET_URL, PHP_URL_HOST); ?></div>
                <p class="text-sm text-slate-600 mt-1">Resolved to: <?php echo $dnsIp; ?></p>
            </div>
        </div>

        <?php if ($response === false): ?>
        <div class="bg-red-500/20 border border-red-500/50 p-4 rounded-xl mb-6">
            <h3 class="font-bold text-red-200 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Chyba spojení (cURL Error)
            </h3>
            <p class="text-red-100"><?php echo htmlspecialchars($error); ?> (Kód: <?php echo $errno; ?>)</p>
        </div>
        <?php endif; ?>

        <div class="space-y-6">
            <section>
                <h3 class="text-lg font-semibold mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Upstream Output
                </h3>
                <pre class="whitespace-pre-wrap"><?php echo $body ? htmlspecialchars($body) : '[Prázdný výstup]'; ?></pre>
            </section>

            <section>
                <h3 class="text-lg font-semibold mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Upstream Headers
                </h3>
                <pre class="text-xs opacity-80"><?php echo htmlspecialchars($headers); ?></pre>
            </section>

            <details class="group">
                <summary class="text-blue-300 cursor-pointer hover:text-white transition-colors mb-2 list-none flex items-center">
                    <svg class="w-4 h-4 mr-2 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7"></path></svg>
                    Zobrazit detailní cURL log (Debug)
                </summary>
                <pre class="text-xs opacity-60 max-h-96 overflow-y-auto"><?php echo htmlspecialchars($verboseLog); ?></pre>
            </details>

            <div class="pt-8 border-t border-blue-800 flex flex-wrap gap-x-8 gap-y-2 text-xs text-blue-400">
                <div><strong>Remote ADDR:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?></div>
                <div><strong>Server SAPI:</strong> <?php echo PHP_SAPI; ?></div>
                <div><strong>Log File:</strong> <?php echo LOG_FILE; ?> (<?php echo file_exists(LOG_FILE) ? round(filesize(LOG_FILE)/1024, 1) . ' KB' : 'neexistuje'; ?>)</div>
                <div><strong>Memory Peak:</strong> <?php echo round(memory_get_peak_usage()/1024/1024, 2); ?> MB</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Pokud byl httpCode špatný, nastavíme ho i pro tento skript na závěr
if ($httpCode >= 400) {
    // http_response_code($httpCode); // Volitelně - pokud chceme aby CRON viděl chybu, ale my chceme vidět to HTML UI
}
