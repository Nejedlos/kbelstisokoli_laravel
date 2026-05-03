<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DmarcTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dmarc:test-send {--email=dmarc@kbelstisokoli.cz : Cílový email pro report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odešle simulovaný kritický DMARC report pro otestování pipeline.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetEmail = $this->option('email');
        $this->info("Připravuji testovací DMARC report pro: {$targetEmail}");

        $xml = $this->generateFakeXml();
        $filename = 'google.com!' . config('app.url') . '!1714694400!1714780800.xml';

        // Zabalíme do GZIP, protože tak to obvykle chodí
        $gzData = gzencode($xml, 9);
        $gzFilename = $filename . '.gz';

        $this->info("Odesílám e-mail s přílohou {$gzFilename}...");

        try {
            Mail::raw('Toto je automaticky generovaný TESTOVACÍ DMARC aggregate report pro ověření funkčnosti monitoringu.', function ($message) use ($targetEmail, $gzData, $gzFilename) {
                $message->to($targetEmail)
                    ->subject('Report Domain: kbelstisokoli.cz Submitter: google.com Report-ID: <TEST-REPORT-123>')
                    ->attachData($gzData, $gzFilename, [
                        'mime' => 'application/gzip',
                    ]);
            });

            $this->success("E-mail byl úspěšně odeslán.");
            $this->info("Nyní vyčkejte na spuštění plánovače (nebo jej spusťte ručně pomocí php artisan dmarc:ingest).");
            $this->info("Poté byste měl obdržet varovný e-mail na technický kontakt.");
        } catch (\Exception $e) {
            $this->error("Chyba při odesílání: " . $e->getMessage());
        }
    }

    protected function generateFakeXml(): string
    {
        $begin = 1714694400;
        $end = 1714780800;
        $domain = 'kbelstisokoli.cz';

        return <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<feedback>
  <report_metadata>
    <org_name>Google Inc. (TEST)</org_name>
    <email>noreply-dmarc-support@google.com</email>
    <extra_contact_info>https://support.google.com/a/answer/2466563</extra_contact_info>
    <report_id>TEST-REPORT-CRITICAL-123</report_id>
    <date_range>
      <begin>{$begin}</begin>
      <end>{$end}</end>
    </date_range>
  </report_metadata>
  <policy_published>
    <domain>{$domain}</domain>
    <adkim>r</adkim>
    <aspf>r</aspf>
    <p>none</p>
    <sp>none</sp>
    <pct>100</pct>
  </policy_published>
  <record>
    <row>
      <source_ip>1.2.3.4</source_ip>
      <count>5</count>
      <policy_evaluated>
        <disposition>none</disposition>
        <dkim>fail</dkim>
        <spf>fail</spf>
      </policy_evaluated>
    </row>
    <identifiers>
      <header_from>{$domain}</header_from>
    </identifiers>
    <auth_results>
      <dkim>
        <domain>malicious-sender.com</domain>
        <result>fail</result>
        <selector>default</selector>
      </dkim>
      <spf>
        <domain>malicious-sender.com</domain>
        <result>fail</result>
      </spf>
    </auth_results>
  </record>
</feedback>
XML;
    }

    protected function success($message)
    {
        $this->output->writeln("<info>✔</info> {$message}");
    }
}
