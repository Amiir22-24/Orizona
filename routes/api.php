<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - ORIZON Real Estate Management System
|--------------------------------------------------------------------------
*/

/*
 * PUBLIC ROUTES - Auth sans token
 */
Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/auth/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/auth/refresh', [App\Http\Controllers\AuthController::class, 'refreshToken']);
Route::post('/password/forgot', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [App\Http\Controllers\AuthController::class, 'resetPassword']);

/*
 * PROTECTED ROUTES - Requièrent auth:sanctum & user actif
 */
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // =============== CLIENT / USER ===============
    Route::prefix('client')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'client']);
        Route::get('/favorites', [App\Http\Controllers\PropertyFavoriteController::class, 'index']);
        Route::post('/favorites/{propertyId}', [App\Http\Controllers\PropertyFavoriteController::class, 'store'])->whereNumber('propertyId');
        Route::delete('/favorites/{propertyId}', [App\Http\Controllers\PropertyFavoriteController::class, 'destroy'])->whereNumber('propertyId');
        Route::get('/occupancy-requests', [App\Http\Controllers\OccupancyController::class, 'index']);
        Route::post('/occupancy-requests', [App\Http\Controllers\OccupancyController::class, 'storeRequest']);
    });

    // =============== AUTH & PROFILE ===============
    Route::prefix('auth')->group(function () {
        Route::get('/me', [App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/user', [App\Http\Controllers\AuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
        Route::put('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile']);
        Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
        Route::post('/upload-photo', [App\Http\Controllers\AuthController::class, 'uploadPhoto']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\UserController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\UserController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
    });

    // =============== PROPERTIES ===============
    Route::prefix('properties')->group(function () {
        Route::get('/', [App\Http\Controllers\PropertyController::class, 'index']);
        Route::get('/search', [App\Http\Controllers\PropertyController::class, 'search']);
        Route::get('/featured', [App\Http\Controllers\PropertyController::class, 'featured']);
        Route::get('/nearby', [App\Http\Controllers\PropertyController::class, 'nearby']);
        Route::get('/validated', [App\Http\Controllers\PropertyController::class, 'validated']);
        Route::get('/{id}', [App\Http\Controllers\PropertyController::class, 'show']);
        Route::post('/', [App\Http\Controllers\PropertyController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\PropertyController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\PropertyController::class, 'destroy']);
        Route::post('/{id}/occupy', [App\Http\Controllers\PropertyController::class, 'occupy']);
        Route::post('/{id}/inquiry', [App\Http\Controllers\PropertyController::class, 'createInquiry']);
        Route::post('/{id}/contract', [App\Http\Controllers\PropertyController::class, 'uploadContract']);
        Route::post('/{id}/release', [App\Http\Controllers\OccupancyController::class, 'releaseProperty']);
    });

    // =============== PROFILES ===============
    Route::prefix('profiles')->group(function () {
        Route::get('/me', [App\Http\Controllers\ProfileController::class, 'me']);
        Route::put('/agent', [App\Http\Controllers\ProfileController::class, 'updateAgent']);
        Route::put('/owner', [App\Http\Controllers\ProfileController::class, 'updateOwner']);
    });

    // =============== FAVORITES ALIASES ===============
    Route::get('/favorites', [App\Http\Controllers\PropertyFavoriteController::class, 'index']);
    Route::post('/favorites/{propertyId}', [App\Http\Controllers\PropertyFavoriteController::class, 'store'])->whereNumber('propertyId');
    Route::delete('/favorites/{propertyId}', [App\Http\Controllers\PropertyFavoriteController::class, 'destroy'])->whereNumber('propertyId');

    // =============== CHAT / CONVERSATIONS (DUAL MAPPING) ===============
    $chatGroup = function () {
        Route::get('/', [App\Http\Controllers\ChatController::class, 'index']);
        Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'index']);
        Route::get('/all', [App\Http\Controllers\ChatController::class, 'allConversations']);
        Route::post('/conversations', [App\Http\Controllers\ChatController::class, 'store']);
        Route::post('/conversations/get-or-create', [App\Http\Controllers\ChatController::class, 'getOrCreate']);
        Route::get('/conversations/{id}', [App\Http\Controllers\ChatController::class, 'show']);
        Route::post('/conversations/{id}/read', [App\Http\Controllers\ChatController::class, 'markAsRead']);
        Route::post('/conversations/{id}/close', [App\Http\Controllers\ChatController::class, 'close']);
        Route::get('/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'messages']);
        Route::post('/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage']);
        Route::post('/messages/{id}/read', [App\Http\Controllers\ChatController::class, 'markMessageRead']);
    };

    Route::prefix('chat')->group($chatGroup);
    Route::prefix('conversations')->group($chatGroup);

    // =============== NOTIFICATIONS ===============
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index']);
        Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [App\Http\Controllers\NotificationController::class, 'markAllRead']);
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
    });

    // =============== PAYMENTS & TRANSACTIONS ===============
    Route::prefix('payments')->group(function () {
        Route::get('/', [App\Http\Controllers\PaymentController::class, 'index']);
        Route::get('/methods', [App\Http\Controllers\PaymentController::class, 'getPaymentMethods']);
        Route::get('/{id}', [App\Http\Controllers\PaymentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\PaymentController::class, 'store']);
        Route::post('/verify', [App\Http\Controllers\PaymentController::class, 'verify']);
        Route::post('/mobile-money/initiate', [App\Http\Controllers\PaymentController::class, 'initiateMobileMoney']);
        Route::post('/mobile-money/verify', [App\Http\Controllers\PaymentController::class, 'verifyPayment']);
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', [App\Http\Controllers\TransactionController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\TransactionController::class, 'show']);
    });

    Route::prefix('receipts')->group(function () {
        Route::get('/', [App\Http\Controllers\ReceiptController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\ReceiptController::class, 'show'])->whereNumber('id');
    });

    // =============== SUBSCRIPTIONS ===============
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [App\Http\Controllers\SubscriptionController::class, 'index']);
        Route::get('/plans', [App\Http\Controllers\SubscriptionController::class, 'plans']);
        Route::get('/{id}', [App\Http\Controllers\SubscriptionController::class, 'show']);
        Route::post('/', [App\Http\Controllers\SubscriptionController::class, 'store']);
        Route::post('/{id}/renew', [App\Http\Controllers\SubscriptionController::class, 'renew']);
        Route::post('/{id}/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel']);
    });

    // =============== DASHBOARDS ===============
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [App\Http\Controllers\DashboardController::class, 'stats']);
        Route::get('/admin', [App\Http\Controllers\DashboardController::class, 'admin']);
        Route::get('/agent', [App\Http\Controllers\DashboardController::class, 'agent']);
        Route::get('/owner', [App\Http\Controllers\DashboardController::class, 'owner']);
        Route::get('/client', [App\Http\Controllers\DashboardController::class, 'client']);
    });

    // =============== OCCUPANCY REQUESTS ===============
    Route::prefix('occupancy-requests')->group(function () {
        Route::get('/', [App\Http\Controllers\OccupancyController::class, 'index']);
        Route::post('/', [App\Http\Controllers\OccupancyController::class, 'storeRequest']);
        Route::get('/{id}', [App\Http\Controllers\OccupancyController::class, 'show']);
        Route::post('/{id}/agent-approve', [App\Http\Controllers\OccupancyController::class, 'agentApprove']);
        Route::post('/{id}/agent-reject', [App\Http\Controllers\OccupancyController::class, 'agentReject']);
        Route::post('/{id}/owner-approve', [App\Http\Controllers\OccupancyController::class, 'ownerApprove']);
        Route::post('/{id}/owner-reject', [App\Http\Controllers\OccupancyController::class, 'ownerReject']);
        Route::post('/{id}/cancel', [App\Http\Controllers\OccupancyController::class, 'clientCancel']);
    });

    // =============== CONTRACTS ===============
    Route::prefix('contracts')->group(function () {
        Route::get('/', [App\Http\Controllers\OccupancyController::class, 'contracts']);
        Route::get('/templates', [App\Http\Controllers\OccupancyController::class, 'contractTemplates']);
        Route::get('/{id}', [App\Http\Controllers\OccupancyController::class, 'contractShow']);
        Route::post('/', [App\Http\Controllers\OccupancyController::class, 'createContract']);
        Route::put('/{id}', [App\Http\Controllers\OccupancyController::class, 'updateContract']);
        Route::post('/{id}/sign', [App\Http\Controllers\OccupancyController::class, 'signContract']);
        Route::post('/{id}/terminate', [App\Http\Controllers\OccupancyController::class, 'terminateContract']);
        Route::get('/{id}/download', [App\Http\Controllers\OccupancyController::class, 'downloadContract']);
        Route::get('/{id}/payments', [App\Http\Controllers\OccupancyController::class, 'contractPayments']);
    });

    // =============== AGENT SECTION (Agent Middleware) ===============
    Route::prefix('agent')->middleware(\App\Http\Middleware\AgentMiddleware::class)->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'agent']);
        Route::get('/properties', [App\Http\Controllers\AgentController::class, 'properties']);
        Route::post('/properties', [App\Http\Controllers\AgentController::class, 'storeProperty']);
        Route::put('/properties/{id}', [App\Http\Controllers\AgentController::class, 'updateProperty']);
        Route::delete('/properties/{id}', [App\Http\Controllers\AgentController::class, 'destroyProperty']);

        Route::get('/commissions', [App\Http\Controllers\CommissionController::class, 'index']);
        Route::get('/commissions/summary', [App\Http\Controllers\CommissionController::class, 'summary']);
        Route::get('/performance', [App\Http\Controllers\AgentController::class, 'performance']);

        Route::get('/territories', [App\Http\Controllers\TerritoryController::class, 'index']);
        Route::post('/territories', [App\Http\Controllers\TerritoryController::class, 'store']);
        Route::put('/territories/{id}', [App\Http\Controllers\TerritoryController::class, 'update']);
        Route::delete('/territories/{id}', [App\Http\Controllers\TerritoryController::class, 'destroy']);

        Route::get('/owners/check', [App\Http\Controllers\OwnerManagementController::class, 'checkOwner']);
        Route::post('/owners/register', [App\Http\Controllers\OwnerManagementController::class, 'registerOwner']);
        Route::get('/owners', [App\Http\Controllers\OwnerManagementController::class, 'getOwners']);
        Route::get('/owners/for-selection', [App\Http\Controllers\OwnerManagementController::class, 'getForSelection']);
        Route::get('/owners/by-matricule/{matricule}', [App\Http\Controllers\OwnerManagementController::class, 'getByMatricule']);

        Route::get('/occupancy-requests/pending', [App\Http\Controllers\OccupancyController::class, 'agentPendingIndex']);
    });

    // =============== OWNER SECTION (Owner Middleware) ===============
    Route::prefix('owner')->middleware(\App\Http\Middleware\OwnerMiddleware::class)->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\OwnerController::class, 'dashboardStats']);
        Route::get('/properties', [App\Http\Controllers\OwnerController::class, 'properties']);
        Route::post('/properties', [App\Http\Controllers\OwnerController::class, 'storeProperty']);
        Route::put('/properties/{id}', [App\Http\Controllers\OwnerController::class, 'updateProperty']);
        Route::get('/contracts', [App\Http\Controllers\OwnerController::class, 'contracts']);
        Route::get('/payments', [App\Http\Controllers\OwnerController::class, 'payments']);
        Route::get('/withdrawals', [App\Http\Controllers\OwnerController::class, 'withdrawals']);
        Route::post('/withdrawals', [App\Http\Controllers\OwnerController::class, 'requestWithdrawal']);
        Route::get('/balance', [App\Http\Controllers\OwnerController::class, 'balance']);
        Route::get('/stats', [App\Http\Controllers\OwnerController::class, 'stats']);

        Route::get('/occupancy-requests/pending', [App\Http\Controllers\OccupancyController::class, 'ownerPendingIndex']);
        Route::post('/occupancy-requests/{id}/approve', [App\Http\Controllers\OccupancyController::class, 'ownerApprove']);
        Route::post('/occupancy-requests/{id}/reject', [App\Http\Controllers\OccupancyController::class, 'ownerReject']);
    });

    // =============== ADMIN SECTION (Admin Middleware) ===============
    Route::prefix('admin')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/dashboard/stats', [App\Http\Controllers\AdminController::class, 'dashboardStats']);
        Route::get('/dashboard/detailed', [App\Http\Controllers\AdminController::class, 'dashboard']);

        Route::get('/users', [App\Http\Controllers\AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [App\Http\Controllers\AdminController::class, 'getUserDetail']);
        Route::post('/users', [App\Http\Controllers\AdminController::class, 'createUser']);
        Route::put('/users/{id}/status', [App\Http\Controllers\AdminController::class, 'updateUserStatus']);

        Route::post('/owners', [App\Http\Controllers\AdminController::class, 'createOwner']);
        Route::get('/owners', [App\Http\Controllers\AdminController::class, 'listOwners']);

        Route::post('/agents', [App\Http\Controllers\AdminController::class, 'createAgent']);
        Route::get('/agents', [App\Http\Controllers\AdminController::class, 'getAgents']);

        Route::prefix('properties')->group(function () {
            Route::get('/', [App\Http\Controllers\AdminController::class, 'getAllProperties']);
            Route::get('/all', [App\Http\Controllers\AdminController::class, 'getAllProperties']);
            Route::get('/new', [App\Http\Controllers\AdminController::class, 'getNewProperties']);
            Route::get('/rejected', [App\Http\Controllers\AdminController::class, 'getRejectedProperties']);
            Route::get('/notifications', [App\Http\Controllers\AdminController::class, 'getPropertyNotifications']);
            Route::get('/{id}', [App\Http\Controllers\AdminController::class, 'getPropertyDetail']);
            Route::put('/{id}', [App\Http\Controllers\AdminController::class, 'updateProperty']);
            Route::post('/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveProperty']);
            Route::post('/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectProperty']);
            Route::delete('/{id}', [App\Http\Controllers\AdminController::class, 'deleteProperty']);
        });

        Route::get('/pending-validations', [App\Http\Controllers\AdminController::class, 'getPendingValidations']);
        Route::get('/withdrawals', [App\Http\Controllers\AdminController::class, 'getWithdrawals']);
        Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveWithdrawal']);
        Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectWithdrawal']);

        Route::get('/transactions', [App\Http\Controllers\AdminController::class, 'getTransactions']);
        Route::get('/transactions/stats', [App\Http\Controllers\AdminController::class, 'getTransactionStats']);
        Route::get('/reports/{type}', [App\Http\Controllers\AdminController::class, 'getReports']);
        Route::get('/stats/growth', [App\Http\Controllers\AdminController::class, 'getGrowthStats']);
        Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'allConversations']);
        Route::get('/contracts', [App\Http\Controllers\OccupancyController::class, 'contracts']);
    });
});
