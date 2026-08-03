@extends('layouts.dashboard')

@section('title', 'Comment ça marche ?')

@section('content')
<div class="max-w-4xl mx-auto space-y-10 py-4">

    <!-- En-tête -->
    <div class="border-b border-gray-200 dark:border-[#3E3E3A] pb-6">
        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] tracking-tight">
            Comment ça se passe pour occuper une maison ou une chambre ?
        </h1>
        <p class="text-base text-[#706f6c] dark:text-[#A1A09A] mt-2 leading-relaxed">
            Processus simple, transparent et sécurisé pour connecter les clients, les agents immobiliers et les propriétaires sur la plateforme ORIZON.
        </p>
    </div>

    <!-- Le Problème de départ -->
    <div class="bg-[#fff2f2] dark:bg-[#1D0002] border border-red-200 dark:border-red-900/50 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-[#f53003] dark:text-[#FF4433] mb-2">
            Le principe général
        </h2>
        <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] leading-relaxed">
            Un propriétaire souhaite mettre son bien en location, tandis qu'un client cherche son futur logement. ORIZON offre un cadre fiable et structuré où chaque partie intervenante est protégée à chaque étape du processus.
        </p>
    </div>

    <!-- Les 6 Étapes -->
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
            Le déroulement en 6 étapes simples
        </h2>

        <div class="grid grid-cols-1 gap-4">

            <!-- Étape 1 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    1
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 1 : Le client trouve le bien qui lui plaît
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        Le client parcourt les propriétés disponibles sur l'application. Lorsqu'il trouve le bien idéal, il clique sur <strong>"Je suis intéressé(e)"</strong>. L'ordinateur enregistre immédiatement la demande d'occupation.
                    </p>
                </div>
            </div>

            <!-- Étape 2 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    2
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 2 : L'agent immobilier effectue la première vérification
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        L'agent responsable de la zone reçoit la notification de la demande. Il analyse les éléments et confirme la disponibilité avant de cliquer sur <strong>"J'approuve"</strong> pour valider le dossier.
                    </p>
                </div>
            </div>

            <!-- Étape 3 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    3
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 3 : Le propriétaire donne son accord final
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        Le propriétaire reçoit la demande validée par l'agent. Il étudie le profil et clique sur <strong>"J'accepte"</strong> pour donner son feu vert définitif.
                    </p>
                </div>
            </div>

            <!-- Étape 4 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    4
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 4 : Établissement et signature du contrat
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        Une fois toutes les validations obtenues, le contrat d'occupation est généré automatiquement avec le montant du loyer, la date d'entrée et les conditions. Le contrat est rattaché aux comptes du client et du propriétaire.
                    </p>
                </div>
            </div>

            <!-- Étape 5 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    5
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 5 : Paiement sécurisé du loyer
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        Le client procède au paiement du loyer directement via son mobile money (T-Money ou Flooz) ou par virement. Le système traite le règlement en toute sécurité.
                    </p>
                </div>
            </div>

            <!-- Étape 6 -->
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6 flex items-start gap-4">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-[#f53003] text-white font-bold text-sm">
                    6
                </span>
                <div>
                    <h3 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                        Étape 6 : Répartition et versement au propriétaire
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1 leading-relaxed">
                        Le loyer est crédité sur le solde du propriétaire (déduction faite de la commission de gestion de l'agent). Le propriétaire peut effectuer des demandes de retrait vers son compte Mobile Money ou bancaire à tout moment.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Gestion des exceptions -->
    <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl p-6 space-y-4">
        <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
            Annulations et refus
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="p-4 border border-gray-100 dark:border-[#3E3E3A] rounded-xl">
                <h4 class="font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Côté Client</h4>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Le client a la possibilité d'annuler sa demande tant qu'elle est en cours de traitement.
                </p>
            </div>
            <div class="p-4 border border-gray-100 dark:border-[#3E3E3A] rounded-xl">
                <h4 class="font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Côté Agent</h4>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    L'agent peut rejeter une demande non conforme ou si le bien est déjà réservé.
                </p>
            </div>
            <div class="p-4 border border-gray-100 dark:border-[#3E3E3A] rounded-xl">
                <h4 class="font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Côté Propriétaire</h4>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    Le propriétaire conserve le dernier mot sur l’acceptation ou le refus de chaque dossier.
                </p>
            </div>
        </div>
    </div>

    <!-- Tableau des Rôles -->
    <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-2xl p-6 space-y-4">
        <h2 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
            Résumé des rôles sur la plateforme
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-[#3E3E3A] text-xs font-bold uppercase text-[#706f6c] dark:text-[#A1A09A]">
                        <th class="pb-3 pr-4">Acteur</th>
                        <th class="pb-3 pr-4">Rôle</th>
                        <th class="pb-3">Action Principale</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#3E3E3A] text-sm">
                    <tr>
                        <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Le Client</td>
                        <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">Demandeur de logement</td>
                        <td class="py-3 text-[#1b1b18] dark:text-[#EDEDEC]">Consulte les offres, demande à occuper, règle le loyer</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">L'Agent Immobilier</td>
                        <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">Intermédiaire et gestionnaire</td>
                        <td class="py-3 text-[#1b1b18] dark:text-[#EDEDEC]">Répertorie les biens, effectue les visites, filtre les demandes</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Le Propriétaire</td>
                        <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">Détenteur du bien</td>
                        <td class="py-3 text-[#1b1b18] dark:text-[#EDEDEC]">Valide le contrat final, perçoit les revenus de location</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-bold text-[#1b1b18] dark:text-[#EDEDEC]">L'Administrateur</td>
                        <td class="py-3 pr-4 text-[#706f6c] dark:text-[#A1A09A]">Superviseur plateforme</td>
                        <td class="py-3 text-[#1b1b18] dark:text-[#EDEDEC]">Contrôle le système, valide les comptes et supervise les transactions</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
