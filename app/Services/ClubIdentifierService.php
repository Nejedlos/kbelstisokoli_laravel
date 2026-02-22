<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ClubIdentifierService
{
    /**
     * Vygeneruje unikátní variabilní symbol.
     * Formát: RRXXXX (RR = rok, XXXX = náhodné číslo nebo sekvence)
     * Pro jednoduchost a unikátnost v tomto zadání použijeme rok + 4 náhodné číslice a ověříme v DB.
     */
    public function generatePaymentVs(): string
    {
        $year = date('y');

        do {
            $number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $vs = $year . $number;
        } while (User::where('payment_vs', $vs)->exists());

        return $vs;
    }

    /**
     * Vygeneruje unikátní ID člena.
     * Formát: KS-RRXXXX
     */
    public function generateClubMemberId(): string
    {
        $year = date('y');

        do {
            $number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $id = 'KS-' . $year . $number;
        } while (User::where('club_member_id', $id)->exists());

        return $id;
    }
}
