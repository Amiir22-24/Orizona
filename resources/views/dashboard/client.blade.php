@extends('layouts.dashboard')

@section('title', 'Espace Client')

@section('content')
    @php
        $user = Auth::user();

        // Contrat actif du client (locataire)
        $myContract = \App\Models\OccupancyContract::with(['property', 'owner', 'agent'])
            ->where('tenant_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();
        $occupiedPropertyId = $myContract?->property_id;

        // Proprietes disponibles (excluant les occupees)
        $allProperties = \App\Models\Property::with(['owner', 'agent'])
            ->where('status', 'validated')
            ->where(function($q) {
                $q->where('is_available', true)
                  ->orWhere('is_occupied', false)
                  ->orWhereNull('is_occupied');
            })
            ->whereDoesntHave('occupancyContracts', fn($q) => $q->where('is_active', true))
            ->latest()
            ->get();

        // Mes demandes
        $myRequests = \App\Models\OccupancyRequest::with('property')
            ->where('client_id', $user->id)
            ->latest()
            ->get();

        $activeRequestPropertyIds = $myRequests
            ->whereIn('status', ['pending_agent', 'pending_owner'])
            ->pluck('property_id')->toArray();

        // Contrats
        $contracts = \App\Models\OccupancyContract::with(['property', 'owner', 'agent'])
            ->where('tenant_id', $user->id)
            ->latest()
            ->get();

        // Favoris (proprietes disponibles seulement)
        $favoriteIds = \App\Models\PropertyFavorite::where('user_id', $user->id)->pluck('property_id')->toArray();
        $favoriteProperties = \App\Models\Property::whereIn('id', $favoriteIds)
            ->where(function($q) {
                $q->where('is_available', true)
                  ->orWhere('is_occupied', false)
                  ->orWhereNull('is_occupied');
            })
            ->latest()
            ->get();

        // Notifications
        $notifications = \App\Models\Notification::where('user_id', $user->id)->latest()->get();
        $unreadNotificationsCount = $notifications->where('is_read', false)->count();

        // Conversations
        $conversations = \App\Models\Conversation::with([
                'property',
                'agent',
                'admin',
                'messages' => fn($q) => $q->with('sender')->orderBy('created_at', 'asc')
            ])
            ->where(fn($q) => $q->where('client_id', $user->id)
                ->orWhereHas('participants', fn($q2) => $q2->where('user_id', $user->id)))
            ->latest('last_message_at')
            ->get();

        $activeRequestsCount = $myRequests->whereIn('status', ['pending_agent', 'pending_owner'])->count();
    @endphp

    <div class="space-y-8">

        @if(session('success'))
            <div class="p-4 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-300 font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300 font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- EN-TETE & KPI --}}
        <div class="space-y-5">
            <div class="border-b border-gray-200 dark:border-[#3E3E3A] pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Espace Client</h1>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Tableau de bord — gestion de vos recherches, demandes, contrats et communications.</p>
                </div>
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Bienvenue, <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ $user->full_name }}</strong>
                </span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="#section-demandes" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Demandes en cours</span>
                    <p class="text-2xl font-bold text-[#f53003] mt-2">{{ $activeRequestsCount }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Sur {{ $myRequests->count() }} au total</span>
                </a>
                <a href="#section-contrats" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Contrats Actifs</span>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ $contracts->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Bails enregistres</span>
                </a>
                <a href="#section-favoris" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Favoris</span>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ count($favoriteIds) }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Coups de coeur</span>
                </a>
                <a href="#section-notifs" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Notifications</span>
                        @if($unreadNotificationsCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 border border-red-200">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $notifications->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Mises a jour</span>
                </a>
            </div>
        </div>

        {{-- SECTION 0 : MON LOGEMENT OCCUPE --}}
        @if($myContract && $myContract->property)
            @php $op = $myContract->property; @endphp
            <section id="section-occupee" class="bg-white dark:bg-[#161615] border-2 border-green-300 dark:border-green-800 rounded-xl p-6 space-y-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                    <div>
                        <span class="text-xs font-semibold text-green-600 uppercase tracking-wider">Mon Logement Actuel</span>
                        <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-0.5">{{ $op->title }}</h2>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                            {{ $op->city }} &mdash; {{ $op->address }}
                            &nbsp;&middot;&nbsp; Loyer : <strong class="text-[#f53003]">{{ number_format($myContract->monthly_rent, 0, ',', ' ') }} XOF</strong>/mois
                        </p>
                    </div>
                    <span class="px-3 py-1.5 text-[11px] font-bold rounded-full bg-green-100 text-green-800 border border-green-200 flex-shrink-0">
                        Contrat Actif depuis le {{ $myContract->start_date?->format('d/m/Y') ?? 'N/A' }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] rounded-xl border border-gray-200 dark:border-[#3E3E3A] space-y-2">
                        <p class="text-[11px] font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Contacts</p>
                        <p class="text-xs text-[#1b1b18] dark:text-[#EDEDEC]">Proprietaire : <strong>{{ $myContract->owner->full_name ?? 'N/A' }}</strong></p>
                        @if($myContract->agent)
                            <p class="text-xs text-[#1b1b18] dark:text-[#EDEDEC]">Agent referent : <strong>{{ $myContract->agent->full_name }}</strong></p>
                        @endif
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Fin de bail : {{ $myContract->end_date?->format('d/m/Y') ?? 'Indeterminee' }}</p>
                        <a href="{{ route('web.contracts.show', $myContract->id) }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 mt-2 px-3 py-1.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">
                            Voir mon contrat (PDF / Numérique)
                        </a>
                    </div>
                    @if($myContract->agent_id)
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] rounded-xl border border-gray-200 dark:border-[#3E3E3A] space-y-3">
                            <p class="text-[11px] font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Contacter l'agent de mon logement</p>
                            <form method="POST" action="{{ route('web.messages.agent') }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $op->id }}">
                                <textarea name="message" rows="3" required
                                    placeholder="Ex : Bonjour, j'ai un probleme de plomberie..."
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none resize-none"></textarea>
                                <button type="submit" class="w-full py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                                    Envoyer a l'agent
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                
                <div class="pt-4 border-t border-gray-100 dark:border-[#2a2a28]">
                    <form method="POST" action="{{ route('web.client.property.release', $op->id) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir libérer cette propriété ? Cette action est irréversible et supprimera la propriété de la plateforme.');"
                            class="px-4 py-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Libérer la propriété
                        </button>
                    </form>
                </div>
            </section>
        @endif

        {{-- SECTION 1 : CATALOGUE DES PROPRIETES DISPONIBLES --}}
        <section id="section-catalogue" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4 gap-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 1</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Proprietes Disponibles</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Biens valides et disponibles, du plus recent au plus ancien.</p>
                </div>
                <span id="properties-count-badge" class="px-3 py-1 bg-[#fff2f2] dark:bg-[#2a100c] text-[#f53003] font-semibold text-xs rounded-full border border-red-200 dark:border-red-900/30">
                    {{ $allProperties->count() }} biens disponibles
                </span>
            </div>

            <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] border border-gray-200 dark:border-[#3E3E3A] rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Mot-cle / ville</label>
                    <input type="text" id="filter-search" oninput="applyPropertyFilters()" placeholder="Ex: Lome, Villa..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Type d'operation</label>
                    <select id="filter-operation" onchange="applyPropertyFilters()"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                        <option value="">Tous (Location / Vente)</option>
                        <option value="rent">Location</option>
                        <option value="sale">Vente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Prix max (XOF)</label>
                    <input type="number" id="filter-max-price" oninput="applyPropertyFilters()" placeholder="Ex: 200000"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                </div>
            </div>

            <div class="h-[520px] overflow-y-auto pr-1">
                <div id="properties-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse($allProperties as $prop)
                        @php
                            $photos = $prop->photos_with_urls;
                            $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                            $coverUrl = $mainPhoto['photo_url'] ?? null;
                            $hasActiveRequest = in_array($prop->id, $activeRequestPropertyIds);
                            $isFavorite = in_array($prop->id, $favoriteIds);
                            $propData = [
                                'id'           => $prop->id,
                                'title'        => $prop->title,
                                'price'        => number_format($prop->price, 0, ',', ' ') . ' ' . $prop->currency,
                                'operation'    => ucfirst($prop->operation_type),
                                'city'         => $prop->city,
                                'neighborhood' => $prop->neighborhood ?? '',
                                'address'      => $prop->address,
                                'bedrooms'     => $prop->bedrooms ?? 0,
                                'bathrooms'    => $prop->bathrooms ?? 0,
                                'surface_area' => $prop->surface_area ?? 0,
                                'description'  => $prop->description ?? 'Aucune description',
                                'owner_name'   => $prop->owner_name ?? 'N/A',
                                'agent_name'   => $prop->agent->full_name ?? 'Agent',
                                'agentId'      => $prop->agent_id,
                                'canRequest'   => !$hasActiveRequest,
                                'photos'       => $photos,
                                'video_url'    => $prop->video_url,
                            ];
                        @endphp
                        <div class="property-card border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] hover:border-[#f53003] transition-all flex flex-col"
                             data-title="{{ strtolower($prop->title . ' ' . $prop->city . ' ' . $prop->address . ' ' . $prop->neighborhood) }}"
                             data-operation="{{ strtolower($prop->operation_type) }}"
                             data-price="{{ $prop->price }}">
                            <div data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))" class="w-full h-44 overflow-hidden bg-gray-100 dark:bg-[#2a2a28] relative flex-shrink-0 cursor-pointer group">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $prop->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Pas de photo</span>
                                    </div>
                                @endif
                                <span class="absolute top-3 left-3 px-2 py-1 text-[10px] font-bold uppercase rounded bg-black/60 text-white backdrop-blur-sm">
                                    {{ ucfirst($prop->operation_type) }}
                                </span>
                                @if(count($photos) > 1)
                                    <span class="absolute bottom-3 left-3 px-2 py-0.5 text-[10px] font-bold rounded bg-black/70 text-white backdrop-blur-sm flex items-center gap-1">
                                        📷 {{ count($photos) }} photos
                                    </span>
                                @endif
                                <form method="POST" action="{{ route('web.favorites.toggle', $prop->id) }}" class="absolute top-3 right-3" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" title="{{ $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                                        class="px-2.5 py-1 text-[11px] font-bold rounded-full transition-all shadow-sm {{ $isFavorite ? 'bg-[#f53003] text-white' : 'bg-white/90 text-[#1b1b18] hover:bg-white' }}">
                                        {{ $isFavorite ? 'Favori' : '+ Favori' }}
                                    </button>
                                </form>
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-extrabold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}</span>
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->bedrooms ?? 0 }} ch.</span>
                                </div>
                                <h3 data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] mb-1 line-clamp-1 cursor-pointer hover:text-[#f53003] transition-colors">{{ $prop->title }}</h3>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-3 line-clamp-1">{{ $prop->city }} &mdash; {{ $prop->address }}</p>
                                <div class="mt-auto space-y-2">
                                    <button type="button"
                                        data-property="{{ json_encode($propData) }}"
                                        onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))"
                                        class="block w-full py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-[#2a2a28] hover:bg-gray-200 dark:hover:bg-[#333330] rounded-lg transition-all">
                                        Voir la fiche &amp; photos ({{ count($photos) }})
                                    </button>
                                    @if($hasActiveRequest)
                                        <span class="block w-full text-center py-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg">Demande en cours...</span>
                                    @else
                                        <button type="button"
                                            onclick="openOccupancyModal({{ $prop->id }}, '{{ addslashes($prop->title) }}', '{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}')"
                                            class="block w-full py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                                            Je suis interesse(e)
                                        </button>
                                    @endif
                                    @if($prop->agent_id)
                                        <button type="button"
                                            onclick="openAgentMsgModal({{ $prop->id }}, '{{ addslashes($prop->title) }}', '{{ addslashes($prop->agent->full_name ?? 'Agent') }}')"
                                            class="block w-full py-2 text-xs font-semibold text-[#f53003] border border-[#f53003] hover:bg-red-50 dark:hover:bg-[#2a100c]/40 rounded-lg transition-all">
                                            Contacter l'agent
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Aucune propriete disponible pour le moment.
                        </div>
                    @endforelse
                </div>
                <div id="no-filter-results" class="hidden py-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Aucune propriete ne correspond a vos criteres.
                </div>
            </div>
        </section>

        {{-- SECTION 2 : MES DEMANDES --}}
        <section id="section-demandes" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 2</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Mes Demandes d'Occupation</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Suivi de vos demandes, de la plus recente a la plus ancienne.</p>
                </div>
                <span class="px-3 py-1 bg-gray-100 dark:bg-[#2a2a28] text-[#1b1b18] dark:text-[#EDEDEC] font-semibold text-xs rounded-full">{{ $myRequests->count() }} demandes</span>
            </div>
            <div class="h-80 overflow-y-auto pr-1 space-y-3">
                @forelse($myRequests as $req)
                    @php
                        $step = 1;
                        if ($req->status === 'pending_owner') $step = 2;
                        elseif ($req->status === 'approved') $step = 3;
                        $isRejected = $req->status === 'rejected';
                        $isCancelled = $req->status === 'cancelled';
                    @endphp
                    <div class="p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-xl space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-[#2a2a28] pb-2">
                            <div>
                                <h4 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $req->property->title ?? 'Propriete supprimee' }}</h4>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                                    Loyer : <strong class="text-[#f53003]">{{ number_format($req->rent_amount, 0, ',', ' ') }} XOF</strong>
                                    &middot; Soumise {{ $req->created_at->diffForHumans() }}
                                </p>
                                @if($req->message)
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5 italic">"{{ $req->message }}"</p>
                                @endif
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                @if(in_array($req->status, ['pending_agent', 'pending_owner']))
                                    <form method="POST" action="{{ route('web.occupancy.cancel', $req->id) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Annuler cette demande ?')"
                                            class="px-3 py-1.5 text-xs text-red-600 bg-red-50 dark:bg-[#2a100c] border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                                            Annuler
                                        </button>
                                    </form>
                                @elseif($isRejected)
                                    <form method="POST" action="{{ route('web.occupancy.delete.rejected', $req->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Supprimer cette demande refusee ?')"
                                            class="px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-[#2a2a28] border border-gray-200 dark:border-[#3E3E3A] rounded-lg hover:bg-gray-200 transition-all">
                                            Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if(!$isRejected && !$isCancelled)
                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="p-2 rounded-lg border {{ $step >= 1 ? 'bg-green-50 text-green-800 border-green-200 dark:bg-green-950/30 dark:border-green-800 dark:text-green-300' : 'bg-gray-50 text-gray-400 border-gray-200' }}">
                                    <span class="font-bold block text-[10px]">1. Transmise</span>
                                    <span class="text-[10px] opacity-80">Enregistree</span>
                                </div>
                                <div class="p-2 rounded-lg border {{ $step >= 2 ? 'bg-green-50 text-green-800 border-green-200 dark:bg-green-950/30 dark:border-green-800 dark:text-green-300' : ($step === 1 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-gray-50 text-gray-400 border-gray-200') }}">
                                    <span class="font-bold block text-[10px]">2. Agent</span>
                                    <span class="text-[10px] opacity-80">{{ $step >= 2 ? 'Approuve' : 'En attente' }}</span>
                                </div>
                                <div class="p-2 rounded-lg border {{ $step === 3 ? 'bg-green-50 text-green-800 border-green-200 dark:bg-green-950/30 dark:border-green-800 dark:text-green-300' : 'bg-gray-50 text-gray-400 border-gray-200' }}">
                                    <span class="font-bold block text-[10px]">3. Proprietaire</span>
                                    <span class="text-[10px] opacity-80">{{ $step === 3 ? 'Bail cree' : 'En attente' }}</span>
                                </div>
                            </div>
                        @else
                            <div class="p-2.5 rounded-lg bg-red-50 text-red-700 border border-red-200 text-xs">
                                <strong>{{ $isRejected ? 'Demande Refusee' : 'Demande Annulee' }}</strong>
                                @if($req->rejection_reason) &mdash; Motif : {{ $req->rejection_reason }} @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Aucune demande d'occupation effectuee.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SECTION 3 : CONTRATS --}}
        <section id="section-contrats" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 3</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Contrats et Documents</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Vos bails enregistres, du plus recent au plus ancien.</p>
                </div>
                <span class="px-3 py-1 bg-green-50 text-green-700 font-semibold text-xs rounded-full border border-green-200">{{ $contracts->count() }} bails</span>
            </div>
            <div class="h-72 overflow-y-auto pr-1 space-y-3">
                @forelse($contracts as $c)
                    <div class="p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $c->property->title ?? 'Propriete' }}</h4>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">Actif</span>
                            </div>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Loyer : <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($c->monthly_rent, 0, ',', ' ') }} XOF</strong>
                                &middot; Debut : {{ $c->start_date?->format('d/m/Y') ?? 'N/A' }}
                                &middot; Signe le {{ $c->created_at->format('d/m/Y') }}
                            </p>
                            <p class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]">
                                Proprietaire : {{ $c->owner->full_name ?? 'N/A' }}
                                @if($c->agent) &middot; Agent : {{ $c->agent->full_name }} @endif
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('web.contracts.show', $c->id) }}" target="_blank" rel="noopener noreferrer"
                               class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm inline-flex items-center gap-1.5">
                                Visualiser le Contrat
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Aucun contrat de location actif.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SECTION 4 : FAVORIS --}}
        <section id="section-favoris" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 4</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Proprietes en Favoris</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Vos coups de coeur (proprietes occupees retirees automatiquement).</p>
                </div>
                <span class="px-3 py-1 bg-gray-100 dark:bg-[#2a2a28] text-[#1b1b18] dark:text-[#EDEDEC] font-semibold text-xs rounded-full">{{ $favoriteProperties->count() }} favoris</span>
            </div>
            <div class="h-72 overflow-y-auto pr-1">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($favoriteProperties as $prop)
                        @php
                            $photos = $prop->photos_with_urls;
                            $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                            $coverUrl = $mainPhoto['photo_url'] ?? null;
                        @endphp
                        <div class="border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] flex flex-col">
                            @if($coverUrl)
                                <div class="w-full h-32 overflow-hidden flex-shrink-0">
                                    <img src="{{ $coverUrl }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-full h-32 bg-gray-100 dark:bg-[#2a2a28] flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Pas de photo</span>
                                </div>
                            @endif
                            <div class="p-3 flex flex-col flex-1">
                                <h3 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] mb-0.5 line-clamp-1">{{ $prop->title }}</h3>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-2 line-clamp-1">{{ $prop->city }} &mdash; {{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}</p>
                                <form method="POST" action="{{ route('web.favorites.toggle', $prop->id) }}" class="mt-auto">
                                    @csrf
                                    <button type="submit" class="w-full py-1.5 text-xs text-red-600 bg-red-50 dark:bg-[#2a100c] border border-red-200 dark:border-red-900/30 rounded-lg hover:bg-red-100 transition-all">
                                        Retirer des favoris
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Aucun favori enregistre.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- SECTION 5 : NOTIFICATIONS & MESSAGERIE INSTANTANÉE --}}
        @php
            $clientConversationsPayload = [];
            foreach ($conversations as $c) {
                $interlocutor = $user->id === $c->client_id ? ($c->agent ?? $c->admin) : $c->client;
                $interlocutorName = $interlocutor?->full_name ?? ($c->agent_id ? 'Agent Immobilier' : 'Support Client');
                $interlocutorRole = $c->agent_id ? 'Agent Immobilier' : 'Support Client';
                $propTitle = $c->property->title ?? ($c->subject ?? 'Discussion Support');
                $initials = mb_strtoupper(mb_substr($interlocutorName, 0, 2));

                $msgsData = [];
                foreach ($c->messages as $m) {
                    $isMe = ($m->sender_id === $user->id);
                    $msgsData[] = [
                        'id'          => $m->id,
                        'sender_name' => $m->sender->full_name ?? 'Utilisateur',
                        'message'     => $m->message,
                        'created_at'  => $m->created_at ? $m->created_at->format('d/m/Y H:i') : '',
                        'is_me'       => $isMe,
                    ];
                }

                $clientConversationsPayload[$c->id] = [
                    'id'                    => $c->id,
                    'property_id'           => $c->property_id,
                    'admin_id'              => $c->admin_id,
                    'interlocutor_name'     => $interlocutorName,
                    'interlocutor_role'     => $interlocutorRole,
                    'interlocutor_initials' => $initials,
                    'property_title'        => $propTitle,
                    'messages'              => $msgsData,
                ];
            }
            $activeClientConvId = session('active_conv_id') ?? array_key_first($clientConversationsPayload);
        @endphp

        <section id="section-notifs" class="space-y-6">
            {{-- NOTIFICATIONS --}}
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-3 gap-3">
                    <div>
                        <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 5</span>
                        <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Mes Notifications</h2>
                    </div>
                    @if($unreadNotificationsCount > 0)
                        <form method="POST" action="{{ route('web.notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-xs text-[#f53003] hover:underline font-semibold">
                                Tout lire ({{ $unreadNotificationsCount }})
                            </button>
                        </form>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="notif-search" oninput="applyNotificationFilters()" placeholder="Rechercher..."
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    <select id="notif-filter-status" onchange="applyNotificationFilters()"
                        class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                        <option value="all">Toutes ({{ $notifications->count() }})</option>
                        <option value="unread">Non lues ({{ $unreadNotificationsCount }})</option>
                        <option value="read">Lues ({{ $notifications->count() - $unreadNotificationsCount }})</option>
                    </select>
                </div>
                <div id="notifications-list" class="h-48 overflow-y-auto pr-1 space-y-2">
                    @forelse($notifications as $notif)
                        <div class="notification-item p-3 border rounded-lg transition-all {{ $notif->is_read ? 'border-gray-100 dark:border-[#2a2a28]' : 'border-red-200 dark:border-red-900/40 bg-red-50/50 dark:bg-[#2a100c]/20' }}"
                             data-read="{{ $notif->is_read ? 'true' : 'false' }}"
                             data-text="{{ strtolower($notif->title . ' ' . $notif->message) }}">
                            <div class="flex items-start justify-between gap-2 mb-0.5">
                                <h4 class="font-semibold text-xs text-[#1b1b18] dark:text-[#EDEDEC] flex items-center gap-1.5">
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-[#f53003] inline-block flex-shrink-0"></span>
                                    @endif
                                    {{ $notif->title }}
                                </h4>
                                <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $notif->message }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] text-center py-6">Aucune notification.</p>
                    @endforelse
                </div>
                <p id="no-notif-results" class="hidden text-xs text-[#706f6c] dark:text-[#A1A09A] text-center py-4">Aucun resultat.</p>
            </div>

            {{-- MESSAGERIE INSTANTANÉE STYLE WHATSAPP / MESSENGER --}}
            <div id="section-messagerie" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-3">
                    <div>
                        <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Messagerie</span>
                        <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Mes Discussions Instantanées</h2>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Discutez en temps réel avec les agents immobiliers et le support.</p>
                    </div>
                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A] font-semibold">{{ $conversations->count() }} conversation(s)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] shadow-sm min-h-[460px]">
                    {{-- COLONNE GAUCHE : LISTE DES DISCUSSIONS --}}
                    <div class="border-r border-gray-200 dark:border-[#3E3E3A] flex flex-col bg-gray-50/50 dark:bg-[#181817]">
                        <div class="p-3 border-b border-gray-200 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-[#1b1b18] dark:text-[#EDEDEC] uppercase tracking-wider">Contacts</h3>
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-100 text-[#f53003] dark:bg-red-950/50 dark:text-red-300">
                                    {{ $conversations->count() }}
                                </span>
                            </div>
                            <input type="text" id="client-chat-search" oninput="filterClientConversationsList()" placeholder="Rechercher un contact..."
                                class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-[#3E3E3A] rounded-lg bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                        </div>

                        <div id="client-conversations-list" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-[#2a2a28] max-h-[400px]">
                            @forelse($conversations as $conv)
                                @php
                                    $payload = $clientConversationsPayload[$conv->id] ?? null;
                                    $lastMsgObj = $conv->messages->last();
                                    $lastMsgText = $lastMsgObj ? $lastMsgObj->message : 'Pas de message';
                                    $lastMsgTime = $lastMsgObj ? $lastMsgObj->created_at->diffForHumans() : '';
                                @endphp
                                <button type="button"
                                    onclick="selectClientConversation({{ $conv->id }})"
                                    id="client-conv-tab-{{ $conv->id }}"
                                    class="client-conv-tab-item w-full p-3 text-left flex items-start gap-3 hover:bg-gray-100/80 dark:hover:bg-[#222220] transition-colors relative"
                                    data-search="{{ strtolower(($payload['interlocutor_name'] ?? '') . ' ' . ($payload['property_title'] ?? '')) }}">
                                    
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#f53003] to-amber-500 text-white font-extrabold text-xs flex items-center justify-center flex-shrink-0 uppercase shadow-sm">
                                        {{ $payload['interlocutor_initials'] ?? 'AG' }}
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <h4 class="font-bold text-xs text-[#1b1b18] dark:text-[#EDEDEC] truncate">{{ $payload['interlocutor_name'] ?? 'Agent' }}</h4>
                                            <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] flex-shrink-0">{{ $lastMsgTime }}</span>
                                        </div>
                                        <p class="text-[11px] font-semibold text-[#f53003] truncate mb-0.5">{{ $payload['property_title'] ?? 'Support' }}</p>
                                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] truncate">
                                            @if($lastMsgObj && $lastMsgObj->sender_id === $user->id)
                                                <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vous : </span>
                                            @endif
                                            {{ $lastMsgText }}
                                        </p>
                                    </div>
                                </button>
                            @empty
                                <div class="p-8 text-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    Aucune discussion disponible.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- COLONNE DROITE : CADRE DE CHAT --}}
                    <div class="md:col-span-2 flex flex-col bg-white dark:bg-[#161615]">
                        {{-- CHAT HEADER --}}
                        <div id="client-chat-header" class="p-3.5 border-b border-gray-200 dark:border-[#3E3E3A] bg-gray-50/50 dark:bg-[#181817] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div id="client-chat-avatar" class="w-9 h-9 rounded-full bg-[#f53003] text-white font-bold text-xs flex items-center justify-center uppercase shadow-sm">
                                    --
                                </div>
                                <div>
                                    <h3 id="client-chat-name" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Sélectionnez une discussion</h3>
                                    <p id="client-chat-property" class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Cliquez sur un contact à gauche pour afficher l'historique des échanges.</p>
                                </div>
                            </div>
                            <span id="client-chat-status-badge" class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-950/40 dark:text-green-300 border border-green-200 hidden">
                                En ligne
                            </span>
                        </div>

                        {{-- MESSAGES STREAM --}}
                        <div id="client-chat-messages-stream" class="flex-1 p-4 overflow-y-auto space-y-3 bg-[#f8f8f7] dark:bg-[#121211] min-h-[300px] max-h-[350px]">
                            <div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Sélectionnez une discussion à gauche pour démarrer.
                            </div>
                        </div>

                        {{-- INPUT FOOTER --}}
                        <div class="p-3 border-t border-gray-200 dark:border-[#3E3E3A] bg-white dark:bg-[#161615]">
                            <form id="client-chat-send-form" method="POST" action="{{ route('web.messages.agent') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="conversation_id" id="client-chat-conv-id">
                                <input type="hidden" name="property_id" id="client-chat-property-id">
                                <input type="text" name="message" id="client-chat-input-text" required placeholder="Tapez votre message ici..."
                                    class="flex-1 px-4 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-xl bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                                <button type="submit" id="client-chat-submit-btn" disabled
                                    class="px-5 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-xl transition-all shadow-sm opacity-50 cursor-not-allowed">
                                    Envoyer ➔
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 6 : CONTACTER L'ADMINISTRATION --}}
        <section id="section-support" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
            <div class="border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Section 6</span>
                <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Contacter l'Administration</h2>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Une question, un probleme ou un signalement ? Ecrivez directement a l'equipe ORIZONA.</p>
            </div>
            <form method="POST" action="{{ route('web.messages.admin') }}" class="space-y-4 max-w-2xl">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Sujet (optionnel)</label>
                    <input type="text" name="subject" placeholder="Ex : Probleme de connexion, Question sur un bien..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Votre message *</label>
                    <textarea name="message" rows="4" required placeholder="Decrivez votre demande ou probleme en detail..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none resize-none"></textarea>
                </div>
                <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">
                    Envoyer a l'administration
                </button>
            </form>
        </section>

    </div>

    {{-- MODAL Demande d'occupation --}}
    <div id="occupancy-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 relative">
            <button type="button" onclick="closeOccupancyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-xl font-bold leading-none">&times;</button>
            <div>
                <h3 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Demande d'Occupation</h3>
                <p id="modal-property-title" class="text-xs font-semibold text-[#f53003] mt-0.5"></p>
                <p id="modal-property-price" class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5"></p>
            </div>
            <form method="POST" action="{{ route('web.occupancy.request') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="property_id" id="modal-property-id">
                <div>
                    <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Date d'entree souhaitee (optionnel)</label>
                    <input type="date" name="start_date" min="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Message pour l'agent (optionnel)</label>
                    <textarea name="message" rows="3" placeholder="Ex: Je souhaiterais visiter ce week-end..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" onclick="closeOccupancyModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2a2a28] rounded-lg transition-all">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">Envoyer ma demande</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL Message a l'agent --}}
    <div id="agent-msg-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 relative">
            <button type="button" onclick="closeAgentMsgModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-xl font-bold leading-none">&times;</button>
            <div>
                <h3 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Contacter l'agent</h3>
                <p id="agent-modal-property" class="text-xs font-semibold text-[#f53003] mt-0.5"></p>
                <p id="agent-modal-name" class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5"></p>
            </div>
            <form method="POST" action="{{ route('web.messages.agent') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="property_id" id="agent-modal-property-id">
                <div>
                    <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Votre message *</label>
                    <textarea name="message" rows="4" required placeholder="Ex: Bonjour, je souhaite avoir plus d'informations..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" onclick="closeAgentMsgModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2a2a28] rounded-lg transition-all">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">Envoyer le message</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CONSULTATION PROPRIÉTÉ & GALERIE PHOTOS --}}
    <div id="property-detail-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl max-w-3xl w-full p-6 shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto">
            <button type="button" onclick="closePropertyDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-2xl font-bold leading-none z-10">&times;</button>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-[#2a2a28] pb-3 pr-8">
                <div>
                    <span id="detail-operation-badge" class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded bg-[#f53003] text-white"></span>
                    <h3 id="detail-title" class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-1"></h3>
                    <p id="detail-location" class="text-xs text-[#706f6c] dark:text-[#A1A09A]"></p>
                </div>
                <div class="sm:text-right">
                    <span id="detail-price" class="text-xl font-extrabold text-[#f53003]"></span>
                    <span id="detail-rating" class="block text-xs font-semibold text-amber-500 mt-0.5">★ 5/5</span>
                </div>
            </div>

            {{-- GALERIE PHOTOS --}}
            <div class="space-y-3">
                <div class="w-full h-64 sm:h-80 bg-black rounded-xl overflow-hidden relative flex items-center justify-center">
                    <img id="detail-main-photo" src="" alt="Photo du bien" class="w-full h-full object-contain">
                    <button type="button" id="detail-prev-btn" onclick="prevDetailPhoto()" class="absolute left-3 p-2 rounded-full bg-black/50 text-white hover:bg-black/80 transition-all font-bold text-lg">&lsaquo;</button>
                    <button type="button" id="detail-next-btn" onclick="nextDetailPhoto()" class="absolute right-3 p-2 rounded-full bg-black/50 text-white hover:bg-black/80 transition-all font-bold text-lg">&rsaquo;</button>
                    <span id="detail-photo-counter" class="absolute bottom-3 right-3 px-3 py-1 rounded-full bg-black/60 text-white text-xs font-semibold backdrop-blur-sm"></span>
                </div>
                
                {{-- THUMBNAILS STRIP --}}
                <div id="detail-thumbnails" class="flex items-center gap-2 overflow-x-auto py-1"></div>
            </div>

            {{-- RENSEIGNEMENTS --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-[#1a1a18] border border-gray-200 dark:border-[#3E3E3A] rounded-xl text-center">
                <div>
                    <span class="text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A] uppercase">Chambres</span>
                    <p id="detail-bedrooms" class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A] uppercase">Salles de bain</span>
                    <p id="detail-bathrooms" class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A] uppercase">Surface</span>
                    <p id="detail-surface" class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]"></p>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A] uppercase">Propriétaire</span>
                    <p id="detail-owner" class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC] truncate"></p>
                </div>
            </div>

            {{-- DESCRIPTION --}}
            <div class="space-y-1">
                <h4 class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">Description</h4>
                <p id="detail-description" class="text-xs text-[#1b1b18] dark:text-[#EDEDEC] leading-relaxed whitespace-pre-line bg-gray-50/50 dark:bg-[#1a1a18]/50 p-3 rounded-lg border border-gray-100 dark:border-[#2a2a28]"></p>
            </div>

            {{-- ACTIONS BOUTONS (CLIENT) --}}
            <div id="detail-actions" class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-[#2a2a28]">
            </div>
        </div>
    </div>

    <script>
        let currentDetailPhotos = [];
        let currentDetailPhotoIndex = 0;

        function openPropertyDetailModal(propData) {
            document.getElementById('detail-title').textContent = propData.title;
            document.getElementById('detail-location').textContent = propData.city + (propData.neighborhood ? ' — ' + propData.neighborhood : '') + ' (' + propData.address + ')';
            document.getElementById('detail-price').textContent = propData.price;
            document.getElementById('detail-operation-badge').textContent = propData.operation;
            document.getElementById('detail-bedrooms').textContent = (propData.bedrooms || 0) + ' chambre(s)';
            document.getElementById('detail-bathrooms').textContent = (propData.bathrooms || 0) + ' SDB';
            document.getElementById('detail-surface').textContent = (propData.surface_area || 0) + ' m²';
            document.getElementById('detail-owner').textContent = propData.owner_name || 'N/A';
            document.getElementById('detail-description').textContent = propData.description || 'Aucune description fournie.';

            // Handle video if exists
            let videoContainer = document.getElementById('detail-video-container');
            if (!videoContainer) {
                videoContainer = document.createElement('div');
                videoContainer.id = 'detail-video-container';
                videoContainer.className = 'mt-4 pt-4 border-t border-gray-100 dark:border-[#2a2a28]';
                document.getElementById('detail-description').parentNode.appendChild(videoContainer);
            }
            if (propData.video_url) {
                videoContainer.innerHTML = '<span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] block mb-2">Vidéo de la propriété</span>' +
                                           '<video controls class="w-full rounded-lg max-h-64 object-cover">' +
                                           '<source src="/storage/' + propData.video_url + '" type="video/mp4">' +
                                           'Votre navigateur ne supporte pas la vidéo.</video>';
                videoContainer.style.display = 'block';
            } else {
                videoContainer.style.display = 'none';
                videoContainer.innerHTML = '';
            }

            currentDetailPhotos = propData.photos || [];
            currentDetailPhotoIndex = 0;

            renderDetailPhotos();

            const actionsContainer = document.getElementById('detail-actions');
            actionsContainer.innerHTML = '';

            if (propData.canRequest) {
                const reqBtn = document.createElement('button');
                reqBtn.type = 'button';
                reqBtn.className = 'w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm';
                reqBtn.textContent = 'Je suis intéressé(e)';
                reqBtn.onclick = function() {
                    closePropertyDetailModal();
                    openOccupancyModal(propData.id, propData.title, propData.price);
                };
                actionsContainer.appendChild(reqBtn);
            }

            if (propData.agentId) {
                const msgBtn = document.createElement('button');
                msgBtn.type = 'button';
                msgBtn.className = 'w-full sm:w-auto px-5 py-2 text-xs font-semibold text-[#f53003] border border-[#f53003] hover:bg-red-50 dark:hover:bg-[#2a100c]/40 rounded-lg transition-all';
                msgBtn.textContent = 'Contacter l\'agent (' + (propData.agent_name || 'Agent') + ')';
                msgBtn.onclick = function() {
                    closePropertyDetailModal();
                    openAgentMsgModal(propData.id, propData.title, propData.agent_name || 'Agent');
                };
                actionsContainer.appendChild(msgBtn);
            }

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'w-full sm:w-auto px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2a2a28] rounded-lg transition-all';
            closeBtn.textContent = 'Fermer';
            closeBtn.onclick = closePropertyDetailModal;
            actionsContainer.appendChild(closeBtn);

            const modal = document.getElementById('property-detail-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePropertyDetailModal() {
            const modal = document.getElementById('property-detail-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function renderDetailPhotos() {
            const mainImg = document.getElementById('detail-main-photo');
            const counter = document.getElementById('detail-photo-counter');
            const thumbsContainer = document.getElementById('detail-thumbnails');
            const prevBtn = document.getElementById('detail-prev-btn');
            const nextBtn = document.getElementById('detail-next-btn');

            thumbsContainer.innerHTML = '';

            if (currentDetailPhotos.length === 0) {
                mainImg.src = 'https://via.placeholder.com/600x400?text=Pas+de+photo';
                counter.textContent = '0 / 0';
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
                return;
            }

            if (currentDetailPhotos.length === 1) {
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
            }

            mainImg.src = currentDetailPhotos[currentDetailPhotoIndex].photo_url;
            counter.textContent = (currentDetailPhotoIndex + 1) + ' / ' + currentDetailPhotos.length;

            currentDetailPhotos.forEach((photo, idx) => {
                const thumb = document.createElement('img');
                thumb.src = photo.photo_url;
                thumb.alt = 'Thumbnail ' + (idx + 1);
                thumb.className = 'w-16 h-12 object-cover rounded-lg border-2 cursor-pointer transition-all flex-shrink-0 ' +
                    (idx === currentDetailPhotoIndex ? 'border-[#f53003] opacity-100 scale-105' : 'border-transparent opacity-60 hover:opacity-100');
                thumb.onclick = function() {
                    currentDetailPhotoIndex = idx;
                    renderDetailPhotos();
                };
                thumbsContainer.appendChild(thumb);
            });
        }

        function prevDetailPhoto() {
            if (currentDetailPhotos.length === 0) return;
            currentDetailPhotoIndex = (currentDetailPhotoIndex - 1 + currentDetailPhotos.length) % currentDetailPhotos.length;
            renderDetailPhotos();
        }

        function nextDetailPhoto() {
            if (currentDetailPhotos.length === 0) return;
            currentDetailPhotoIndex = (currentDetailPhotoIndex + 1) % currentDetailPhotos.length;
            renderDetailPhotos();
        }

        document.getElementById('property-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) closePropertyDetailModal();
        });

        function openOccupancyModal(propertyId, title, price) {
            document.getElementById('modal-property-id').value = propertyId;
            document.getElementById('modal-property-title').textContent = title;
            document.getElementById('modal-property-price').textContent = 'Loyer : ' + price;
            const modal = document.getElementById('occupancy-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeOccupancyModal() {
            const modal = document.getElementById('occupancy-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function openAgentMsgModal(propertyId, propertyTitle, agentName) {
            document.getElementById('agent-modal-property-id').value = propertyId;
            document.getElementById('agent-modal-property').textContent = propertyTitle;
            document.getElementById('agent-modal-name').textContent = 'Agent : ' + agentName;
            const modal = document.getElementById('agent-msg-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeAgentMsgModal() {
            const modal = document.getElementById('agent-msg-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.getElementById('occupancy-modal').addEventListener('click', function(e) {
            if (e.target === this) closeOccupancyModal();
        });
        document.getElementById('agent-msg-modal').addEventListener('click', function(e) {
            if (e.target === this) closeAgentMsgModal();
        });
        function applyPropertyFilters() {
            const searchVal = document.getElementById('filter-search').value.toLowerCase().trim();
            const operationVal = document.getElementById('filter-operation').value.toLowerCase();
            const maxPriceVal = parseFloat(document.getElementById('filter-max-price').value);
            const cards = document.querySelectorAll('.property-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const operation = card.getAttribute('data-operation') || '';
                const price = parseFloat(card.getAttribute('data-price')) || 0;
                const matchesSearch = !searchVal || title.includes(searchVal);
                const matchesOperation = !operationVal || operation === operationVal;
                const matchesPrice = isNaN(maxPriceVal) || price <= maxPriceVal;
                if (matchesSearch && matchesOperation && matchesPrice) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            const badge = document.getElementById('properties-count-badge');
            if (badge) badge.textContent = visibleCount + ' bien(s) correspondant(s)';
            const noResults = document.getElementById('no-filter-results');
            if (noResults) noResults.classList.toggle('hidden', !(visibleCount === 0 && cards.length > 0));
        }
        function applyNotificationFilters() {
            const searchVal = document.getElementById('notif-search').value.toLowerCase().trim();
            const statusVal = document.getElementById('notif-filter-status').value;
            const items = document.querySelectorAll('.notification-item');
            let visibleCount = 0;
            items.forEach(item => {
                const isRead = item.getAttribute('data-read') === 'true';
                const text = item.getAttribute('data-text') || '';
                const matchesSearch = !searchVal || text.includes(searchVal);
                let matchesStatus = true;
                if (statusVal === 'unread') matchesStatus = !isRead;
                if (statusVal === 'read') matchesStatus = isRead;
                if (matchesSearch && matchesStatus) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            const noResults = document.getElementById('no-notif-results');
            if (noResults) noResults.classList.toggle('hidden', !(visibleCount === 0 && items.length > 0));
        }

        const clientConversationsData = @json($clientConversationsPayload);
        let activeClientConvId = @json($activeClientConvId);

        function selectClientConversation(convId) {
            const data = clientConversationsData[convId];
            if (!data) return;

            activeClientConvId = convId;

            document.querySelectorAll('.client-conv-tab-item').forEach(el => {
                el.classList.remove('bg-red-50', 'dark:bg-red-950/30', 'border-l-4', 'border-[#f53003]');
            });
            const activeTab = document.getElementById('client-conv-tab-' + convId);
            if (activeTab) {
                activeTab.classList.add('bg-red-50', 'dark:bg-red-950/30', 'border-l-4', 'border-[#f53003]');
            }

            document.getElementById('client-chat-avatar').textContent = data.interlocutor_initials;
            document.getElementById('client-chat-name').textContent = data.interlocutor_name + ' (' + data.interlocutor_role + ')';
            document.getElementById('client-chat-property').textContent = 'Sujet : ' + data.property_title;
            document.getElementById('client-chat-status-badge').classList.remove('hidden');

            document.getElementById('client-chat-conv-id').value = data.id || '';
            document.getElementById('client-chat-property-id').value = data.property_id || '';

            const sendForm = document.getElementById('client-chat-send-form');
            if (data.admin_id && !data.property_id) {
                sendForm.action = "{{ route('web.messages.admin') }}";
            } else {
                sendForm.action = "{{ route('web.messages.agent') }}";
            }

            const submitBtn = document.getElementById('client-chat-submit-btn');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

            const container = document.getElementById('client-chat-messages-stream');
            container.innerHTML = '';

            if (!data.messages || data.messages.length === 0) {
                container.innerHTML = '<div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucun message dans cette discussion. Écrivez le premier !</div>';
                return;
            }

            data.messages.forEach(msg => {
                const msgDiv = document.createElement('div');
                msgDiv.className = 'flex flex-col ' + (msg.is_me ? 'items-end' : 'items-start');

                const bubble = document.createElement('div');
                bubble.className = 'max-w-[80%] p-3 text-xs shadow-sm ' +
                    (msg.is_me
                        ? 'bg-[#f53003] text-white rounded-2xl rounded-tr-xs'
                        : 'bg-white dark:bg-[#1f1f1d] text-[#1b1b18] dark:text-[#EDEDEC] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl rounded-tl-xs');

                if (!msg.is_me) {
                    const senderLabel = document.createElement('span');
                    senderLabel.className = 'block font-bold text-[10px] text-[#f53003] mb-1';
                    senderLabel.textContent = msg.sender_name;
                    bubble.appendChild(senderLabel);
                }

                const txt = document.createElement('p');
                txt.className = 'whitespace-pre-line leading-relaxed';
                txt.textContent = msg.message;
                bubble.appendChild(txt);

                const timeSpan = document.createElement('span');
                timeSpan.className = 'block text-[9px] mt-1 text-right ' + (msg.is_me ? 'text-white/80' : 'text-[#706f6c] dark:text-[#A1A09A]');
                timeSpan.textContent = msg.created_at;
                bubble.appendChild(timeSpan);

                msgDiv.appendChild(bubble);
                container.appendChild(msgDiv);
            });

            container.scrollTop = container.scrollHeight;
        }

        function filterClientConversationsList() {
            const searchVal = document.getElementById('client-chat-search').value.toLowerCase().trim();
            document.querySelectorAll('.client-conv-tab-item').forEach(item => {
                const search = item.getAttribute('data-search') || '';
                if (!searchVal || search.includes(searchVal)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (activeClientConvId && clientConversationsData[activeClientConvId]) {
                selectClientConversation(activeClientConvId);
            } else {
                const keys = Object.keys(clientConversationsData);
                if (keys.length > 0) {
                    selectClientConversation(keys[0]);
                }
            }
        });

        // ── AUTO-POLLING CLIENT : rafraîchissement toutes les 5 secondes ──
        let clientPollingInterval = null;
        let clientLastKnownMsgIds = {};

        function renderClientMessages(messages) {
            const container = document.getElementById('client-chat-messages-stream');
            if (!messages || messages.length === 0) {
                container.innerHTML = '<div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucun message. Écrivez le premier !</div>';
                return;
            }
            container.innerHTML = '';
            messages.forEach(msg => {
                const msgDiv = document.createElement('div');
                msgDiv.className = 'flex flex-col ' + (msg.is_me ? 'items-end' : 'items-start');

                const bubble = document.createElement('div');
                bubble.className = 'max-w-[80%] p-3 text-xs shadow-sm ' +
                    (msg.is_me
                        ? 'bg-[#f53003] text-white rounded-2xl rounded-tr-xs'
                        : 'bg-white dark:bg-[#1f1f1d] text-[#1b1b18] dark:text-[#EDEDEC] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl rounded-tl-xs');

                if (!msg.is_me) {
                    const senderLabel = document.createElement('span');
                    senderLabel.className = 'block font-bold text-[10px] text-[#f53003] mb-1';
                    senderLabel.textContent = msg.sender_name;
                    bubble.appendChild(senderLabel);
                }

                const txt = document.createElement('p');
                txt.className = 'whitespace-pre-line leading-relaxed';
                txt.textContent = msg.message;
                bubble.appendChild(txt);

                const timeSpan = document.createElement('span');
                timeSpan.className = 'block text-[9px] mt-1 text-right ' + (msg.is_me ? 'text-white/80' : 'text-[#706f6c] dark:text-[#A1A09A]');
                timeSpan.textContent = msg.created_at;
                bubble.appendChild(timeSpan);

                msgDiv.appendChild(bubble);
                container.appendChild(msgDiv);
            });
            container.scrollTop = container.scrollHeight;
        }

        function startClientPolling(convId) {
            stopClientPolling();
            clientPollingInterval = setInterval(function() {
                if (!convId) return;
                fetch('/web/conversations/' + convId + '/messages', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.messages) {
                        const lastIds = clientLastKnownMsgIds[convId] || [];
                        const newIds = data.messages.map(m => m.id);
                        const hasNew = newIds.some(id => !lastIds.includes(id));
                        if (hasNew) {
                            clientLastKnownMsgIds[convId] = newIds;
                            if (clientConversationsData[convId]) {
                                clientConversationsData[convId].messages = data.messages;
                            }
                            renderClientMessages(data.messages);
                        }
                    }
                })
                .catch(() => {});
            }, 5000);
        }

        function stopClientPolling() {
            if (clientPollingInterval) {
                clearInterval(clientPollingInterval);
                clientPollingInterval = null;
            }
        }

        // Patch selectClientConversation to also start polling
        const _origSelectClientConv = selectClientConversation;
        function selectClientConversation(convId) {
            _origSelectClientConv(convId);
            clientLastKnownMsgIds[convId] = (clientConversationsData[convId]?.messages || []).map(m => m.id);
            startClientPolling(convId);
        }
    </script>
@endsection

