<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * MySQL: MODIFY COLUMN pour changer le type de status
     */
    public function up(): void
    {
        Schema::table('occupancy_requests', function (Blueprint $table) {
            // Ajout des nouvelles colonnes si elles n'existent pas
            if (!Schema::hasColumn('occupancy_requests', 'agent_validated_at')) {
                $table->timestamp('agent_validated_at')->nullable();
            }
            if (!Schema::hasColumn('occupancy_requests', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable();
            }
        });

        // MySQL: changer ENUM en VARCHAR pour plus de flexibilité
        DB::statement("ALTER TABLE occupancy_requests MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('occupancy_requests', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['agent_validated_at', 'rejected_by']);
        });

        DB::statement("ALTER TABLE occupancy_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
