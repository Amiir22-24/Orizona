<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['name' => 'Basic'],
            [
                'price' => 5000,
                'duration_days' => 30,
                'features' => ['Jusqu\'à 3 propriétés', 'Support par email', 'Statistiques de base'],
                'is_active' => true,
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['name' => 'Premium'],
            [
                'price' => 15000,
                'duration_days' => 30,
                'features' => ['Jusqu\'à 15 propriétés', 'Mise en avant', 'Support prioritaire', 'Statistiques avancées'],
                'is_active' => true,
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 30000,
                'duration_days' => 30,
                'features' => ['Propriétés illimitées', 'Badge Vérifié Pro', 'Mise en avant Premium', 'Support dédié 24/7', 'Rapports exportables'],
                'is_active' => true,
            ]
        );
    }
}
