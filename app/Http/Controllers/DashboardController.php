<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\OccupancyRequest;
use App\Models\OccupancyContract;
use App\Models\Transaction;
use App\Models\PropertyFavorite;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * General stats endpoint that branches based on role
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        return match ($user->user_type) {
            'admin' => $this->admin($request),
            'agent' => $this->agent($request),
            'owner' => $this->owner($request),
            default => $this->client($request),
        };
    }

    /**
     * Admin Dashboard - Complete Spec Structure
     */
    public function admin(Request $request)
    {
        $data = [
            'users' => [
                'total' => User::count(),
                'admins' => User::where('user_type', 'admin')->count(),
                'agents' => User::where('user_type', 'agent')->count(),
                'owners' => User::where('user_type', 'owner')->count(),
                'users' => User::where('user_type', 'user')->count(),
                'active' => User::where('status', 'validated')->orWhere('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->orWhere('status', 'rejected')->count(),
            ],
            'properties' => [
                'total' => Property::count(),
                'pending_validation' => Property::where('status', 'pending')->count(),
                'validated' => Property::where('status', 'validated')->count(),
                'rejected' => Property::where('status', 'rejected')->count(),
                'available' => Property::where('is_available', true)->count(),
                'occupied' => Property::where('is_occupied', true)->count(),
                'residential' => Property::where('catalog_type', 'residential')->count(),
                'commercial' => Property::where('catalog_type', 'commercial')->count(),
                'project' => Property::where('catalog_type', 'project')->count(),
            ],
            'contracts' => [
                'total' => OccupancyContract::count(),
                'active' => OccupancyContract::where('is_active', true)->count(),
                'expired' => OccupancyContract::where('end_date', '<', now())->count(),
                'terminated' => OccupancyContract::where('is_active', false)->count(),
            ],
            'financial' => [
                'total_transactions' => (float) Transaction::where('status', 'succeeded')->sum('amount'),
                'monthly_transactions' => (float) Transaction::where('status', 'succeeded')->whereMonth('created_at', now()->month)->sum('amount'),
                'pending_payments' => (float) Payment::where('status', 'pending')->sum('amount'),
                'total_commissions' => (float) Commission::sum('amount'),
            ],
            'inquiries' => [
                'total' => OccupancyRequest::count(),
                'pending' => OccupancyRequest::where('status', 'pending')->count(),
                'contacted' => OccupancyRequest::where('status', 'agent_approved')->count(),
                'accepted' => OccupancyRequest::where('status', 'owner_approved')->count(),
                'rejected' => OccupancyRequest::where('status', 'rejected')->count(),
            ],
            'pending_validations' => [
                'agents' => User::where('user_type', 'agent')->where('status', 'pending')->count(),
                'owners' => User::where('user_type', 'owner')->where('status', 'pending')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Agent Dashboard - Complete Spec Structure
     */
    public function agent(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_properties' => Property::where('agent_id', $user->id)->count(),
            'active_properties' => Property::where('agent_id', $user->id)->where('status', 'validated')->count(),
            'pending_properties' => Property::where('agent_id', $user->id)->where('status', 'pending')->count(),
            'occupied_properties' => Property::where('agent_id', $user->id)->where('is_occupied', true)->count(),
            'total_commissions' => (float) Commission::where('agent_id', $user->id)->sum('amount'),
            'pending_commissions' => (float) Commission::where('agent_id', $user->id)->where('status', 'pending')->sum('amount'),
            'paid_commissions' => (float) Commission::where('agent_id', $user->id)->where('status', 'paid')->sum('amount'),
        ];

        $recentActivities = Notification::where('user_id', $user->id)->latest()->take(5)->get();
        $monthlyCommissions = Commission::where('agent_id', $user->id)
            ->selectRaw('MONTHNAME(created_at) as month, SUM(amount) as commissions')
            ->groupBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'monthly_commissions' => $monthlyCommissions,
            ],
        ]);
    }

    /**
     * Owner Dashboard - Complete Spec Structure
     */
    public function owner(Request $request)
    {
        $user = $request->user();

        $monthlyRevenue = Transaction::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $totalRevenue = Transaction::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $approvedWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $availableBalance = max(0, $totalRevenue - $approvedWithdrawals - $pendingWithdrawals);

        $stats = [
            'total_properties' => Property::where('owner_id', $user->id)->count(),
            'available_properties' => Property::where('owner_id', $user->id)->where('is_available', true)->count(),
            'occupied_properties' => Property::where('owner_id', $user->id)->where('is_occupied', true)->count(),
            'monthly_revenue' => (float) $monthlyRevenue,
            'total_revenue' => (float) $totalRevenue,
            'pending_withdrawals' => (float) $pendingWithdrawals,
            'available_balance' => (float) $availableBalance,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Client Dashboard - Complete Spec Structure
     */
    public function client(Request $request)
    {
        $user = $request->user();

        $data = [
            'favorites_count' => PropertyFavorite::where('user_id', $user->id)->count(),
            'active_contracts' => OccupancyContract::where('tenant_id', $user->id)->where('is_active', true)->count(),
            'pending_requests' => OccupancyRequest::where('client_id', $user->id)->where('status', 'pending')->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'recent_payments' => Payment::where('user_id', $user->id)->latest()->take(5)->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
