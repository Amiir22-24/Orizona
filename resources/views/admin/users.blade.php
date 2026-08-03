@extends('layouts.dashboard')

@section('title', 'Administration - Utilisateurs')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Gestion des Utilisateurs
                </h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ $counts['all'] }} comptes enregistrés sur ORIZONA
                </p>
            </div>

            <!-- Filtres par type -->
            <div class="flex flex-wrap gap-2">
                @php
                    $types = [
                        'all' => ['label' => 'Tous', 'color' => 'bg-gray-100 dark:bg-[#3E3E3A] text-[#1b1b18] dark:text-[#EDEDEC]'],
                        'admin' => ['label' => 'Admins', 'color' => 'bg-purple-100 text-purple-700'],
                        'agent' => ['label' => 'Agents', 'color' => 'bg-blue-100 text-blue-700'],
                        'owner' => ['label' => 'Propriétaires', 'color' => 'bg-amber-100 text-amber-700'],
                        'user' => ['label' => 'Clients', 'color' => 'bg-green-100 text-green-700'],
                    ];
                @endphp
                @foreach($types as $key => $t)
                    <a href="{{ route('admin.web.users', ['type' => $key, 'search' => $search]) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all
                              {{ $type === $key ? $t['color'] . ' ring-2 ring-offset-1 ring-[#f53003]' : 'bg-gray-100 dark:bg-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        {{ $t['label'] }} ({{ $counts[$key] }})
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Recherche -->
        <form method="GET" action="{{ route('admin.web.users') }}" class="flex gap-3">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, email, téléphone, matricule..."
                   class="flex-1 px-4 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-[#f53003]">
            <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                Rechercher
            </button>
        </form>

        <!-- Tableau des utilisateurs -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#1a1a18]">
                            <th class="px-4 py-3">Nom</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Téléphone</th>
                            <th class="px-4 py-3">Rôle</th>
                            <th class="px-4 py-3">Matricule</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                        @forelse($users as $u)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a18] transition-all">
                                <td class="px-4 py-3 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-[#fff2f2] dark:bg-[#1D0002] text-[#f53003] flex items-center justify-center font-bold text-sm">
                                            {{ strtoupper(substr($u->first_name, 0, 1)) }}{{ strtoupper(substr($u->last_name, 0, 1)) }}
                                        </div>
                                        {{ $u->full_name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $u->email }}</td>
                                <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $u->phone ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md
                                        {{ $u->user_type === 'admin' ? 'bg-purple-100 text-purple-700' :
                                           ($u->user_type === 'agent' ? 'bg-blue-100 text-blue-700' :
                                           ($u->user_type === 'owner' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')) }}">
                                        {{ ucfirst($u->user_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs font-mono text-[#706f6c] dark:text-[#A1A09A]">{{ $u->matricule ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                        {{ in_array($u->status, ['validated', 'active']) ? 'bg-green-100 text-green-700' :
                                           ($u->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($u->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.web.user-detail', $u->id) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    Aucun utilisateur trouvé pour ce filtre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Inscription Agent / Propriétaire par Admin -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <!-- Formulaire Création Agent -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Inscrire un Agent</h2>
                <form action="{{ route('admin.web.users.agents.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Prénom</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Nom</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Téléphone</label>
                        <input type="text" name="phone" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                    </div>
                    <button type="submit" class="w-full py-2.5 text-sm font-bold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                        Inscrire l'Agent
                    </button>
                    <p class="text-xs text-[#706f6c] text-center mt-2">Un email contenant son matricule et mot de passe lui sera envoyé.</p>
                </form>
            </div>

            <!-- Formulaire Création Propriétaire -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Inscrire un Propriétaire</h2>
                <form action="{{ route('admin.web.users.owners.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Prénom</label>
                            <input type="text" name="first_name" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Nom</label>
                            <input type="text" name="last_name" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#706f6c] dark:text-[#A1A09A] mb-1">Téléphone</label>
                        <input type="text" name="phone" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]">
                    </div>
                    <button type="submit" class="w-full py-2.5 text-sm font-bold text-white bg-[#1b1b18] dark:bg-white dark:text-[#1b1b18] hover:bg-gray-800 dark:hover:bg-gray-200 rounded-lg transition-all">
                        Inscrire le Propriétaire
                    </button>
                    <p class="text-xs text-[#706f6c] text-center mt-2">Un email contenant son matricule et mot de passe lui sera envoyé.</p>
                </form>
            </div>
        </div>

    </div>
@endsection

