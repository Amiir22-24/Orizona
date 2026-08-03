<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * MySQL: utiliser change() pour rendre la colonne nullable
     */
    public function up(): void
    {
        Schema::table('occupancy_contracts', function (Blueprint $table) {
            $table->string('contract_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('occupancy_contracts', function (Blueprint $table) {
            $table->string('contract_url')->nullable(false)->change();
        });
    }
};
