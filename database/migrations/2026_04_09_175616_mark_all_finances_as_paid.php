<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Označit všechny "zapsané" platby za "potvrzené" (confirmed)
        DB::table('finance_payments')
            ->where('status', 'recorded')
            ->update([
                'status' => 'confirmed',
                'updated_at' => now(),
            ]);

        // 2. Označit všechny rozpracované předpisy za "uhrazené" (paid)
        // Toto zahrnuje stavy: open, partially_paid, overdue
        DB::table('finance_charges')
            ->whereIn('status', ['open', 'partially_paid', 'overdue'])
            ->update([
                'status' => 'paid',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Zpětný chod zde není bezpečný, protože neznáme původní stavy jednotlivých záznamů.
    }
};
