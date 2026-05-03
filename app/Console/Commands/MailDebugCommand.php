<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class MailDebugCommand extends Command
{
    protected $signature = 'mail:debug {email} {--prod : Použít produkční SMTP}';
    protected $description = 'Odešle jednoduchý testovací e-mail pro ověření konektivity.';

    public function handle()
    {
        $target = $this->argument('email');
        $useProd = $this->option('prod');

        if ($useProd) {
            $this->info("Konfiguruji produkční SMTP (Webglobe)...");
            Config::set('mail.mailers.smtp.host', env('PROD_MAIL_HOST'));
            Config::set('mail.mailers.smtp.port', env('PROD_MAIL_PORT'));
            Config::set('mail.mailers.smtp.username', env('PROD_MAIL_USERNAME'));
            Config::set('mail.mailers.smtp.password', env('PROD_MAIL_PASSWORD'));
            Config::set('mail.mailers.smtp.encryption', env('PROD_MAIL_ENCRYPTION'));
            Config::set('mail.from.address', env('PROD_MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', env('PROD_MAIL_FROM_NAME'));

            // Re-vytvoření transportu (v Laravelu nutné pro projevení změn v Configu během runtime)
            Mail::purge();
        }

        $this->info("Odesílám testovací e-mail na: {$target}...");

        try {
            Mail::raw('Ahoj, toto je testovací e-mail z Junie (Laravel). Pokud ho vidíš, odesílání funguje!', function ($message) use ($target) {
                $message->to($target)
                    ->subject('Test mail konektivity - ' . now()->toDateTimeString());
            });
            $this->info("E-mail byl úspěšně předán k odeslání.");
        } catch (\Exception $e) {
            $this->error("Chyba: " . $e->getMessage());
        }
    }
}
