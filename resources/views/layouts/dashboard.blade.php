<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Orizona</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            @layer theme{:root,:host{--font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif}}
            @layer base{*,:after,:before{box-sizing:border-box;margin:0;padding:0}html{font-family:var(--font-sans)}}
            body { background-color: #FDFDFC; color: #1b1b18; font-family: 'Instrument Sans', sans-serif; min-height: 100vh; }
        </style>
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        orizona: '#f53003',
                        orizonaDark: '#161615',
                        orizonaLight: '#fff2f2',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex flex-col justify-between">
    {{-- Header Header / Top Navbar --}}
    <header class="w-full bg-white dark:bg-[#161615] border-b border-gray-200 dark:border-[#3E3E3A] px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="text-2xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                    Orizona
                </a>
                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-[#fff2f2] dark:bg-[#1D0002] text-[#f53003] dark:text-[#FF4433] border border-red-200 dark:border-red-900">
                    {{ ucfirst(Auth::user()->user_type) }}
                </span>
            </div>

            {{-- Centre : lien guide --}}
            <a href="{{ route('how-it-works') }}"
               class="hidden md:inline-flex items-center gap-1.5 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] hover:text-[#f53003] dark:hover:text-[#FF4433] transition-colors {{ request()->routeIs('how-it-works') ? 'text-[#f53003] dark:text-[#FF4433]' : '' }}">
                Comment ça marche ?
            </a>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ Auth::user()->full_name }}</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ Auth::user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-[#f53003] hover:bg-orange-600 text-white font-medium text-xs rounded-lg transition-all duration-200 hover:shadow-md">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-7xl w-full mx-auto p-6 lg:p-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-[#1D0002] border border-green-300 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-[#1D0002] border border-red-300 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @if(Auth::check() && Auth::user()->user_type === 'admin')
            {{-- Sous-menu Admin --}}
            <nav class="mb-8 flex flex-wrap items-center gap-2 pb-4 border-b border-gray-200 dark:border-[#3E3E3A]">
                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'label' => 'Vue d\'ensemble', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['route' => 'admin.web.properties', 'label' => 'Propriétés', 'icon' => 'M3 7l9-4 9 4v2H3V7z M3 10h18v9a1 1 0 01-1 1H4a1 1 0 01-1-1v-9z'],
                        ['route' => 'admin.web.users', 'label' => 'Utilisateurs', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['route' => 'admin.web.support', 'label' => 'Aide & Suggestions', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                        ['route' => 'admin.web.notifications', 'label' => 'Notifications', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                    ];
                @endphp
                @foreach($adminLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg transition-all
                              {{ request()->routeIs($link['route']) || (in_array($link['route'], ['admin.web.properties', 'admin.web.property-detail']) && request()->is('admin/properties*')) || (in_array($link['route'], ['admin.web.users', 'admin.web.user-detail']) && request()->is('admin/users*'))
                                  ? 'bg-[#f53003] text-white shadow-md'
                                  : 'bg-white dark:bg-[#161615] border border-gray-200 dark:border-[#3E3E3A] text-[#706f6c] dark:text-[#A1A09A] hover:text-[#f53003]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                        </svg>
                        {{ $link['label'] }}
                        @if($link['route'] === 'admin.web.notifications' && \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count() > 0)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-600 text-white">
                                {{ \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count() }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="w-full bg-white dark:bg-[#161615] border-t border-gray-200 dark:border-[#3E3E3A] px-6 py-4 text-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
        Orizona © {{ date('Y') }} — Gestion Immobilière Intelligente
    </footer>
</body>
</html>
