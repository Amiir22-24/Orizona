<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\OccupancyRequest;
use App\Models\Notification;
use App\Models\AgentProfile;
use App\Models\OwnerProfile;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\OccupancyContract;
use App\Http\Resources\UserResource;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * List all users
     */
    public function getUsers(Request $request) {
        $query = User::query()->orderBy('created_at', 'desc');
        if ($request->has('type')) $query->where('user_type', $request->type);
        if ($request->has('status')) $query->where('status', $request->status);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $query->paginate($request->get('per_page', 20))
            ],
            'stats' => [
                'total' => User::count(),
                'agents' => User::where('user_type', 'agent')->count(),
                'owners' => User::where('user_type', 'owner')->count(),
                'clients' => User::where('user_type', 'user')->count(),
            ]
        ]);
    }

    /**
     * Get user detail
     */
    public function getUserDetail(Request $request, $id)
    {
        $user = User::with(['agentProfile', 'ownerProfile'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Create user (Admin)
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:user,owner,agent,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if ($validated['user_type'] === 'agent') {
            return $this->createAgent($request);
        }

        if ($validated['user_type'] === 'owner') {
            return $this->createOwner($request);
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'status' => 'validated',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => UserResource::make($user),
        ], 201);
    }

    /**
     * Create a new owner (admin only)
     */
    public function createOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'owner_type' => 'nullable|in:individual,company',
            'company_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $year = now()->format('Y');
        $count = User::where('matricule', 'LIKE', "PROP-{$year}%")->count();
        $matricule = "PROP-{$year}-" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        $owner = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'owner',
            'status' => 'validated',
            'matricule' => $matricule,
        ]);

        $owner->ownerProfile()->create([
            'owner_type' => $validated['owner_type'] ?? 'individual',
            'company_name' => $validated['company_name'] ?? null,
            'is_active' => true,
            'validation_status' => 'validated',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Propriétaire créé avec succès',
            'data' => UserResource::make($owner->load('ownerProfile')),
        ], 201);
    }

    /**
     * List all owners
     */
    public function listOwners(Request $request)
    {
        $owners = User::with('ownerProfile')
            ->where('user_type', 'owner')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $owners]);
    }

    /**
     * Create a new agent
     */
    public function createAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $year = now()->format('Y');
        $count = User::where('matricule', 'LIKE', "AGT-{$year}%")->count();
        $matricule = "AGT-{$year}-" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        $agent = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'agent',
            'status' => 'validated',
            'matricule' => $matricule,
        ]);

        $agent->agentProfile()->create([
            'registration_number' => $matricule,
            'validation_status' => 'validated',
            'is_active' => true,
        ]);

        Notification::create([
            'user_id' => $agent->id,
            'type'    => 'agent_registered',
            'title'   => 'Compte Agent créé',
            'message' => 'Bienvenue, votre compte agent a été créé avec succès.',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agent créé avec succès',
            'data' => UserResource::make($agent->load('agentProfile')),
        ], 201);
    }

    /**
     * Get all agents
     */
    public function getAgents(Request $request)
    {
        $query = User::with(['agentProfile'])
            ->where('user_type', 'agent')
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $agents = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $agents,
            'stats' => [
                'total_agents' => User::where('user_type', 'agent')->count(),
                'validated_agents' => User::where('user_type', 'agent')->where('status', 'validated')->count(),
                'pending_agents' => User::where('user_type', 'agent')->where('status', 'pending')->count(),
            ]
        ]);
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:validated,inactive,rejected,active',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);
        $newStatus = $validator->validated()['status'];

        $user->update([
            'status' => $newStatus,
            'validation_notes' => $validator->validated()['notes'] ?? null,
            'validated_at' => ($newStatus === 'validated' || $newStatus === 'active') ? now() : $user->validated_at,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'account_status_changed',
            'title' => 'Statut du compte mis à jour',
            'message' => "Le statut de votre compte a été changé en: {$newStatus}",
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut utilisateur mis à jour',
            'data' => UserResource::make($user->fresh()),
        ]);
    }

    public function getPendingValidations(Request $request)
    {
        $pendingProperties = Property::where('status', 'pending')
            ->with(['owner', 'agent'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $pendingProperties,
            'stats' => [
                'total_pending' => Property::where('status', 'pending')->count(),
                'total_rejected' => Property::where('status', 'rejected')->count(),
            ]
        ]);
    }

    public function dashboardStats(Request $request) {
        $dashboardController = new DashboardController();
        return $dashboardController->admin($request);
    }

    public function dashboard(Request $request) {
        return $this->dashboardStats($request);
    }

    public function getAllProperties(Request $request) {
        return response()->json(['success' => true, 'data' => PropertyResource::collection(Property::with(['owner', 'agent'])->latest()->paginate($request->get('per_page', 20)))]);
    }

    public function getPropertyDetail(Request $request, $id) {
        return response()->json(['success' => true, 'data' => PropertyResource::make(Property::with(['owner', 'agent'])->findOrFail($id))]);
    }

    public function approveProperty(Request $request, $id) {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'validated', 'was_auto_validated' => false]);
        
        Notification::create([
            'user_id' => $property->owner_id,
            'type' => 'property_validated',
            'title' => 'Propriété validée',
            'message' => "Votre propriété \"{$property->title}\" a été validée par un administrateur.",
            'is_read' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Propriété validée par l\'admin', 'data' => PropertyResource::make($property)]);
    }

    public function rejectProperty(Request $request, $id) {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'rejected', 'rejection_reason' => $request->notes ?? $request->rejection_reason]);
        
        Notification::create([
            'user_id' => $property->owner_id,
            'type' => 'property_rejected',
            'title' => 'Propriété rejetée',
            'message' => "Votre propriété \"{$property->title}\" a été rejetée. Motif: " . ($request->notes ?? $request->rejection_reason),
            'is_read' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Propriété rejetée', 'data' => PropertyResource::make($property)]);
    }

    public function updateProperty(Request $request, $id) {
        $property = Property::findOrFail($id);
        $property->update($request->all());
        return response()->json(['success' => true, 'message' => 'Propriété mise à jour', 'data' => PropertyResource::make($property)]);
    }

    public function deleteProperty(Request $request, $id) {
        $property = Property::findOrFail($id);
        $property->delete();
        return response()->json(['success' => true, 'message' => 'Propriété supprimée']);
    }

    public function getNewProperties(Request $request) {
        return response()->json(['success' => true, 'data' => PropertyResource::collection(Property::with(['owner', 'agent'])->where('status', 'pending')->latest()->paginate($request->get('per_page', 20)))]);
    }

    public function getRejectedProperties(Request $request) {
        return response()->json(['success' => true, 'data' => PropertyResource::collection(Property::with(['owner', 'agent'])->where('status', 'rejected')->latest()->paginate($request->get('per_page', 20)))]);
    }

    public function getPropertyNotifications(Request $request) {
        return response()->json(['success' => true, 'data' => Notification::where('type', 'LIKE', 'property%')->latest()->paginate($request->get('per_page', 20))]);
    }

    public function getWithdrawals(Request $request) {
        $withdrawals = Withdrawal::with('user')->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
        return response()->json(['success' => true, 'data' => $withdrawals]);
    }

    public function approveWithdrawal(Request $request, $id) {
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update(['status' => 'approved', 'processed_at' => now(), 'notes' => $request->notes]);
        
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'type' => 'withdrawal_processed',
            'title' => 'Retrait Approuvé',
            'message' => "Votre demande de retrait de {$withdrawal->amount} XOF a été approuvée.",
            'is_read' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Retrait approuvé', 'data' => $withdrawal]);
    }

    public function rejectWithdrawal(Request $request, $id) {
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update(['status' => 'rejected', 'notes' => $request->notes]);

        Notification::create([
            'user_id' => $withdrawal->user_id,
            'type' => 'withdrawal_rejected',
            'title' => 'Retrait Rejeté',
            'message' => "Votre demande de retrait de {$withdrawal->amount} XOF a été rejetée.",
            'is_read' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Retrait rejeté', 'data' => $withdrawal]);
    }

    public function getTransactions(Request $request) {
        $transactions = Transaction::with(['user', 'property'])->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function getTransactionStats(Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'total_volume' => (float) Transaction::where('status', 'succeeded')->sum('amount'),
                'total_count' => Transaction::count(),
                'succeeded_count' => Transaction::where('status', 'succeeded')->count(),
            ]
        ]);
    }

    public function getReports(Request $request, $type) {
        return response()->json([
            'success' => true,
            'type' => $type,
            'data' => [
                'generated_at' => now()->toIso8601String(),
                'summary' => 'Rapport global ' . $type,
                'metrics' => [
                    'total_users' => User::count(),
                    'total_properties' => Property::count(),
                    'total_contracts' => OccupancyContract::count(),
                ]
            ]
        ]);
    }

    public function getGrowthStats(Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'properties_this_month' => Property::whereMonth('created_at', now()->month)->count(),
                'revenue_this_month' => (float) Transaction::whereMonth('created_at', now()->month)->sum('amount'),
            ]
        ]);
    }
}
