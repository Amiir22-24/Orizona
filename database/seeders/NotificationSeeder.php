<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            ['id' => 1, 'user_id' => 1, 'type' => 'agent_pending', 'title' => 'Nouvelle demande agent', 'message' => 'Jean Dupont demande validation', 'is_read' => false],
            ['id' => 2, 'user_id' => 1, 'type' => 'property_rejected', 'title' => 'Propriété rejetée', 'message' => 'Studio Treichville rejeté', 'is_read' => true],
            ['id' => 3, 'user_id' => 2, 'type' => 'occupancy_request', 'title' => 'Nouvelle demande', 'message' => 'Paul Durand pour Appart Cocody', 'is_read' => false],
            ['id' => 4, 'user_id' => 2, 'type' => 'commission_paid', 'title' => 'Commission reçue', 'message' => 'Commission Property 1: 25000 XOF', 'is_read' => true],
            ['id' => 5, 'user_id' => 3, 'type' => 'property_validated', 'title' => 'Bien validé', 'message' => 'Appart Cocody validé par admin', 'is_read' => false],
            ['id' => 6, 'user_id' => 3, 'type' => 'occupancy_approved', 'title' => 'Location approuvée', 'message' => 'Paul Durand approuvé Property 1', 'is_read' => true],
            ['id' => 7, 'user_id' => 4, 'type' => 'contract_signed', 'title' => 'Contrat signé', 'message' => 'Contrat Appart Cocody signé', 'is_read' => false],
        ];

        foreach ($notifications as $notif) {
            $notif['created_at'] = now()->subMinutes(rand(1, 1440));
            $notif['updated_at'] = $notif['created_at'];
            DB::table('notifications')->updateOrInsert(['id' => $notif['id']], $notif);
        }

        echo "✅ 7 notifications créées (mix read/unread pour tous users)\n";
    }
}

