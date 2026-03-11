<?php

require __DIR__.'/vendor/autoload.php';

if (!function_exists('pre_log')) {
    function pre_log($m, $c = []) { echo "LOG: $m " . (empty($c) ? "" : json_encode($c, JSON_UNESCAPED_UNICODE)) . "\n"; }
}
if (!function_exists('add_breadcrumb')) {
    function add_breadcrumb($l, $d = []) { echo "BREADCRUMB: $l " . (empty($d) ? "" : json_encode($d, JSON_UNESCAPED_UNICODE)) . "\n"; }
}

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;

// Mockování Livewire komponenty
class MockHelpPage {
    public $searchQuery = '';
    public $currentFile = null;
    public $currentCategory = null;
    public $search = '';

    public function getTree() {
        return app(\App\Services\Help\HelpService::class)->getNavigationTree();
    }

    public function getHomeData() {
        return app(\App\Services\Help\HelpService::class)->getHomeData();
    }

    public function getCategoryData() {
        if (!$this->currentCategory) return null;
        return app(\App\Services\Help\HelpService::class)->getCategoryData($this->currentCategory);
    }

    public function getArticleData() {
        if (!$this->currentFile) return null;
        return app(\App\Services\Help\HelpService::class)->getArticleData($this->currentFile);
    }

    public function getSearchResults() {
        return app(\App\Services\Help\HelpService::class)->search($this->searchQuery);
    }
}

$user = User::whereHas("roles", function($q) { $q->where("name", "admin"); })->first() ?: User::first();
Auth::login($user);
Filament::setCurrentPanel(Filament::getPanel("admin"));

$mode = $argv[1] ?? 'landing'; // landing, category, article
$mock = new MockHelpPage();

if ($mode === 'category') {
    $cat = DB::table('help_categories')->where('is_active', true)->first();
    $mock->currentCategory = $cat->slug ?? 'sport';
    echo "Simulating CATEGORY detail: {$mock->currentCategory}\n";
} elseif ($mode === 'article') {
    $art = DB::table('help_articles')->where('is_published', true)->first();
    $mock->currentFile = $art->slug ?? 'jak-pouzivat-napovedu';
    echo "Simulating ARTICLE detail: {$mock->currentFile}\n";
} else {
    echo "Simulating LANDING page\n";
}

try {
    echo "Rendering start...\n";
    $html = View::make("filament.pages.help-v2", [
        'page' => $mock,
        'searchQuery' => $mock->searchQuery,
        'currentFile' => $mock->currentFile,
        'currentCategory' => $mock->currentCategory,
    ])->render();

    echo "Render successful. Length: " . strlen($html) . "\n";
    echo "Memory peak: " . round(memory_get_peak_usage(true)/1024/1024, 2) . "MB\n";
} catch (\Throwable $e) {
    echo "Render failed: " . $e->getMessage() . "\n";
    echo "In file: " . $e->getFile() . ":" . $e->getLine() . "\n";
    // Check pre-boot log
    if (file_exists('storage/logs/pre-boot.log')) {
        echo "\nLast lines of pre-boot.log:\n";
        echo shell_exec('tail -n 20 storage/logs/pre-boot.log');
    }
}
