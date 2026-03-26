<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Services\ScreenshotService;
echo "Starting Screenshot Pipeline Test...\n";
$svc = new ScreenshotService();
$dom = '<h1>Hello from Webglobe Local Render</h1><p>This was rendered via wkhtmltoimage fallback because NAS is blocked.</p>';
try {
    echo "Attempting capture (NAS 8s timeout + wkhtmltoimage local)...\n";
    $start = microtime(true);
    $result = $svc->captureViaPlaywrightFromDom($dom, [
        'viewport' => ['width' => 800, 'height' => 600],
        'context' => [
            'head' => '<style>h1 { color: red; }</style>',
        ]
    ]);
    $duration = microtime(true) - $start;
    echo "Capture finished in " . round($duration, 2) . "s\n";
    if (!empty($result['data_url'])) {
        echo "SUCCESS! Received image data URL (size: " . strlen($result['data_url']) . ")\n";
        $base64 = explode(',', $result['data_url'])[1];
        file_put_contents('test_pipeline_result.png', base64_decode($base64));
        echo "Saved result to test_pipeline_result.png\n";
    } else {
        echo "FAILED: Result data_url is empty.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
