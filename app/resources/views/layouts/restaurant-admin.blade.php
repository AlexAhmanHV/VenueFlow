<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Theme Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'block' : 'hidden'" class="fixed inset-y-0 left-0 z-30 w-64 transform bg-gray-900 shadow-lg transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 lg:block lg:w-80">
            <div class="flex h-full flex-col justify-between">
                <div class="py-6 px-4">
                    <div class="mb-8 flex items-center">
                        <a href="/" class="text-2xl font-bold text-white">VenueFlow</a>
                    </div>
                    
                    @php
                        $slug = request()->route('slug')
                            ?? request()->route('restaurant_slug')
                            ?? optional(request()->attributes->get('restaurant'))->slug;
                    @endphp

                    <nav class="space-y-2 text-gray-300">
                        <a href="{{ route('restaurant.admin.dashboard', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10v11h6V10H3zm18 11h-6V10h6v11zM9 4v17h6V4H9z"></path></svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('restaurant.admin.operations', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Driftvy</span>
                        </a>
                        <a href="{{ route('restaurant.admin.bookings.live', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Live board</span>
                        </a>
                        
                        <div class="pt-4">
                            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Manage</p>
                            <div class="mt-2 space-y-2">
                                <a href="{{ route('restaurant.admin.resources.index', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14v-2h-2v2h2zm0-4v-2h-2v2h2zm-4 4v-2h-2v2h2zm0-4v-2h-2v2h2zm-4 4v-2H9v2h2zm-4 0v-2H5v2h2zm-4-4v-2H1v2h2zm18-4v-2h-2v2h2z"></path></svg>
                                    <span>Resurser</span>
                                </a>
                                <a href="{{ route('restaurant.admin.schedule.index', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>Schema</span>
                                </a>
                                <a href="{{ route('restaurant.admin.menu.index', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                    <span>Meny</span>
                                </a>
                                <a href="{{ route('restaurant.admin.staff.index', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2a3 3 0 00-3 3v2h3zM4 15a4 4 0 014-4h2a4 4 0 014 4v5H4v-5zM9 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <span>Personal</span>
                                </a>
                                <a href="{{ route('restaurant.admin.settings.edit', $slug) }}" class="flex items-center space-x-3 rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-gray-700 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.096 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Inställningar</span>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="flex items-center justify-between border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    @isset($header)
                       <div class="ml-4">{{ $header }}</div>
                    @endisset
                </div>
            </header>
            @if (config('demo.public_mode') && ! session((string) config('demo.session_flag', 'demo.full_access_granted')))
                <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Read-only demo mode: changes are blocked.
                    <a href="{{ route('demo.access.show') }}" class="font-semibold underline">Unlock full access</a>
                    to enable write actions.
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
            <footer class="border-t border-gray-200 bg-white px-4 py-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto flex w-full max-w-4xl items-center justify-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                    <img src="{{ asset('favicon.ico') }}" alt="VenueFlow logo" class="h-5 w-5 rounded">
                    <span>Skapad av</span>
                    <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-800 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">AlexAhman.se</a>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
