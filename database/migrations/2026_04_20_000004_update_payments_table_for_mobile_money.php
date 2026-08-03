<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'property_id')) {
                $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('set null');
            }
            if (!Schema::hasColumn('payments', 'contract_id')) {
                $table->foreignId('contract_id')->nullable()->constrained('occupancy_contracts')->onDelete('set null');
            }
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 50)->default('mobile_money');
            }
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type', 50)->default('rent');
            }
            if (!Schema::hasColumn('payments', 'operator')) {
                $table->string('operator', 50)->nullable();
            }
            if (!Schema::hasColumn('payments', 'phone_number')) {
                $table->string('phone_number', 30)->nullable();
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('payments', 'ussd_code')) {
                $table->string('ussd_code', 20)->nullable();
            }
            if (!Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 10)->default('XOF');
            }
            if (!Schema::hasColumn('payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
            if (!Schema::hasColumn('payments', 'external_reference')) {
                $table->string('external_reference', 255)->nullable();
            }
            if (!Schema::hasColumn('payments', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });

        // Make stripe_charge_id nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_charge_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['property_id', 'contract_id', 'payment_method', 'payment_type', 'operator', 'phone_number', 'transaction_id', 'ussd_code', 'currency', 'verified_at', 'external_reference', 'metadata']);
        });
    }
};
