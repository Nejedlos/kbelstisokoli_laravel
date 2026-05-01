<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Schema;

echo "--- DIAGNOSE START ---\n";
echo "DB Connection: " . config('database.default') . "\n";
echo "Table Prefix: " . DB::getTablePrefix() . "\n";

$columns = Schema::getColumnListing('users');
echo "Columns in 'users' table: " . implode(', ', $columns) . "\n";

$required_columns = ['name', 'email', 'club_member_id', 'payment_vs', 'membership_status', 'is_active', 'onboarding_completed_at'];
foreach ($required_columns as $col) {
    if (!in_array($col, $columns)) {
        echo "!!! MISSING COLUMN: $col\n";
    }
}

echo "Testing User::first()...\n";
try {
    $user = User::with(['externalMappings', 'roles', 'playerProfile.primaryTeam'])->first();
    if ($user) {
        echo "User found: " . $user->name . " (ID: " . $user->id . ")\n";
        echo "Is Ghost: " . ($user->isGhost() ? 'Yes' : 'No') . "\n";
        echo "External Mappings Count: " . $user->externalMappings->count() . "\n";
    } else {
        echo "No users found in database!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR fetching user: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "--- DIAGNOSE END ---\n";
