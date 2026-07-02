<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VenueFlow') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|syne:600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f4f6f2] dark:bg-slate-900 dark:text-white">

    @php
        $restaurant = request()->attributes->get('restaurant');
        $slug = $restaurant?->slug
            ?? request()->route('slug')
            ?? request()->route('restaurant_slug');
    @endphp

    {{-- Top navigation --}}
    <nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-white/60 bg-white/70 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/70">
        <div class="vf-container">
            <div class="flex h-16 items-center justify-between">

                {{-- Left: logo + restaurant name --}}
                <div class="flex items-center gap-5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 transition hover:opacity-80">
                        <x-application-logo class="block h-8 w-auto fill-current text-emerald-600" />
                        <span class="hidden text-base font-bold tracking-tight text-slate-900 dark:text-white sm:inline">VenueFlow</span>
                    </a>

                    @if($restaurant)
                        <div class="hidden h-5 w-px bg-slate-200 dark:bg-slate-700 sm:block"></div>
                        <span class="hidden font-display text-sm font-semibold text-slate-700 dark:text-slate-300 sm:inline">{{ $restaurant->name }}</span>
                    @endif
                </div>

                {{-- Right: theme toggle + user dropdown --}}
                <div class="flex items-center gap-3">
                    <button @click="Alpine.store('theme').toggle()" class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
                        <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <svg x-show="$store.theme.dark" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </button>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Logga ut</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                    <button @click="open = !open" class="rounded-md p-2 text-slate-400 transition hover:bg-slate-100 sm:hidden">
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div :class="{'block': open, 'hidden': !open}" class="hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 sm:hidden">
            @if($restaurant && $slug)
            <div class="space-y-0.5 px-4 py-3 text-sm">
                @php
                    $mobileItems = [
                        ['label' => 'Dashboard', 'route' => 'restaurant.admin.dashboard'],
                        ['label' => 'Live board', 'route' => 'restaurant.admin.bookings.live'],
                        ['label' => 'Analys', 'route' => 'restaurant.admin.analytics'],
                        ['label' => 'Resurser', 'route' => 'restaurant.admin.resources.index'],
                        ['label' => 'Inställningar', 'route' => 'restaurant.admin.settings.edit'],
                    ];
                @endphp
                @foreach($mobileItems as $item)
                    <a href="{{ route($item['route'], $slug) }}" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
            @endif
            <div class="border-t border-slate-200 px-7 py-3 dark:border-slate-800">
                <div class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            </div>
        </div>
    </nav>

    {{-- Restaurant sub-nav --}}
    @if($restaurant && $slug)
    <div class="border-b border-black/[0.07] bg-white/50 backdrop-blur-sm dark:border-white/10 dark:bg-slate-900/50">
        <div class="vf-container">
            <div class="flex items-center overflow-x-auto" style="scrollbar-width:none">
                @php
                    $subNavItems = [
                        ['label' => 'Dashboard', 'route' => 'restaurant.admin.dashboard', 'match' => 'restaurant.admin.dashboard'],
                        ['label' => 'Driftvy', 'route' => 'restaurant.admin.operations', 'match' => 'restaurant.admin.operations'],
                        ['label' => 'Live board', 'route' => 'restaurant.admin.bookings.live', 'match' => 'restaurant.admin.bookings.live'],
                        ['label' => 'Analys', 'route' => 'restaurant.admin.analytics', 'match' => 'restaurant.admin.analytics*'],
                        ['label' => 'Meny', 'route' => 'restaurant.admin.menu.index', 'match' => 'restaurant.admin.menu.*'],
                        ['label' => 'Schema', 'route' => 'restaurant.admin.schedule.index', 'match' => 'restaurant.admin.schedule.*'],
                        ['label' => 'Resurser', 'route' => 'restaurant.admin.resources.index', 'match' => 'restaurant.admin.resources.*'],
                        ['label' => 'Personal', 'route' => 'restaurant.admin.staff.index', 'match' => 'restaurant.admin.staff.*'],
                        ['label' => 'Inställningar', 'route' => 'restaurant.admin.settings.edit', 'match' => 'restaurant.admin.settings.*'],
                    ];
                @endphp
                @foreach($subNavItems as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a
                        href="{{ route($item['route'], $slug) }}"
                        class="relative shrink-0 px-4 py-3 text-sm font-medium transition-colors duration-150 {{ $active ? 'text-emerald-800 dark:text-emerald-400' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}"
                    >
                        {{ $item['label'] }}
                        @if($active)
                            <span class="absolute bottom-0 left-3 right-3 h-0.5 rounded-full bg-emerald-700"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(config('demo.public_mode') && !session((string) config('demo.session_flag', 'demo.full_access_granted')))
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900 dark:border-amber-800/40 dark:bg-amber-950/30 dark:text-amber-200">
            Demo read-only: ändringar är blockerade.
            <a href="{{ route('demo.access.show') }}" class="font-semibold underline">Lås upp full access</a>
        </div>
    @endif

    <main class="vf-container py-8">
        {{ $slot }}
    </main>

    <footer class="py-8">
        <div class="mx-auto flex w-full max-w-4xl items-center justify-center gap-2 text-xs text-slate-400">
            <span>Skapad av</span>
            <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-500 transition hover:text-emerald-700">AlexAhman.se</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
