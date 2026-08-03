<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        // Conversation: Agent-Client (Property 1)
        DB::table('conversations')->updateOrInsert(
            ['property_id' => 1, 'subject' => 'Discussion Appartement Cocody - Paul Durand'],
            [
                'type' => 'direct',
                'is_archived' => false,
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ]
        );

        // Récupérer l'ID réel (lastInsertId retourne 0 après un UPDATE)
        $conversation = DB::table('conversations')
            ->where('property_id', 1)
            ->where('subject', 'Discussion Appartement Cocody - Paul Durand')
            ->first();

        if (!$conversation) {
            echo "❌ ChatSeeder: conversation non trouvée\n";
            return;
        }

        $conversationId = $conversation->id;

        // Participants (vider et recréer pour éviter les duplicatas)
        DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->delete();

        DB::table('conversation_participants')->insert([
            ['conversation_id' => $conversationId, 'user_id' => 2, 'created_at' => now()->subDays(2), 'updated_at' => now()],
            ['conversation_id' => $conversationId, 'user_id' => 4, 'created_at' => now()->subDays(2), 'updated_at' => now()],
        ]);

        // Messages (vider et recréer)
        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->delete();

        $messages = [
            ['conversation_id' => $conversationId, 'sender_id' => 4, 'message' => 'Bonjour, intéressé par votre appartement Cocody. Disponible pour visite?', 'is_read' => true, 'created_at' => now()->subDays(2)->addHours(10)],
            ['conversation_id' => $conversationId, 'sender_id' => 2, 'message' => 'Bonjour Paul, oui disponible samedi 14h. Propriétaire d\'accord.', 'is_read' => true, 'created_at' => now()->subDays(2)->addHours(11)],
            ['conversation_id' => $conversationId, 'sender_id' => 4, 'message' => 'Parfait, j\'y serai. Merci!', 'is_read' => true, 'created_at' => now()->subDays(2)->addHours(12)],
            ['conversation_id' => $conversationId, 'sender_id' => 2, 'message' => 'Visite OK, demande soumise et approuvée. Contrat prêt.', 'is_read' => false, 'created_at' => now()->subDay()->addHours(15)],
            ['conversation_id' => $conversationId, 'sender_id' => 4, 'message' => 'Super, merci pour votre réactivité!', 'is_read' => false, 'created_at' => now()->subDay()->addHours(16)],
        ];

        foreach ($messages as $msg) {
            $msg['updated_at'] = $msg['created_at'];
            $msg['type'] = 'text';
            DB::table('messages')->insert($msg);
        }

        echo "✅ Chat créé (Conv ID: {$conversationId}), 5 messages (2 unread)\n";
    }
}
