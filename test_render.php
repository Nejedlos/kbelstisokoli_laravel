<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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

    public function getSearchResults() { return collect([]); }
}

$user = User::whereHas("roles", function($q) { $q->where("name", "admin"); })->first();
if (!$user) {
    $user = User::first();
}
Auth::login($user);
Filament::setCurrentPanel(Filament::getPanel("admin"));

try {
    echo "Rendering Help page...\n";
    $html = View::make("filament.pages.help-v2", [
        'this' => new MockHelpPage(),
        'searchQuery' => '',
        'currentFile' => null,
        'currentCategory' => null,
        'search' => '',
    ])->render();

    echo "Render successful. Length: " . strlen($html) . "\n";
    echo "Memory peak: " . round(memory_get_peak_usage(true)/1024/1024, 2) . "MB\n";
} catch (\Throwable $e) {
    echo "Render failed: " . $e->getMessage() . "\n";
    echo "In file: " . $e->getFile() . ":" . $e->getLine() . "\n";
    // tail pre-boot.log if exists
    if (file_exists('storage/logs/pre-boot.log')) {
        echo "\nLast 10 lines of pre-boot.log:\n";
        echo shell_exec('tail -n 10 storage/logs/pre-boot.log');
    }
}
