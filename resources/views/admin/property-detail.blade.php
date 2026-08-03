@extends('layouts.dashboard')

@section('title', 'Administration - Détail Propriété')

@section('content')
    @php
        $photos = $property->photos_with_urls;
        $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
        $coverUrl = $mainPhoto['photo_url'] ?? null;
    @endphp

    <div class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.web.properties') }}" class="text-sm font-semibold text-[#706f6c] dark:text-[#A1A09A] hover:text-[#f53003] transition-colors">
                    &larr; Retour aux propriétés
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $property->title }}</h1>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $property->city }} — {{ $property->address }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 text-xs font-bold rounded-full
                    {{ $property->status === 'validated' ? 'bg-green-100 text-green-700' :
                       ($property->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($property->status) }}
                </span>
                <span class="px-3 py-1.5 text-xs font-bold rounded-full {{ $property->is_occupied ? 'bg-[#f53003]/10 text-[#f53003]' : 'bg-green-100 text-green-700' }}">
                    {{ $property->is_occupied ? 'Occupée' : 'Disponible' }}
                </span>
                <form action="{{ route('admin.web.properties.destroy', $property->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette propriété ? Cette action est irréversible.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- Alerte si Propriété Rejetée -->
        @if($property->status === 'rejected')
            <div class="bg-red-50 dark:bg-[#1D0002] border border-red-200 dark:border-red-900 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-sm">✕</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-red-700 dark:text-red-300">Cette propriété a été rejetée</h4>
                    <p class="text-xs text-red-600 dark:text-red-400">
                        <strong>Rejetée par :</strong> {{ $property->rejectedBy->full_name ?? 'Administrateur' }}
                        @if($property->rejectedBy) ({{ $property->rejectedBy->email }}) @endif
                    </p>
                    @if($property->rejection_reason)
                        <p class="text-xs text-red-600 dark:text-red-400 italic">
                            <strong>Motif du rejet :</strong> "{{ $property->rejection_reason }}"
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Actions de Validation admin -->
        @if($property->status === 'pending' || $property->status === 'rejected')
            <div class="bg-amber-50 dark:bg-[#1D0002] border border-amber-200 dark:border-amber-800 rounded-xl p-4 flex flex-wrap items-center gap-3">
                <span class="text-xs font-bold text-amber-800 dark:text-amber-300">Validation de la propriété :</span>
                <form method="POST" action="{{ route('admin.web.property-approve', $property->id) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all">
                        ✓ Valider la propriété
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.web.property-reject', $property->id) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Motif du rejet" required
                           class="px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-red-500">
                    <button type="submit" onclick="return confirm('Confirmer le rejet de cette propriété ?')"
                            class="px-4 py-2 text-xs font-semibold text-red-600 bg-white border border-red-300 hover:bg-red-50 rounded-lg transition-all">
                        ✕ Rejeter
                    </button>
                </form>
            </div>
        @endif

        <!-- SECTION GALERIE MÉDIAS (PHOTOS & VIDÉO) -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-[#2a2a28] pb-3">
                <div>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Galerie Médias (Photos &amp; Vidéo)</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Visualisation complète des éléments visuels répertoriés pour ce bien.</p>
                </div>
                <span class="px-3 py-1 bg-gray-100 dark:bg-[#2a2a28] text-xs font-bold rounded-full text-[#1b1b18] dark:text-[#EDEDEC]">
                    {{ count($photos) }} photo(s) @if($property->video_url) &bull; 1 vidéo @endif
                </span>
            </div>

            @if(count($photos) > 0)
                <div class="space-y-3">
                    <div class="w-full h-72 sm:h-96 bg-black rounded-xl overflow-hidden relative flex items-center justify-center">
                        <img id="admin-main-photo" src="{{ $coverUrl }}" alt="Photo principale" class="w-full h-full object-contain">
                        @if(count($photos) > 1)
                            <button type="button" onclick="prevAdminPhoto()" class="absolute left-3 p-2 rounded-full bg-black/50 text-white hover:bg-black/80 transition-all font-bold text-lg">&lsaquo;</button>
                            <button type="button" onclick="nextAdminPhoto()" class="absolute right-3 p-2 rounded-full bg-black/50 text-white hover:bg-black/80 transition-all font-bold text-lg">&rsaquo;</button>
                            <span id="admin-photo-counter" class="absolute bottom-3 right-3 px-3 py-1 rounded-full bg-black/60 text-white text-xs font-semibold backdrop-blur-sm">1 / {{ count($photos) }}</span>
                        @endif
                    </div>

                    @if(count($photos) > 1)
                        <div id="admin-thumbnails" class="flex items-center gap-2 overflow-x-auto py-1">
                            @foreach($photos as $idx => $photo)
                                <img src="{{ $photo['photo_url'] }}" alt="Photo {{ $idx + 1 }}"
                                     onclick="selectAdminPhoto({{ $idx }})"
                                     class="admin-thumb-img w-20 h-14 object-cover rounded-lg border-2 cursor-pointer transition-all flex-shrink-0 {{ $idx === 0 ? 'border-[#f53003] opacity-100' : 'border-transparent opacity-60 hover:opacity-100' }}"
                                     data-idx="{{ $idx }}" data-url="{{ $photo['photo_url'] }}">
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="p-8 text-center bg-gray-50 dark:bg-[#1a1a18] rounded-xl border border-dashed border-gray-300 dark:border-[#3E3E3A]">
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucune photo disponible pour cette propriété.</p>
                </div>
            @endif

            @if($property->video_url)
                <div class="pt-4 border-t border-gray-100 dark:border-[#2a2a28]">
                    <span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] block mb-2">Vidéo de la propriété :</span>
                    <video controls class="w-full rounded-xl max-h-80 object-cover shadow-sm">
                        <source src="{{ asset('storage/' . $property->video_url) }}" type="video/mp4">
                        Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                </div>
            @endif
        </div>

        <!-- Infos propriété & Intervenants -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Informations générales</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Prix</span><span class="font-bold text-[#f53003]">{{ number_format($property->price, 0, ',', ' ') }} {{ $property->currency }} / mois</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Type</span><span class="font-semibold">{{ ucfirst($property->property_type) }} — {{ ucfirst($property->operation_type) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Catalogue</span><span class="font-semibold">{{ ucfirst($property->catalog_type) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Chambres</span><span class="font-semibold">{{ $property->bedrooms ?? 0 }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Salles de bain</span><span class="font-semibold">{{ $property->bathrooms ?? 0 }}</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Surface</span><span class="font-semibold">{{ $property->surface_area ?? 0 }} m²</span></div>
                    <div class="flex justify-between"><span class="text-[#706f6c] dark:text-[#A1A09A]">Qualité</span><span class="font-semibold">{{ $property->quality_label }} ({{ $property->quality_score }}/100)</span></div>
                    @if($property->description)
                        <p class="pt-2 border-t border-gray-100 dark:border-[#3E3E3A] text-xs text-[#706f6c] dark:text-[#A1A09A] italic">{{ $property->description }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Propriétaire &amp; Agent</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] rounded-xl">
                        <p class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Propriétaire</p>
                        @if($property->owner)
                            <a href="{{ route('admin.web.user-detail', $property->owner->id) }}" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003]">
                                {{ $property->owner->full_name }}
                            </a>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $property->owner->email }} — {{ $property->owner->phone }}</p>
                            <p class="text-xs font-mono text-[#706f6c] dark:text-[#A1A09A]">Matricule : {{ $property->owner->matricule ?? '-' }}</p>
                        @else
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $property->owner_name ?? 'Non renseigné' }}</p>
                        @endif
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] rounded-xl">
                        <p class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Agent référent</p>
                        @if($property->agent)
                            <a href="{{ route('admin.web.user-detail', $property->agent->id) }}" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003]">
                                {{ $property->agent->full_name }}
                            </a>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $property->agent->email }} — {{ $property->agent->phone }}</p>
                        @else
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $property->agent_name ?? 'Aucun agent' }}</p>
                        @endif
                    </div>
                    @if($property->occupiedBy)
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a18] rounded-xl">
                            <p class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] mb-1">Occupant actuel</p>
                            <a href="{{ route('admin.web.user-detail', $property->occupiedBy->id) }}" class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003]">
                                {{ $property->occupiedBy->full_name }}
                            </a>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Depuis {{ $property->occupied_at?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Demandes d'occupation (Triées du plus récent au plus ancien) -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Historique des Demandes d'occupation ({{ $requests->count() }})</h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Toutes les demandes formulées par les clients (du plus récent au plus ancien).</p>
                </div>
            </div>
            <div class="space-y-4">
                @forelse($requests as $req)
                    @php
                        $isRejected = $req->status === 'rejected';
                        $rejectorName = $req->rejector
                            ? $req->rejector->full_name . ' (' . ucfirst($req->rejector->user_type) . ')'
                            : ($req->agent_id && str_contains($req->rejection_reason ?? '', 'agent')
                                ? 'Agent'
                                : ($req->owner_id ? 'Propriétaire' : 'Admin'));
                        $reason = $req->rejection_reason ?? ($req->agent_notes ?? null);
                    @endphp
                    <div class="p-4 border rounded-xl transition-all {{ $isRejected ? 'border-red-200 dark:border-red-900/50 bg-red-50/40 dark:bg-[#1D0002]/40' : 'border-gray-200 dark:border-[#3E3E3A] bg-white dark:bg-[#161615]' }}">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $req->client->full_name ?? 'Client inconnu' }}</p>
                                    @if($req->client && $req->client->phone)
                                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">({{ $req->client->phone }})</span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                                    Soumise le <strong>{{ $req->created_at->format('d/m/Y à H:i') }}</strong> — Loyer proposé : <strong class="text-[#f53003]">{{ number_format($req->rent_amount ?? $req->proposed_amount ?? 0, 0, ',', ' ') }} XOF</strong>
                                </p>
                                @if($req->message)
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1.5 italic bg-white/60 dark:bg-[#161615]/60 p-2 rounded border border-gray-100 dark:border-[#2a2a28]">
                                        "{{ $req->message }}"
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col sm:items-end gap-1.5 flex-shrink-0">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                    {{ in_array($req->status, ['pending_agent', 'pending_owner', 'pending']) ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300' :
                                       ($req->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300 border border-green-300' :
                                       ($isRejected ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ str_replace('_', ' ', ucfirst($req->status)) }}
                                </span>
                                @if($req->agent)
                                    <span class="text-[11px] text-[#706f6c] dark:text-[#A1A09A]">Agent : {{ $req->agent->full_name }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Détails du Refus si la demande a été rejetée --}}
                        @if($isRejected)
                            <div class="mt-3 pt-3 border-t border-red-200 dark:border-red-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-1.5 text-red-700 dark:text-red-400 font-semibold">
                                    <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Demande rejetée par : <strong class="underline">{{ $rejectorName }}</strong></span>
                                </div>
                                @if($reason)
                                    <div class="text-xs text-red-600 dark:text-red-400 italic">
                                        Motif : "{{ $reason }}"
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] text-center py-6">Aucune demande d'occupation enregistrée pour cette propriété.</p>
                @endforelse
            </div>
        </div>

        <!-- Contrats -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Contrats ({{ $contracts->count() }})</h2>
            </div>
            <div class="space-y-3">
                @forelse($contracts as $contract)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div>
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $contract->tenant->full_name ?? 'Locataire' }}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Loyer {{ number_format($contract->monthly_rent ?? 0, 0, ',', ' ') }} XOF —
                                {{ $contract->start_date?->format('d/m/Y') }} → {{ $contract->end_date?->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $contract->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $contract->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <a href="{{ route('web.contracts.show', $contract->id) }}" target="_blank"
                               class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                Voir le contrat
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun contrat pour cette propriété.</p>
                @endforelse
            </div>
        </div>

        <!-- Discussions -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Discussions liées ({{ $conversations->count() }})</h2>
            </div>
            <div class="space-y-3">
                @forelse($conversations as $conv)
                    <div class="p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $conv->subject ?? 'Discussion' }}</p>
                            <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $conv->messages()->count() }} messages</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($conv->client)
                                <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-100 dark:bg-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A]">Client : {{ $conv->client->full_name }}</span>
                            @endif
                            @if($conv->agent)
                                <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-100 dark:bg-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A]">Agent : {{ $conv->agent->full_name }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune discussion pour cette propriété.</p>
                @endforelse
            </div>
        </div>

        <!-- Transactions -->
        @if($transactions->count() > 0)
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Transactions ({{ $transactions->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Utilisateur</th>
                            <th class="py-2 pr-4">Montant</th>
                            <th class="py-2">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                        @foreach($transactions as $txn)
                            <tr>
                                <td class="py-2 pr-4 text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 pr-4">{{ $txn->user->full_name ?? 'N/A' }}</td>
                                <td class="py-2 pr-4 font-bold text-[#f53003]">{{ number_format($txn->amount, 0, ',', ' ') }} XOF</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $txn->status === 'succeeded' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($txn->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- SCRIPTS SLIDER GALERIE PHOTOS --}}
    <script>
        const adminPhotos = @json(array_column($photos, 'photo_url'));
        let currentAdminPhotoIdx = 0;

        function renderAdminPhoto() {
            if (adminPhotos.length === 0) return;
            const mainImg = document.getElementById('admin-main-photo');
            const counter = document.getElementById('admin-photo-counter');
            if (mainImg) mainImg.src = adminPhotos[currentAdminPhotoIdx];
            if (counter) counter.textContent = (currentAdminPhotoIdx + 1) + ' / ' + adminPhotos.length;

            document.querySelectorAll('.admin-thumb-img').forEach((thumb, idx) => {
                if (idx === currentAdminPhotoIdx) {
                    thumb.classList.add('border-[#f53003]', 'opacity-100');
                    thumb.classList.remove('border-transparent', 'opacity-60');
                } else {
                    thumb.classList.remove('border-[#f53003]', 'opacity-100');
                    thumb.classList.add('border-transparent', 'opacity-60');
                }
            });
        }

        function selectAdminPhoto(idx) {
            currentAdminPhotoIdx = idx;
            renderAdminPhoto();
        }

        function prevAdminPhoto() {
            if (adminPhotos.length === 0) return;
            currentAdminPhotoIdx = (currentAdminPhotoIdx - 1 + adminPhotos.length) % adminPhotos.length;
            renderAdminPhoto();
        }

        function nextAdminPhoto() {
            if (adminPhotos.length === 0) return;
            currentAdminPhotoIdx = (currentAdminPhotoIdx + 1) % adminPhotos.length;
            renderAdminPhoto();
        }
    </script>
@endsection
