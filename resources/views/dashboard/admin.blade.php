@extends('layouts.dashboard')

@section('title', 'Espace Admin')

@section('content')
    @php
        $allProperties = \App\Models\Property::with(['owner', 'agent'])->latest()->get();
        $allUsers = \App\Models\User::latest()->get();
        $totalVolume = \App\Models\Transaction::where('status', 'succeeded')->sum('amount');
    @endphp

    <div class="space-y-10">

        <!-- Header Title -->
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                Administration Globale
            </h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Gestion complète des propriétés, utilisateurs et demandes d'occupation ORIZON
            </p>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Total Utilisateurs</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $allUsers->count() }}</p>
                <p class="text-xs text-[#f53003] font-medium mt-1">Admins, Agents, Owners, Clients</p>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Catalogue Propriétés</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $allProperties->count() }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">Enregistrées sur ORIZON</p>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Validations en Attente</span>
                <p class="text-3xl font-extrabold text-amber-600 mt-2">{{ $allProperties->where('status', 'pending')->count() }}</p>
                <p class="text-xs text-amber-600 font-medium mt-1">Propriétés à valider</p>
            </div>
        </div>

        <!-- Section 1: Toutes les Propriétés -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Toutes les Propriétés</h2>
                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $allProperties->count() }} propriétés</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">
                            <th class="pb-3 pr-4">Photo</th>
                            <th class="pb-3 pr-4">Propriété</th>
                            <th class="pb-3 pr-4">Ville</th>
                            <th class="pb-3 pr-4">Prix</th>
                            <th class="pb-3 pr-4">Propriétaire</th>
                            <th class="pb-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                        @forelse($allProperties as $prop)
                            @php
                                $photos = $prop->photos_with_urls;
                                $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                                $coverUrl = $mainPhoto['photo_url'] ?? null;
                            @endphp
                            <tr>
                                <td class="py-3 pr-4">
                                    @if($coverUrl)
                                        <img src="{{ $coverUrl }}" alt="{{ $prop->title }}" class="w-14 h-10 object-cover rounded-lg">
                                    @else
                                        <div class="w-14 h-10 rounded-lg bg-gray-100 dark:bg-[#2a2a28] flex items-center justify-center">
                                            <span class="text-[9px] text-[#706f6c]">N/A</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $prop->title }}</td>
                                <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->city }}</td>
                                <td class="py-3 pr-4 font-extrabold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}</td>
                                <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->owner_name ?? ($prop->owner->full_name ?? 'N/A') }}</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                        {{ $prop->status === 'validated' ? 'bg-green-100 text-green-700' :
                                           ($prop->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($prop->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune propriété enregistrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Tous les Utilisateurs -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Tous les Utilisateurs</h2>
                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $allUsers->count() }} comptes</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">
                            <th class="pb-3 pr-4">Nom</th>
                            <th class="pb-3 pr-4">Email</th>
                            <th class="pb-3 pr-4">Téléphone</th>
                            <th class="pb-3 pr-4">Rôle</th>
                            <th class="pb-3 pr-4">Matricule</th>
                            <th class="pb-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                        @forelse($allUsers as $u)
                            <tr>
                                <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $u->full_name }}</td>
                                <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">{{ $u->email }}</td>
                                <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">{{ $u->phone ?? 'N/A' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-[#fff2f2] text-[#f53003]">
                                        {{ ucfirst($u->user_type) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-xs font-mono text-[#706f6c] dark:text-[#A1A09A]">{{ $u->matricule ?? '-' }}</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                        {{ ucfirst($u->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun utilisateur.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
