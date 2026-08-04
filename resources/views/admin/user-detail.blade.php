@extends('layouts.dashboard')

@section('title', 'Administration - Détail Utilisateur')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.web.users') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#f53003]">
                    &larr; Retour
                </a>
                <div class="w-14 h-14 rounded-full bg-[#fff2f2] dark:bg-[#1D0002] text-[#f53003] flex items-center justify-center font-bold text-xl">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $user->full_name }}</h1>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $user->email }} — {{ $user->phone }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 text-xs font-bold rounded-md
                    {{ $user->user_type === 'admin' ? 'bg-purple-100 text-purple-700' :
                       ($user->user_type === 'agent' ? 'bg-blue-100 text-blue-700' :
                       ($user->user_type === 'owner' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')) }}">
                    {{ ucfirst($user->user_type) }}
                </span>
                <span class="px-3 py-1.5 text-xs font-bold rounded-full
                    {{ in_array($user->status, ['validated', 'active']) ? 'bg-green-100 text-green-700' :
                       ($user->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>
        </div>

        <!-- Changement de statut et Suppression -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <form method="POST" action="{{ route('admin.web.user-status', $user->id) }}" class="flex flex-wrap items-center gap-3">
                @csrf
                <span class="text-xs font-bold text-[#706f6c] dark:text-[#A1A09A]">Changer le statut :</span>
                <select name="status" class="px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-[#f53003]">
                    <option value="validated" {{ $user->status === 'validated' ? 'selected' : '' }}>Validé</option>
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="pending" {{ $user->status === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Désactivé</option>
                    <option value="rejected" {{ $user->status === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                </select>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                    Mettre à jour
                </button>
            </form>

            @if($user->id !== Auth::id())
                <form action="{{ route('admin.web.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement l\'utilisateur {{ addslashes($user->full_name) }} ? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer le compte
                    </button>
                </form>
            @endif
        </div>

        <!-- Informations personnelles -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Informations personnelles</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Nom complet</span><span class="font-semibold">{{ $user->full_name }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Email</span><span class="font-semibold">{{ $user->email }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Téléphone</span><span class="font-semibold">{{ $user->phone ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Adresse</span><span class="font-semibold">{{ $user->address ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Ville</span><span class="font-semibold">{{ $user->city ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Région</span><span class="font-semibold">{{ $user->region ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Matricule</span><span class="font-semibold font-mono">{{ $user->matricule ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Inscrit le</span><span class="font-semibold">{{ $user->created_at->format('d/m/Y') }}</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Profil spécifique</h2>
                @if($user->user_type === 'owner' && $user->ownerProfile)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Type de propriétaire</span><span class="font-semibold">{{ ucfirst($user->ownerProfile->owner_type ?? 'individual') }}</span></div>
                        <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Société</span><span class="font-semibold">{{ $user->ownerProfile->company_name ?? 'N/A' }}</span></div>
                        <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Statut validation</span><span class="font-semibold">{{ ucfirst($user->ownerProfile->validation_status ?? 'N/A') }}</span></div>
                    </div>
                @elseif($user->user_type === 'agent' && $user->agentProfile)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">N° enregistrement</span><span class="font-semibold">{{ $user->agentProfile->registration_number ?? 'N/A' }}</span></div>
                        <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Statut validation</span><span class="font-semibold">{{ ucfirst($user->agentProfile->validation_status ?? 'N/A') }}</span></div>
                    </div>
                @else
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun profil spécifique pour ce type de compte.</p>
                @endif

                <h3 class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-6 mb-3">Propriétés associées ({{ $properties->count() }})</h3>
                <div class="space-y-2">
                    @forelse($properties as $prop)
                        <a href="{{ route('admin.web.property-detail', $prop->id) }}" class="flex items-center justify-between p-3 border border-gray-200 dark:border-[#3E3E3A] rounded-lg hover:border-[#f53003] transition-all">
                            <span class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $prop->title }}</span>
                            <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->is_occupied ? 'Occupée' : 'Disponible' }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucune propriété associée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Demandes d'occupation -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Demandes d'occupation</h2>
            <div class="space-y-3">
                @php
                    $allRequests = collect()
                        ->merge($requestsAsClient->map(fn($r) => ['role' => 'Client', 'req' => $r]))
                        ->merge($requestsAsOwner->map(fn($r) => ['role' => 'Propriétaire', 'req' => $r]))
                        ->merge($requestsAsAgent->map(fn($r) => ['role' => 'Agent', 'req' => $r]))
                        ->sortByDesc(fn($item) => $item['req']->created_at);
                @endphp
                @forelse($allRequests as $item)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div>
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $item['req']->property->title ?? 'Propriété' }}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Rôle : {{ $item['role'] }} — Demandé le {{ $item['req']->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                {{ in_array($item['req']->status, ['pending_agent', 'pending_owner']) ? 'bg-amber-100 text-amber-700' :
                                   ($item['req']->status === 'approved' ? 'bg-green-100 text-green-700' :
                                   ($item['req']->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ str_replace('_', ' ', ucfirst($item['req']->status)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune demande d'occupation.</p>
                @endforelse
            </div>
        </div>

        <!-- Contrats -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Contrats</h2>
            <div class="space-y-3">
                @php
                    $allContracts = collect()
                        ->merge($contractsAsTenant->map(fn($c) => ['role' => 'Locataire', 'contract' => $c]))
                        ->merge($contractsAsOwner->map(fn($c) => ['role' => 'Propriétaire', 'contract' => $c]))
                        ->merge($contractsAsAgent->map(fn($c) => ['role' => 'Agent', 'contract' => $c]))
                        ->sortByDesc(fn($item) => $item['contract']->created_at);
                @endphp
                @forelse($allContracts as $item)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div>
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $item['contract']->property->title ?? 'Propriété' }}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Rôle : {{ $item['role'] }} — Loyer {{ number_format($item['contract']->monthly_rent ?? 0, 0, ',', ' ') }} XOF
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $item['contract']->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $item['contract']->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <a href="{{ route('web.contracts.show', $item['contract']->id) }}" target="_blank"
                               class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline">Voir</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun contrat.</p>
                @endforelse
            </div>
        </div>

        <!-- Discussions -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Discussions ({{ $conversations->count() }})</h2>
            <div class="space-y-3">
                @forelse($conversations as $conv)
                    <div class="p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $conv->subject ?? 'Discussion' }}</p>
                            <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $conv->messages()->count() }} messages</span>
                        </div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            @if($conv->property)
                                Propriété : {{ $conv->property->title ?? 'N/A' }}
                            @else
                                {{ $conv->type === 'support' ? 'Support / Aide' : 'Discussion directe' }}
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune discussion.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection

