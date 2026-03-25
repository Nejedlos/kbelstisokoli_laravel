<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- KBELSTI SOKOLI DIAGNOSTIKA ---\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Public Path: " . public_path() . "\n";
echo "Base Path: " . base_path() . "\n";
echo "Env PROD_PUBLIC_PATH: " . env('PROD_PUBLIC_PATH') . "\n";
echo "Env PUBLIC_PATH_MODE: " . env('PUBLIC_PATH_MODE') . "\n";

echo "\n--- FILESYSTEM CONFIG ---\n";
echo "Default Disk: " . config('filesystems.default') . "\n";
echo "Media Disk: " . config('media-library.disk_name') . "\n";
$publicPathDisk = config('filesystems.disks.public_path');
echo "Disk 'public_path' root: " . ($publicPathDisk['root'] ?? 'NOT DEFINED') . "\n";

echo "\n--- UPLOADS DIRECTORY ---\n";
$targetUploads = public_path('uploads');
echo "Target Uploads: $targetUploads\n";
if (file_exists($targetUploads)) {
    echo "Uploads exists: YES\n";
    echo "Uploads writable: " . (is_writable($targetUploads) ? "YES" : "NO") . "\n";
    echo "Uploads owner: " . posix_getpwuid(fileowner($targetUploads))['name'] . "\n";
} else {
    echo "Uploads exists: NO\n";
}

echo "\n--- LATEST MEDIA ENTRIES (AVATAR) ---\n";
$latestMedia = DB::table('media')
    ->where('collection_name', 'avatar')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($latestMedia as $m) {
    echo "ID: {$m->id}, Model ID: {$m->model_id}, Disk: {$m->disk}, Name: {$m->file_name}\n";
    $disk = Storage::disk($m->disk);
    $path = $m->id . '/' . $m->file_name; // Default Spatie path if not customized
    // But we have CustomPathGenerator.
    echo "  Path (DB): " . $m->id . "\n";
}

echo "\n--- TESTING PATH GENERATOR ---\n";
$user = User::first();
if ($user) {
    echo "Testing Path Generator for User #{$user->id}\n";
    $generator = app(App\Services\Media\CustomPathGenerator::class);

    $media = new \Spatie\MediaLibrary\MediaCollections\Models\Media();
    $media->model_type = get_class($user);
    $media->model_id = $user->id;
    $media->collection_name = 'avatar';
    $media->id = 999999;

    echo "  CustomPath: " . $generator->getPath($media) . "\n";
    echo "  Full expected path: " . public_path($generator->getPath($media)) . "\n";
}

echo "\n--- END ---\n";
