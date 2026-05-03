<?php

namespace App\Services\Dmarc;

use App\Models\Dmarc\DmarcRecord;

class DmarcRecommendationService
{
    public function getRecommendations(array $analysis): array
    {
        $dkimAligned = $analysis['dkim_aligned'] ?? false;
        $spfAligned = $analysis['spf_aligned'] ?? false;
        $dmarcPass = $analysis['dmarc_pass'] ?? false;
        $knownSender = $analysis['known_sender'] ?? null;
        $ip = $analysis['source_ip'] ?? 'Neznámá IP';
        $disposition = $analysis['disposition'] ?? 'none';

        $summary = '';
        $probableCause = '';
        $whatToCheck = [];
        $howToFix = [];
        $policyRec = 'keep_none';

        if ($dmarcPass) {
            $summary = "Vše v pořádku. E-mail prošel DMARC autentizací.";
            $probableCause = "Legitimní odesílání.";
            $policyRec = 'none/quarantine/reject';
        } elseif (!$dkimAligned && !$spfAligned) {
            $summary = "Kritické selhání: E-mail z IP {$ip} neprošel SPF ani DKIM zarovnáním.";
            $probableCause = $knownSender
                ? "Legitimní služba ({$knownSender['name']}) není správně nastavena."
                : "Neznámý server se pokouší posílat e-maily za vaši doménu.";

            $whatToCheck = [
                "Zjistit, komu patří IP adresa {$ip}.",
                "Ověřit reverse DNS.",
                "Porovnat IP s interním seznamem služeb.",
                "Ověřit, zda v daný čas někdo skutečně odesílal legitimní e-mail."
            ];

            if ($knownSender) {
                $howToFix[] = "Přidat IP {$ip} nebo příslušný include do SPF záznamu.";
                $howToFix[] = "Nastavit DKIM podpis pro tuto službu.";
            } else {
                $howToFix[] = "Pokud IP není legitimní, nepřidávejte ji do SPF.";
            }

            $policyRec = ($disposition === 'none') ? 'quarantine' : 'reject';
        } elseif (!$spfAligned) {
            $summary = "SPF zarovnání selhalo, ale e-mail prošel díky DKIM.";
            $probableCause = "Časté u přeposílání e-mailů nebo pokud chybí Return-Path zarovnání.";
            $whatToCheck = [
                "Zkontrolovat Return-Path (Envelope From) doménu.",
                "Ověřit, zda poskytovatel podporuje 'Custom Bounce Domain'."
            ];
            $howToFix[] = "Nastavit vlastní obálkovou doménu u poskytovatele služeb.";
        } elseif (!$dkimAligned) {
            $summary = "DKIM zarovnání selhalo, ale e-mail prošel díky SPF.";
            $probableCause = "E-mail není podepsán vaší doménou nebo podpis chybí.";
            $whatToCheck = [
                "Zkontrolovat nastavení DKIM u poskytovatele.",
                "Ověřit existenci a správnost DKIM selektoru v DNS."
            ];
            $howToFix[] = "Nastavit DKIM podepisování pro doménu v administraci odesílací služby.";
        }

        return [
            'summary' => $summary,
            'probable_cause' => $probableCause,
            'what_to_check' => $whatToCheck,
            'how_to_fix' => $howToFix,
            'policy_recommendation' => $policyRec,
            'severity' => $analysis['severity'] ?? 'info',
            'risk_score' => $analysis['risk_score'] ?? 0,
        ];
    }
}
