<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $restaurant->name ?? config('app.name', 'VenueFlow') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=oswald:400,500,600,700|barlow:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body antialiased">
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
                    if (! is_dir($path)) return [];
                    return collect(\Illuminate\Support\Facades\File::files($path))
                        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $allowedExtensions, true))
                        ->map(fn ($file) => $dir.'/'.$file->getFilename())
                        ->all();
                })
                ->sort()->values()->all();

            $bgImagePath = count($bgCandidates)
                ? $bgCandidates[$rotationSeed % count($bgCandidates)]
                : null;
        @endphp

        <div class="pub-shell relative min-h-[100dvh] overflow-x-hidden bg-[#0d0d0d] text-white">

            {{-- Background image with strong dark overlay --}}
            @if($bgImagePath)
                <div
                    class="pointer-events-none fixed inset-0 bg-cover bg-center bg-no-repeat opacity-30"
                    style="background-image: url('{{ asset($bgImagePath) }}');"
                    aria-hidden="true"
                ></div>
            @endif
            <div class="pointer-events-none fixed inset-0 bg-gradient-to-b from-black/80 via-black/40 to-[#0d0d0d]" aria-hidden="true"></div>

            {{-- Subtle red ambient glow top-right --}}
            <div class="pointer-events-none fixed right-0 top-0 h-[600px] w-[600px] -translate-y-1/4 translate-x-1/4 rounded-full bg-red-900/20 blur-[120px]" aria-hidden="true"></div>

            <main class="relative z-10">
                {{ $slot }}
            </main>

            <footer class="relative z-10 border-t border-white/10 py-8">
                <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
                    <p class="font-display text-xs uppercase tracking-[0.2em] text-white/40">
                        {{ $restaurant->name ?? 'VenueFlow' }}
                    </p>
                    <p class="text-xs text-white/30">
                        Bokningssystem av
                        <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="text-white/50 transition hover:text-white">
                            AlexAhman.se
                        </a>
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
