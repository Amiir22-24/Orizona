@extends('layouts.dashboard')

@section('title', 'Administration - Notifications')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Notifications de la Plateforme
                </h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Toutes les alertes sur l'activité ORIZONA
                </p>
            </div>
            <form method="POST" action="{{ route('admin.web.notifications-read-all') }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#f53003] hover:bg-orange-600 rounded-lg transition-all">
                    Tout marquer comme lu
                </button>
            </form>
        </div>

        <!-- Liste des notifications -->
        <div class="bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] rounded-xl p-6">
            <div class="space-y-3">
                @forelse($notifications as $notif)
                    <div class="flex items-start gap-4 p-4 border border-gray-200 dark:border-[#3E3E3A] rounded-lg
                        {{ $notif->is_read ? 'opacity-70' : 'border-[#f53003]/40 bg-[#fff2f2] dark:bg-[#1D0002]' }}">
                        <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center
                            {{ $notif->type === 'admin_alert' ? 'bg-[#f53003]/10 text-[#f53003]' : 'bg-blue-100 text-blue-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $notif->title }}</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-0.5">{{ $notif->message }}</p>
                                </div>
                                <span class="text-[10px] text-[#706f6c] dark:text-[#A1A09A] whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            @if($notif->type !== 'admin_alert')
                                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 dark:bg-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $notif->type }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune notification pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection

