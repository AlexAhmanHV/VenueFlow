<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
        @php
            $restaurant = request()->attributes->get('restaurant');
            $slugSeed = $restaurant?->slug ?? 'venueflow';
            $rotationSeed = crc32($slugSeed) + (int) now()->dayOfYear;
            $backgroundDirs = [
                'images/restaurant-backgrounds',
                'images/restaurantbackgrounds',
                'public-images-restaurantbackgrounds',
            ];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $bgCandidates = collect($backgroundDirs)
                ->flatMap(function (string $dir) use ($allowedExtensions) {
                    $path = public_path($dir);
                    if (! is_dir($path)) {
                        return [];
                    }

                    return collect(\Illuminate\Support\Facades\File::files($path))
                        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $allowedExtensions, true))
                        ->map(fn ($file) => $dir.'/'.$file->getFilename())
                        ->all();
                })
                ->sort()
                ->values()
                ->all();

            $bgImagePath = count($bgCandidates)
                ? $bgCandidates[$rotationSeed % count($bgCandidates)]
                : 'images/restaurant-backgrounds/bg-01.jpg';
        @endphp

        <div class="relative min-h-screen overflow-x-hidden bg-gradient-to-b from-slate-100 via-slate-50 to-white dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 dark:text-white">
            @if($restaurant)
                <div
                    class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-15"
                    style="background-image: url('{{ asset($bgImagePath) }}');"
                ></div>
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white/75 via-slate-50/80 to-white/90 dark:from-slate-900/80 dark:via-slate-900/85 dark:to-slate-950/95"></div>
            @endif

            <div class="pointer-events-none absolute -left-20 top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-20 top-1/3 h-80 w-80 rounded-full bg-cyan-300/20 blur-3xl"></div>

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

            <footer class="relative z-10 py-8">
                <div class="mx-auto flex w-full max-w-4xl items-center justify-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                    <span>Skapad av</span>
                    <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-800 hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400">AlexAhman.se</a>
                </div>
            </footer>
        </div>
    </body>
</html>
