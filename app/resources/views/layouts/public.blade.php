<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $restaurant->name ?? config('app.name', 'VenueFlow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
        @php
            $bgImages = ['bg-01.svg', 'bg-02.svg', 'bg-03.svg', 'bg-04.svg', 'bg-05.svg', 'bg-06.svg'];
            $slugSeed = $restaurant?->slug ?? 'venueflow';
            $rotationSeed = crc32($slugSeed) + (int) now()->dayOfYear;
            $bgImage = $bgImages[$rotationSeed % count($bgImages)];
        @endphp

        <div class="relative min-h-screen overflow-x-hidden bg-gray-50 dark:bg-gray-900">
            <div
                class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-20"
                style="background-image: url('{{ asset('images/restaurant-backgrounds/'.$bgImage) }}');"
            ></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white/70 via-white/75 to-slate-50/90 dark:from-slate-900/80 dark:via-slate-900/85 dark:to-slate-950/95"></div>

            <!-- Page Content -->
            <main class="relative z-10">
                {{ $slot }}
            </main>
            <footer class="relative z-10 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Powered by VenueFlow
            </footer>
        </div>
    </body>
</html>
