<?php
use App\Models\User;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Diagnostika fotek hráčů...\n";
echo "Base path: " . base_path() . "\n";
echo "Public path: " . public_path() . "\n";
echo "Uploads disk root: " . config('filesystems.disks.public_path.root') . "\n";

$usersWithPhotos = User::whereHas('media', function($q) {
    $q->where('collection_name', 'player_photos');
})->with('media')->limit(5)->get();

if ($usersWithPhotos->isEmpty()) {
    echo "Žádní uživatelé s fotkami v DB.\n";
}

foreach ($usersWithPhotos as $user) {
    echo "\nHráč: {$user->display_name} (ID: {$user->id})\n";
    $mediaItems = $user->getMedia('player_photos');
    foreach ($mediaItems as $media) {
        $path = $media->getPath();
        $rosterPath = $media->getPath('roster');
        $url = $media->getUrl();
        $exists = file_exists($path);
        $rosterExists = file_exists($rosterPath);
        echo "  - Media ID: {$media->id}\n";
        echo "    Cesta: {$path}\n";
        echo "    Cesta (roster): {$rosterPath}\n";
        echo "    URL:   {$url}\n";
        echo "    Existuje na disku: " . ($exists ? 'ANO' : 'NE') . "\n";
        echo "    Existuje (roster): " . ($rosterExists ? 'ANO' : 'NE') . "\n";

        if (!$exists && !$rosterExists) {
            // Zkusíme se podívat, jestli není v defaultním public_path aplikace
            $relative = str_replace(public_path(), '', $path);
            $alternativePath = base_path('public' . $relative);
            if (file_exists($alternativePath)) {
                echo "    NALEZENO NA ALTERNATIVNÍ CESTĚ: {$alternativePath}\n";
            }
        }
    }
}
