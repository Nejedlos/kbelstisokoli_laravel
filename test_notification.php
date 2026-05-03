<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dmarc\DmarcIncident;
use App\Notifications\Dmarc\CriticalDmarcIncidentNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

$email = 'nejedlymi@gmail.com';

// Nastavení produkčního SMTP (pokud jsme na produkci, env() to vytáhne z reálného .env)
Config::set('mail.mailers.smtp.host', env('PROD_MAIL_HOST', env('MAIL_HOST')));
Config::set('mail.mailers.smtp.port', env('PROD_MAIL_PORT', env('MAIL_PORT')));
Config::set('mail.mailers.smtp.username', env('PROD_MAIL_USERNAME', env('MAIL_USERNAME')));
Config::set('mail.mailers.smtp.password', env('PROD_MAIL_PASSWORD', env('MAIL_PASSWORD')));
Config::set('mail.mailers.smtp.encryption', env('PROD_MAIL_ENCRYPTION', env('MAIL_ENCRYPTION')));
Config::set('mail.from.address', env('PROD_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')));
Config::set('mail.from.name', env('PROD_MAIL_FROM_NAME', env('MAIL_FROM_NAME')));

Mail::purge();

$incident = DmarcIncident::latest()->first();
if (!$incident) {
    echo "No incident found in DB to test with.\n";
    exit;
}

echo "Sending test notification for incident #{$incident->id} to {$email}...\n";

try {
    Notification::route('mail', $email)->notify(new CriticalDmarcIncidentNotification($incident));
    echo "Notification sent successfully (according to Laravel).\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
