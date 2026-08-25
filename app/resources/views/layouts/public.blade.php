<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $restaurant->name ?? config('app.name', 'VenueFlow') }}</title>
        <meta name="description" content="Boka {{ $restaurant->name ?? 'aktiviteter och events' }} direkt - utan konto och utan krångel.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=syne:500,600,700,800|dm-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @keyframes pub-reveal {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes pub-reveal-right {
                from { opacity: 0; transform: translateY(10px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes pub-pulse-dot {
                0%, 100% { transform: scale(1); opacity: 1; }
                50%       { transform: scale(1.6); opacity: 0.4; }
            }
            .pub-reveal   { animation: pub-reveal 0.7s cubic-bezier(0.16,1,0.3,1) both; }
            .pub-reveal-2 { animation: pub-reveal 0.7s 0.1s cubic-bezier(0.16,1,0.3,1) both; }
            .pub-reveal-3 { animation: pub-reveal 0.7s 0.2s cubic-bezier(0.16,1,0.3,1) both; }
            .pub-reveal-r { animation: pub-reveal-right 0.8s 0.35s cubic-bezier(0.16,1,0.3,1) both; }
            .pub-pulse-dot { animation: pub-pulse-dot 2.4s ease-in-out infinite; }

            @media (prefers-reduced-motion: reduce) {
                .pub-reveal, .pub-reveal-2, .pub-reveal-3, .pub-reveal-r {
                    animation: none; opacity: 1; transform: none;
                }
                .pub-pulse-dot { animation: none; }
            }
        </style>
    </head>
    <body class="font-body antialiased">
        @php
            $embed = $embed ?? false;
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

        <div class="pub-shell relative {{ $embed ? '' : 'min-h-[100dvh]' }} overflow-x-hidden bg-[#0c0c0c] text-white">

            @unless($embed)
                {{-- Background image --}}
                @if($bgImagePath)
                    <div
                        class="pointer-events-none fixed inset-0 bg-cover bg-center bg-no-repeat"
                        style="background-image: url('{{ asset($bgImagePath) }}'); opacity: 0.18;"
                        aria-hidden="true"
                    ></div>
                @endif

                {{-- Gradient vignette - darker at edges, lighter center to let photo breathe --}}
                <div
                    class="pointer-events-none fixed inset-0"
                    style="background: linear-gradient(to bottom, rgba(12,12,12,0.85) 0%, rgba(12,12,12,0.45) 40%, rgba(12,12,12,0.75) 100%);"
                    aria-hidden="true"
                ></div>
            @endunless

            <main class="relative z-10">
                {{ $slot }}
            </main>

            @unless($embed)
                <footer class="relative z-10 border-t py-7" style="border-color:rgba(255,255,255,0.07)">
                    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 sm:flex-row sm:px-10">
                        <p class="font-display text-[11px] font-semibold tracking-[0.18em] text-white/25" style="text-transform:uppercase">
                            {{ $restaurant->name ?? 'VenueFlow' }}
                        </p>
                        <p class="text-xs text-white/25">
                            Bokningssystem av
                            <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer"
                               class="text-white/40 underline underline-offset-4 transition hover:text-white/70">
                                AlexAhman.se
                            </a>
                        </p>
                    </div>
                </footer>
            @endunless
        </div>

        @if($embed)
            <script>
                (function () {
                    function reportHeight() {
                        window.parent.postMessage({ venueflowEmbedHeight: document.documentElement.scrollHeight }, '*');
                    }
                    new ResizeObserver(reportHeight).observe(document.body);
                    window.addEventListener('load', reportHeight);
                    reportHeight();
                })();
            </script>
        @endif
    </body>
</html>
