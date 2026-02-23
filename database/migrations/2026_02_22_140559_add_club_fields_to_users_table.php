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
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('display_name')->nullable()->after('last_name');
            $table->string('phone_secondary')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('display_name');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('preferred_locale')->default('cs')->after('gender');
            $table->string('nationality')->nullable()->after('preferred_locale');
            $table->string('club_member_id')->unique()->nullable()->after('email');
            $table->string('payment_vs')->unique()->nullable()->after('club_member_id');
            $table->string('membership_status')->default('pending')->after('payment_vs');
            $table->string('membership_type')->nullable()->after('membership_status');
            $table->date('membership_started_at')->nullable()->after('membership_type');
            $table->date('membership_ended_at')->nullable()->after('membership_started_at');
            $table->boolean('finance_ok')->default(false)->after('membership_ended_at');
            $table->string('payment_method')->nullable()->after('finance_ok');
            $table->text('payment_note')->nullable()->after('payment_method');
            $table->string('address_street')->nullable()->after('payment_note');
            $table->string('address_city')->nullable()->after('address_street');
            $table->string('address_zip')->nullable()->after('address_city');
            $table->string('address_country')->default('CZ')->after('address_zip');
            $table->string('emergency_contact_name')->nullable()->after('address_country');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('public_contact_note')->nullable()->after('emergency_contact_phone');
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
