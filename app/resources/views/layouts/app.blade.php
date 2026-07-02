<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VenueFlow') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|syne:600,700,800&display=swap" rel="stylesheet" />

        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen overflow-x-hidden bg-[#f4f6f2] dark:bg-slate-900 dark:text-white">

            @include('layouts.navigation')

            @isset($header)
                <header class="relative z-10">
                    <div class="vf-container py-6">
                        <div class="vf-card p-5 sm:p-6">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <main class="relative z-10">
                {{ $slot }}
            </main>

            @stack('scripts')

            <footer class="relative z-10 py-8">
                <div class="mx-auto flex w-full max-w-4xl items-center justify-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                    <span>Skapad av</span>
                    <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-500 transition hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-400">AlexAhman.se</a>
                </div>
            </footer>
        </div>
    </body>
</html>
