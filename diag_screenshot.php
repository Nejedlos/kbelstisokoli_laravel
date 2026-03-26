<?php
/**
 * Screenshot System Diagnosis Script
 * Upload this to /secret/public/diag_screenshot.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== SCREENSHOT SYSTEM DIAGNOSIS ===\n\n";

echo "1. PHP ENVIRONMENT\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "User: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_getuid())['name'] : 'N/A') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Extensions: " . (extension_loaded('curl') ? 'curl OK, ' : 'curl MISSING, ') . (extension_loaded('openssl') ? 'openssl OK' : 'openssl MISSING') . "\n\n";

function test_curl($url, $name) {
    echo "--- TESTING CONNECTION: {$name} ---\n";
    echo "URL: {$url}\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $start = microtime(true);
    $response = curl_exec($ch);
    $duration = microtime(true) - $start;
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);

    if ($errno === 0) {
        echo "RESULT: SUCCESS!\n";
        echo "HTTP Status: " . $info['http_code'] . "\n";
        echo "Total Time: " . round($duration, 3) . "s\n";
        echo "Connect Time: " . round($info['connect_time'], 3) . "s\n";
        echo "Primary IP: " . ($info['primary_ip'] ?? 'N/A') . "\n";
        if (!empty($response)) {
             echo "Response Preview: " . substr(trim($response), 0, 100) . "...\n";
        }
    } else {
        echo "RESULT: FAILED!\n";
        echo "Error ({$errno}): {$error}\n";
    }
    echo "\nVerbose Log Output:\n" . $verboseLog . "\n\n";
}

// Test external
test_curl('https://www.google.com', 'Google (External Connectivity Test)');

// Test NAS DNS
test_curl('https://screenshot.kbelstisokoli.cz/health', 'NAS via DNS (HTTPS)');

// Test NAS DNS HTTP
test_curl('http://screenshot.kbelstisokoli.cz/health', 'NAS via DNS (HTTP)');

// Test NAS IP
test_curl('https://128.0.178.250/health', 'NAS via IP (HTTPS)');

echo "=== DIAGNOSIS COMPLETE ===";
