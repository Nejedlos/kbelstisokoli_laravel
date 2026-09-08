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
            if (Schema::hasColumn('attendances', 'status') && ! Schema::hasColumn('attendances', 'planned_status')) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->renameColumn('status', 'planned_status');
                });
            }

            Schema::table('attendances', function (Blueprint $table) {
                if (! Schema::hasColumn('attendances', 'actual_status')) {
                    $table->string('actual_status')->nullable()->after(Schema::hasColumn('attendances', 'planned_status') ? 'planned_status' : 'status');
                }
                if (! Schema::hasColumn('attendances', 'is_mismatch')) {
                    $table->boolean('is_mismatch')->default(false)->after('actual_status');
                }
                if (! Schema::hasColumn('attendances', 'metadata')) {
                    $table->longText('metadata')->nullable()->after('responded_at');
                }
            });

            Schema::table('finance_charges', function (Blueprint $table) {
                if (! Schema::hasColumn('finance_charges', 'metadata')) {
                    $table->longText('metadata')->nullable()->after('created_by_id');
                }
            });

            Schema::table('finance_payments', function (Blueprint $table) {
                if (! Schema::hasColumn('finance_payments', 'metadata')) {
                    $table->longText('metadata')->nullable()->after('recorded_by_id');
                }
            });

            return;
        }

        $prefix = DB::getTablePrefix();

        // Attendances
        $tableAtt = $prefix.'attendances';
        try {
            // Rename status -> planned_status
            $columnStatus = DB::select("SHOW COLUMNS FROM {$tableAtt} LIKE 'status'");
            $columnPlanned = DB::select("SHOW COLUMNS FROM {$tableAtt} LIKE 'planned_status'");
            if (! empty($columnStatus) && empty($columnPlanned)) {
                DB::statement("ALTER TABLE {$tableAtt} CHANGE status planned_status VARCHAR(255)");
            }

            // actual_status
            $columnActual = DB::select("SHOW COLUMNS FROM {$tableAtt} LIKE 'actual_status'");
            if (empty($columnActual)) {
                DB::statement("ALTER TABLE {$tableAtt} ADD COLUMN actual_status VARCHAR(255) NULL AFTER planned_status");
            }

            // is_mismatch
            $columnMismatch = DB::select("SHOW COLUMNS FROM {$tableAtt} LIKE 'is_mismatch'");
            if (empty($columnMismatch)) {
                DB::statement("ALTER TABLE {$tableAtt} ADD COLUMN is_mismatch TINYINT(1) DEFAULT 0 NOT NULL AFTER actual_status");
            }

            // metadata
            $columnMetadata = DB::select("SHOW COLUMNS FROM {$tableAtt} LIKE 'metadata'");
            if (empty($columnMetadata)) {
                DB::statement("ALTER TABLE {$tableAtt} ADD COLUMN metadata LONGTEXT NULL AFTER responded_at");
            }
        } catch (Throwable $e) {
        }

        // Finance Charges
        $tableCharges = $prefix.'finance_charges';
        try {
            $columnMetadata = DB::select("SHOW COLUMNS FROM {$tableCharges} LIKE 'metadata'");
            if (empty($columnMetadata)) {
                DB::statement("ALTER TABLE {$tableCharges} ADD COLUMN metadata LONGTEXT NULL AFTER created_by_id");
            }
        } catch (Throwable $e) {
        }

        // Finance Payments
        $tablePayments = $prefix.'finance_payments';
        try {
            $columnMetadata = DB::select("SHOW COLUMNS FROM {$tablePayments} LIKE 'metadata'");
            if (empty($columnMetadata)) {
                DB::statement("ALTER TABLE {$tablePayments} ADD COLUMN metadata LONGTEXT NULL AFTER recorded_by_id");
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('finance_payments', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            Schema::table('finance_charges', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });

            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn(['is_mismatch', 'actual_status']);
                $table->renameColumn('planned_status', 'status');
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        try {
            DB::statement("ALTER TABLE {$prefix}finance_payments DROP COLUMN IF EXISTS metadata");
            DB::statement("ALTER TABLE {$prefix}finance_charges DROP COLUMN IF EXISTS metadata");
            DB::statement("ALTER TABLE {$prefix}attendances DROP COLUMN IF EXISTS is_mismatch");
            DB::statement("ALTER TABLE {$prefix}attendances DROP COLUMN IF EXISTS actual_status");
            DB::statement("ALTER TABLE {$prefix}attendances CHANGE planned_status status VARCHAR(255)");
        } catch (Throwable $e) {
        }
    }
};
