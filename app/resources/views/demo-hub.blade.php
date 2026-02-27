<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VenueFlow Demo Hub</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <main class="relative min-h-screen overflow-x-hidden bg-gradient-to-b from-slate-100 via-slate-50 to-white">
        <div class="pointer-events-none absolute -left-20 top-20 h-72 w-72 rounded-full bg-emerald-300/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 top-1/3 h-80 w-80 rounded-full bg-cyan-300/25 blur-3xl"></div>

        <section class="vf-container relative z-10 py-10 sm:py-14">
            <div class="vf-card p-6 sm:p-8">
                <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Portfolio Demo</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">VenueFlow Demo Hub</h1>
                <p class="mt-3 max-w-3xl text-sm text-slate-600 sm:text-base">
                    Utforska publika bokningsflödet direkt. Privilegierade admin-vyer är låsta i publik demo.
                </p>
                @if (config('demo.public_mode'))
                    <div class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                        Full admin/superadmin kräver access key.
                        Gå till <span class="font-semibold">/demo/full-access</span>, ange nyckeln och logga in igen.
                        <a class="font-semibold underline" href="{{ route('demo.access.show') }}">Lås upp full demo</a>.
                    </div>
                    <div class="mt-3 rounded-md border border-cyan-300 bg-cyan-50 p-4 text-sm text-cyan-900">
                        Read-only restaurangägare:
                        <span class="font-semibold">owner@demo.test / password</span>
                    </div>
                @endif
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">1. Gästflöde</p>
                    <h2 class="mt-2 text-xl font-semibold">Publik bokning</h2>
                    <p class="mt-2 text-sm text-slate-600">Testa meny, aktiviteter, bokning utan inloggning och bekräftelseflöde.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">Öppna restaurangsida</a>
                        <a class="vf-btn-secondary" href="{{ route('public.booking.create', 'golfbaren') }}">Boka direkt</a>
                    </div>
                </article>

                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">2. Restaurangadmin</p>
                    <h2 class="mt-2 text-xl font-semibold">Operativ drift</h2>
                    <p class="mt-2 text-sm text-slate-600">Dashboard, live board, resurser, schema, meny och personal.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('login') }}">Logga in</a>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Admin dashboard</a>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.live', 'golfbaren') }}">Live board</a>
                    </div>
                </article>

                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">3. SuperAdmin</p>
                    <h2 class="mt-2 text-xl font-semibold">Plattform</h2>
                    <p class="mt-2 text-sm text-slate-600">Hantera restauranger, aktiviteter samt rätt- och dryckeskatalog.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('platform.restaurants.index') }}">Restauranger</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.dish-templates.index') }}">Rättkatalog</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.drink-templates.index') }}">Dryckeskatalog</a>
                    </div>
                </article>
            </div>

            <div class="mt-6 vf-card p-5">
                <h3 class="text-lg font-semibold">Snabbtest</h3>
                <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm text-slate-700">
                    <li>Öppna publik sida och skapa en bokning på Golfbaren.</li>
                    <li>Lås upp full demo via <span class="font-semibold">/demo/full-access</span> om du vill testa admin/superadmin.</li>
                    <li>Logga in och kontrollera bokningen i live board.</li>
                    <li>Öppna plattformssidor och lägg till mallrätt eller dryckmall.</li>
                </ol>
            </div>
        </section>
    </main>
</body>
</html>
