<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login user with email/matricule and password
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $emailOrMatricule = $credentials['email'];
        $password = $credentials['password'];

        $user = User::where('email', $emailOrMatricule)
            ->orWhere('matricule', $emailOrMatricule)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiant ou email non trouvé',
                'error_type' => 'email_not_found',
                'help' => 'Vérifiez l\'adresse email ou le matricule saisi.',
                'suggestion' => 'Assurez-vous de saisir une adresse email valide ou votre numéro de matricule.',
            ], 401);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect',
                'error_type' => 'invalid_password',
                'help' => 'Le mot de passe fourni ne correspond pas à ce compte.',
                'suggestion' => 'Utilisez la fonctionnalité "Mot de passe oublié" si vous avez égaré votre mot de passe.',
            ], 401);
        }

        if ($user->status === 'inactive' || $user->status === 'rejected' || $user->status === 'banned') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé ou suspendu.',
                'error_type' => 'account_disabled',
                'help' => 'Contactez l\'administration d\'Orizon pour réactiver votre compte.',
                'suggestion' => 'Contactez support@orizon.com pour assistance.',
            ], 403);
        }

        $user = $this->authService->loadUserProfile($user);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['user_type']) && $validated['user_type'] === 'agent') {
            return response()->json([
                'success' => false,
                'message' => 'L\'inscription en tant qu\'agent se fait uniquement par un administrateur.',
                'error_type' => 'registration_blocked',
            ], 403);
        }

        $user = $this->authService->register($validated);
        $token = $user->createToken('api')->plainTextToken;

        Notification::create([
            'user_id' => $user->id,
            'type' => 'registration_success',
            'title' => 'Bienvenue sur ORIZON',
            'message' => 'Votre inscription a été validée avec succès.',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie - Votre compte est immédiatement validé et actif!',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'auto_validated' => true,
            ],
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Get current user profile
     */
    public function me(Request $request)
    {
        $user = $this->authService->loadUserProfile($request->user());

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Refresh authentication token
     */
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token absente',
            ], 401);
        }

        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou introuvable',
            ], 401);
        }

        $user = $accessToken->tokenable;
        $accessToken->delete();
        $newToken = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $newToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'avatar' => 'nullable|string|max:2048',
        ]);

        $user = $this->authService->updateProfile($request->user(), $validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $file = $request->file('photo');
        $path = $file->store('avatars', 'public');
        $url = asset('storage/' . $path);

        $user = $request->user();
        $user->update(['avatar' => $url]);

        return response()->json([
            'success' => true,
            'message' => 'Photo de profil mise à jour',
            'data' => [
                'avatar' => $url,
                'user' => UserResource::make($user->fresh()),
            ],
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $validated = $request->validated();
        $success = $this->authService->changePassword(
            $request->user(),
            $validated['current_password'],
            $validated['new_password']
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect',
                'error_type' => 'invalid_current_password',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès',
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse email non trouvée',
                'error_type' => 'email_not_found',
            ], 404);
        }

        // Token simulation
        $token = Str::random(60);

        return response()->json([
            'success' => true,
            'message' => 'Un email de réinitialisation vous a été envoyé.',
            'reset_token' => $token,
        ]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable',
                'error_type' => 'user_not_found',
            ], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.',
        ]);
    }
}
