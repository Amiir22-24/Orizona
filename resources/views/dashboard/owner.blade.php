@extends('layouts.dashboard')

@section('title', 'Espace Propriétaire')

@section('content')
    @php
        $user = Auth::user();
        $ownerProperties = \App\Models\Property::with(['agent'])->where('owner_id', $user->id)->latest()->get();
        $pendingOwnerRequests = \App\Models\OccupancyRequest::with(['property', 'client', 'agent'])
            ->where('owner_id', $user->id)
            ->where('status', 'pending_owner')
            ->latest()->get();
        $contracts = \App\Models\OccupancyContract::with(['property', 'tenant'])->where('owner_id', $user->id)->latest()->get();
        $notifications = \App\Models\Notification::where('user_id', $user->id)->latest()->get();
    @endphp

    <div class="space-y-10">

        <!-- Header Title & Balance Stats -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Espace Propriétaire
                </h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Matricule Propriétaire: <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $user->matricule ?? 'PROP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                </p>
            </div>
        </div>

        <!-- Section Demandes à Valider (pending_owner) -->
        @if($pendingOwnerRequests->count() > 0)
        <div class="bg-blue-50 dark:bg-[#001220] border border-blue-200 dark:border-blue-800 rounded-xl p-6">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Demandes d'occupation en attente de votre décision
                    </h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                        L'agent a déjà donné son accord. À vous d'accepter ou de refuser.
                    </p>
                </div>
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold">
                    {{ $pendingOwnerRequests->count() }}
                </span>
            </div>

            <div class="space-y-4">
                @foreach($pendingOwnerRequests as $req)
                    <div class="bg-white dark:bg-[#161615] border border-blue-100 dark:border-[#3E3E3A] rounded-xl p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                            <div>
                                <h4 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $req->property->title ?? 'Propriété' }}
                                </h4>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                                    Client intéressé : <span class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $req->client->full_name ?? 'Client' }}</span>
                                    — Loyer : <span class="text-[#f53003] font-semibold">{{ number_format($req->rent_amount, 0, ',', ' ') }} XOF</span>
                                </p>
                                @if($req->agent)
                                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-1 font-medium">
                                        ✓ Approuvé par l'agent : {{ $req->agent->full_name }}
                                        @if($req->agent_notes) (Note: "{{ $req->agent_notes }}") @endif
                                    </p>
                                @endif
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                                    Reçu {{ $req->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Voir l'aperçu du contrat avant décision --}}
                        <div class="mb-3">
                            <a href="{{ route('web.contracts.preview', $req->id) }}" target="_blank"
                               class="inline-flex items-center gap-2 text-xs font-semibold text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Visualiser le contrat avant de décider
                            </a>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col sm:flex-row gap-3">
                            <form method="POST" action="{{ route('web.occupancy.owner.approve', $req->id) }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all">
                                    J'accepte
                                </button>
                            </form>
                            <form method="POST" action="{{ route('web.occupancy.owner.reject', $req->id) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="reason" value="Refusé par le propriétaire">
                                <button type="submit"
                                    onclick="return confirm('Confirmer le refus de cette demande ?')"
                                    class="w-full py-2.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-all">
                                    Je refuse
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Section 1: Mes Propriétés -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Mes Propriétés
                    </h2>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">Cliquez sur une propriété pour en consulter la fiche complète, les photos et la vidéo.</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-gray-100 dark:bg-[#2a2a28] rounded-full text-[#1b1b18] dark:text-[#EDEDEC]">
                    {{ $ownerProperties->count() }} biens
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($ownerProperties as $prop)
                    @php
                        $photos = $prop->photos_with_urls;
                        $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                        $coverUrl = $mainPhoto['photo_url'] ?? null;
                        $propData = [
                            'id'           => $prop->id,
                            'title'        => $prop->title,
                            'city'         => $prop->city,
                            'neighborhood' => $prop->neighborhood,
                            'price'        => number_format($prop->price, 0, ',', ' ') . ' ' . $prop->currency,
                            'operation'    => ucfirst($prop->operation_type),
                            'address'      => $prop->address,
                            'bedrooms'     => $prop->bedrooms ?? 0,
                            'bathrooms'    => $prop->bathrooms ?? 0,
                            'surface_area' => $prop->surface_area ?? 0,
                            'description'  => $prop->description ?? 'Aucune description fournie',
                            'owner_name'   => $user->full_name,
                            'agent_name'   => $prop->agent->full_name ?? ($prop->agent_name ?? 'Aucun agent'),
                            'status'       => ucfirst($prop->status),
                            'is_occupied'  => $prop->is_occupied,
                            'photos'       => $photos,
                            'video_url'    => $prop->video_url,
                        ];
                    @endphp
                    <div class="border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden bg-white dark:bg-[#161615] hover:border-[#f53003] transition-all flex flex-col">
                        {{-- Image cliquable --}}
                        <div data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))"
                             class="w-full h-44 overflow-hidden bg-gray-100 dark:bg-[#2a2a28] relative flex-shrink-0 cursor-pointer group">
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
                            <span class="absolute top-3 right-3 px-2 py-0.5 text-[10px] font-bold rounded {{ $prop->is_occupied ? 'bg-amber-500 text-white' : 'bg-green-600 text-white' }}">
                                {{ $prop->is_occupied ? 'Occupée' : 'Disponible' }}
                            </span>
                        </div>

                        {{-- Info --}}
                        <div class="p-4 flex flex-col flex-1">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-extrabold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }} / mois</span>
                                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->bedrooms ?? 0 }} ch. &middot; {{ $prop->surface_area ?? 0 }} m²</span>
                            </div>
                            <h3 data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))"
                                class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC] mb-1 line-clamp-1 cursor-pointer hover:text-[#f53003] transition-colors">
                                {{ $prop->title }}
                            </h3>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-3 line-clamp-1">{{ $prop->city }} &mdash; {{ $prop->address }}</p>

                            <button type="button" data-property="{{ json_encode($propData) }}" onclick="openPropertyDetailModal(JSON.parse(this.getAttribute('data-property')))"
                                class="w-full py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-[#2a2a28] hover:bg-gray-200 dark:hover:bg-[#333330] rounded-lg transition-all mb-3">
                                Voir la fiche &amp; photos ({{ count($photos) }})
                            </button>

                            <div class="mt-auto pt-3 border-t border-gray-100 dark:border-[#3E3E3A] flex items-center justify-between text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                <span>Agent: <strong>{{ $prop->agent->full_name ?? ($prop->agent_name ?? 'Aucun') }}</strong></span>
                                <span class="font-semibold text-green-600">Statut: {{ ucfirst($prop->status) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Aucune propriété enregistrée sous votre nom pour le moment.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Section 2: Contrats de location -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                Contrats &amp; Locataires Actuels
            </h2>
            <div class="space-y-3">
                @forelse($contracts as $c)
                    <div class="p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $c->property->title ?? 'Propriété' }}</h4>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Locataire: {{ $c->tenant->full_name ?? 'Locataire' }} — Loyer: {{ number_format($c->monthly_rent, 0, ',', ' ') }} XOF</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('web.contracts.show', $c->id) }}" 
                               target="_blank"
                               class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                Voir le contrat
                            </a>
                            <span class="text-xs font-semibold text-green-600">Actif</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Aucun contrat actif pour vos propriétés.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- MODAL CONSULTATION PROPRIÉTÉ & GALERIE PHOTOS (PROPRIÉTAIRE) --}}
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
                    <span id="detail-status-badge" class="block text-xs font-semibold text-green-600 mt-0.5"></span>
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
                    <span class="text-[10px] font-semibold text-[#706f6c] dark:text-[#A1A09A] uppercase">Agent Référent</span>
                    <p id="detail-agent" class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC] truncate"></p>
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

    <script>
        let currentDetailPhotos = [];
        let currentDetailPhotoIndex = 0;

        function openPropertyDetailModal(propData) {
            document.getElementById('detail-title').textContent = propData.title;
            document.getElementById('detail-location').textContent = propData.city + (propData.neighborhood ? ' — ' + propData.neighborhood : '') + ' (' + propData.address + ')';
            document.getElementById('detail-price').textContent = propData.price;
            document.getElementById('detail-operation-badge').textContent = propData.operation;
            document.getElementById('detail-status-badge').textContent = 'Statut : ' + propData.status;
            document.getElementById('detail-bedrooms').textContent = (propData.bedrooms || 0) + ' chambre(s)';
            document.getElementById('detail-bathrooms').textContent = (propData.bathrooms || 0) + ' SDB';
            document.getElementById('detail-surface').textContent = (propData.surface_area || 0) + ' m²';
            document.getElementById('detail-agent').textContent = propData.agent_name || 'Aucun agent';
            document.getElementById('detail-description').textContent = propData.description || 'Aucune description fournie.';

            // Intégration vidéo si elle existe
            let videoContainer = document.getElementById('detail-video-container');
            if (!videoContainer) {
                videoContainer = document.createElement('div');
                videoContainer.id = 'detail-video-container';
                videoContainer.className = 'mt-4 pt-4 border-t border-gray-100 dark:border-[#2a2a28]';
                document.getElementById('detail-description').parentNode.appendChild(videoContainer);
            }
            if (propData.video_url) {
                videoContainer.innerHTML = '<span class="text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] block mb-2">Vidéo de la propriété</span>' +
                                           '<video controls class="w-full rounded-lg max-h-64 object-cover shadow-sm">' +
                                           '<source src="/storage/' + propData.video_url + '" type="video/mp4">' +
                                           'Votre navigateur ne supporte pas la lecture de vidéo.</video>';
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
    </script>
@endsection
