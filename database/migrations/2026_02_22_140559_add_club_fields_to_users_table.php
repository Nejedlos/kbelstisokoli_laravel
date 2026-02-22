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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'display_name')) {
                $table->string('display_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'phone_secondary')) {
                $table->string('phone_secondary')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale')->default('cs')->after('gender');
            }
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable()->after('preferred_locale');
            }
            if (!Schema::hasColumn('users', 'club_member_id')) {
                $table->string('club_member_id')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'payment_vs')) {
                $table->string('payment_vs')->unique()->nullable()->after('club_member_id');
            }
            if (!Schema::hasColumn('users', 'membership_status')) {
                $table->string('membership_status')->default('pending')->after('payment_vs');
            }
            if (!Schema::hasColumn('users', 'membership_type')) {
                $table->string('membership_type')->nullable()->after('membership_status');
            }
            if (!Schema::hasColumn('users', 'membership_started_at')) {
                $table->date('membership_started_at')->nullable()->after('membership_type');
            }
            if (!Schema::hasColumn('users', 'membership_ended_at')) {
                $table->date('membership_ended_at')->nullable()->after('membership_started_at');
            }
            if (!Schema::hasColumn('users', 'finance_ok')) {
                $table->boolean('finance_ok')->default(false)->after('membership_ended_at');
            }
            if (!Schema::hasColumn('users', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('finance_ok');
            }
            if (!Schema::hasColumn('users', 'payment_note')) {
                $table->text('payment_note')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('users', 'address_street')) {
                $table->string('address_street')->nullable()->after('payment_note');
            }
            if (!Schema::hasColumn('users', 'address_city')) {
                $table->string('address_city')->nullable()->after('address_street');
            }
            if (!Schema::hasColumn('users', 'address_zip')) {
                $table->string('address_zip')->nullable()->after('address_city');
            }
            if (!Schema::hasColumn('users', 'address_country')) {
                $table->string('address_country')->default('CZ')->after('address_zip');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('address_country');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'public_contact_note')) {
                $table->text('public_contact_note')->nullable()->after('emergency_contact_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'display_name', 'date_of_birth', 'gender',
                'preferred_locale', 'nationality', 'club_member_id', 'payment_vs',
                'membership_status', 'membership_type', 'membership_started_at', 'membership_ended_at',
                'finance_ok', 'payment_method', 'payment_note', 'address_street', 'address_city',
                'address_zip', 'address_country', 'emergency_contact_name', 'emergency_contact_phone',
                'public_contact_note', 'phone_secondary'
            ]);
        });
    }
};
