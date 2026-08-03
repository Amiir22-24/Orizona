<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * MySQL: MODIFY COLUMN pour changer l'ENUM et ajouter 'land'
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE properties MODIFY COLUMN catalog_type ENUM('residential', 'commercial', 'project', 'land') NOT NULL DEFAULT 'residential'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE properties MODIFY COLUMN catalog_type ENUM('residential', 'commercial', 'project') NOT NULL DEFAULT 'residential'");
    }
};
