<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seul le seeder Admin est activé — les autres seeders
     * (utilisateurs de test, propriétés, etc.) ne sont pas
     * fonctionnels dans cet environnement.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
        ]);

        echo "\n✅ SEEDER TERMINÉ\n";
        echo "Admin: admin@ori.com / password123\n";
        echo "Matricule: ADMIN-001\n";
    }
}
