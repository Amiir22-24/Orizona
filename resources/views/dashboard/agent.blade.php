@extends('layouts.dashboard')

@section('title', 'Espace Agent')

@section('content')
    @php
        $user = Auth::user();

        // 1. Propriétés répertoriées par cet agent (du plus récent au plus ancien)
        $agentProperties = \App\Models\Property::where('agent_id', $user->id)
            ->latest()
            ->get();

        // 2. Demandes d'occupation en attente d'approbation par l'agent
        $pendingAgentRequests = \App\Models\OccupancyRequest::with(['property', 'client'])
            ->where('agent_id', $user->id)
            ->where('status', 'pending_agent')
            ->latest()
            ->get();

        // 3. Conversations de l'agent avec les clients (sur ses propriétés répertoriées)
        $conversations = \App\Models\Conversation::with([
                'property',
                'client',
                'agent',
                'admin',
                'messages' => fn($q) => $q->with('sender')->orderBy('created_at', 'asc')
            ])
            ->where('agent_id', $user->id)
            ->latest('last_message_at')
            ->get();

        // 4. Notifications de l'agent (du plus récent au plus ancien)
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->latest()
            ->get();
        $unreadNotificationsCount = $notifications->where('is_read', false)->count();

        // Liste des propriétaires pour le formulaire
        $owners = \App\Models\User::where('user_type', 'owner')->get();
    @endphp

    <div class="space-y-8">

        {{-- Flash Messages --}}
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

        {{-- EN-TÊTE & KPI --}}
        <div class="space-y-5">
            <div class="border-b border-gray-200 dark:border-[#3E3E3A] pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Espace Agent Immobilier</h1>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">
                        Matricule Agent : <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ $user->matricule ?? 'AGT-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</strong>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        Bienvenue, <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ $user->full_name }}</strong>
                    </span>
                    <button type="button" onclick="openAddPropertyModal()"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                        + Répertorier une Propriété
                    </button>
                </div>
            </div>

            {{-- Cartes KPI --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="#section-properties" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Biens Répertoriés</span>
                    <p class="text-2xl font-bold text-[#f53003] mt-2">{{ $agentProperties->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Sous votre gestion</span>
                </a>
                <a href="#section-validations" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Demandes à Valider</span>
                    <p class="text-2xl font-bold text-amber-600 mt-2">{{ $pendingAgentRequests->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">En attente de votre avis</span>
                </a>
                <a href="#section-messagerie" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Conversations</span>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $conversations->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Clients intéressés</span>
                </a>
                <a href="#section-notifications" class="p-5 bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl hover:border-[#f53003] transition-all block">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase text-[#706f6c] dark:text-[#A1A09A]">Notifications</span>
                        @if($unreadNotificationsCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 border border-red-200">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mt-2">{{ $notifications->count() }}</p>
                    <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] mt-1 block">Avis récents</span>
                </a>
            </div>
        </div>

        {{-- SOUS-SECTION 1 : RÉPERTORIER & GÉRER LES PROPRIÉTÉS --}}
        <section id="section-properties" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4 gap-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Sous-section 1</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Propriétés Répertoriées &amp; Gérées</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Biens enregistrés par vos soins, du plus récent au plus ancien.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-gray-100 dark:bg-[#2a2a28] text-[#1b1b18] dark:text-[#EDEDEC] font-semibold text-xs rounded-full">
                        {{ $agentProperties->count() }} biens
                    </span>
                    <button type="button" onclick="openAddPropertyModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">
                        + Répertorier un bien
                    </button>
                </div>
            </div>

            {{-- Grille à taille fixe avec scroll interne (h-[500px]) --}}
            <div class="h-[500px] overflow-y-auto pr-1">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse($agentProperties as $prop)
                        @php
                            $photos = $prop->photos_with_urls;
                            $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                            $coverUrl  = $mainPhoto['photo_url'] ?? null;
                            $isOccupied = $prop->is_occupied || $prop->status === 'occupied';
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
                                'agent_name'   => $prop->agent_name ?? 'Agent',
                                'photos'       => $photos,
                                'video_url'    => $prop->video_url,
                            ];
                        @endphp
                        <div class="border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] hover:border-[#f53003] transition-all flex flex-col">
                            <div data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))" class="w-full h-40 overflow-hidden bg-gray-100 dark:bg-[#2a2a28] relative flex-shrink-0 cursor-pointer group">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $prop->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Pas de photo</span>
                                    </div>
                                @endif
                                <span class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-black/60 text-white backdrop-blur-sm">
                                    {{ ucfirst($prop->operation_type) }}
                                </span>
                                @if(count($photos) > 1)
                                    <span class="absolute bottom-3 left-3 px-2 py-0.5 text-[10px] font-bold rounded bg-black/70 text-white backdrop-blur-sm">
                                        📷 {{ count($photos) }} photos
                                    </span>
                                @endif
                                <span class="absolute top-3 right-3 px-2 py-0.5 text-[10px] font-bold rounded {{ $isOccupied ? 'bg-amber-500 text-white' : 'bg-green-600 text-white' }}">
                                    {{ $isOccupied ? 'Occupée' : 'Disponible' }}
                                </span>
                            </div>

                            <div class="p-4 flex flex-col flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm font-extrabold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}</span>
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->bedrooms ?? 0 }} ch. &middot; {{ $prop->surface_area ?? 0 }} m²</span>
                                </div>
                                <h3 data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] mb-1 line-clamp-1 cursor-pointer hover:text-[#f53003] transition-colors">{{ $prop->title }}</h3>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-2 line-clamp-1">{{ $prop->city }} &mdash; {{ $prop->address }}</p>
                                <button type="button" data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))" class="w-full py-1 mb-2 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-[#2a2a28] hover:bg-gray-200 dark:hover:bg-[#333330] rounded-lg transition-all">
                                    Aperçu &amp; Photos ({{ count($photos) }})
                                </button>
                                <div class="mt-auto pt-2 border-t border-gray-100 dark:border-[#2a2a28] flex items-center justify-between text-[11px] text-[#706f6c] dark:text-[#A1A09A]">
                                    <span>Propriétaire : <strong>{{ $prop->owner_name ?? 'N/A' }}</strong></span>
                                    <span class="font-semibold text-amber-500">★ {{ $prop->star_rating ?? 5 }}/5</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Vous n'avez répertorié aucune propriété pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- SOUS-SECTION 2 : VALIDATION D'OCCUPATION D'UNE PROPRIÉTÉ --}}
        <section id="section-validations" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-4">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Sous-section 2</span>
                    <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Validation d'Occupation d'une Propriété</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Demandes des clients pour vos propriétés répertoriées (du plus récent au plus ancien).</p>
                </div>
                <span class="px-3 py-1 bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 font-semibold text-xs rounded-full border border-amber-200">
                    {{ $pendingAgentRequests->count() }} en attente
                </span>
            </div>

            {{-- Conteneur à taille fixe avec scroll interne (h-80) --}}
            <div class="h-80 overflow-y-auto pr-1 space-y-3">
                @forelse($pendingAgentRequests as $req)
                    <div class="p-4 border border-amber-200 dark:border-amber-900/40 bg-amber-50/40 dark:bg-[#1a1500]/30 rounded-xl space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-amber-100 dark:border-amber-900/20 pb-2">
                            <div>
                                <h4 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $req->property->title ?? 'Propriété' }}
                                </h4>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                                    Client : <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ $req->client->full_name ?? 'N/A' }}</strong>
                                    &middot; Loyer : <strong class="text-[#f53003]">{{ number_format($req->rent_amount, 0, ',', ' ') }} XOF</strong>
                                    &middot; Reçu {{ $req->created_at->diffForHumans() }}
                                </p>
                                @if($req->message)
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1 italic">"{{ $req->message }}"</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <form method="POST" action="{{ route('web.occupancy.agent.approve', $req->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all shadow-sm">
                                        J'approuve (Transmettre au propriétaire)
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('web.occupancy.agent.reject', $req->id) }}">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Refuser cette demande ?')"
                                        class="px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 dark:bg-[#2a100c] border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                                        Refuser
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Aucune demande d'occupation en attente de votre validation.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SOUS-SECTION 3 : MESSAGERIE STYLE WHATSAPP / MESSENGER --}}
        @php
            $agentConversationsPayload = [];
            foreach ($conversations as $c) {
                $interlocutor = $c->client ?? $c->agent ?? $c->admin;
                $interlocutorName = $interlocutor?->full_name ?? 'Client';
                $interlocutorRole = 'Client';
                $propTitle = $c->property->title ?? ($c->subject ?? 'Propriété');
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

                $agentConversationsPayload[$c->id] = [
                    'id'                    => $c->id,
                    'property_id'           => $c->property_id,
                    'client_id'             => $c->client_id,
                    'interlocutor_name'     => $interlocutorName,
                    'interlocutor_role'     => $interlocutorRole,
                    'interlocutor_initials' => $initials,
                    'property_title'        => $propTitle,
                    'messages'              => $msgsData,
                ];
            }
            $activeConvId = session('active_conv_id') ?? array_key_first($agentConversationsPayload);
        @endphp

        <section id="section-messagerie" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-3">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Sous-section 3</span>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Messagerie &amp; Discussions Clients</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Interface de messagerie instantanée avec les clients intéressés ou occupants.</p>
                </div>
                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A] font-semibold">{{ $conversations->count() }} discussion(s) active(s)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] shadow-sm min-h-[460px]">
                {{-- COLONNE GAUCHE : LISTE DES DISCUSSIONS --}}
                <div class="border-r border-gray-200 dark:border-[#3E3E3A] flex flex-col bg-gray-50/50 dark:bg-[#181817]">
                    <div class="p-3 border-b border-gray-200 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-[#1b1b18] dark:text-[#EDEDEC] uppercase tracking-wider">Discussions</h3>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-100 text-[#f53003] dark:bg-red-950/50 dark:text-red-300">
                                {{ $conversations->count() }}
                            </span>
                        </div>
                        <input type="text" id="agent-chat-search" oninput="filterAgentConversationsList()" placeholder="Rechercher un client..."
                            class="w-full px-3 py-1.5 text-xs border border-gray-200 dark:border-[#3E3E3A] rounded-lg bg-gray-50 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div id="agent-conversations-list" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-[#2a2a28] max-h-[400px]">
                        @forelse($conversations as $conv)
                            @php
                                $payload = $agentConversationsPayload[$conv->id] ?? null;
                                $lastMsgObj = $conv->messages->last();
                                $lastMsgText = $lastMsgObj ? $lastMsgObj->message : 'Pas de message';
                                $lastMsgTime = $lastMsgObj ? $lastMsgObj->created_at->diffForHumans() : '';
                            @endphp
                            <button type="button"
                                onclick="selectAgentConversation({{ $conv->id }})"
                                id="agent-conv-tab-{{ $conv->id }}"
                                class="agent-conv-tab-item w-full p-3 text-left flex items-start gap-3 hover:bg-gray-100/80 dark:hover:bg-[#222220] transition-colors relative"
                                data-search="{{ strtolower(($payload['interlocutor_name'] ?? '') . ' ' . ($payload['property_title'] ?? '')) }}">
                                
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#f53003] to-amber-500 text-white font-extrabold text-xs flex items-center justify-center flex-shrink-0 uppercase shadow-sm">
                                    {{ $payload['interlocutor_initials'] ?? 'CL' }}
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h4 class="font-bold text-xs text-[#1b1b18] dark:text-[#EDEDEC] truncate">{{ $payload['interlocutor_name'] ?? 'Client' }}</h4>
                                        <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] flex-shrink-0">{{ $lastMsgTime }}</span>
                                    </div>
                                    <p class="text-[11px] font-semibold text-[#f53003] truncate mb-0.5">{{ $payload['property_title'] ?? 'Propriété' }}</p>
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
                    <div id="agent-chat-header" class="p-3.5 border-b border-gray-200 dark:border-[#3E3E3A] bg-gray-50/50 dark:bg-[#181817] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div id="agent-chat-avatar" class="w-9 h-9 rounded-full bg-[#f53003] text-white font-bold text-xs flex items-center justify-center uppercase shadow-sm">
                                --
                            </div>
                            <div>
                                <h3 id="agent-chat-name" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Sélectionnez une discussion</h3>
                                <p id="agent-chat-property" class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Cliquez sur un client à gauche pour afficher l'historique des échanges.</p>
                            </div>
                        </div>
                        <span id="agent-chat-status-badge" class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-950/40 dark:text-green-300 border border-green-200 hidden">
                            Discussion active
                        </span>
                    </div>

                    {{-- MESSAGES STREAM --}}
                    <div id="agent-chat-messages-stream" class="flex-1 p-4 overflow-y-auto space-y-3 bg-[#f8f8f7] dark:bg-[#121211] min-h-[300px] max-h-[350px]">
                        <div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Sélectionnez une discussion à gauche pour démarrer.
                        </div>
                    </div>

                    {{-- INPUT FOOTER --}}
                    <div class="p-3 border-t border-gray-200 dark:border-[#3E3E3A] bg-white dark:bg-[#161615]">
                        <form id="agent-chat-send-form" method="POST" action="{{ route('web.messages.agent') }}" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="conversation_id" id="agent-chat-conv-id">
                            <input type="hidden" name="property_id" id="agent-chat-property-id">
                            <input type="hidden" name="client_id" id="agent-chat-client-id">
                            <input type="text" name="message" id="agent-chat-input-text" required placeholder="Tapez votre message ici..."
                                class="flex-1 px-4 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-xl bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                            <button type="submit" id="agent-chat-submit-btn" disabled
                                class="px-5 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-xl transition-all shadow-sm opacity-50 cursor-not-allowed">
                                Envoyer ➔
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- SOUS-SECTION 4 : NOTIFICATIONS AGENT --}}
        <section id="section-notifications" class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-3 gap-3">
                <div>
                    <span class="text-xs font-semibold text-[#f53003] uppercase tracking-wider">Sous-section 4</span>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Notifications Agent</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Alertes et mises à jour sur vos activités (du plus récent au plus ancien).</p>
                </div>
                @if($unreadNotificationsCount > 0)
                    <form method="POST" action="{{ route('web.notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="text-xs text-[#f53003] hover:underline font-semibold">
                            Tout marquer comme lu ({{ $unreadNotificationsCount }})
                        </button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2">
                <input type="text" id="agent-notif-search" oninput="applyAgentNotificationFilters()" placeholder="Rechercher une notification..."
                    class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                <select id="agent-notif-filter-status" onchange="applyAgentNotificationFilters()"
                    class="w-full px-3 py-1.5 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    <option value="all">Toutes ({{ $notifications->count() }})</option>
                    <option value="unread">Non lues ({{ $unreadNotificationsCount }})</option>
                    <option value="read">Lues ({{ $notifications->count() - $unreadNotificationsCount }})</option>
                </select>
            </div>

            {{-- Conteneur à taille fixe avec scroll interne (h-64) --}}
            <div id="agent-notifications-list" class="h-64 overflow-y-auto pr-1 space-y-2">
                @forelse($notifications as $notif)
                    <div class="agent-notification-item p-3 border rounded-lg transition-all {{ $notif->is_read ? 'border-gray-100 dark:border-[#2a2a28]' : 'border-red-200 dark:border-red-900/40 bg-red-50/50 dark:bg-[#2a100c]/20' }}"
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
            <p id="agent-no-notif-results" class="hidden text-xs text-[#706f6c] dark:text-[#A1A09A] text-center py-4">
                Aucun résultat pour ce filtre.
            </p>
        </section>

    </div>

    {{-- MODAL : RÉPERTORIER UNE PROPRIÉTÉ (FORMULAIRE AGENT) --}}
    <div id="agent-add-property-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto">
            <button type="button" onclick="closeAddPropertyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-xl font-bold leading-none">&times;</button>

            <div>
                <h3 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Répertorier une Nouvelle Propriété</h3>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Renseignez les caractéristiques du bien pour l'ajouter au catalogue ORIZONA.</p>
            </div>

            <form method="POST" action="{{ route('web.agent.properties.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-full">
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Titre de l'annonce *</label>
                        <input type="text" name="title" required placeholder="Ex : Villa moderne 4 pièces avec piscine"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Type de bien *</label>
                        <select name="property_type" required
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                            <option value="apartment">Appartement</option>
                            <option value="house">Maison / Villa</option>
                            <option value="studio">Studio</option>
                            <option value="office">Bureau / Commercial</option>
                            <option value="land">Terrain</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Type d'opération *</label>
                        <select name="operation_type" required
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                            <option value="rent">Location</option>
                            <option value="sale">Vente</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Prix (XOF) *</label>
                        <input type="number" name="price" required placeholder="Ex : 150000" min="0"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Ville *</label>
                        <input type="text" name="city" required placeholder="Ex : Lomé"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Quartier</label>
                        <input type="text" name="neighborhood" placeholder="Ex : Tokoin"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Adresse complète *</label>
                        <input type="text" name="address" required placeholder="Ex : Boulevard du 13 Janvier"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Nombre de chambres</label>
                        <input type="number" name="bedrooms" value="1" min="0"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Nombre de salles de bain</label>
                        <input type="number" name="bathrooms" value="1" min="0"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Surface (m²)</label>
                        <input type="number" name="surface_area" placeholder="Ex : 120" min="0" step="0.1"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Matricule du propriétaire (Optionnel)</label>
                        <input type="text" name="owner_matricule" placeholder="Ex : OWN-2026-000001"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none">
                        <p class="text-[10px] text-[#706f6c] mt-0.5">Saisissez le matricule du propriétaire s'il est déjà inscrit.</p>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Images de la propriété (Sélection multiple autorisée)</label>
                        <input type="file" name="photos[]" accept="image/*" multiple
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC]">
                        <p class="text-[11px] text-[#706f6c] dark:text-[#A1A09A] mt-1">Vous pouvez sélectionner plusieurs images d'un coup (façade, pièces, salon, etc.).</p>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Vidéo de la propriété (Optionnel)</label>
                        <input type="file" name="video" accept="video/mp4,video/webm,video/ogg"
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC]">
                        <p class="text-[11px] text-[#706f6c] dark:text-[#A1A09A] mt-1">Format accepté : MP4, WebM, OGG. Taille max : 20 Mo.</p>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-xs font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Description détaillée</label>
                        <textarea name="description" rows="3" placeholder="Décrivez les atouts de la propriété..."
                            class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-[#f53003] focus:outline-none resize-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-[#2a2a28]">
                    <button type="button" onclick="closeAddPropertyModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2a2a28] rounded-lg transition-all">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">
                        Répertorier la Propriété
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CONSULTATION PROPRIÉTÉ & GALERIE PHOTOS (AGENT) --}}
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

            <div class="flex justify-end pt-3 border-t border-gray-100 dark:border-[#2a2a28]">
                <button type="button" onclick="closePropertyDetailModal()" class="px-5 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all shadow-sm">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
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

        function openAddPropertyModal() {
            const modal = document.getElementById('agent-add-property-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAddPropertyModal() {
            const modal = document.getElementById('agent-add-property-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('agent-add-property-modal').addEventListener('click', function(e) {
            if (e.target === this) closeAddPropertyModal();
        });

        function applyAgentNotificationFilters() {
            const searchVal = document.getElementById('agent-notif-search').value.toLowerCase().trim();
            const statusVal = document.getElementById('agent-notif-filter-status').value;
            const items = document.querySelectorAll('.agent-notification-item');
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

            const noResults = document.getElementById('agent-no-notif-results');
            if (noResults) noResults.classList.toggle('hidden', !(visibleCount === 0 && items.length > 0));
        }

        const agentConversationsData = @json($agentConversationsPayload);
        let activeAgentConvId = @json($activeConvId);

        function selectAgentConversation(convId) {
            const data = agentConversationsData[convId];
            if (!data) return;

            activeAgentConvId = convId;

            document.querySelectorAll('.agent-conv-tab-item').forEach(el => {
                el.classList.remove('bg-red-50', 'dark:bg-red-950/30', 'border-l-4', 'border-[#f53003]');
            });
            const activeTab = document.getElementById('agent-conv-tab-' + convId);
            if (activeTab) {
                activeTab.classList.add('bg-red-50', 'dark:bg-red-950/30', 'border-l-4', 'border-[#f53003]');
            }

            document.getElementById('agent-chat-avatar').textContent = data.interlocutor_initials;
            document.getElementById('agent-chat-name').textContent = data.interlocutor_name + ' (' + data.interlocutor_role + ')';
            document.getElementById('agent-chat-property').textContent = 'Propriété : ' + data.property_title;
            document.getElementById('agent-chat-status-badge').classList.remove('hidden');

            document.getElementById('agent-chat-conv-id').value = data.id || '';
            document.getElementById('agent-chat-property-id').value = data.property_id || '';
            document.getElementById('agent-chat-client-id').value = data.client_id || '';

            const submitBtn = document.getElementById('agent-chat-submit-btn');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

            // Init lastKnown IDs and render (will be overridden below once polling functions defined)
            if (typeof lastKnownMessageIds !== 'undefined') {
                lastKnownMessageIds[convId] = (data.messages || []).map(m => m.id);
            }

            if (typeof renderAgentMessages === 'function') {
                renderAgentMessages(data.messages);
            } else {
                // Fallback inline render
                const container = document.getElementById('agent-chat-messages-stream');
                container.innerHTML = '';
                if (!data.messages || data.messages.length === 0) {
                    container.innerHTML = '<div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucun message dans cette discussion. Écrivez le premier !</div>';
                } else {
                    data.messages.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'flex flex-col ' + (msg.is_me ? 'items-end' : 'items-start');
                        const bubble = document.createElement('div');
                        bubble.className = 'max-w-[80%] p-3 text-xs shadow-sm ' +
                            (msg.is_me ? 'bg-[#f53003] text-white rounded-2xl rounded-tr-xs'
                                : 'bg-white dark:bg-[#1f1f1d] text-[#1b1b18] dark:text-[#EDEDEC] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl rounded-tl-xs');
                        const txt = document.createElement('p');
                        txt.className = 'whitespace-pre-line leading-relaxed';
                        txt.textContent = msg.message;
                        bubble.appendChild(txt);
                        msgDiv.appendChild(bubble);
                        container.appendChild(msgDiv);
                    });
                    container.scrollTop = container.scrollHeight;
                }
            }

            if (typeof startPolling === 'function') {
                startPolling(convId);
            }
        }

        function filterAgentConversationsList() {
            const searchVal = document.getElementById('agent-chat-search').value.toLowerCase().trim();
            document.querySelectorAll('.agent-conv-tab-item').forEach(item => {
                const search = item.getAttribute('data-search') || '';
                if (!searchVal || search.includes(searchVal)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (activeAgentConvId && agentConversationsData[activeAgentConvId]) {
                selectAgentConversation(activeAgentConvId);
            } else {
                const keys = Object.keys(agentConversationsData);
                if (keys.length > 0) {
                    selectAgentConversation(keys[0]);
                }
            }
        });

        // ── AUTO-POLLING : rafraîchissement des messages toutes les 5 secondes ──
        let pollingInterval = null;
        let lastKnownMessageIds = {};

        function renderAgentMessages(messages) {
            const container = document.getElementById('agent-chat-messages-stream');
            if (!messages || messages.length === 0) {
                container.innerHTML = '<div class="h-full flex items-center justify-center text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucun message dans cette discussion. Écrivez le premier !</div>';
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

        function startPolling(convId) {
            stopPolling();
            pollingInterval = setInterval(function() {
                if (!convId) return;
                fetch('/web/conversations/' + convId + '/messages', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.messages) {
                        const lastIds = lastKnownMessageIds[convId] || [];
                        const newIds = data.messages.map(m => m.id);
                        const hasNew = newIds.some(id => !lastIds.includes(id));
                        if (hasNew) {
                            lastKnownMessageIds[convId] = newIds;
                            // Aussi mettre à jour le payload local
                            if (agentConversationsData[convId]) {
                                agentConversationsData[convId].messages = data.messages;
                            }
                            renderAgentMessages(data.messages);
                            // Mettre à jour l'aperçu dans la liste de gauche
                            updateConvTabPreview(convId, data.messages);
                        }
                    }
                })
                .catch(() => {});
            }, 5000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        function updateConvTabPreview(convId, messages) {
            const tab = document.getElementById('agent-conv-tab-' + convId);
            if (!tab || !messages || messages.length === 0) return;
            const lastMsg = messages[messages.length - 1];
            const previewEl = tab.querySelector('p:last-of-type');
            if (previewEl) {
                previewEl.textContent = lastMsg.is_me ? 'Vous : ' + lastMsg.message : lastMsg.message;
            }
        }
    </script>
@endsection

