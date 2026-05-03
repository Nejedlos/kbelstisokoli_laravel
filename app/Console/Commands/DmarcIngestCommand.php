<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Dmarc\DmarcMailbox;
use App\Services\Dmarc\DmarcImapService;

#[Signature('dmarc:ingest {--mailbox= : Specifické ID mailboxu}')]
#[Description('Stáhne a zpracuje DMARC aggregate reporty z IMAP mailboxů.')]
class DmarcIngestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DmarcImapService $service)
    {
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
