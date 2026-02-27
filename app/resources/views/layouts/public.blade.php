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

        <div class="relative min-h-screen overflow-x-hidden bg-gray-50 dark:bg-gray-900">
            <div
                class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-20"
                style="background-image: url('{{ asset($bgImagePath) }}');"
            ></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white/70 via-white/75 to-slate-50/90 dark:from-slate-900/80 dark:via-slate-900/85 dark:to-slate-950/95"></div>

            <!-- Page Content -->
            <main class="relative z-10">
                {{ $slot }}
            </main>
            <footer class="relative z-10 py-8">
                <div class="mx-auto flex w-full max-w-4xl items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>Skapad av</span>
                    <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-800 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">AlexAhman.se</a>
                </div>
            </footer>
        </div>
    </body>
</html>
