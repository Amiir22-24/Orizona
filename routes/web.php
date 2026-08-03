<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\OccupancyWebController;
use App\Http\Controllers\AdminWebController;

/*
|--------------------------------------------------------------------------
| Web Routes - ORIZONA Frontend Auth
|--------------------------------------------------------------------------
|
| Routes d'authentification pour l'interface web
|
*/

// Page d'accueil publique
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->user_type) {
            'admin' => redirect('/admin/dashboard'),
            'agent' => redirect('/agent/dashboard'),
            'owner' => redirect('/owner/dashboard'),
            default => redirect('/client/dashboard'),
        };
    }
    return view('welcome');
});

// Routes d'authentification (web)
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Dashboard (protégé)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return match ($user->user_type) {
            'admin' => redirect()->route('admin.dashboard'),
            'agent' => redirect()->route('agent.dashboard'),
            'owner' => redirect()->route('owner.dashboard'),
            default => view('dashboard.client'),
        };
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Redirige /admin/dashboard vers le nouveau tableau de bord admin
        Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');

        // Propriétés
        Route::get('/properties', [AdminWebController::class, 'properties'])->name('web.properties');
        Route::get('/properties/{id}', [AdminWebController::class, 'propertyDetail'])->name('web.property-detail');
        Route::post('/properties/{id}/approve', [AdminWebController::class, 'approveProperty'])->name('web.property-approve');
        Route::post('/properties/{id}/reject', [AdminWebController::class, 'rejectProperty'])->name('web.property-reject');
        Route::delete('/properties/{id}', [AdminWebController::class, 'destroyProperty'])->name('web.properties.destroy');

        // Utilisateurs
        Route::get('/users', [AdminWebController::class, 'users'])->name('web.users');
        Route::get('/users/{id}', [AdminWebController::class, 'userDetail'])->name('web.user-detail');
        Route::post('/users/{id}/status', [AdminWebController::class, 'updateUserStatus'])->name('web.user-status');
        Route::post('/users/agents', [AdminWebController::class, 'storeAgent'])->name('web.users.agents.store');
        Route::post('/users/owners', [AdminWebController::class, 'storeOwner'])->name('web.users.owners.store');

        // Aide & Suggestions
        Route::get('/support', [AdminWebController::class, 'support'])->name('web.support');
        Route::post('/support/{id}/reply', [AdminWebController::class, 'supportReply'])->name('web.support-reply');

        // Notifications
        Route::get('/notifications', [AdminWebController::class, 'notifications'])->name('web.notifications');
        Route::post('/notifications/read-all', [AdminWebController::class, 'markAllNotificationsRead'])->name('web.notifications-read-all');
    });

    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.agent');
        })->name('dashboard');
    });

    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.owner');
        })->name('dashboard');
    });

    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.client');
        })->name('dashboard');
    });

    // ── Workflow d'occupation (Blade web forms) ──────────────────────────────
    Route::prefix('web/occupancy-requests')->name('web.occupancy.')->group(function () {
        Route::post('/',                        [OccupancyWebController::class, 'request'])      ->name('request');
        Route::post('/{id}/agent-approve',      [OccupancyWebController::class, 'agentApprove']) ->name('agent.approve');
        Route::post('/{id}/agent-reject',       [OccupancyWebController::class, 'agentReject'])  ->name('agent.reject');
        Route::post('/{id}/owner-approve',      [OccupancyWebController::class, 'ownerApprove']) ->name('owner.approve');
        Route::post('/{id}/owner-reject',       [OccupancyWebController::class, 'ownerReject'])  ->name('owner.reject');
        Route::post('/{id}/cancel',             [OccupancyWebController::class, 'clientCancel'])          ->name('cancel');
        Route::delete('/{id}/delete-rejected',  [OccupancyWebController::class, 'clientDeleteRejected'])  ->name('delete.rejected');
    });

    // ── Favoris & Notifications Web ─────────────────────────────────────────
    Route::post('/web/favorites/toggle/{propertyId}', [OccupancyWebController::class, 'toggleFavorite'])->name('web.favorites.toggle');
    Route::post('/web/notifications/mark-all-read', [OccupancyWebController::class, 'markAllNotificationsRead'])->name('web.notifications.mark-all-read');

    // ── Messagerie Client (Agent & Admin) ────────────────────────────────────
    Route::post('/web/messages/agent', [OccupancyWebController::class, 'sendMessageToAgent'])->name('web.messages.agent');
    Route::post('/web/messages/admin', [OccupancyWebController::class, 'sendMessageToAdmin'])->name('web.messages.admin');
    Route::get('/web/conversations/{id}/messages', [OccupancyWebController::class, 'getConversationMessages'])->name('web.conversations.messages');

    // ── Consultation des Contrats (Fichier PDF ou Contrat Numérique) ───────────
    Route::get('/web/contracts/preview/{requestId}', [OccupancyWebController::class, 'previewContract'])->name('web.contracts.preview');
    Route::get('/web/contracts/{id}', [OccupancyWebController::class, 'showContract'])->name('web.contracts.show');

    // ── Libération de propriété ─────────────────────────────────────────────
    Route::post('/web/client/property/{id}/release', [OccupancyWebController::class, 'releaseProperty'])->name('web.client.property.release');

    // ── Répertorier une propriété par l'Agent ────────────────────────────────
    Route::post('/web/agent/properties', [OccupancyWebController::class, 'agentStoreProperty'])->name('web.agent.properties.store');

    // ── Page guide "Comment ça marche" ───────────────────────────────────────
    Route::get('/comment-ca-marche', function () {
        return view('pages.how-it-works');
    })->name('how-it-works');
});
