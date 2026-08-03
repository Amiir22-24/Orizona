@extends('layouts.dashboard')

@section('title', 'Administration - Propriétés')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Gestion des Propriétés
                </h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ $counts['all'] }} propriétés répertoriées sur ORIZONA
                </p>
            </div>

            <!-- Filtres -->
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        'all' => ['label' => 'Toutes', 'color' => 'bg-gray-100 dark:bg-[#3E3E3A] text-[#1b1b18] dark:text-[#EDEDEC]'],
                        'available' => ['label' => 'Disponibles', 'color' => 'bg-green-100 text-green-700'],
                        'occupied' => ['label' => 'Occupées', 'color' => 'bg-[#f53003]/10 text-[#f53003]'],
                        'pending' => ['label' => 'En attente', 'color' => 'bg-amber-100 text-amber-700'],
                        'validated' => ['label' => 'Validées', 'color' => 'bg-blue-100 text-blue-700'],
                        'rejected' => ['label' => 'Rejetées', 'color' => 'bg-red-100 text-red-700'],
                    ];
                @endphp
                @foreach($filters as $key => $f)
                    <a href="{{ route('admin.web.properties', ['filter' => $key, 'search' => $search]) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all
                              {{ $filter === $key ? $f['color'] . ' ring-2 ring-offset-1 ring-[#f53003]' : 'bg-gray-100 dark:bg-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        {{ $f['label'] }} ({{ $counts[$key] }})
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Recherche -->
        <form method="GET" action="{{ route('admin.web.properties') }}" class="flex gap-3">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par titre, ville, adresse..."
                   class="flex-1 px-4 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-[#f53003]">
            <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                Rechercher
            </button>
        </form>

        <!-- Tableau des propriétés -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#1a1a18]">
                            <th class="px-4 py-3">Photo</th>
                            <th class="px-4 py-3">Propriété</th>
                            <th class="px-4 py-3">Ville</th>
                            <th class="px-4 py-3">Prix</th>
                            <th class="px-4 py-3">Propriétaire</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Occupation</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                        @forelse($properties as $prop)
                            @php
                                $photos = $prop->photos_with_urls;
                                $mainPhoto = collect($photos)->firstWhere('is_main', true) ?? collect($photos)->first();
                                $coverUrl = $mainPhoto['photo_url'] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#1a1a18] transition-all">
                                <td class="px-4 py-3">
                                    @if($coverUrl)
                                        <img src="{{ $coverUrl }}" alt="{{ $prop->title }}" class="w-14 h-10 object-cover rounded-lg">
                                    @else
                                        <div class="w-14 h-10 rounded-lg bg-gray-100 dark:bg-[#2a2a28] flex items-center justify-center">
                                            <span class="text-[9px] text-[#706f6c]">N/A</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $prop->title }}</td>
                                <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->city }}</td>
                                <td class="px-4 py-3 font-extrabold text-[#f53003]">{{ number_format($prop->price, 0, ',', ' ') }} {{ $prop->currency }}</td>
                                <td class="px-4 py-3 text-[#706f6c] dark:text-[#A1A09A]">{{ $prop->owner_name ?? ($prop->owner->full_name ?? 'N/A') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                        {{ $prop->status === 'validated' ? 'bg-green-100 text-green-700' :
                                           ($prop->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($prop->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $prop->is_occupied ? 'bg-[#f53003]/10 text-[#f53003]' : 'bg-green-100 text-green-700' }}">
                                        {{ $prop->is_occupied ? 'Occupée' : 'Disponible' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('admin.web.property-detail', $prop->id) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Détails
                                        </a>
                                        
                                        <form action="{{ route('admin.web.properties.destroy', $prop->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette propriété ? Cette action est irréversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    Aucune propriété trouvée pour ce filtre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

