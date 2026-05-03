<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Dmarc\DmarcMailbox;
use App\Services\Dmarc\DmarcImapService;

#[Signature('dmarc:ingest {--mailbox= : Specifické ID mailboxu} {--prod : Použít produkční SMTP pro notifikace}')]
#[Description('Stáhne a zpracuje DMARC aggregate reporty z IMAP mailboxů.')]
class DmarcIngestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DmarcImapService $service)
    {
        if ($this->option('prod') && env('PROD_MAIL_HOST')) {
            $this->info("Konfiguruji produkční SMTP pro notifikace...");
            config([
                'mail.mailers.smtp.host' => env('PROD_MAIL_HOST'),
                'mail.mailers.smtp.port' => env('PROD_MAIL_PORT'),
                'mail.mailers.smtp.username' => env('PROD_MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('PROD_MAIL_PASSWORD'),
                'mail.mailers.smtp.encryption' => env('PROD_MAIL_ENCRYPTION'),
                'mail.from.address' => env('PROD_MAIL_FROM_ADDRESS'),
                'mail.from.name' => env('PROD_MAIL_FROM_NAME'),
            ]);
            \Illuminate\Support\Facades\Mail::purge();
        }

        $mailboxId = $this->option('mailbox');

        $query = DmarcMailbox::where('status', 'active');
        if ($mailboxId) {
            $query->where('id', $mailboxId);
        }

        $mailboxes = $query->get();

        if ($mailboxes->isEmpty()) {
            $this->warn('Nebyly nalezeny žádné aktivní DMARC mailboxy.');
            return;
        }

        foreach ($mailboxes as $mailbox) {
            $this->info("Zpracovávám mailbox: {$mailbox->email}");
            $run = $service->ingest($mailbox);
            $this->info("Hotovo. Zpracováno {$run->reports_processed} reportů, chyby: {$run->errors_count}.");
        }
    }
}
