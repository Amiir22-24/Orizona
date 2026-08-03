<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OwnerProfile;
use App\Mail\MatriculeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
{
    /**
     * Affiche la page de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Affiche la page d'inscription
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Traite la connexion
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => "L'email ou matricule est requis",
            'password.required' => 'Le mot de passe est requis',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Tentative d'authentification (email ou matricule)
        $credentials = $request->only('email', 'password');
        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'matricule';
        
        $user = User::where($field, $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email/matricule ou mot de passe incorrect.',
            ])->withInput();
        }

        if ($user->status !== 'validated' && $user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Votre compte n\'est pas encore validé. Veuillez contacter l\'administration.',
            ])->withInput();
        }

        // Connexion via session
        Auth::login($user, $request->boolean('remember'));

        // Redirection selon le rôle
        return redirect()->intended($this->redirectTo($user));
    }

    /**
     * Traite l'inscription
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'            => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'required|string|unique:users,phone|max:30',
            'password'              => 'required|string|min:6|confirmed',
            'user_type'             => 'required|in:user,owner',
        ], [
            'first_name.required' => 'Le prénom est requis',
            'last_name.required'  => 'Le nom est requis',
            'email.required'      => 'L\'email est requis',
            'email.unique'        => 'Cet email est déjà utilisé',
            'phone.required'      => 'Le téléphone est requis',
            'phone.unique'        => 'Ce téléphone est déjà utilisé',
            'password.required'   => 'Le mot de passe est requis',
            'password.min'        => 'Le mot de passe doit contenir au moins 6 caractères',
            'password.confirmed'  => 'La confirmation du mot de passe ne correspond pas',
            'user_type.required'  => 'Le type d\'utilisateur est requis',
            'user_type.in'        => 'Le type d\'utilisateur doit être Locataire ou Propriétaire',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Création de l'utilisateur
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'user_type'  => $request->user_type,
            'status'     => 'pending', // En attente de validation
        ]);

        // Si propriétaire, créer le profil et générer le matricule
        if ($request->user_type === 'owner') {
            $matricule = $this->generateOwnerMatricule();
            $user->update(['matricule' => $matricule]);

            OwnerProfile::create([
                'user_id'           => $user->id,
                'owner_type'        => 'individual',
                'is_active'         => true,
                'validation_status' => 'pending',
            ]);

            // Envoi du matricule par email
            try {
                Mail::to($user->email)->send(new MatriculeMail($user, $matricule));
            } catch (\Exception $e) {
                // Ne pas bloquer l'inscription si l'email échoue
            }
        } elseif ($request->user_type === 'user') {
            // Envoi du message de bienvenue pour le client
            try {
                Mail::to($user->email)->send(new \App\Mail\ClientWelcomeMail($user, $request->password));
            } catch (\Exception $e) {
                // Ne pas bloquer l'inscription
            }
        }

        // Connexion automatique après inscription
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue sur Orizona ! Votre compte a été créé avec succès.');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirection après connexion selon le rôle
     */
    protected function redirectTo(User $user): string
    {
        return match ($user->user_type) {
            'admin' => '/admin/dashboard',
            'agent' => '/agent/dashboard',
            'owner' => '/owner/dashboard',
            default => '/client/dashboard',
        };
    }

    /**
     * Génération du matricule propriétaire
     */
    protected function generateOwnerMatricule(): string
    {
        $year = now()->format('Y');
        $count = User::where('matricule', 'LIKE', "OWN-{$year}%")->count();
        return "OWN-{$year}-" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }
}

