<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Agent
        DB::table('users')->updateOrInsert(
            ['email' => 'agent@ori.com'], // Clé de recherche unique
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'phone' => '+2250700000002',
                'password' => Hash::make('password123'),
                'user_type' => 'agent',
                'status' => 'validated',
                'matricule' => 'AGT-2025-000001',
                'address' => 'Abidjan, Cocody',
                'city' => 'Abidjan',
                'region' => 'Lagunes',
                'avatar' => 'https://ui-avatars.com/api/?name=Jean+Dupont&background=4caf50&color=fff&size=128&bold=true',
                'validation_notes' => 'Agent validé',
                'validated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Owner
        DB::table('users')->updateOrInsert(
            ['email' => 'owner@ori.com'],
            [
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'phone' => '+2250700000003',
                'password' => Hash::make('password123'),
                'user_type' => 'owner',
                'status' => 'validated',
                'matricule' => 'PROP-2025-000001',
                'address' => 'Abidjan, Marcory',
                'city' => 'Abidjan',
                'region' => 'Lagunes',
                'avatar' => 'https://ui-avatars.com/api/?name=Marie+Martin&background=f44336&color=fff&size=128&bold=true',
                'validation_notes' => 'Propriétaire validé',
                'validated_at' => now()->subDays(5),
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ]
        );

        // Client
        DB::table('users')->updateOrInsert(
            ['email' => 'client@ori.com'],
            [
                'first_name' => 'Paul',
                'last_name' => 'Durand',
                'phone' => '+2250700000004',
                'password' => Hash::make('password123'),
                'user_type' => 'user',
                'status' => 'validated',
                'address' => 'Abidjan, Treichville',
                'city' => 'Abidjan',
                'region' => 'Lagunes',
                'avatar' => 'https://ui-avatars.com/api/?name=Paul+Durand&background=ff9800&color=fff&size=128&bold=true',
                'validation_notes' => 'Client standard',
                'validated_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ]
        );

        echo "✅ Utilisateurs créés: Agent, Owner, Client\n";
    }
}

