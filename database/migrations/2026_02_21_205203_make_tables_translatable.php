<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrace byla sloučena do původních create migrací pro kompatibilitu s Webglobe
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // V down metodě bychom měli teoreticky vracet na string/text,
        // ale jelikož v datech bude JSON, tak by to mohlo způsobit problémy.
        // Pro jednoduchost a bezpečnost v této fázi necháme json.
    }
};
