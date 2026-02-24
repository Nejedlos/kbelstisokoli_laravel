<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\AiIndexService::class);
$query = 'logo';
echo "Hledám: $query\n";
$results = $service->search($query, 'cs', 5, 'admin');

foreach ($results as $i => $doc) {
    $q = 'logo';
    $title = Illuminate\Support\Str::lower($doc->title);
    $content = Illuminate\Support\Str::lower(Illuminate\Support\Str::limit($doc->content, 2000, ''));
    $keywords = Illuminate\Support\Str::lower(implode(' ', $doc->keywords ?? []));

    $score = 0;
    $score += substr_count($title, $q) * 10;
    $score += substr_count($keywords, $q) * 8;
    $score += substr_count($content, $q) * 2;

    echo ($i + 1) . ". " . $doc->title . " (Score: $score, Type: " . $doc->type . ")\n";
    echo "   Keywords: " . json_encode($doc->keywords) . "\n\n";
}

if ($results->isEmpty()) {
    echo "Žádné výsledky nenalezeny.\n";
}
