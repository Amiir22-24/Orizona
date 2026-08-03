@extends('layouts.dashboard')

@section('title', 'Administration - Aide & Suggestions')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                Messages d'aide &amp; Suggestions
            </h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Demandes d'assistance et suggestions envoyées par les utilisateurs
            </p>
        </div>

        @forelse($conversations as $conv)
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl overflow-hidden">
                <!-- En-tête conversation -->
                <div class="p-5 border-b border-gray-200 dark:border-[#3E3E3A] bg-gray-50 dark:bg-[#1a1a18]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-base text-[#1b1b18] dark:text-[#EDEDEC]">
                                {{ $conv->subject ?? 'Demande d\'aide' }}
                            </h2>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">
                                Envoyé par <span class="font-semibold">{{ $conv->client->full_name ?? 'Client' }}</span>
                                @if($conv->client)
                                    ({{ $conv->client->email }})
                                @endif
                                — {{ $conv->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                {{ $conv->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $conv->status === 'active' ? 'Ouverte' : 'Fermée' }}
                            </span>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">
                                {{ $conv->messages()->count() }} message(s)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="p-5 space-y-3">
                    @forelse($conv->messages as $msg)
                        <div class="flex gap-3 {{ $msg->sender_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <div class="flex-1 max-w-[75%]">
                                <div class="px-4 py-3 rounded-xl text-sm
                                    {{ $msg->sender_id === auth()->id()
                                        ? 'bg-[#f53003] text-white ml-auto'
                                        : 'bg-gray-100 dark:bg-[#1a1a18] text-[#1b1b18] dark:text-[#EDEDEC]' }}">
                                    <p class="text-xs font-bold mb-1 {{ $msg->sender_id === auth()->id() ? 'text-white/80' : 'text-[#706f6c] dark:text-[#A1A09A]' }}">
                                        {{ $msg->sender->full_name ?? 'Utilisateur' }} — {{ $msg->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    {{ $msg->message }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun message dans cette conversation.</p>
                    @endforelse
                </div>

                <!-- Réponse admin -->
                <div class="p-5 border-t border-gray-200 dark:border-[#3E3E3A]">
                    <form method="POST" action="{{ route('admin.web.support-reply', $conv->id) }}" class="flex gap-3">
                        @csrf
                        <input type="text" name="message" placeholder="Écrire une réponse à {{ $conv->client->first_name ?? 'l\'utilisateur' }}..."
                               required class="flex-1 px-4 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none focus:ring-2 focus:ring-[#f53003]">
                        <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                            Répondre
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-10 text-center">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun message d'aide ou de suggestion pour le moment.</p>
            </div>
        @endforelse

    </div>
@endsection

