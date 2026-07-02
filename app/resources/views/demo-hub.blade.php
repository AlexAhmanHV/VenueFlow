<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VenueFlow — Bokningssystem för venues</title>
    <meta name="description" content="VenueFlow kopplar gästbokning direkt till live-drift. Byggt med Laravel, Reverb, Supabase och Tailwind.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }

        /* ── Orb background ── */
        @keyframes dh-orb1 {
            0%, 100% { transform: translate(0px,0px) scale(1); }
            33%       { transform: translate(-80px,60px) scale(1.08); }
            66%       { transform: translate(40px,-50px) scale(0.96); }
        }
        @keyframes dh-orb2 {
            0%, 100% { transform: translate(0px,0px) scale(1); }
            40%       { transform: translate(70px,-40px) scale(1.06); }
            75%       { transform: translate(-50px,70px) scale(0.93); }
        }
        @keyframes dh-orb3 {
            0%, 100% { transform: translate(0px,0px) scale(1); }
            50%       { transform: translate(60px,55px) scale(1.12); }
        }
        .dh-orb1 { animation: dh-orb1 22s ease-in-out infinite; }
        .dh-orb2 { animation: dh-orb2 28s ease-in-out infinite; }
        .dh-orb3 { animation: dh-orb3 18s ease-in-out infinite; }

        /* Grain overlay */
        .dh-grain::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 60;
            pointer-events: none;
            opacity: 0.035;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            background-size: 160px 160px;
        }

        /* ── Reveal ── */
        @keyframes dh-rise {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes dh-ping {
            75%, 100% { transform: scale(2.2); opacity: 0; }
        }
        .dh-rise   { animation: dh-rise 0.65s ease both; }
        .dh-rise-2 { animation: dh-rise 0.65s 0.12s ease both; }
        .dh-ping   { animation: dh-ping 2.2s cubic-bezier(0,0,0.2,1) infinite; }

        .dh-cell-hover { transition: background-color 0.25s ease, border-color 0.25s ease; }

        /* ── Buttons ── */
        .dh-btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #10b981; color: #fff;
            padding: 12px 24px; font-size: 0.875rem; font-weight: 700;
            border: none; border-radius: 0;
            transition: background 0.18s ease, transform 0.12s ease;
            text-decoration: none; position: relative; overflow: hidden;
        }
        .dh-btn-primary:hover { background: #059669; }
        .dh-btn-primary:active { transform: translateY(1px); }

        .dh-btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.45); font-size: 0.875rem; font-weight: 600;
            text-decoration: none; transition: color 0.18s ease;
            border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 2px;
        }
        .dh-btn-secondary:hover { color: rgba(255,255,255,0.9); border-color: rgba(255,255,255,0.40); }
        .dh-btn-secondary .dh-arrow { transition: transform 0.18s ease; }
        .dh-btn-secondary:hover .dh-arrow { transform: translateX(4px); }

        /* ── Mobile menu ── */
        .dh-mobile-menu {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        /* ── About modal ── */
        .dh-modal-backdrop {
            transition: opacity 0.25s ease;
        }
        .dh-modal-panel {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .dh-orb1, .dh-orb2, .dh-orb3 { animation: none; }
            .dh-rise, .dh-rise-2 { animation: none; opacity: 1; transform: none; }
        }
    </style>
</head>
<body class="dh-grain bg-[#0b0b0b] text-white antialiased">
<div class="relative min-h-[100dvh] w-full overflow-x-hidden"
     x-data="{ menuOpen: false, aboutOpen: false }"
     @keydown.escape.window="menuOpen = false; aboutOpen = false">

    {{-- Animated background orbs --}}
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
        <div class="dh-orb1 absolute -left-40 -top-40 h-[600px] w-[600px] rounded-full blur-[120px]"
             style="background:radial-gradient(circle,rgba(16,185,129,0.13) 0%,transparent 70%)"></div>
        <div class="dh-orb2 absolute -right-32 top-1/3 h-[500px] w-[500px] rounded-full blur-[100px]"
             style="background:radial-gradient(circle,rgba(4,120,87,0.10) 0%,transparent 70%)"></div>
        <div class="dh-orb3 absolute bottom-0 left-1/3 h-[450px] w-[450px] rounded-full blur-[110px]"
             style="background:radial-gradient(circle,rgba(16,185,129,0.07) 0%,transparent 70%)"></div>
    </div>

    {{-- ── NAV ─────────────────────────────────────────────────── --}}
    <nav class="relative z-20 mx-auto max-w-[1440px] px-5 py-5 sm:px-8 lg:px-12">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-xs font-black text-white">VF</div>
                <span class="text-sm font-bold tracking-tight">VenueFlow</span>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden items-center gap-7 md:flex">
                <a href="{{ route('public.landing', 'golfbaren') }}" class="text-sm font-medium text-white/40 transition hover:text-white">Gästflöde</a>
                <a href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}" class="text-sm font-medium text-white/40 transition hover:text-white">Admin</a>
                <a href="{{ route('platform.restaurants.index') }}" class="text-sm font-medium text-white/40 transition hover:text-white">Plattform</a>
                <button @click="aboutOpen = true"
                        class="text-sm font-medium text-white/40 transition hover:text-white">
                    Om projektet
                </button>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}"
                   class="rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-white/70 transition hover:border-white/40 hover:text-white">
                    Logga in
                </a>
                {{-- Hamburger (mobile) --}}
                <button @click="menuOpen = !menuOpen"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/15 transition hover:border-white/30 md:hidden"
                        aria-label="Meny">
                    <svg x-show="!menuOpen" class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="menuOpen" x-cloak class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="menuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mt-3 flex flex-col gap-1 rounded-2xl p-3 md:hidden"
             style="background-color:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08)">
            <a href="{{ route('public.landing', 'golfbaren') }}" class="rounded-xl px-4 py-3 text-sm font-medium text-white/60 transition hover:bg-white/5 hover:text-white">Gästflöde</a>
            <a href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}" class="rounded-xl px-4 py-3 text-sm font-medium text-white/60 transition hover:bg-white/5 hover:text-white">Admin</a>
            <a href="{{ route('platform.restaurants.index') }}" class="rounded-xl px-4 py-3 text-sm font-medium text-white/60 transition hover:bg-white/5 hover:text-white">Plattform</a>
            <button @click="menuOpen = false; aboutOpen = true"
                    class="rounded-xl px-4 py-3 text-left text-sm font-medium text-white/60 transition hover:bg-white/5 hover:text-white">
                Om projektet
            </button>
        </div>
    </nav>

    {{-- ── HERO ─────────────────────────────────────────────────── --}}
    <section class="relative z-10 mx-auto flex max-w-[1440px] min-h-[calc(100dvh-72px)] flex-col justify-center px-5 pb-16 pt-6 sm:px-8 lg:px-12">
        <div class="grid items-end gap-12 lg:grid-cols-[1fr_260px] lg:gap-16">

            <div class="dh-rise">
                <h1 class="text-[clamp(4rem,9.5vw,9.5rem)] font-black leading-[0.86] tracking-[-0.02em] text-white" style="text-wrap:balance">
                    Guest booking.<br>Live operations.
                </h1>
                <p class="mt-7 max-w-[44ch] text-lg leading-relaxed text-white/50">
                    Utforska ett komplett bokningssystem med realtid, köhantering, analys och QR.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-6">
                    <a href="{{ route('public.landing', 'golfbaren') }}" class="dh-btn-primary">
                        Starta gästflöde
                    </a>
                    <a href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}" class="dh-btn-secondary">
                        Se adminyta <span class="dh-arrow">&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- Animated counters --}}
            <div class="dh-rise-2 hidden lg:flex lg:flex-col lg:gap-0 lg:pb-1">
                <div class="border-t border-white/10 py-6"
                     x-data="dhCounter(0, 3, 900)"
                     x-init="$nextTick(() => start())">
                    <p class="text-5xl font-black leading-none text-emerald-400" x-text="value"></p>
                    <p class="mt-2 text-xs text-white/30">systemnivåer</p>
                </div>
                <div class="border-t border-white/10 py-6"
                     x-data="dhCounter(0, 5, 1100)"
                     x-init="$nextTick(() => start())">
                    <p class="text-5xl font-black leading-none text-white">
                        <span x-text="value"></span><span x-show="done">+</span>
                    </p>
                    <p class="mt-2 text-xs text-white/30">realtidsfunktioner</p>
                </div>
                <div class="border-t border-white/10 pt-6">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="dh-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <p class="text-sm font-bold text-white">WebSocket live</p>
                    </div>
                    <p class="mt-2 text-xs text-white/30">Laravel Reverb</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── DEMO ACCESS STRIP ────────────────────────────────────── --}}
    @if (config('demo.public_mode'))
    <div class="relative z-10 mx-auto max-w-[1440px] px-5 pb-10 sm:px-8 lg:px-12">
        <div class="flex flex-col justify-between gap-5 rounded-2xl border border-emerald-900/50 p-6 sm:flex-row sm:items-center"
             style="background-color:rgba(4,120,87,0.12)">
            <div>
                <p class="font-bold text-white">Full admin och superadmin är låst i publik demo.</p>
                <p class="mt-1 text-sm text-white/40">Gå till /demo/full-access och ange access key för att låsa upp.</p>
            </div>
            <a href="{{ route('demo.access.show') }}" class="dh-btn-primary shrink-0">Lås upp full demo</a>
        </div>
    </div>
    @endif

    {{-- ── PATHS BENTO ──────────────────────────────────────────── --}}
    <section class="relative z-10 mx-auto max-w-[1440px] px-5 py-16 sm:px-8 sm:py-24 lg:px-12">
        <h2 class="mb-10 text-4xl font-black leading-[1] tracking-tight sm:text-5xl">
            Tre vyer, ett<br>sammankopplat system.
        </h2>
        <div class="grid gap-3 lg:grid-cols-[1.35fr_1fr]">
            <a href="{{ route('public.landing', 'golfbaren') }}"
               class="dh-cell-hover group relative flex min-h-64 flex-col justify-between overflow-hidden rounded-2xl p-7 lg:min-h-[430px]"
               style="background-color:rgba(4,120,87,0.18); border:1px solid rgba(4,120,87,0.30)">
                <div>
                    <span class="text-xs font-black text-emerald-500" style="letter-spacing:0.16em">A</span>
                    <h3 class="mt-4 text-3xl font-black tracking-tight text-white">Publik bokning</h3>
                    <p class="mt-3 max-w-xs text-sm leading-6 text-white/50">
                        Boka aktivitet, välj tid och slot, fyll i uppgifter. Det kompletta bokningsflödet.
                    </p>
                </div>
                <div>
                    <div class="mb-5 flex flex-wrap gap-2">
                        @foreach(['Restaurangsida','Bokningsflödet','Kölista','QR-kod'] as $tag)
                        <span class="rounded-full px-3 py-1 text-xs font-semibold text-emerald-300"
                              style="background-color:rgba(4,120,87,0.30)">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <div class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400"
                         style="border-bottom:1px solid rgba(52,211,153,0.25);padding-bottom:2px">
                        <span>Öppna restaurangsida</span>
                        <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                    </div>
                </div>
            </a>
            <div class="flex flex-col gap-3">
                <a href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}"
                   class="dh-cell-hover group flex min-h-52 flex-col justify-between rounded-2xl p-6 lg:min-h-0 lg:flex-1"
                   style="background-color:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08)">
                    <div>
                        <span class="text-xs font-black text-white/20" style="letter-spacing:0.16em">B</span>
                        <h3 class="mt-3 text-2xl font-black tracking-tight text-white">Restaurangadmin</h3>
                        <p class="mt-2 text-sm text-white/40">Dashboard, live board, meny, schema, resurser och personal.</p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach(['Dashboard','Live board','Meny','Schema','Resurser','Analys'] as $mod)
                        <span class="rounded-lg px-2.5 py-1 text-[11px] font-semibold text-white/40"
                              style="background-color:rgba(255,255,255,0.07)">{{ $mod }}</span>
                        @endforeach
                    </div>
                </a>
                <a href="{{ route('platform.restaurants.index') }}"
                   class="dh-cell-hover group flex min-h-52 flex-col justify-between rounded-2xl p-6 lg:min-h-0 lg:flex-1"
                   style="background-color:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08)">
                    <div>
                        <span class="text-xs font-black text-white/20" style="letter-spacing:0.16em">C</span>
                        <h3 class="mt-3 text-2xl font-black tracking-tight text-white">SuperAdmin</h3>
                        <p class="mt-2 text-sm text-white/40">Hantera restauranger och global rätt- och dryckeskatalog.</p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-1.5">
                        @foreach(['Restauranger','Rättkatalog','Dryckeskatalog'] as $cat)
                        <span class="rounded-lg px-2.5 py-1 text-[11px] font-semibold text-white/40"
                              style="background-color:rgba(255,255,255,0.07)">{{ $cat }}</span>
                        @endforeach
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- ── DEMO ROUTE ───────────────────────────────────────────── --}}
    <section class="relative z-10 mx-auto max-w-[1440px] border-t px-5 py-16 sm:px-8 sm:py-20 lg:px-12"
             style="border-color:rgba(255,255,255,0.08)">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.7fr] lg:items-start lg:gap-20">
            <div>
                <h2 class="text-2xl font-black tracking-tight">Rekommenderat demo-flöde.</h2>
                <p class="mt-3 text-sm leading-relaxed text-white/40">Tre steg för att se systemet från alla vinklar.</p>
            </div>
            <ol class="grid gap-px sm:grid-cols-3" style="border-top:1px solid rgba(255,255,255,0.08)">
                @foreach([
                    ['Boka', 'Testa publik bokning på Golfbarens sida. Välj aktivitet, tid och slutför flödet.'],
                    ['Administrera', 'Logga in och öppna admin-dashboarden. Se bokningar, live board och analys.'],
                    ['Observera', 'Öppna live board i en annan flik och se realtidsuppdateringar via WebSocket.'],
                ] as $step)
                <li class="pt-7 pr-6">
                    <p class="font-bold text-white">{{ $step[0] }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/40">{{ $step[1] }}</p>
                </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ── TECH STACK ───────────────────────────────────────────── --}}
    <section class="relative z-10 mx-auto max-w-[1440px] border-t px-5 pb-20 pt-16 sm:px-8 sm:pb-28 sm:pt-20 lg:px-12"
             style="border-color:rgba(255,255,255,0.08)">
        <div class="grid gap-14 lg:grid-cols-[auto_1fr] lg:items-start lg:gap-24">
            <h2 class="text-2xl font-black tracking-tight lg:max-w-[13ch]">Byggt med moderna verktyg.</h2>
            <div class="grid grid-cols-2 gap-x-14 gap-y-10 sm:grid-cols-3">
                @foreach([
                    ['Backend',       ['Laravel 11', 'PHP 8.3', 'Laravel Reverb']],
                    ['Frontend',      ['Tailwind CSS', 'Alpine.js', 'Vite']],
                    ['Infra & Extra', ['Supabase', 'endroid/qr-code']],
                ] as [$group, $items])
                <div>
                    <p class="mb-4 text-[10.5px] font-semibold text-white/25"
                       style="letter-spacing:0.15em; text-transform:uppercase">{{ $group }}</p>
                    <ul class="space-y-2.5">
                        @foreach($items as $item)
                        <li class="text-sm font-semibold text-white/70">{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── FEATURES + CREDENTIALS ──────────────────────────────── --}}
    <section class="relative z-10 mx-auto max-w-[1440px] border-t px-5 pb-16 sm:px-8 lg:px-12"
             style="border-color:rgba(255,255,255,0.08)">
        <div class="rounded-2xl p-7 sm:p-9" style="background-color:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08)">
            <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-start">
                <div>
                    <h3 class="text-lg font-black tracking-tight">Vad som finns i denna demo</h3>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach([
                            'Realtid via WebSockets','Waitlist-system','QR-koder',
                            'Kapacitetsanalys','Återkommande bokningar','Multi-tenant',
                            'Förbeställd mat','Live board',
                        ] as $feature)
                        <span class="rounded-full px-3 py-1 text-xs font-semibold text-white/50"
                              style="border:1px solid rgba(255,255,255,0.10)">{{ $feature }}</span>
                        @endforeach
                    </div>
                </div>
                @if (config('demo.public_mode'))
                <div class="rounded-xl p-5 lg:min-w-56"
                     style="background-color:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.10)">
                    <p class="text-[10px] font-bold text-emerald-400"
                       style="letter-spacing:0.16em; text-transform:uppercase">Read-only konto</p>
                    <p class="mt-3 text-base font-black text-white">owner@demo.test</p>
                    <p class="mt-0.5 text-sm text-white/40">password</p>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ── FOOTER ───────────────────────────────────────────────── --}}
    <footer class="relative z-10 mx-auto flex max-w-[1440px] items-center justify-between border-t px-5 py-8 text-sm sm:px-8 lg:px-12"
            style="border-color:rgba(255,255,255,0.08)">
        <span class="font-bold text-white/70">VenueFlow</span>
        <div class="flex items-center gap-6">
            <button @click="aboutOpen = true"
                    class="text-sm text-white/25 transition hover:text-white/60">
                Om projektet
            </button>
            <span class="text-white/25">
                Skapad av
                <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer"
                   class="font-semibold text-white/50 underline underline-offset-4 transition hover:text-white">
                    AlexAhman.se
                </a>
            </span>
        </div>
    </footer>

    {{-- ── ABOUT MODAL ──────────────────────────────────────────── --}}
    <div x-show="aboutOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         role="dialog" aria-modal="true">

        {{-- Backdrop --}}
        <div class="dh-modal-backdrop absolute inset-0 bg-black/80 backdrop-blur-sm"
             x-show="aboutOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="aboutOpen = false"></div>

        {{-- Panel --}}
        <div class="dh-modal-panel relative max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-2xl p-7 sm:p-10"
             x-show="aboutOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             style="background-color:#111; border:1px solid rgba(255,255,255,0.10)">

            <div class="flex items-start justify-between gap-4">
                <h2 class="text-2xl font-black tracking-tight">Om VenueFlow</h2>
                <button @click="aboutOpen = false"
                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white/40 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="mt-5 text-sm leading-7 text-white/55">
                VenueFlow är ett portfolioprojekt som visar hur ett multi-tenant bokningssystem kan byggas med
                ett modernt PHP-stack. Systemet hanterar hela flödet från publik gästbokning till operativ
                restaurangadmin och plattformsnivå-katalog.
            </p>

            <div class="mt-8 space-y-6">

                <div style="border-top:1px solid rgba(255,255,255,0.07)" class="pt-6">
                    <p class="text-xs font-bold text-emerald-400" style="letter-spacing:0.14em;text-transform:uppercase">Arkitektur</p>
                    <ul class="mt-4 space-y-3 text-sm text-white/55">
                        <li><span class="font-semibold text-white/80">Multi-tenant</span> - varje restaurang är en tenant. Middleware löser ut restaurang från slug och verifierar membership innan admin-åtkomst.</li>
                        <li><span class="font-semibold text-white/80">Realtid</span> - Laravel Reverb (first-party WebSocket-server). Events implementerar ShouldBroadcast. Frontend använder Laravel Echo + pusher-js.</li>
                        <li><span class="font-semibold text-white/80">Bokningsflöde</span> - session-baserad cart, slot-tillgänglighet kontrolleras i databastransaktion med lås för att undvika dubbelbokningar.</li>
                        <li><span class="font-semibold text-white/80">Waitlist</span> - när en bokning avbokas triggas en Job som notifierar nästa i kö via e-post.</li>
                    </ul>
                </div>

                <div style="border-top:1px solid rgba(255,255,255,0.07)" class="pt-6">
                    <p class="text-xs font-bold text-emerald-400" style="letter-spacing:0.14em;text-transform:uppercase">Tekniska val</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            ['Laravel 11', 'Convention over configuration, Eloquent ORM, Reverb inbyggt, Blade för SSR utan SPA-overhead.'],
                            ['Supabase', 'Managed PostgreSQL. Session Pooler löser IPv4/IPv6-kompatibilitet på Render-hosting.'],
                            ['Alpine.js', 'Lätt interaktivitet direkt i Blade-templates utan build-steg för enkel state.'],
                            ['Laravel Reverb', 'First-party WebSocket-server. Ersätter Pusher - self-hosted, ingen extern tjänst.'],
                            ['Tailwind v3', 'Utility-first, fungerar direkt med Laravels Blade och Vite-pipeline.'],
                            ['endroid/qr-code', 'Pure PHP QR-generering utan ext-gd-beroende - fungerar på alla host-miljöer.'],
                        ] as [$name, $desc])
                        <div class="rounded-xl p-4" style="background-color:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07)">
                            <p class="text-sm font-bold text-white/80">{{ $name }}</p>
                            <p class="mt-1 text-xs leading-5 text-white/40">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="border-top:1px solid rgba(255,255,255,0.07)" class="pt-6 flex items-center gap-4">
                    <a href="https://github.com/AlexAhmanHV/VenueFlow"
                       target="_blank" rel="noopener noreferrer"
                       class="dh-btn-primary text-sm">
                        GitHub &rarr;
                    </a>
                    <button @click="aboutOpen = false" class="dh-btn-secondary text-sm">Stäng</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function dhCounter(from, to, duration) {
    return {
        value: from,
        done: false,
        start() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.value = to; this.done = true; return;
            }
            const steps = Math.ceil(duration / 16);
            const increment = (to - from) / steps;
            let current = from;
            let count = 0;
            const timer = setInterval(() => {
                count++;
                current += increment;
                if (count >= steps) {
                    this.value = to;
                    this.done = true;
                    clearInterval(timer);
                } else {
                    this.value = Math.floor(current);
                }
            }, 16);
        }
    };
}
</script>
</body>
</html>
