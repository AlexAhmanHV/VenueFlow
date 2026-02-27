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
    <main class="relative min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_10%_10%,#d1fae5_0%,transparent_35%),radial-gradient(circle_at_90%_20%,#cffafe_0%,transparent_30%),linear-gradient(to_bottom,#f8fafc,#ffffff)]">
        <div class="vf-container relative z-10 py-8 sm:py-12">
            <section class="vf-card overflow-hidden">
                <div class="grid gap-0 lg:grid-cols-5">
                    <div class="p-6 sm:p-8 lg:col-span-3 lg:p-10">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Portfolio Demo</p>
                        <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">
                            VenueFlow Demo Hub
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                            Utforska g&auml;stfl&ouml;de, restaurangadmin och plattformsvyer p&aring; ett st&auml;lle.
                            I publik demo &auml;r adminskrivningar l&aring;sta tills full access l&aring;ses upp.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">Starta g&auml;stfl&ouml;de</a>
                            <a class="vf-btn-secondary" href="{{ route('login') }}">Logga in</a>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 bg-slate-50 p-6 lg:col-span-2 lg:border-l lg:border-t-0 lg:p-8">
                        <h2 class="text-lg font-semibold">Snabbstart</h2>
                        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-700">
                            <li>Testa publik bokning p&aring; Golfbaren.</li>
                            <li>Logga in som read-only restaurang&auml;gare.</li>
                            <li>L&aring;s upp full demo om du vill skriva i admin.</li>
                        </ol>

                        @if (config('demo.public_mode'))
                            <div class="mt-5 rounded-xl border border-cyan-300 bg-cyan-50 p-4 text-sm text-cyan-900">
                                <p class="font-semibold">Read-only konto</p>
                                <p class="mt-1"><span class="font-semibold">owner@demo.test</span> / <span class="font-semibold">password</span></p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            @if (config('demo.public_mode'))
                <section class="mt-5 vf-card border-amber-300 bg-amber-50/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm text-amber-900">
                            <p class="font-semibold">Full admin/superadmin &auml;r l&aring;st i publik demo.</p>
                            <p class="mt-1">
                                G&aring; till <span class="font-semibold">/demo/full-access</span>, ange access key och logga in igen.
                            </p>
                        </div>
                        <a class="vf-btn-primary" href="{{ route('demo.access.show') }}">L&aring;s upp full demo</a>
                    </div>
                </section>
            @endif

            <section class="mt-5 grid gap-4 lg:grid-cols-3">
                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">1. G&auml;stfl&ouml;de</p>
                    <h2 class="mt-2 text-xl font-semibold">Publik bokning</h2>
                    <p class="mt-2 text-sm text-slate-600">Boka aktivitet utan inloggning och f&ouml;lj bekr&auml;ftelsefl&ouml;det.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">&Ouml;ppna restaurangsida</a>
                        <a class="vf-btn-secondary" href="{{ route('public.booking.create', 'golfbaren') }}">Boka direkt</a>
                    </div>
                </article>

                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">2. Restaurangadmin</p>
                    <h2 class="mt-2 text-xl font-semibold">Operativ drift</h2>
                    <p class="mt-2 text-sm text-slate-600">Dashboard, live board, meny, schema, resurser och personal.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Admin dashboard</a>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.live', 'golfbaren') }}">Live board</a>
                    </div>
                </article>

                <article class="vf-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-700">3. SuperAdmin</p>
                    <h2 class="mt-2 text-xl font-semibold">Plattform</h2>
                    <p class="mt-2 text-sm text-slate-600">Hantera restauranger samt global r&auml;tt- och dryckeskatalog.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="vf-btn-primary" href="{{ route('platform.restaurants.index') }}">Restauranger</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.dish-templates.index') }}">R&auml;ttkatalog</a>
                        <a class="vf-btn-secondary" href="{{ route('platform.drink-templates.index') }}">Dryckeskatalog</a>
                    </div>
                </article>
            </section>

            <footer class="mt-8 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
                Skapad av
                <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-900 underline decoration-emerald-400 decoration-2 underline-offset-2 hover:text-emerald-700">
                    AlexAhman.se
                </a>
            </footer>
        </div>
    </main>
</body>
</html>
