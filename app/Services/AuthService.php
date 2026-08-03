<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\MatriculeMail;

class AuthService
{
    /**
     * Authenticate user with email/matricule and password
     */
    public function login(string $email, string $password): ?User
    {
        $user = User::where('email', $email)
                    ->orWhere('matricule', $email)
                    ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'user_type' => $data['user_type'],
            'status' => 'validated',
            'matricule' => $data['matricule'] ?? null,
        ]);

        if ($user->user_type === 'owner') {
            $matricule = $this->generateOwnerMatricule();
            $user->update(['matricule' => $matricule]);
            
            $user->ownerProfile()->create([
                'owner_type' => $data['owner_type'] ?? 'individual',
                'company_name' => $data['company_name'] ?? null,
                'is_active' => true,
                'validation_status' => 'validated',
            ]);

            try {
                Mail::to($user->email)->send(new MatriculeMail($user, $matricule));
            } catch (\Exception $e) {
                \Log::warning('Could not send matricule email: ' . $e->getMessage());
            }
        }

        return $user;
    }

    /**
     * Generate unique owner matricule (Format: PROP-YYYY-XXXXXX)
     */
    public function generateOwnerMatricule(): string
    {
        $year = now()->format('Y');
        $count = User::where('matricule', 'LIKE', "PROP-{$year}%")->count();
        return "PROP-{$year}-" . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);
        return true;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    /**
     * Load user profile based on user type
     */
    public function loadUserProfile(User $user): User
    {
        if ($user->user_type === 'agent') {
            $user->load('agentProfile');
        } elseif ($user->user_type === 'owner') {
            $user->load('ownerProfile');
        }

        return $user;
    }
}
