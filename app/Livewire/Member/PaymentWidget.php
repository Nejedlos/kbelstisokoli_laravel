<?php

namespace App\Livewire\Member;

use App\Models\Setting;
use Livewire\Component;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class PaymentWidget extends Component
{
    public string $bankAccount;
    public string $bankName;
    public string $vs;
    public string $memberName;
    public string $note = '';
    public string $ss = '';
    public string $qrCodeDataUri = '';

    public function mount()
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $this->vs = $user->payment_vs ?: '';
        $this->memberName = $user->name;

        // Načteme nastavení z databáze
        $dbSettings = Setting::pluck('value', 'key')->toArray();
        $this->bankAccount = $dbSettings['bank_account'] ?? '6022854477/6363';
        $this->bankName = $dbSettings['bank_name'] ?? 'Air Bank a.s.';

        $this->generateQrCode();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['note', 'ss'])) {
            $this->generateQrCode();
        }
    }

    public function generateQrCode()
    {
        // Převedeme bankovní účet na IBAN
        $iban = $this->convertToIban($this->bankAccount);
        if (!$iban) {
            $this->qrCodeDataUri = '';
            return;
        }

        // SPAYD formát (Short Payment Descriptor)
        // SPD*1.0*ACC:iban*CC:CZK*VS:variable_symbol*SS:specific_symbol*MSG:message
        $spayd = "SPD*1.0*ACC:{$iban}*CC:CZK";

        if ($this->vs) {
            $spayd .= "*VS:{$this->vs}";
        }

        if ($this->ss) {
            $spayd .= "*SS:{$this->ss}";
        }

        // Pokud není zadána poznámka, použijeme jméno člena jako výchozí
        $message = $this->note ?: $this->memberName;
        if ($message) {
            $spayd .= "*MSG:" . $this->sanitizeMessage($message);
        }

        try {
            $options = new QROptions([
                'version'    => 4,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
                'scale'      => 5,
                'addQuietzone' => true,
            ]);

            $this->qrCodeDataUri = (new QRCode($options))->render($spayd);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QR Code generation failed: ' . $e->getMessage());
            $this->qrCodeDataUri = '';
        }
    }

    protected function sanitizeMessage(string $msg): string
    {
        // Limit pro SPAYD zprávu je obvykle 60-140 znaků, banky často berou jen 60
        $msg = mb_substr($msg, 0, 60);

        // Odstranění diakritiky (pro lepší kompatibilitu s bankovními systémy)
        $msg = \Illuminate\Support\Str::ascii($msg);

        // Ponechat jen alfanumerické znaky, mezery, tečky a čárky
        $msg = preg_replace('/[^A-Za-z0-9 ,.]/', '', $msg);

        // Odstranit vícenásobné mezery a oříznout
        $msg = preg_replace('/\s+/', ' ', $msg);
        return trim($msg);
    }

    protected function convertToIban(string $account): ?string
    {
        // Formát: [předčíslí-]číslo/banka
        if (!preg_match('/^(?:(\d{1,6})-)?(\d{1,10})\/(\d{4})$/', $account, $matches)) {
            return null;
        }

        $prefix = $matches[1] ? str_pad($matches[1], 6, '0', STR_PAD_LEFT) : '000000';
        $number = str_pad($matches[2], 10, '0', STR_PAD_LEFT);
        $bankCode = $matches[3];

        // IBAN CZ kód země (C=12, Z=35) + kontrolní číslice (zatím 00)
        // bbbb ssss sscc cccc cccc 12 35 00
        $checkString = $bankCode . $prefix . $number . '123500';

        // Modulo 97 s velkým číslem
        $mod = 0;
        for ($i = 0; $i < strlen($checkString); $i++) {
            $mod = ($mod * 10 + (int)$checkString[$i]) % 97;
        }

        $checkDigits = str_pad(98 - $mod, 2, '0', STR_PAD_LEFT);

        return "CZ{$checkDigits}{$bankCode}{$prefix}{$number}";
    }

    public function render()
    {
        return view('livewire.member.payment-widget');
    }
}
