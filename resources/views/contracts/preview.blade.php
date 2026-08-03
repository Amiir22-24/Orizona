<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu du Contrat N° ORIZONA-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 12pt; }
            .print-container { border: none !important; shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-full py-8">

    {{-- barre d'outils haut (masquée à l'impression) --}}
    <div class="no-print max-w-4xl mx-auto mb-6 px-4 flex items-center justify-between">
        <a href="{{ url()->previous() ?: route('owner.dashboard') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all shadow-sm">
            &larr; Retour au tableau de bord
        </a>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('web.occupancy.owner.approve', $contract->occupancy_request_id) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all shadow-md">
                    ✓ J'accepte &amp; signer le contrat
                </button>
            </form>
            <form method="POST" action="{{ route('web.occupancy.owner.reject', $contract->occupancy_request_id) }}" class="inline">
                @csrf
                <input type="hidden" name="reason" value="Refusé par le propriétaire">
                <button type="submit"
                    onclick="return confirm('Confirmer le refus de cette demande ?')"
                    class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-lg transition-all shadow-sm">
                    ✕ Je refuse
                </button>
            </form>
        </div>
    </div>

    <div class="no-print max-w-4xl mx-auto mb-4 px-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-xs text-yellow-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span><strong>Aperçu du contrat :</strong> Ceci est une version préliminaire. Vous pouvez visualiser le contrat avant de l'approuver ou de le refuser.</span>
        </div>
    </div>

    {{-- Document Contrat (aperçu) --}}
    <main class="print-container max-w-4xl mx-auto bg-white border border-gray-200 shadow-xl rounded-2xl p-8 sm:p-12 space-y-8">
        
        {{-- En-tête officiel --}}
        <div class="border-b border-gray-200 pb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-extrabold text-[#f53003] tracking-widest uppercase">Plateforme Immobilière ORIZONA</span>
                <h1 class="text-2xl font-black text-gray-900 mt-1 uppercase tracking-tight">Contrat de Bail d'Habitation</h1>
                <p class="text-xs text-gray-500 mt-1">Document Numérique Officiel &middot; Conformité Réglementaire</p>
            </div>
            <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-[11px] font-extrabold rounded-full border border-yellow-200">
                    APERÇU - NON SIGNÉ
                </span>
                <p class="text-xs font-mono text-gray-600 mt-2">N° ORIZONA-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[11px] text-gray-500">Date d'émission : {{ $contract->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Section 1 : Identification des Parties --}}
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold text-[#f53003] uppercase tracking-wider border-b border-red-100 pb-1">1. Identification des Parties</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                {{-- Bailleur (Propriétaire) --}}
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-1">
                    <span class="font-bold text-gray-400 uppercase text-[10px]">Le Bailleur (Propriétaire)</span>
                    <p class="font-bold text-sm text-gray-900">{{ $contract->owner->full_name ?? 'N/A' }}</p>
                    <p class="text-gray-600">Email : {{ $contract->owner->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">Téléphone : {{ $contract->owner->phone ?? 'N/A' }}</p>
                </div>

                {{-- Preneur (Locataire) --}}
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-1">
                    <span class="font-bold text-gray-400 uppercase text-[10px]">Le Preneur (Locataire)</span>
                    <p class="font-bold text-sm text-gray-900">{{ $contract->tenant->full_name ?? 'N/A' }}</p>
                    <p class="text-gray-600">Email : {{ $contract->tenant->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">Téléphone : {{ $contract->tenant->phone ?? 'N/A' }}</p>
                </div>
            </div>

            @if($contract->agent)
                <div class="p-3 bg-red-50/50 rounded-lg border border-red-100 text-xs flex items-center justify-between">
                    <span class="text-gray-600">Agent Immobilier Référent : <strong>{{ $contract->agent->full_name }}</strong></span>
                    <span class="text-gray-500 text-[11px]">{{ $contract->agent->email }}</span>
                </div>
            @endif
        </div>

        {{-- Section 2 : Désignation du bien --}}
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold text-[#f53003] uppercase tracking-wider border-b border-red-100 pb-1">2. Désignation du Logement</h2>
            
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-gray-900">{{ $contract->property->title ?? 'Propriété' }}</h3>
                    <span class="px-2 py-0.5 rounded bg-gray-200 font-bold uppercase text-[10px] text-gray-700">
                        {{ ucfirst($contract->property->type ?? 'Bien') }} &middot; {{ ucfirst($contract->property->operation_type ?? 'Location') }}
                    </span>
                </div>
                <p class="text-gray-600">
                    <strong>Localisation :</strong> {{ $contract->property->city ?? 'N/A' }}, {{ $contract->property->neighborhood ?? '' }} &mdash; {{ $contract->property->address ?? 'N/A' }}
                </p>
                @if($contract->property->description)
                    <p class="text-gray-500 text-[11px] italic">"{{ $contract->property->description }}"</p>
                @endif
            </div>
        </div>

        {{-- Section 3 : Conditions financières et Durée --}}
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold text-[#f53003] uppercase tracking-wider border-b border-red-100 pb-1">3. Conditions Financières & Durée du Bail</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <span class="text-gray-500 uppercase text-[10px] font-bold block">Loyer Mensuel</span>
                    <span class="text-lg font-black text-[#f53003] mt-1 block">{{ number_format($contract->monthly_rent, 0, ',', ' ') }} XOF</span>
                    <span class="text-[10px] text-gray-400">Payable d'avance</span>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <span class="text-gray-500 uppercase text-[10px] font-bold block">Dépôt de Garantie</span>
                    <span class="text-lg font-black text-gray-800 mt-1 block">{{ number_format($contract->deposit_amount, 0, ',', ' ') }} XOF</span>
                    <span class="text-[10px] text-gray-400">Restituable en fin de bail</span>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <span class="text-gray-500 uppercase text-[10px] font-bold block">Prise d'effet</span>
                    <span class="text-sm font-bold text-gray-800 mt-1 block">{{ $contract->start_date?->format('d/m/Y') ?? 'À la signature' }}</span>
                    <span class="text-[10px] text-gray-400">Échéance : {{ $contract->end_date?->format('d/m/Y') ?? 'Indéterminée' }}</span>
                </div>
            </div>
        </div>

        {{-- Section 4 : Clauses contractuelles --}}
        <div class="space-y-3 text-xs text-gray-700 leading-relaxed">
            <h2 class="text-xs font-extrabold text-[#f53003] uppercase tracking-wider border-b border-red-100 pb-1">4. Conditions Générales & Engagements</h2>
            
            <ol class="list-decimal list-inside space-y-2">
                <li><strong>Destination des lieux :</strong> Le logement est destiné exclusivement à l'habitation principale du Preneur. Toute sous-location ou cession est interdite sans l'accord écrit du Bailleur.</li>
                <li><strong>Paiement du loyer :</strong> Le Preneur s'engage à régler le loyer mensuel convenu au plus tard le 5 de chaque mois.</li>
                <li><strong>Entretien et jouissance paisible :</strong> Le Preneur maintiendra les lieux en bon état d'entretien et veillera au respect du voisinage.</li>
                <li><strong>Résiliation :</strong> Toute résiliation anticipée doit être notifiée par préavis écrit d'au moins un mois conformément aux règles en vigueur.</li>
            </ol>
        </div>

        {{-- Signatures & Validation Numérique --}}
        <div class="pt-6 border-t border-gray-200 space-y-6">
            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <div class="space-y-8">
                    <span class="font-bold text-gray-500 uppercase text-[11px]">Signature du Bailleur</span>
                    <div class="h-16 flex items-center justify-center border-b border-dashed border-gray-300">
                        <span class="font-mono text-gray-400 text-xs italic">[En attente de votre signature]</span>
                    </div>
                    <p class="text-[11px] font-bold text-gray-800">{{ $contract->owner->full_name ?? 'Propriétaire' }}</p>
                </div>
                <div class="space-y-8">
                    <span class="font-bold text-gray-500 uppercase text-[11px]">Signature du Preneur</span>
                    <div class="h-16 flex items-center justify-center border-b border-dashed border-gray-300">
                        <span class="font-mono text-gray-400 text-xs italic">[Signé Numériquement via ORIZONA]</span>
                    </div>
                    <p class="text-[11px] font-bold text-gray-800">{{ $contract->tenant->full_name ?? 'Locataire' }}</p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-[11px] text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <strong>Certification ORIZONA :</strong> Contrat généré le 
                    {{ $contract->created_at->format('d/m/Y à H:i') }}.
                </div>
                <div class="font-mono text-[10px] text-gray-400">
                    APERÇU - Non contractuel
                </div>
            </div>
        </div>

    </main>
</body>
</html>

