<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\OccupancyRequest;
use App\Models\OccupancyContract;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminWebController
 * Contrôleur web de la section Administration (tableau de bord, propriétés,
 * utilisateurs, messages d'aide/suggestion, notifications plateforme).
 */
class AdminWebController extends Controller
{
    /**
     * Vue d'ensemble admin avec statistiques globales.
     * GET /admin/dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_properties'      => Property::count(),
            'available_properties'  => Property::where('is_available', true)->where('is_occupied', false)->count(),
            'occupied_properties'   => Property::where('is_occupied', true)->count(),
            'pending_properties'    => Property::where('status', 'pending')->count(),
            'validated_properties'  => Property::where('status', 'validated')->count(),
            'rejected_properties'   => Property::where('status', 'rejected')->count(),
            'total_users'           => User::count(),
            'admin_users'           => User::where('user_type', 'admin')->count(),
            'agent_users'           => User::where('user_type', 'agent')->count(),
            'owner_users'           => User::where('user_type', 'owner')->count(),
            'client_users'          => User::where('user_type', 'user')->count(),
            'total_requests'        => OccupancyRequest::count(),
            'pending_requests'      => OccupancyRequest::whereIn('status', ['pending_agent', 'pending_owner'])->count(),
            'approved_requests'     => OccupancyRequest::where('status', 'approved')->count(),
            'rejected_requests'     => OccupancyRequest::where('status', 'rejected')->count(),
            'total_contracts'       => OccupancyContract::count(),
            'active_contracts'      => OccupancyContract::where('is_active', true)->count(),
            'total_transactions'    => (float) Transaction::where('status', 'succeeded')->sum('amount'),
            'total_support'         => Conversation::whereNotNull('admin_id')->count(),
            'unread_notifications'  => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
        ];

        $recentProperties = Property::with(['owner', 'agent'])->latest()->take(5)->get();
        $recentRequests   = OccupancyRequest::with(['property', 'client'])->latest()->take(5)->get();
        $recentContracts  = OccupancyContract::with(['property', 'tenant'])->latest()->take(5)->get();
        $recentSupport    = Conversation::with(['client', 'agent'])->whereNotNull('admin_id')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentProperties', 'recentRequests', 'recentContracts', 'recentSupport'));
    }

    /**
     * Liste des propriétés avec filtres.
     * GET /admin/properties?filter=all|available|occupied|pending|validated|rejected
     */
    public function properties(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');

        $query = Property::with(['owner', 'agent', 'occupancyRequests'])->latest();

        switch ($filter) {
            case 'available':
                $query->where('is_available', true)->where('is_occupied', false);
                break;
            case 'occupied':
                $query->where('is_occupied', true);
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'validated':
                $query->where('status', 'validated');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $properties = $query->get();

        $counts = [
            'all'       => Property::count(),
            'available' => Property::where('is_available', true)->where('is_occupied', false)->count(),
            'occupied'  => Property::where('is_occupied', true)->count(),
            'pending'   => Property::where('status', 'pending')->count(),
            'validated' => Property::where('status', 'validated')->count(),
            'rejected'  => Property::where('status', 'rejected')->count(),
        ];

        return view('admin.properties', compact('properties', 'counts', 'filter', 'search'));
    }

    /**
     * Détail d'une propriété : discussions, demandes, contrats liés.
     * GET /admin/properties/{id}
     */
    public function propertyDetail($id)
    {
        $property = Property::with(['owner', 'agent', 'occupiedBy', 'rejectedBy'])->findOrFail($id);

        $requests = OccupancyRequest::with(['client', 'agent', 'rejector'])
            ->where('property_id', $id)
            ->latest()
            ->get();

        $contracts = OccupancyContract::with(['tenant', 'agent'])
            ->where('property_id', $id)
            ->latest()
            ->get();

        $conversations = Conversation::with(['client', 'agent', 'messages'])
            ->where('property_id', $id)
            ->latest()
            ->get();

        $transactions = Transaction::with('user')
            ->where('property_id', $id)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.property-detail', compact('property', 'requests', 'contracts', 'conversations', 'transactions'));
    }

    /**
     * Liste des utilisateurs classés par type.
     * GET /admin/users?type=all|admin|agent|owner|user&search=
     */
    public function users(Request $request)
    {
        $type   = $request->get('type', 'all');
        $search = $request->get('search');

        $query = User::with(['ownerProfile', 'agentProfile'])->latest();

        if ($type !== 'all') {
            $query->where('user_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('matricule', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->get();

        $counts = [
            'all'    => User::count(),
            'admin'  => User::where('user_type', 'admin')->count(),
            'agent'  => User::where('user_type', 'agent')->count(),
            'owner'  => User::where('user_type', 'owner')->count(),
            'user'   => User::where('user_type', 'user')->count(),
        ];

        return view('admin.users', compact('users', 'counts', 'type', 'search'));
    }

    /**
     * Détail d'un utilisateur : infos, propriétés, contrats, demandes.
     * GET /admin/users/{id}
     */
    public function userDetail($id)
    {
        $user = User::with(['ownerProfile', 'agentProfile'])->findOrFail($id);

        $properties = Property::where('owner_id', $id)
            ->orWhere('agent_id', $id)
            ->latest()
            ->get();

        $requestsAsClient = OccupancyRequest::with('property')->where('client_id', $id)->latest()->get();
        $requestsAsOwner  = OccupancyRequest::with(['property', 'client'])->where('owner_id', $id)->latest()->get();
        $requestsAsAgent  = OccupancyRequest::with(['property', 'client'])->where('agent_id', $id)->latest()->get();

        $contractsAsTenant = OccupancyContract::with('property')->where('tenant_id', $id)->latest()->get();
        $contractsAsOwner  = OccupancyContract::with(['property', 'tenant'])->where('owner_id', $id)->latest()->get();
        $contractsAsAgent  = OccupancyContract::with(['property', 'tenant'])->where('agent_id', $id)->latest()->get();

        $conversations = Conversation::where('client_id', $id)
            ->orWhere('agent_id', $id)
            ->orWhere('admin_id', $id)
            ->latest()
            ->get();

        return view('admin.user-detail', compact(
            'user',
            'properties',
            'requestsAsClient',
            'requestsAsOwner',
            'requestsAsAgent',
            'contractsAsTenant',
            'contractsAsOwner',
            'contractsAsAgent',
            'conversations'
        ));
    }

    /**
     * Messages d'aide / suggestions envoyés par les utilisateurs.
     * GET /admin/support
     */
    public function support()
    {
        $conversations = Conversation::with(['client', 'agent', 'messages', 'messages.sender'])
            ->whereNotNull('admin_id')
            ->latest()
            ->get();

        return view('admin.support', compact('conversations'));
    }

    /**
     * Répondre à un message d'aide/suggestion d'un utilisateur.
     * POST /admin/support/{id}/reply
     */
    public function supportReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $conversation = Conversation::with(['client', 'agent'])->findOrFail($id);

        if (!$conversation->admin_id) {
            abort(403, 'Action non autorisée.');
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'message'         => $request->message,
            'is_read'         => false,
        ]);

        $conversation->update([
            'last_message'    => $request->message,
            'last_message_at' => now(),
        ]);

        // Notifier le client que l'admin a répondu
        if ($conversation->client_id) {
            Notification::create([
                'user_id' => $conversation->client_id,
                'type'    => 'support_response',
                'title'   => 'Réponse de l\'administration',
                'message' => 'L\'administration a répondu à votre demande d\'aide.',
                'data'    => ['conversation_id' => $conversation->id],
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Votre réponse a été envoyée.');
    }

    /**
     * Notifications plateforme (toutes les alertes reçues par l'admin).
     * GET /admin/notifications
     */
    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->get();

        // Marquer comme lues
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.notifications', compact('notifications'));
    }

    /**
     * Marquer toutes les notifications comme lues.
     * POST /admin/notifications/read-all
     */
    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Valider une propriété depuis le détail admin.
     * POST /admin/properties/{id}/approve
     */
    public function approveProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'validated', 'was_auto_validated' => false]);

        Notification::create([
            'user_id' => $property->owner_id,
            'type'    => 'property_validated',
            'title'   => 'Propriété validée',
            'message' => "Votre propriété \"{$property->title}\" a été validée par un administrateur.",
            'data'    => ['property_id' => $property->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Propriété validée avec succès.');
    }

    /**
     * Rejeter une propriété depuis le détail admin.
     * POST /admin/properties/{id}/reject
     */
    public function rejectProperty(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $reason = $request->get('reason', 'Propriété non conforme');

        $property->update([
            'status'          => 'rejected',
            'rejection_reason'=> $reason,
        ]);

        Notification::create([
            'user_id' => $property->owner_id,
            'type'    => 'property_rejected',
            'title'   => 'Propriété rejetée',
            'message' => "Votre propriété \"{$property->title}\" a été rejetée. Motif : {$reason}",
            'data'    => ['property_id' => $property->id],
            'is_read' => false,
        ]);

        return back()->with('success', 'Propriété rejetée.');
    }

    /**
     * Changer le statut d'un utilisateur.
     * POST /admin/users/{id}/status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:validated,inactive,rejected,pending',
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]);

        Notification::create([
            'user_id' => $user->id,
            'type'    => 'account_status_changed',
            'title'   => 'Statut du compte mis à jour',
            'message' => "Le statut de votre compte a été changé en : {$request->status}",
            'is_read' => false,
        ]);

        return back()->with('success', 'Statut de l\'utilisateur mis à jour.');
    }

    /**
     * Supprimer une propriété (Admin).
     * DELETE /admin/properties/{id}
     */
    public function destroyProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return redirect()->route('admin.web.properties')->with('success', 'La propriété a été supprimée avec succès.');
    }

    /**
     * Inscrire un Agent (Admin).
     * POST /admin/users/agents
     */
    public function storeAgent(Request $request)
    {
        return $this->storeUserByAdmin($request, 'agent');
    }

    /**
     * Inscrire un Propriétaire (Admin).
     * POST /admin/users/owners
     */
    public function storeOwner(Request $request)
    {
        return $this->storeUserByAdmin($request, 'owner');
    }

    private function storeUserByAdmin(Request $request, $role)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|unique:users,phone|max:30',
        ]);

        $plainPassword = \Illuminate\Support\Str::random(10);
        $matriculePrefix = $role === 'owner' ? 'OWN' : 'AGT';
        $year = now()->format('Y');
        $count = User::where('matricule', 'LIKE', "{$matriculePrefix}-{$year}%")->count();
        $matricule = "{$matriculePrefix}-{$year}-" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => \Illuminate\Support\Facades\Hash::make($plainPassword),
            'user_type'  => $role,
            'status'     => 'validated',
            'matricule'  => $matricule,
        ]);

        if ($role === 'owner') {
            \App\Models\OwnerProfile::create([
                'user_id'           => $user->id,
                'owner_type'        => 'individual',
                'is_active'         => true,
                'validation_status' => 'validated',
            ]);
        } elseif ($role === 'agent') {
            \App\Models\AgentProfile::create([
                'user_id'           => $user->id,
                'agency_name'       => 'Orizona',
                'is_active'         => true,
                'validation_status' => 'validated',
            ]);
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserRegisteredByAdminMail($user, $plainPassword));
        } catch (\Exception $e) {
            // Ignorer l'erreur d'email
        }

        return back()->with('success', ucfirst($role) . ' créé avec succès. L\'email a été envoyé.');
    }
}

