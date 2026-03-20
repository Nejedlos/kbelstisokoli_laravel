<?php
/**
 * CRON PROXY - Kbelští sokoli
 * Minimalistický skript pro spuštění plánovače z kořene hostingu na subdoménu.
 * Kompatibilní s PHP 5.3+ (včetně PHP 5.5 na Webglobe).
 */

// Konfigurace cílové URL s tokenem (vyžádání JSON pro diagnostiku)
$url = 'https://new.kbelstisokoli.cz/system/schedule/6f72f0cdf9f8ce4dbc1860899c94a9ad?json=1';

// Spuštění požadavku přes cURL
$ch = curl_init($url);
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false, // Důležité pro Webglobe loopback
    CURLOPT_TIMEOUT        => 90,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT      => 'KS-Cron-Proxy/4.0',
    CURLOPT_HTTPHEADER     => array('Accept: application/json')
));

$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Výstup jako prostý text
header('Content-Type: text/plain; charset=utf-8');

echo "CRON PROXY STATUS: " . ($httpCode === 200 ? "SUCCESS" : "ERROR") . "\n";
echo "HTTP CODE: $httpCode\n";
if ($error) echo "cURL ERROR: $error\n";
echo "PHP VERSION: " . PHP_VERSION . "\n";
echo "--------------------------------------------------\n\n";

if ($response) {
    // Pokus o parsování JSON pro lepší přehlednost
    $data = json_decode($response, true);
    if ($data && isset($data['status'])) {
        echo "Stav z Laravelu: " . $data['status'] . "\n";
        if (isset($data['heartbeat'])) {
            $hb = $data['heartbeat'];
            echo "Zápis heartbeatu: " . ($hb['success'] ? "OK" : "SELHAL") . "\n";
            echo "Čas zápisu: " . (isset($hb['time']) ? $hb['time'] : 'N/A') . "\n";
            echo "Cache Driver: " . (isset($hb['cache_driver']) ? $hb['cache_driver'] : 'N/A') . "\n";
            if (isset($hb['error'])) echo "Chyba zápisu: " . $hb['error'] . "\n";
        }
        echo "\nVýstup plánovače:\n" . (isset($data['output']) ? $data['output'] : 'Žádný výstup');
    } else {
        echo "Odpověď z Laravelu (Raw):\n" . $response;
    }
} else {
    echo "Žádná odpověď z Laravelu (prázdné tělo).";
}
