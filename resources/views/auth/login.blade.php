@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    {{-- Left Side - Formulaire --}}
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md">
            <!-- Logo et titre -->
            <div class="mb-8 text-center lg:text-left">
                <h1 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                    Orizona
                </h1>
                <p class="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                    Connectez-vous à votre espace
                </p>
            </div>

            <!-- Messages d'erreur/succès -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-[#1D0002] border border-red-300 dark:border-red-500 rounded-lg">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-600 dark:text-[#FF4433]">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-[#1D0002] border border-green-300 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Formulaire -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Email ou Matricule
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}"
                        required 
                        autofocus
                        placeholder="exemple@email.com"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Mot de passe -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        Mot de passe
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none transition-all duration-200"
                    >
                </div>

                <!-- Bouton Connexion -->
                <button 
                    type="submit"
                    class="w-full py-3 px-6 bg-[#f53003] hover:bg-orange-600 text-white font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:scale-[1.02] focus:ring-2 focus:ring-[#f53003] focus:ring-offset-2 focus:outline-none"
                >
                    Se connecter
                </button>

                <!-- Lien inscription -->
                <div class="text-center">
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}" class="font-medium text-[#f53003] hover:text-[#f53003] underline underline-offset-4">
                            Créer un compte
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    {{-- Right Side - Bannière --}}
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
                Gérez vos biens immobiliers
            </h2>
            <p class="text-[#706f6c] dark:text-[#A1A09A] leading-relaxed">
                Orizona vous offre une plateforme complète pour la gestion de vos propriétés, 
                locations et transactions immobilières en toute simplicité.
            </p>
            <div class="mt-6 flex justify-center gap-2">
                <span class="inline-block w-8 h-1.5 rounded-full bg-[#f53003]"></span>
                <span class="inline-block w-4 h-1.5 rounded-full bg-gray-200 dark:bg-[#3E3E3A]"></span>
                <span class="inline-block w-4 h-1.5 rounded-full bg-gray-200 dark:bg-[#3E3E3A]"></span>
            </div>
        </div>
    </div>
@endsection

