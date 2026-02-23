<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$user = User::where('email', 'nejedlymi@gmail.com')->first();
if (!$user) {
    die("User not found\n");
}

Auth::login($user);

echo "Testing access for: " . $user->email . "\n";
echo "Is active: " . ($user->is_active ? 'yes' : 'no') . "\n";
echo "Can access admin: " . ($user->canAccessAdmin() ? 'yes' : 'no') . "\n";

$urls = ['/admin', '/auth/two-factor-setup', '/clenska-sekce/dashboard'];

foreach ($urls as $url) {
    $request = Illuminate\Http\Request::create($url, 'GET');
    // Nasimulujeme session pro login
    $request->setLaravelSession($app['session']->driver());

    $response = $kernel->handle($request);

    echo "URL: $url -> Status: " . $response->getStatusCode();
    if ($response->isRedirection()) {
        echo " -> Redirect to: " . $response->headers->get('Location');
    }
    echo "\n";
}
