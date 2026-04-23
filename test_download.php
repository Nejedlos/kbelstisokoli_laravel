<?php
use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Test stahování z cz.basketball...\n";
$url = "https://cz.basketball/min.php?file=http://cbf.cz/hraci/foto_2015/34168.jpg";

try {
    echo "Stahuji z: {$url}\n";
    $response = Http::timeout(10)
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ])
        ->get($url);

    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "Úspěch! Velikost: " . strlen($response->body()) . " bytes\n";
        $type = $response->header('Content-Type');
        echo "Content-Type: {$type}\n";
    } else {
        echo "Chyba: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nZkouším přímé URL (pokud min.php selže)...\n";
$url2 = "https://cbf.cz/hraci/foto_2015/34168.jpg";
try {
    echo "Stahuji z: {$url2}\n";
    $response = Http::timeout(10)
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ])
        ->get($url2);

    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "Úspěch! Velikost: " . strlen($response->body()) . " bytes\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
