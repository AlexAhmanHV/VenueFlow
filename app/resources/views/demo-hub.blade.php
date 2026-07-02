<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VenueFlow — Bokningssystem för venues</title>
    <meta name="description" content="VenueFlow kopplar gästbokning direkt till live-drift. Byggt med Laravel, Reverb, Supabase och Tailwind.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800,900|oswald:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<main class="relative min-h-[100dvh] w-full max-w-full overflow-x-hidden bg-[#f4f6f2] text-slate-950">

    {{-- Blueprint grid --}}
    <div class="pointer-events-none absolute inset-0 vf-blueprint-grid opacity-60"></div>
    <div class="pointer-events-none absolute left-1/2 top-0 h-px w-[92vw] -translate-x-1/2 bg-slate-950/20"></div>

    <div class="vf-container relative z-10 py-5 sm:py-7">

        {{-- ── NAV ────────────────────────────────────────────── --}}
        <nav class="vf-topbar">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-950/15 bg-slate-950 text-xs font-black text-white">VF</div>
                <span class="text-sm font-black tracking-tight text-slate-950">VenueFlow</span>
            </a>
            <div class="hidden items-center gap-1 md:flex">
                <a class="vf-nav-link" href="{{ route('public.landing', 'golfbaren') }}">Gästflöde</a>
                <a class="vf-nav-link" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Admin</a>
                <a class="vf-nav-link" href="{{ route('platform.restaurants.index') }}">Plattform</a>
            </div>
            <a class="vf-btn-secondary !px-4 !py-2" href="{{ route('login') }}">Logga in</a>
        </nav>
    </div>

    {{-- ── MARQUEE ─────────────────────────────────────────────── --}}
    <div class="mt-6 overflow-hidden border-y border-slate-950/10 bg-slate-950 py-3.5">
        <div class="vf-marquee-track flex w-max gap-0">
            @php
                $features = [
                    'Real-time WebSockets', 'Waitlist-system', 'QR-koder',
                    'Kapacitetsanalys', 'Återkommande bokningar', 'Multi-tenant',
                    'Förbeställning', 'Live board', 'Laravel Reverb', 'Supabase',
                ];
            @endphp
            @foreach(array_merge($features, $features) as $f)
                <span class="mx-5 text-[11px] font-bold uppercase tracking-[0.22em] text-white/35">{{ $f }}</span>
                <span class="text-white/15 select-none">·</span>
            @endforeach
        </div>
    </div>

    <div class="vf-container relative z-10">

        {{-- ── HERO ────────────────────────────────────────────── --}}
        <section class="grid min-h-[80dvh] gap-10 py-14 sm:py-20 lg:grid-cols-2 lg:items-center lg:py-24">

            {{-- Left --}}
            <div class="vf-reveal">
                <p class="vf-kicker">Bokningssystem för moderna venues</p>
                <h1 class="mt-5 max-w-2xl text-[clamp(3rem,5.4vw,5.6rem)] font-black leading-[0.9] tracking-tight text-slate-950" style="text-wrap:balance">
                    Guest booking meets live operations.
                </h1>
                <p class="mt-7 max-w-xl text-lg leading-8 text-slate-600">
                    Utforska gästflöde, restaurangadmin och plattformsvyer på ett ställe.
                    Realtidsuppdateringar via WebSockets, köhantering och QR-koder inbyggt.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">Starta gästflöde</a>
                    <a class="vf-btn-secondary" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Se adminyta</a>
                </div>
            </div>

            {{-- Right: mock live board UI --}}
            <div class="vf-reveal-delay">
                <div class="relative">
                    {{-- Glow behind card --}}
                    <div class="pointer-events-none absolute -inset-4 rounded-3xl bg-emerald-400/10 blur-2xl"></div>

                    {{-- Mock UI card --}}
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[0_32px_80px_rgba(15,23,42,0.14)]">

                        {{-- Titlebar --}}
                        <div class="flex items-center justify-between border-b border-slate-100 bg-white px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                </span>
                                <span class="text-xs font-bold text-slate-700">Live board · Golfbaren</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">{{ now()->format('H:i') }}</span>
                                <div class="flex gap-1">
                                    <div class="h-2.5 w-2.5 rounded-full bg-slate-200"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-slate-200"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-slate-900"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Resource grid header --}}
                        <div class="grid grid-cols-4 gap-px border-b border-slate-100 bg-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            <div class="bg-white px-3 py-2">Tid</div>
                            <div class="bg-white px-3 py-2">Golf 1</div>
                            <div class="bg-white px-3 py-2">Shuffleboard</div>
                            <div class="bg-white px-3 py-2">Dart</div>
                        </div>

                        {{-- Mock booking rows --}}
                        @php
                        $mockRows = [
                            ['17:00', 'Andersson · 2p', null, 'Karlsson · 4p'],
                            ['17:30', 'Andersson · 2p', 'Berg · 3p', 'Karlsson · 4p'],
                            ['18:00', null, 'Berg · 3p', null],
                            ['18:30', 'Nilsson · 6p', null, 'Johansson · 2p'],
                            ['19:00', 'Nilsson · 6p', 'Eriksson · 2p', 'Johansson · 2p'],
                        ];
                        $colors = ['bg-emerald-100 text-emerald-800', 'bg-sky-100 text-sky-800', 'bg-violet-100 text-violet-800', 'bg-amber-100 text-amber-800'];
                        @endphp
                        <div class="divide-y divide-slate-50">
                            @foreach($mockRows as $i => $row)
                            <div class="grid grid-cols-4 gap-px bg-slate-100 text-[11px]">
                                <div class="bg-white px-3 py-2.5 font-mono font-semibold text-slate-400">{{ $row[0] }}</div>
                                @foreach([$row[1], $row[2], $row[3]] as $j => $cell)
                                <div class="bg-white px-2 py-2">
                                    @if($cell)
                                        <span class="inline-block rounded px-1.5 py-0.5 font-semibold {{ $colors[($i + $j) % count($colors)] }}">
                                            {{ $cell }}
                                        </span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>

                        {{-- Bottom status bar --}}
                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-4 py-2.5">
                            <span class="text-[10px] text-slate-400">5 aktiva bokningar</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">WebSocket live</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (config('demo.public_mode'))
            <section class="vf-access-strip vf-reveal">
                <div>
                    <p class="font-black text-slate-950">Full admin/superadmin är låst i publik demo.</p>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-700">
                        Gå till <span class="font-bold">/demo/full-access</span>, ange access key och logga in igen.
                    </p>
                </div>
                <a class="vf-btn-primary shrink-0" href="{{ route('demo.access.show') }}">Lås upp full demo</a>
            </section>
        @endif

        {{-- ── SYSTEM PATHS ─────────────────────────────────────── --}}
        <section class="py-16 sm:py-24">
            <div class="mb-10 grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
                <div>
                    <p class="vf-kicker">System paths</p>
                    <h2 class="mt-3 vf-section-title">Three lenses, one connected product.</h2>
                </div>
                <p class="max-w-3xl text-base leading-7 text-slate-600 lg:justify-self-end">
                    Samma objekt ett venue faktiskt hanterar: gäster, bokningar,
                    resurser, menyer, personal och plattformsnivå-kataloger.
                </p>
            </div>

            <div class="vf-path-board">
                <article class="vf-path-row">
                    <div class="vf-path-marker">A</div>
                    <div>
                        <p class="vf-kicker">Gästflöde</p>
                        <h3 class="mt-2 text-3xl font-black tracking-tight">Publik bokning</h3>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Boka aktivitet utan inloggning och följ bekräftelseflödet.</p>
                    </div>
                    <div class="vf-path-media">
                        <img src="{{ asset('images/restaurant-backgrounds/bg-01.jpg') }}" alt="Public booking" class="h-full w-full object-cover">
                    </div>
                    <div class="vf-path-actions">
                        <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">Öppna restaurangsida</a>
                        <a class="vf-btn-secondary" href="{{ route('public.booking.create', 'golfbaren') }}">Boka direkt</a>
                    </div>
                </article>

                <article class="vf-path-row">
                    <div class="vf-path-marker">B</div>
                    <div>
                        <p class="vf-kicker">Restaurangadmin</p>
                        <h3 class="mt-2 text-3xl font-black tracking-tight">Operativ drift</h3>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Dashboard, live board, meny, schema, resurser och personal.</p>
                    </div>
                    <div class="vf-module-stack" aria-hidden="true">
                        <span>Dashboard</span>
                        <span>Live board</span>
                        <span>Meny</span>
                        <span>Schema</span>
                        <span>Resurser</span>
                        <span>Analys</span>
                    </div>
                    <div class="vf-path-actions">
                        <a class="vf-btn-primary" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Admin dashboard</a>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.live', 'golfbaren') }}">Live board</a>
                    </div>
                </article>

                <article class="vf-path-row">
                    <div class="vf-path-marker">C</div>
                    <div>
                        <p class="vf-kicker">Plattform</p>
                        <h3 class="mt-2 text-3xl font-black tracking-tight">SuperAdmin</h3>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Hantera restauranger samt global rätt- och dryckeskatalog.</p>
                    </div>
                    <div class="vf-catalog-lines" aria-hidden="true">
                        <span>Restaurants</span>
                        <span>Dish templates</span>
                        <span>Drink templates</span>
                    </div>
                    <div class="vf-path-actions">
                        <a class="vf-btn-primary" href="{{ route('platform.restaurants.index') }}">Restauranger</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.dish-templates.index') }}">Rättkatalog</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.drink-templates.index') }}">Dryckeskatalog</a>
                    </div>
                </article>
            </div>
        </section>

        {{-- ── QUICKSTART + DEMO SCOPE ─────────────────────────── --}}
        <section class="grid gap-5 pb-16 sm:pb-24 lg:grid-cols-[0.85fr_1.15fr]">
            <aside class="vf-quickstart">
                <p class="vf-kicker">Snabbstart</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">Recommended demo route.</h2>
                <ol class="mt-7 space-y-5 text-sm text-slate-700">
                    <li class="vf-step"><span>1</span><p>Testa publik bokning på Golfbaren.</p></li>
                    <li class="vf-step"><span>2</span><p>Logga in och se admin-dashboarden.</p></li>
                    <li class="vf-step"><span>3</span><p>Öppna live board i en annan flik och se realtidsuppdateringar.</p></li>
                </ol>
            </aside>

            <div class="vf-demo-scope">
                <div>
                    <p class="vf-kicker">Features</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-white">One app, three operational lenses.</h2>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-white/70">Realtid, köhantering, analys och QR — utan externa tjänster.</p>
                </div>
                @if (config('demo.public_mode'))
                    <div class="vf-credential">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-200">Read-only konto</p>
                        <p class="mt-2 text-lg font-black text-white">owner@demo.test</p>
                        <p class="text-sm text-white/60">password</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ── TECH STACK ───────────────────────────────────────── --}}
        <section class="border-t border-slate-950/10 pb-20 pt-16 sm:pb-28 sm:pt-20">
            <div class="mb-10">
                <p class="vf-kicker">Under huven</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Byggt med moderna verktyg.</h2>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

                @php
                $stack = [
                    ['Laravel 11', 'PHP-ramverk', '#FF2D20', 'border-red-200 hover:border-red-400 hover:shadow-[0_8px_30px_rgba(239,68,68,0.18)]'],
                    ['PHP 8.3', 'Serverspråk', '#777BB4', 'border-violet-200 hover:border-violet-400 hover:shadow-[0_8px_30px_rgba(139,92,246,0.18)]'],
                    ['Laravel Reverb', 'WebSockets / realtid', '#047857', 'border-emerald-200 hover:border-emerald-400 hover:shadow-[0_8px_30px_rgba(4,120,87,0.18)]'],
                    ['Supabase', 'PostgreSQL-databas', '#3ECF8E', 'border-teal-200 hover:border-teal-400 hover:shadow-[0_8px_30px_rgba(16,185,129,0.18)]'],
                    ['Tailwind CSS', 'Utility-first CSS', '#38BDF8', 'border-sky-200 hover:border-sky-400 hover:shadow-[0_8px_30px_rgba(56,189,248,0.18)]'],
                    ['Alpine.js', 'Lätt JS-interaktivitet', '#77C1D2', 'border-cyan-200 hover:border-cyan-400 hover:shadow-[0_8px_30px_rgba(34,211,238,0.18)]'],
                    ['Vite', 'Frontend-byggverktyg', '#BD34FE', 'border-purple-200 hover:border-purple-400 hover:shadow-[0_8px_30px_rgba(168,85,247,0.18)]'],
                    ['endroid/qr-code', 'QR-kodsgenerering', '#64748B', 'border-slate-200 hover:border-slate-400 hover:shadow-[0_8px_30px_rgba(100,116,139,0.18)]'],
                ];
                @endphp

                @foreach($stack as [$name, $desc, $color, $classes])
                <div class="group relative overflow-hidden rounded-2xl border bg-white p-5 transition duration-300 {{ $classes }}">
                    <div class="pointer-events-none absolute right-3 top-3 h-16 w-16 rounded-full opacity-0 blur-xl transition-opacity duration-300 group-hover:opacity-100"
                         style="background: {{ $color }}33"></div>
                    <div class="h-2 w-8 rounded-full transition-all duration-300 group-hover:w-12"
                         style="background: {{ $color }}"></div>
                    <p class="mt-4 text-sm font-black tracking-tight text-slate-950">{{ $name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ── FOOTER ───────────────────────────────────────────── --}}
        <footer class="flex flex-col items-center justify-between gap-4 border-t border-slate-950/15 py-8 text-sm text-slate-500 sm:flex-row">
            <span class="font-bold text-slate-950">VenueFlow</span>
            <span>
                Skapad av
                <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer"
                   class="font-bold text-slate-950 underline decoration-emerald-700 decoration-2 underline-offset-4 hover:text-emerald-900">
                    AlexAhman.se
                </a>
            </span>
        </footer>

    </div>
</main>
</body>
</html>
