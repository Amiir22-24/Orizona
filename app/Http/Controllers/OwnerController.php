<?php

namespace App\Http\Controllers;

use App\Http\Controllers\DashboardController;
use App\Models\OccupancyContract;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    /**
     * Dashboard stats for owner
     */
    public function dashboardStats(Request $request)
    {
        $dashboardController = new DashboardController();
        return $dashboardController->owner($request);
    }

    /**
     * Owner properties
     */
    public function properties(Request $request)
    {
        $user = $request->user();
        $properties = Property::where('owner_id', $user->id)
            ->with(['agent'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties),
            'pagination' => [
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
            ]
        ]);
    }

    /**
     * Create property as owner
     */
    public function storeProperty(Request $request)
    {
        $propertyController = app(PropertyController::class);
        return $propertyController->store($request);
    }

    /**
     * Update property as owner
     */
    public function updateProperty(Request $request, $id)
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($id);
        $propertyController = app(PropertyController::class);
        return $propertyController->update(app(\App\Http\Requests\Property\UpdatePropertyRequest::class), $property);
    }

    /**
     * Owner contracts
     */
    public function contracts(Request $request)
    {
        $user = $request->user();
        $query = OccupancyContract::with(['property', 'tenant'])
            ->where('owner_id', $user->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    /**
     * Payments received by owner
     */
    public function payments(Request $request)
    {
        $user = $request->user();
        $payments = Transaction::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->with(['user', 'property'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Withdrawals list for owner
     */
    public function withdrawals(Request $request)
    {
        $withdrawals = Withdrawal::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Request a withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000',
            'method' => 'required|string|in:tmoney,flooz,bank',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Calculate available balance
        $totalRevenue = Transaction::whereHas('property', fn($q) => $q->where('owner_id', $user->id))->sum('amount');
        $approvedWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $availableBalance = max(0, $totalRevenue - $approvedWithdrawals - $pendingWithdrawals);

        if ($validator->validated()['amount'] > $availableBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant pour effectuer ce retrait.',
                'available_balance' => $availableBalance,
            ], 422);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $validator->validated()['amount'],
            'method' => $validator->validated()['method'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de retrait enregistrée et en cours de traitement.',
            'data' => $withdrawal,
        ], 201);
    }

    /**
     * Get owner balance
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        $totalRevenue = Transaction::whereHas('property', fn($q) => $q->where('owner_id', $user->id))->sum('amount');
        $approvedWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount');
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $availableBalance = max(0, $totalRevenue - $approvedWithdrawals - $pendingWithdrawals);

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => (float) $totalRevenue,
                'approved_withdrawals' => (float) $approvedWithdrawals,
                'pending_withdrawals' => (float) $pendingWithdrawals,
                'available_balance' => (float) $availableBalance,
            ]
        ]);
    }

    /**
     * Get owner stats
     */
    public function stats(Request $request)
    {
        return $this->dashboardStats($request);
    }
}
