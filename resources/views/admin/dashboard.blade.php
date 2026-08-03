@extends('layouts.dashboard')

@section('title', 'Administration - Vue d\'ensemble')

@section('content')
    <div class="space-y-10">

        <!-- Header Title -->
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                Administration Globale
            </h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Gestion complète des propriétés, utilisateurs et demandes d'occupation ORIZONA
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Propriétés -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Propriétés</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $stats['total_properties'] }}</p>
                <div class="mt-3 space-y-1 text-xs">
                    <p class="flex justify-between text-green-600"><span>Disponibles</span><span class="font-bold">{{ $stats['available_properties'] }}</span></p>
                    <p class="flex justify-between text-[#f53003]"><span>Occupées</span><span class="font-bold">{{ $stats['occupied_properties'] }}</span></p>
                    <p class="flex justify-between text-amber-600"><span>En attente</span><span class="font-bold">{{ $stats['pending_properties'] }}</span></p>
                </div>
            </div>

            <!-- Utilisateurs -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Utilisateurs</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $stats['total_users'] }}</p>
                <div class="mt-3 space-y-1 text-xs">
                    <p class="flex justify-between text-[#706f6c]"><span>Agents</span><span class="font-bold">{{ $stats['agent_users'] }}</span></p>
                    <p class="flex justify-between text-[#706f6c]"><span>Propriétaires</span><span class="font-bold">{{ $stats['owner_users'] }}</span></p>
                    <p class="flex justify-between text-[#706f6c]"><span>Clients</span><span class="font-bold">{{ $stats['client_users'] }}</span></p>
                </div>
            </div>

            <!-- Demandes d'occupation -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Demandes d'occupation</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $stats['total_requests'] }}</p>
                <div class="mt-3 space-y-1 text-xs">
                    <p class="flex justify-between text-amber-600"><span>En attente</span><span class="font-bold">{{ $stats['pending_requests'] }}</span></p>
                    <p class="flex justify-between text-green-600"><span>Approuvées</span><span class="font-bold">{{ $stats['approved_requests'] }}</span></p>
                    <p class="flex justify-between text-red-600"><span>Refusées</span><span class="font-bold">{{ $stats['rejected_requests'] }}</span></p>
                </div>
            </div>

            <!-- Contrats -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-5">
                <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Contrats</span>
                <p class="text-3xl font-extrabold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $stats['total_contracts'] }}</p>
                <div class="mt-3 space-y-1 text-xs">
                    <p class="flex justify-between text-green-600"><span>Actifs</span><span class="font-bold">{{ $stats['active_contracts'] }}</span></p>
                    <p class="flex justify-between text-amber-600"><span>Demandes en attente</span><span class="font-bold">{{ $stats['pending_requests'] }}</span></p>
                    <p class="flex justify-between text-[#706f6c]"><span>Messages support</span><span class="font-bold">{{ $stats['total_support'] }}</span></p>
                </div>
            </div>
        </div>

        <!-- Dernières propriétés -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Dernières propriétés</h2>
                <a href="{{ route('admin.web.properties') }}" class="text-xs font-semibold text-[#f53003] hover:underline">Voir tout &rarr;</a>
            </div>
            <div class="space-y-3">
                @forelse($recentProperties as $prop)
                    <a href="{{ route('admin.web.property-detail', $prop->id) }}" class="flex items-center justify-between p-3 border border-gray-200 dark:border-[#3E3E3A] rounded-lg hover:border-[#f53003] transition-all">
                        <div>
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $prop->title }}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->city }} — {{ $prop->owner_name ?? ($prop->owner->full_name ?? 'N/A') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} XOF</span>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                {{ $prop->is_occupied ? 'bg-[#f53003]/10 text-[#f53003]' : 'bg-green-100 text-green-700' }}">
                                {{ $prop->is_occupied ? 'Occupée' : 'Disponible' }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune propriété récente.</p>
                @endforelse
            </div>
        </div>

        <!-- Dernières demandes & contrats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Dernières demandes</h2>
                <div class="space-y-3">
                    @forelse($recentRequests as $req)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                            <div>
                                <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $req->property->title ?? 'Propriété' }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Client : {{ $req->client->full_name ?? 'N/A' }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                {{ in_array($req->status, ['pending_agent', 'pending_owner']) ? 'bg-amber-100 text-amber-700' :
                                   ($req->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                {{ str_replace('_', ' ', ucfirst($req->status)) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune demande récente.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Derniers contrats</h2>
                <div class="space-y-3">
                    @forelse($recentContracts as $contract)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                            <div>
                                <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $contract->property->title ?? 'Propriété' }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Locataire : {{ $contract->tenant->full_name ?? 'N/A' }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $contract->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $contract->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun contrat récent.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Derniers messages support -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Derniers messages d'aide</h2>
                <a href="{{ route('admin.web.support') }}" class="text-xs font-semibold text-[#f53003] hover:underline">Voir tout &rarr;</a>
            </div>
            <div class="space-y-3">
                @forelse($recentSupport as $conv)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div>
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $conv->subject ?? 'Demande d\'aide' }}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                {{ $conv->client->full_name ?? 'Client' }} — {{ $conv->last_message_at?->diffForHumans() ?? $conv->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">{{ $conv->messages()->count() }} msg</span>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun message d'aide.</p>
                @endforelse
            </div>
        </div>

    </div>
@endsection

