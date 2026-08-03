<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        // AgentProfile pour l'agent
        DB::table('agent_profiles')->updateOrInsert(
            ['user_id' => 2], // Recherche par user_id
            [
                'registration_number' => 'AGT-2025-000001',
                'commission_rate' => 10.0,
                'validation_status' => 'validated',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // OwnerProfile pour le propriétaire
        DB::table('owner_profiles')->updateOrInsert(
            ['user_id' => 3], // Recherche par user_id
            [
                'owner_type' => 'individual',
                'company_name' => null,
                'validation_status' => 'validated',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        echo "✅ Profiles créés: AgentProfile(2), OwnerProfile(3)\n";
    }
}

