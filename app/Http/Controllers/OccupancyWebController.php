<?php

namespace App\Http\Controllers;

use App\Models\OccupancyRequest;
use App\Models\OccupancyContract;
use App\Models\Property;
use App\Models\Notification;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\PropertyFavorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OccupancyWebController
 * Gère les actions du workflow d'occupation via l'interface web Blade.
 * Réutilise la même logique que OccupancyController (API) mais retourne
 * des redirections avec messages flash au lieu de réponses JSON.
 */
class OccupancyWebController extends Controller
{
    /**
     * Client soumet une demande d'occupation pour une propriété.
     * POST /web/occupancy-requests
     */
    public function request(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'message'     => 'nullable|string|max:1000',
            'start_date'  => 'nullable|date',
        ]);

        $property = Property::findOrFail($request->property_id);

        if (!$property->is_available) {
            return back()->with('error', 'Cette propriété n\'est plus disponible.');
        }

        // Vérifie si une demande est déjà en cours
        $existing = OccupancyRequest::where('property_id', $property->id)
            ->where('client_id', Auth::id())
            ->whereIn('status', ['pending_agent', 'pending_owner'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous avez déjà une demande en cours pour cette propriété.');
        }

        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->addDay();

        $occupancyRequest = OccupancyRequest::create([
            'property_id'     => $request->property_id,
            'client_id'       => Auth::id(),
            'agent_id'        => $property->agent_id,
            'owner_id'        => $property->owner_id,
            'status'          => $property->agent_id ? 'pending_agent' : 'pending_owner',
            'message'         => $request->message ?? '',
            'rent_amount'     => $property->price,
            'proposed_amount' => $property->price,
            'start_date'      => $startDate,
            'end_date'        => (clone $startDate)->addYear(),
        ]);

        // Notifier l'agent s'il existe
        if ($property->agent_id) {
            Notification::create([
                'user_id' => $property->agent_id,
                'type'    => 'occupancy_request_agent',
                'title'   => 'Nouvelle demande d\'occupation',
                'message' => 'Un client est intéressé par la propriété : ' . $property->title,
                'data'    => ['request_id' => $occupancyRequest->id, 'property_id' => $property->id],
                'is_read' => false,
            ]);
        }

        // Notifier le propriétaire
        if ($property->owner_id) {
            Notification::create([
                'user_id' => $property->owner_id,
                'type'    => 'occupancy_request',
                'title'   => 'Nouvelle demande d\'occupation',
                'message' => 'Un client a demandé à occuper votre propriété : ' . $property->title,
                'data'    => ['request_id' => $occupancyRequest->id],
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Votre demande a bien été envoyée. L\'agent sera contacté.');
    }

    /**
     * Agent approuve une demande → passe en pending_owner.
     * POST /web/occupancy-requests/{id}/agent-approve
     */
    public function agentApprove(Request $request, $id)
    {
        $req = OccupancyRequest::with('property')->findOrFail($id);

        if ($req->agent_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $req->update([
            'status'             => 'pending_owner',
            'agent_notes'        => $request->notes ?? '',
            'agent_validated_at' => now(),
        ]);

        // Notifier le propriétaire
        Notification::create([
            'user_id' => $req->owner_id,
            'type'    => 'occupancy_request_owner',
            'title'   => 'Demande approuvée par l\'agent',
            'message' => 'L\'agent a approuvé une demande d\'occupation. Votre validation est requise.',
            'data'    => ['request_id' => $req->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Demande approuvée. Le propriétaire va être notifié.');
    }

    /**
     * Agent refuse une demande.
     * POST /web/occupancy-requests/{id}/agent-reject
     */
    public function agentReject(Request $request, $id)
    {
        $req = OccupancyRequest::findOrFail($id);

        if ($req->agent_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $req->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason ?? 'Refusé par l\'agent',
            'rejected_by'      => Auth::id(),
        ]);

        // Notifier le client
        Notification::create([
            'user_id' => $req->client_id,
            'type'    => 'occupancy_rejected',
            'title'   => 'Demande refusée',
            'message' => 'Votre demande d\'occupation a été refusée par l\'agent.',
            'data'    => ['request_id' => $req->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Demande refusée.');
    }

    /**
     * Propriétaire approuve une demande → crée le contrat.
     * POST /web/occupancy-requests/{id}/owner-approve
     */
    public function ownerApprove(Request $request, $id)
    {
        $req = OccupancyRequest::with('property')->findOrFail($id);

        if ($req->owner_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        // Crée le contrat
        $contract = OccupancyContract::create([
            'occupancy_request_id' => $req->id,
            'property_id'          => $req->property_id,
            'tenant_id'            => $req->client_id,
            'owner_id'             => $req->owner_id,
            'agent_id'             => $req->agent_id,
            'start_date'           => $req->start_date ?? now(),
            'end_date'             => $req->end_date ?? now()->addYear(),
            'is_active'            => true,
            'monthly_rent'         => $req->property->price,
            'deposit_amount'       => 0,
            'signed_at'            => now(),
        ]);

        $req->update(['status' => 'approved']);

        // Marquer la propriété comme occupée
        $req->property->update([
            'is_available' => false,
            'is_occupied'  => true,
        ]);

        // Retirer la propriété des favoris de TOUS les autres clients (pas le locataire)
        PropertyFavorite::where('property_id', $req->property_id)
            ->where('user_id', '!=', $req->client_id)
            ->delete();

        // Notifier le client
        Notification::create([
            'user_id' => $req->client_id,
            'type'    => 'occupancy_approved',
            'title'   => 'Félicitations ! Votre demande est approuvée',
            'message' => 'Le propriétaire a accepté votre demande. Votre contrat est prêt.',
            'data'    => ['contract_id' => $contract->id],
            'is_read' => false,
        ]);

        // Notifier l'agent
        if ($req->agent_id) {
            Notification::create([
                'user_id' => $req->agent_id,
                'type'    => 'property_occupied_agent',
                'title'   => 'Contrat finalisé',
                'message' => 'Le propriétaire a validé la demande. Le contrat a été créé.',
                'data'    => ['contract_id' => $contract->id],
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Demande acceptée. Le contrat a été créé et le client notifié.');
    }

    /**
     * Propriétaire refuse une demande.
     * POST /web/occupancy-requests/{id}/owner-reject
     */
    public function ownerReject(Request $request, $id)
    {
        $req = OccupancyRequest::findOrFail($id);

        if ($req->owner_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $req->update([
            'status'                 => 'rejected',
            'owner_rejection_reason' => $request->reason ?? 'Refusé par le propriétaire',
            'owner_reviewed_at'      => now(),
            'rejected_by'            => Auth::id(),
        ]);

        // Notifier le client
        Notification::create([
            'user_id' => $req->client_id,
            'type'    => 'occupancy_rejected',
            'title'   => 'Demande refusée',
            'message' => 'Le propriétaire n\'a pas accepté votre demande d\'occupation.',
            'data'    => ['request_id' => $req->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Demande refusée.');
    }

    /**
     * Client annule sa propre demande.
     * POST /web/occupancy-requests/{id}/cancel
     */
    public function clientCancel($id)
    {
        $req = OccupancyRequest::findOrFail($id);

        if ($req->client_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        if (!in_array($req->status, ['pending_agent', 'pending_owner'])) {
            return back()->with('error', 'Cette demande ne peut plus être annulée.');
        }

        $req->update(['status' => 'cancelled']);

        return back()->with('success', 'Votre demande a été annulée.');
    }

    /**
     * Ajoute ou retire une propriété des favoris du client.
     * POST /web/favorites/toggle/{propertyId}
     */
    public function toggleFavorite($propertyId)
    {
        $user = Auth::user();
        $property = Property::findOrFail($propertyId);

        $favorite = \App\Models\PropertyFavorite::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Propriété retirée de vos favoris.');
        }

        \App\Models\PropertyFavorite::create([
            'user_id'     => $user->id,
            'property_id' => $property->id,
        ]);

        return back()->with('success', 'Propriété ajoutée à vos favoris.');
    }

    /**
     * Marque toutes les notifications de l'utilisateur comme lues.
     * POST /web/notifications/mark-all-read
     */
    public function markAllNotificationsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Client supprime une demande refusée (status 'rejected').
     * DELETE /web/occupancy-requests/{id}/delete-rejected
     */
    public function clientDeleteRejected($id)
    {
        $req = OccupancyRequest::findOrFail($id);

        if ($req->client_id !== Auth::id()) {
            return back()->with('error', 'Action non autorisée.');
        }

        if ($req->status !== 'rejected') {
            return back()->with('error', 'Seules les demandes refusées peuvent être supprimées.');
        }

        $req->delete();

        return back()->with('success', 'La demande refusée a été supprimée.');
    }

    /**
     * AJAX : Récupérer les messages d'une conversation (polling).
     * GET /web/conversations/{id}/messages
     */
    public function getConversationMessages($id)
    {
        $user = Auth::user();
        $conversation = Conversation::with([
            'messages' => fn($q) => $q->with('sender')->orderBy('created_at', 'asc')
        ])->findOrFail($id);

        // Vérification d'accès : l'utilisateur doit être participant
        $isParticipant = $conversation->client_id === $user->id
            || $conversation->agent_id === $user->id
            || $conversation->admin_id === $user->id;

        if (!$isParticipant) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $messages = $conversation->messages->map(function ($m) use ($user) {
            return [
                'id'          => $m->id,
                'sender_name' => $m->sender->full_name ?? 'Utilisateur',
                'message'     => $m->message,
                'created_at'  => $m->created_at ? $m->created_at->format('d/m/Y H:i') : '',
                'is_me'       => ($m->sender_id === $user->id),
            ];
        });

        return response()->json([
            'messages'          => $messages,
            'last_message'      => $conversation->last_message,
            'last_message_at'   => $conversation->last_message_at,
        ]);
    }

    /**
     * Client envoie un message à l'agent concernant une propriété.
     * POST /web/messages/agent
     */
    public function sendMessageToAgent(Request $request)
    {
        $request->validate([
            'property_id'     => 'nullable|exists:properties,id',
            'conversation_id' => 'nullable|exists:conversations,id',
            'message'         => 'required|string|max:2000',
        ]);

        $sender = Auth::user();
        $conversation = null;

        if ($request->filled('conversation_id')) {
            $conversation = Conversation::with(['property', 'client', 'agent'])->find($request->conversation_id);
        }

        if (!$conversation && $request->filled('property_id')) {
            $property = Property::with('agent')->findOrFail($request->property_id);
            $agentId = $property->agent_id;
            if (!$agentId) {
                return back()->with('error', 'Cette propriété n\'a pas d\'agent associé.');
            }

            $clientId = ($sender->id === $agentId) ? ($request->client_id ?? $property->occupied_by_user_id ?? $sender->id) : $sender->id;

            $conversation = Conversation::where('property_id', $property->id)
                ->where('client_id', $clientId)
                ->where('agent_id', $agentId)
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'subject'     => 'Discussion — ' . $property->title,
                    'property_id' => $property->id,
                    'client_id'   => $clientId,
                    'agent_id'    => $agentId,
                    'status'      => 'active',
                ]);

                ConversationParticipant::firstOrCreate(
                    ['conversation_id' => $conversation->id, 'user_id' => $clientId]
                );
                ConversationParticipant::firstOrCreate(
                    ['conversation_id' => $conversation->id, 'user_id' => $agentId]
                );
            }
        }

        if (!$conversation) {
            return back()->with('error', 'Impossible d\'identifier la discussion cible.');
        }

        // Créer le message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'message'         => $request->message,
            'is_read'         => false,
        ]);

        // Mettre à jour la conversation
        $conversation->update([
            'last_message'    => $request->message,
            'last_message_at' => now(),
        ]);

        // Déterminer le destinataire de la notification
        $recipientId = ($sender->id === $conversation->agent_id) ? $conversation->client_id : $conversation->agent_id;
        $propertyTitle = $conversation->property->title ?? 'la propriété';
        $notifTitle  = ($sender->id === $conversation->agent_id) ? "Réponse de l'agent immobilier" : "Nouveau message de " . $sender->full_name;
        $notifBody   = ($sender->id === $conversation->agent_id)
            ? "L'agent " . $sender->full_name . " vous a répondu pour : " . $propertyTitle
            : $sender->full_name . " vous a envoyé un message pour : " . $propertyTitle;

        if ($recipientId) {
            Notification::create([
                'user_id' => $recipientId,
                'type'    => 'new_message_agent',
                'title'   => $notifTitle,
                'message' => $notifBody,
                'data'    => ['conversation_id' => $conversation->id, 'property_id' => $conversation->property_id],
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Votre message a été transmis avec succès.')->with('active_conv_id', $conversation->id);
    }

    /**
     * Client envoie un message à l'administrateur (support).
     * POST /web/messages/admin
     */
    public function sendMessageToAdmin(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);

        $client = Auth::user();

        // Trouver un admin actif
        $admin = User::where('user_type', 'admin')->first();
        if (!$admin) {
            return back()->with('error', 'Aucun administrateur disponible.');
        }

        // Trouver ou créer la conversation support client-admin
        $conversation = Conversation::where('client_id', $client->id)
            ->where('admin_id', $admin->id)
            ->whereNull('property_id')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'subject'  => $request->subject ?? 'Demande de support',
                'client_id' => $client->id,
                'admin_id'  => $admin->id,
                'status'    => 'active',
            ]);

            ConversationParticipant::firstOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $client->id]
            );
            ConversationParticipant::firstOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $admin->id]
            );
        }

        // Créer le message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $client->id,
            'message'         => $request->message,
            'is_read'         => false,
        ]);

        $conversation->update([
            'last_message'    => $request->message,
            'last_message_at' => now(),
        ]);

        // Notifier l'admin
        Notification::create([
            'user_id' => $admin->id,
            'type'    => 'client_support_message',
            'title'   => 'Message de support — ' . $client->full_name,
            'message' => substr($request->message, 0, 120),
            'data'    => ['conversation_id' => $conversation->id, 'client_id' => $client->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Votre message a été envoyé à l\'administration.');
    }

    /**
     * Aperçu du contrat pour le propriétaire avant approbation.
     * GET /web/contracts/preview/{requestId}
     */
    public function previewContract($requestId)
    {
        $user = Auth::user();
        $req = OccupancyRequest::with(['property', 'client', 'agent'])->findOrFail($requestId);

        if ($req->owner_id !== $user->id && !$user->is_admin) {
            abort(403, 'Action non autorisée.');
        }

        // Simuler un objet contrat à partir de la demande pour l'aperçu
        $contract = (object) [
            'id' => $req->id,
            'property' => $req->property,
            'tenant' => $req->client,
            'owner' => $user,
            'agent' => $req->agent,
            'monthly_rent' => $req->rent_amount ?? $req->property->price,
            'deposit_amount' => 0,
            'start_date' => $req->start_date,
            'end_date' => $req->end_date,
            'signed_at' => now(),
            'created_at' => $req->created_at,
            'is_preview' => true,
            'occupancy_request_id' => $req->id,
        ];

        return view('contracts.preview', compact('contract', 'req'));
    }

    /**
     * Visualiser ou télécharger un contrat de location.
     * GET /web/contracts/{id}
     */
    public function showContract($id)
    {
        $user = Auth::user();
        $contract = OccupancyContract::with(['property', 'tenant', 'owner', 'agent'])
            ->findOrFail($id);

        if (!$user->is_admin && $contract->tenant_id !== $user->id && $contract->owner_id !== $user->id && $contract->agent_id !== $user->id) {
            abort(403, 'Action non autorisée.');
        }

        // Si un fichier PDF physique existe dans le stockage, le servir
        if ($contract->getRawOriginal('contract_url')) {
            $rawPath = ltrim($contract->getRawOriginal('contract_url'), '/');
            $cleanPath = preg_replace('#^storage/#i', '', $rawPath);
            $fullStoragePath = storage_path('app/public/' . $cleanPath);

            if (file_exists($fullStoragePath) && is_file($fullStoragePath)) {
                return response()->file($fullStoragePath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="contract_' . $contract->id . '.pdf"'
                ]);
            }
        }

        // Sinon, afficher la vue HTML officielle du contrat numérique
        return view('contracts.show', compact('contract'));
    }

    /**
     * Agent répertorie une nouvelle propriété.
     * POST /web/agent/properties
     */
    public function agentStoreProperty(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'property_type'  => 'required|string',
            'operation_type' => 'required|string',
            'price'          => 'required|numeric|min:0',
            'city'           => 'required|string|max:255',
            'address'        => 'required|string|max:255',
            'neighborhood'   => 'nullable|string|max:255',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'surface_area'   => 'nullable|numeric|min:0',
            'description'    => 'nullable|string',
            'owner_matricule'=> 'nullable|string|exists:users,matricule',
            'owner_name'     => 'nullable|string|max:255',
            'photos'         => 'nullable|array',
            'photos.*'       => 'image|max:5000',
            'photo'          => 'nullable|image|max:5000',
            'video'          => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:20480', // 20MB max
        ]);

        $agent = Auth::user();

        $ownerId = $agent->id;
        $ownerName = $agent->full_name;

        if ($request->filled('owner_matricule')) {
            $owner = User::where('matricule', $request->owner_matricule)->first();
            if ($owner) {
                $ownerId = $owner->id;
                $ownerName = $owner->full_name;
            }
        } elseif ($request->filled('owner_name')) {
            $ownerName = $request->owner_name;
        }

        $photoData = [];
        if ($request->hasFile('photos')) {
            $files = $request->file('photos');
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('properties', 'public');
                    $photoData[] = [
                        'photo_url' => $path,
                        'is_main'   => ($index === 0),
                    ];
                }
            }
        } elseif ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file && $file->isValid()) {
                $path = $file->store('properties', 'public');
                $photoData[] = [
                    'photo_url' => $path,
                    'is_main'   => true,
                ];
            }
        }

        $videoUrl = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file && $file->isValid()) {
                $videoUrl = $file->store('property_videos', 'public');
            }
        }

        Property::create([
            'title'          => $request->title,
            'description'    => $request->description ?? 'Aucune description',
            'catalog_type'   => 'residential',
            'property_type'  => in_array($request->property_type, ['apartment','house','villa','studio','bureau','land','commercial','garage']) ? $request->property_type : 'apartment',
            'operation_type' => in_array($request->operation_type, ['rent','sale','lease','reservation']) ? $request->operation_type : 'rent',
            'price'          => $request->price,
            'currency'       => 'XOF',
            'price_period'   => 'monthly',
            'condition'      => 'good',
            'address'        => $request->address,
            'city'           => $request->city,
            'region'         => $request->region ?? 'Maritime',
            'neighborhood'   => $request->neighborhood ?? '',
            'latitude'       => $request->latitude ?? 6.1372,
            'longitude'      => $request->longitude ?? 1.2125,
            'bedrooms'       => $request->bedrooms ?? 0,
            'bathrooms'      => $request->bathrooms ?? 0,
            'surface_area'   => $request->surface_area ?? 0,
            'floors'         => $request->floors ?? 1,
            'star_rating'    => 5,
            'owner_id'       => $ownerId,
            'owner_name'     => $ownerName,
            'agent_id'       => $agent->id,
            'agent_name'     => $agent->full_name,
            'status'         => 'validated',
            'is_available'   => true,
            'is_occupied'    => false,
            'photos'         => $photoData,
            'video_url'      => $videoUrl,
            'amenities'      => [],
        ]);

        return back()->with('success', 'Propriété répertoriée avec succès !');
    }

    /**
     * Client libère une propriété qu'il occupe.
     * POST /web/client/property/{id}/release
     */
    public function releaseProperty(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        
        // Vérifier que la propriété est occupée par l'utilisateur courant (via le contrat actif ou les demandes approuvées)
        // Simplification : on vérifie s'il y a un contrat actif pour ce client
        $activeContract = OccupancyContract::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (!$activeContract && $property->occupied_by_user_id !== Auth::id()) {
            // Alternative si la logique de "occupied_by" est différente
            $approvedRequest = OccupancyRequest::where('property_id', $property->id)
                ->where('client_id', Auth::id())
                ->where('status', 'approved')
                ->first();
                
            if (!$approvedRequest) {
                return back()->with('error', 'Vous n\'occupez pas cette propriété.');
            }
        }

        // Marquer le contrat comme inactif s'il existe
        if (isset($activeContract)) {
            $activeContract->update(['is_active' => false]);
        }

        // Supprimer la propriété de la plateforme
        $property->delete();

        return redirect()->route('dashboard')->with('success', 'La propriété a été libérée et supprimée de la plateforme.');
    }
}
