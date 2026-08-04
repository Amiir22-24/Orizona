<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Ajoute la valeur 'land' au champ catalog_type.
     *
     * MySQL  : ALTER TABLE ... MODIFY COLUMN ENUM(...)
     * PostgreSQL : Les colonnes enum() de Laravel sont implémentées en VARCHAR
     *              avec une contrainte CHECK. On supprime l'ancienne contrainte
     *              et on en crée une nouvelle incluant 'land'.
     *
     * Solution universelle : on utilise DB::statement conditionné par le driver.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL : supprimer l'ancienne contrainte CHECK et en créer une nouvelle
            DB::statement("ALTER TABLE properties DROP CONSTRAINT IF EXISTS properties_catalog_type_check");
            DB::statement("ALTER TABLE properties ADD CONSTRAINT properties_catalog_type_check CHECK (catalog_type IN ('residential', 'commercial', 'project', 'land'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE properties MODIFY COLUMN catalog_type ENUM('residential', 'commercial', 'project', 'land') NOT NULL DEFAULT 'residential'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE properties DROP CONSTRAINT IF EXISTS properties_catalog_type_check");
            DB::statement("ALTER TABLE properties ADD CONSTRAINT properties_catalog_type_check CHECK (catalog_type IN ('residential', 'commercial', 'project'))");
        } else {
            DB::statement("ALTER TABLE properties MODIFY COLUMN catalog_type ENUM('residential', 'commercial', 'project') NOT NULL DEFAULT 'residential'");
        }
    }
};
