<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Crée ou met à jour le compte administrateur système.
     *
     * Utilise updateOrInsert pour être idempotent :
     * peut être relancé plusieurs fois sans créer de doublons.
     *
     * Connexion : admin@ori.com / password123
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@ori.com'],
            [
                'first_name'       => 'Super',
                'last_name'        => 'Admin',
                'phone'            => '+2250700000001',
                'password'         => Hash::make('password123'),
                'user_type'        => 'admin',
                'status'           => 'validated',
                'avatar'           => null,
                'validation_notes' => 'Super Admin système',
                'matricule'        => 'ADMIN-001',
                'is_admin'         => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

        $this->command->info('✅ Admin synchronisé: admin@ori.com / password123');
    }
}
