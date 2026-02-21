<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path')->index();
            $table->string('target_path')->nullable();
            $table->string('target_url')->nullable();
            $table->string('target_type')->default('internal'); // internal, external
            $table->integer('status_code')->default(301); // 301, 302
            $table->boolean('is_active')->default(true);
            $table->string('match_type')->default('exact'); // exact, prefix, pattern
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('hits_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unikátní index pro aktivní přesměrování s exact matchem (prevence duplicit)
            // Poznámka: SQLite nepodporuje WHERE v unikátním indexu tak jednoduše,
            // proto raději budeme validovat v aplikaci, ale index pro výkon necháme.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
