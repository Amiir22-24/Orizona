<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Ajoute agent_validated_at, rejected_by à occupancy_requests
     * et change le type de status de ENUM → VARCHAR(50).
     *
     * MySQL  : ALTER TABLE ... MODIFY COLUMN status VARCHAR(50)
     * PostgreSQL : On utilise Schema Builder ->change() via doctrine/dbal
     *              OU on supprime l'ancienne contrainte CHECK et on change
     *              le type de colonne nativement.
     *
     * Solution universelle : conditionné par le driver DB.
     */
    public function up(): void
    {
        // 1. Ajout des nouvelles colonnes (identique pour tous les drivers)
        Schema::table('occupancy_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('occupancy_requests', 'agent_validated_at')) {
                $table->timestamp('agent_validated_at')->nullable();
            }
            if (!Schema::hasColumn('occupancy_requests', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable();
            }
        });

        // 2. Changer ENUM → VARCHAR(50) selon le driver
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL : supprimer la contrainte CHECK existante sur status,
            // puis modifier le type de la colonne en VARCHAR(50)
            DB::statement("ALTER TABLE occupancy_requests DROP CONSTRAINT IF EXISTS occupancy_requests_status_check");
            DB::statement("ALTER TABLE occupancy_requests ALTER COLUMN status TYPE VARCHAR(50) USING status::VARCHAR(50)");
            DB::statement("ALTER TABLE occupancy_requests ALTER COLUMN status SET DEFAULT 'pending'");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE occupancy_requests MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('occupancy_requests', function (Blueprint $table) {
            if (Schema::hasColumn('occupancy_requests', 'agent_validated_at')) {
                $table->dropColumn('agent_validated_at');
            }
            if (Schema::hasColumn('occupancy_requests', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
        });

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE occupancy_requests DROP CONSTRAINT IF EXISTS occupancy_requests_status_check");
            DB::statement("ALTER TABLE occupancy_requests ALTER COLUMN status TYPE VARCHAR(50) USING status::VARCHAR(50)");
            DB::statement("ALTER TABLE occupancy_requests ADD CONSTRAINT occupancy_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        } else {
            DB::statement("ALTER TABLE occupancy_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};
