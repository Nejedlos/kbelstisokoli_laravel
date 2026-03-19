<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Pomocná funkce pro opravu JSON dat
        $fixJson = function (string $table, array $columns) {
            if (! Schema::hasTable($table)) return;

            foreach ($columns as $column) {
                try {
                    // 1. Vyčištění prázdných řetězců
                    DB::table($table)
                        ->where($column, '')
                        ->orWhere($column, '""')
                        ->update([$column => null]);

                    // 2. Oprava neplatných JSONů (zabalení do uvozovek)
                    DB::table($table)
                        ->whereNotNull($column)
                        ->select(['id', $column])
                        ->chunkById(500, function ($rows) use ($table, $column) {
                            foreach ($rows as $row) {
                                $value = $row->{$column};
                                if (! $this->isValidJson($value)) {
                                    DB::table($table)
                                        ->where('id', $row->id)
                                        ->update([$column => json_encode($value, JSON_UNESCAPED_UNICODE)]);
                                }
                            }
                        });
                } catch (\Throwable $e) {}
            }
        };

        // Tabulka ai_documents byla problematická
        if (Schema::hasTable('ai_documents')) {
            $fixJson('ai_documents', ['title', 'summary', 'content', 'keywords', 'metadata']);
            Schema::table('ai_documents', function (Blueprint $table) {
                // Převod na JSON typ v MySQL 8
                $table->json('title')->nullable()->change();
                $table->json('summary')->nullable()->change();
                $table->json('content')->nullable()->change();
                $table->json('keywords')->nullable()->change();
                $table->json('metadata')->nullable()->change();
            });
        }

        // Tabulka settings a value
        if (Schema::hasTable('settings')) {
            $fixJson('settings', ['value']);
            Schema::table('settings', function (Blueprint $table) {
                $table->json('value')->nullable()->change();
            });
        }

        // Tabulka feedback_reports (předchozí migrace ji sice měla, ale pro jistotu)
        if (Schema::hasTable('feedback_reports')) {
            $fixJson('feedback_reports', ['viewport', 'screen', 'meta']);
            Schema::table('feedback_reports', function (Blueprint $table) {
                $table->json('viewport')->nullable()->change();
                $table->json('screen')->nullable()->change();
                $table->json('meta')->nullable()->change();
            });
        }
    }

    /**
     * Pomocná metoda pro ověření JSONu v PHP
     */
    protected function isValidJson($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if ($value === 'null' || $value === 'true' || $value === 'false') {
            return true;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('ai_documents')) {
            Schema::table('ai_documents', function (Blueprint $table) {
                $table->string('title')->nullable()->change();
                $table->text('summary')->nullable()->change();
                $table->longText('content')->nullable()->change();
                $table->longText('keywords')->nullable()->change();
                $table->longText('metadata')->nullable()->change();
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('value')->nullable()->change();
            });
        }
    }
};
