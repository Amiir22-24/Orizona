@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
    {{-- Left Side - Bannière (inversée par rapport au login) --}}
    <div class="hidden lg:flex lg:w-1/2 lg:h-screen bg-[#fff2f2] dark:bg-[#1D0002] items-center justify-center p-8">
        <div class="max-w-sm text-center">
            <div class="mb-6">
                <svg class="w-full h-auto text-[#f53003] dark:text-[#FF4433]" viewBox="0 0 200 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="200" height="80" rx="12" fill="currentColor" opacity="0.1"/>
                    <path d="M40 20L60 40H50V60H30V40H20L40 20Z" fill="currentColor"/>
                    <circle cx="100" cy="35" r="12" fill="currentColor" opacity="0.3"/>
                    <rect x="130" y="25" width="40" height="8" rx="4" fill="currentColor" opacity="0.5"/>
                    <rect x="130" y="40" width="30" height="8" rx="4" fill="currentColor" opacity="0.3"/>
                    <rect x="130" y="55" width="35" height="8" rx="4" fill="currentColor" opacity="0.2"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                Rejoignez Orizona
            </h2>
            <p class="text-[#706f6c] dark:text-[#A1A09A] leading-relaxed">
                Créez votre compte et commencez à gérer vos biens, 
                trouver des locataires ou des propriétés facilement.
            </p>
            <div class="mt-6 flex justify-center gap-2">
                <span class="inline-block w-4 h-1.5 rounded-full bg-gray-200 dark:bg-[#3E3E3A]"></span>
                <span class="inline-block w-8 h-1.5 rounded-full bg-[#f53003]"></span>
                <span class="inline-block w-4 h-1.5 rounded-full bg-gray-200 dark:bg-[#3E3E3A]"></span>
            </div>
        </div>
    </div>

    {{-- Right Side - Formulaire d'inscription --}}
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12 overflow-y-auto">
        <div class="w-full max-w-md py-6">
            <!-- Logo et titre -->
            <div class="mb-8 text-center lg:text-left">
                <h1 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                    Créer un compte
                </h1>
                <p class="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                    Rejoignez Orizona dès aujourd'hui
                </p>
            </div>

            <!-- Messages d'erreur -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-[#1D0002] border border-red-300 dark:border-red-500 rounded-lg">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-600 dark:text-[#FF4433]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulaire -->
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Type d'utilisateur -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Je suis
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 dark:border-[#3E3E3A] rounded-xl cursor-pointer hover:border-[#f53003] transition-all duration-200 {{ old('user_type', 'user') === 'user' ? 'border-[#f53003] bg-orange-50 dark:bg-[#1D0002]' : '' }}">
                            <input type="radio" name="user_type" value="user" class="sr-only" {{ old('user_type') === 'user' ? 'checked' : '' }} checked>
                            <svg class="w-8 h-8 mb-2 text-[#706f6c] dark:text-[#A1A09A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Locataire</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 dark:border-[#3E3E3A] rounded-xl cursor-pointer hover:border-[#f53003] transition-all duration-200 {{ old('user_type') === 'owner' ? 'border-[#f53003] bg-orange-50 dark:bg-[#1D0002]' : '' }}">
                            <input type="radio" name="user_type" value="owner" class="sr-only" {{ old('user_type') === 'owner' ? 'checked' : '' }}>
                            <svg class="w-8 h-8 mb-2 text-[#706f6c] dark:text-[#A1A09A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Propriétaire</span>
                        </label>
                    </div>
                </div>

                <!-- Prénom -->
                <div class="space-y-2">
                    <label for="first_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Prénom <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="first_name" 
                        id="first_name" 
                        value="{{ old('first_name') }}"
                        required
                        placeholder="Votre prénom"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Nom -->
                <div class="space-y-2">
                    <label for="last_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="last_name" 
                        id="last_name" 
                        value="{{ old('last_name') }}"
                        required
                        placeholder="Votre nom"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}"
                        required
                        placeholder="exemple@email.com"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Téléphone -->
                <div class="space-y-2">
                    <label for="phone" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Téléphone <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        value="{{ old('phone') }}"
                        required
                        placeholder="+225 01 02 03 04 05"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Mot de passe -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        placeholder="Minimum 6 caractères"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Confirmation mot de passe -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        required
                        placeholder="Ressaisissez le mot de passe"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Bouton inscription -->
                <button 
                    type="submit"
                    class="w-full py-3 px-6 bg-[#f53003] hover:bg-orange-600 text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:scale-[1.02] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none"
                >
                    Créer mon compte
                </button>

                <!-- Lien connexion -->
                <div class="text-center">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Déjà un compte ?
                        <a href="{{ route('login') }}" class="font-medium text-[#f53003] hover:text-[#f53003] underline underline-offset-4">
                            Se connecter
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection

