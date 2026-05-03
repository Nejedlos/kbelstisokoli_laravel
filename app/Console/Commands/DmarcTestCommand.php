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
    protected $signature = 'dmarc:test-send
                            {--email=dmarc@kbelstisokoli.cz : Cílový email pro report}
                            {--count=1 : Počet reportů k odeslání}';

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
        $count = (int) $this->option('count');

        // Pokud jsme v lokálním prostředí bez funkčního SMTP, zkusíme použít produkční konfiguraci pro odeslání
        if (config('mail.mailers.smtp.host') === '127.0.0.1' && env('PROD_MAIL_HOST')) {
            config([
                'mail.mailers.smtp.host' => env('PROD_MAIL_HOST'),
                'mail.mailers.smtp.port' => env('PROD_MAIL_PORT'),
                'mail.mailers.smtp.username' => env('PROD_MAIL_USERNAME'),
                'mail.mailers.smtp.password' => env('PROD_MAIL_PASSWORD'),
                'mail.mailers.smtp.encryption' => env('PROD_MAIL_ENCRYPTION'),
                'mail.from.address' => env('PROD_MAIL_FROM_ADDRESS'),
                'mail.from.name' => env('PROD_MAIL_FROM_NAME'),
            ]);
        }

        $this->info("Připravuji {$count} testovacích DMARC reportů pro: {$targetEmail}");

        $scenarios = [
            ['org' => 'google.com', 'ip' => '209.85.222.1', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'seznam.cz', 'ip' => '77.75.72.1', 'dkim' => 'pass', 'spf' => 'fail'],
            ['org' => 'outlook.com', 'ip' => '40.92.0.1', 'dkim' => 'fail', 'spf' => 'pass'],
            ['org' => 'yahoomail.com', 'ip' => '66.163.128.1', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'spam-bot.net', 'ip' => '190.12.34.56', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'unknown-relay.io', 'ip' => '5.6.7.8', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'attacker.ru', 'ip' => '91.200.12.34', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'phish-host.biz', 'ip' => '103.45.67.89', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'mail-tester.com', 'ip' => '94.23.206.89', 'dkim' => 'fail', 'spf' => 'fail'],
            ['org' => 'fake-google.com', 'ip' => '8.8.4.4', 'dkim' => 'fail', 'spf' => 'fail'],
        ];

        for ($i = 0; $i < $count; $i++) {
            $scenario = $scenarios[$i % count($scenarios)];
            $reportId = 'TEST-REPORT-' . time() . '-' . ($i + 1);

            $xml = $this->generateFakeXml($scenario, $reportId);
            $filename = "{$scenario['org']}!" . config('app.url') . '!' . time() . "!{$reportId}.xml";

            $gzData = gzencode($xml, 9);
            $gzFilename = $filename . '.gz';

            $this->info("({$i}/{$count}) Odesílám e-mail od {$scenario['org']} (IP: {$scenario['ip']})...");

            try {
                Mail::raw("Toto je automaticky generovaný TESTOVACÍ DMARC report č. " . ($i + 1) . " od {$scenario['org']} pro ověření funkčnosti monitoringu.", function ($message) use ($targetEmail, $gzData, $gzFilename, $scenario, $reportId) {
                    $message->to($targetEmail)
                        ->subject("Report Domain: kbelstisokoli.cz Submitter: {$scenario['org']} Report-ID: <{$reportId}>")
                        ->attachData($gzData, $gzFilename, [
                            'mime' => 'application/gzip',
                        ]);
                });
            } catch (\Exception $e) {
                $this->error("Chyba při odesílání reportu {$i}: " . $e->getMessage());
            }

            // Malá pauza, aby se neodeslalo vše v jednu sekundu a měly unikátní IDs/časy
            if ($count > 1) {
                usleep(500000); // 0.5s
            }
        }

        $this->success("Všech {$count} e-mailů bylo odesláno do fronty/odesílání.");
        $this->info("Nyní můžete spustit: php artisan dmarc:ingest");
    }

    protected function generateFakeXml(array $scenario, string $reportId): string
    {
        $begin = time() - 86400;
        $end = time();
        $domain = 'kbelstisokoli.cz';
        $org = $scenario['org'];
        $ip = $scenario['ip'];
        $dkim = $scenario['dkim'];
        $spf = $scenario['spf'];

        return <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<feedback>
  <report_metadata>
    <org_name>{$org} (TEST SCENARIO)</org_name>
    <email>dmarc-support@{$org}</email>
    <report_id>{$reportId}</report_id>
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
      <source_ip>{$ip}</source_ip>
      <count>1</count>
      <policy_evaluated>
        <disposition>none</disposition>
        <dkim>{$dkim}</dkim>
        <spf>{$spf}</spf>
      </policy_evaluated>
    </row>
    <identifiers>
      <header_from>{$domain}</header_from>
    </identifiers>
    <auth_results>
      <dkim>
        <domain>unknown-sender.net</domain>
        <result>{$dkim}</result>
        <selector>default</selector>
      </dkim>
      <spf>
        <domain>unknown-sender.net</domain>
        <result>{$spf}</result>
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
