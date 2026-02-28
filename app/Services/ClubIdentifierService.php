<?php

namespace App\Services;

use App\Models\User;

class ClubIdentifierService
{
    /**
     * Vygeneruje unikátní variabilní symbol.
     * Formát: RRMMXXXX (RR = rok, MM = měsíc, XXXX = náhodné číslo)
     * Celkem 8 cifer pro "hezký" variabilní symbol.
     */
    public function generatePaymentVs(): string
    {
        $yearMonth = date('ym');

        do {
            $number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $vs = $yearMonth.$number;
        } while (User::where('payment_vs', $vs)->exists());

        return $vs;
    }

    /**
     * Vygeneruje unikátní ID člena.
     * Formát: KS-RRXXXX (RR = rok, XXXX = náhodné číslo)
     */
    public function generateClubMemberId(): string
    {
        $year = date('y');

        do {
            $number = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $id = 'KS-'.$year.$number;
        } while (User::where('club_member_id', $id)->exists());

        return $id;
    }
}
