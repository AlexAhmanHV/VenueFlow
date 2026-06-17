<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VenueFlow Demo Hub</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <main class="relative min-h-[100dvh] w-full max-w-full overflow-x-hidden bg-[#f4f6f2] text-slate-950">
        <div class="pointer-events-none absolute inset-0 vf-blueprint-grid"></div>
        <div class="pointer-events-none absolute left-1/2 top-0 h-px w-[92vw] -translate-x-1/2 bg-slate-950/20"></div>

        <div class="vf-container relative z-10 py-5 sm:py-7">
            <nav class="vf-topbar">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('favicon.ico') }}" alt="VenueFlow logo" class="h-9 w-9 rounded-xl border border-slate-950/15 bg-white p-1">
                    <span class="text-sm font-black tracking-tight text-slate-950">VenueFlow</span>
                </a>
                <div class="hidden items-center gap-1 md:flex">
                    <a class="vf-nav-link" href="{{ route('public.landing', 'golfbaren') }}">G&auml;stfl&ouml;de</a>
                    <a class="vf-nav-link" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Admin</a>
                    <a class="vf-nav-link" href="{{ route('platform.restaurants.index') }}">Plattform</a>
                </div>
                <a class="vf-btn-secondary !px-4 !py-2" href="{{ route('login') }}">Logga in</a>
            </nav>

            <section class="grid min-h-[78dvh] gap-10 py-14 sm:py-20 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:py-24">
                <div class="vf-reveal">
                    <p class="vf-kicker">Service blueprint</p>
                    <h1 class="mt-5 max-w-4xl text-[clamp(3.1rem,5.6vw,5.8rem)] font-black leading-[0.9] tracking-tight text-slate-950">
                        VenueFlow maps guest booking to live operations.
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-700 sm:text-xl">
                        Utforska g&auml;stfl&ouml;de, restaurangadmin och plattformsvyer p&aring; ett st&auml;lle.
                        I publik demo &auml;r adminskrivningar l&aring;sta tills full access l&aring;ses upp, s&aring; fl&ouml;det k&auml;nns lugnt under tryck.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">Starta g&auml;stfl&ouml;de</a>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}">Se adminyta</a>
                    </div>
                </div>

                <div class="vf-reveal-delay">
                    <div class="vf-map-shell">
                        <div class="flex flex-col gap-4 border-b border-slate-950/10 pb-5 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="vf-kicker">Golfbaren demo</p>
                                <h2 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Workflow map</h2>
                            </div>
                            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-900/15 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-950">
                                <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                Ready
                            </div>
                        </div>

                        <div class="vf-flow-map mt-6">
                            <a href="{{ route('public.landing', 'golfbaren') }}" class="vf-flow-node vf-flow-node-primary">
                                <span class="vf-node-index">01</span>
                                <span class="vf-node-title">Guest</span>
                                <span class="vf-node-copy">Restaurant page and menu entry.</span>
                            </a>
                            <a href="{{ route('public.booking.create', 'golfbaren') }}" class="vf-flow-node">
                                <span class="vf-node-index">02</span>
                                <span class="vf-node-title">Booking</span>
                                <span class="vf-node-copy">3 steg utan inloggning.</span>
                            </a>
                            <div class="vf-flow-node">
                                <span class="vf-node-index">03</span>
                                <span class="vf-node-title">Preorder</span>
                                <span class="vf-node-copy">Food and drink before arrival.</span>
                            </div>
                            <a href="{{ route('restaurant.admin.dashboard', 'golfbaren') }}" class="vf-flow-node">
                                <span class="vf-node-index">04</span>
                                <span class="vf-node-title">Admin</span>
                                <span class="vf-node-copy">Dashboard, menu, schedule, staff.</span>
                            </a>
                            <a href="{{ route('restaurant.admin.bookings.live', 'golfbaren') }}" class="vf-flow-node vf-flow-node-dark">
                                <span class="vf-node-index">05</span>
                                <span class="vf-node-title">Live board</span>
                                <span class="vf-node-copy">Operational booking view.</span>
                            </a>
                            <a href="{{ route('platform.restaurants.index') }}" class="vf-flow-node">
                                <span class="vf-node-index">06</span>
                                <span class="vf-node-title">Platform</span>
                                <span class="vf-node-copy">Restaurants and global catalogs.</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            @if (config('demo.public_mode'))
                <section class="vf-access-strip vf-reveal">
                    <div>
                        <p class="font-black text-slate-950">Full admin/superadmin &auml;r l&aring;st i publik demo.</p>
                        <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-700">
                            G&aring; till <span class="font-bold">/demo/full-access</span>, ange access key och logga in igen.
                        </p>
                    </div>
                    <a class="vf-btn-primary shrink-0" href="{{ route('demo.access.show') }}">L&aring;s upp full demo</a>
                </section>
            @endif

            <section class="py-16 sm:py-24">
                <div class="mb-10 grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
                    <div>
                        <p class="vf-kicker">System paths</p>
                        <h2 class="mt-3 vf-section-title">Three lenses, one connected product.</h2>
                    </div>
                    <p class="max-w-3xl text-base leading-7 text-slate-700 lg:justify-self-end">
                        The demo is structured around the same objects a venue actually manages: guests, bookings,
                        resources, menus, staff, and platform-level catalogs.
                    </p>
                </div>

                <div class="vf-path-board">
                    <article class="vf-path-row">
                        <div class="vf-path-marker">A</div>
                        <div>
                            <p class="vf-kicker">G&auml;stfl&ouml;de</p>
                            <h3 class="mt-2 text-3xl font-black tracking-tight">Publik bokning</h3>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Boka aktivitet utan inloggning och f&ouml;lj bekr&auml;ftelsefl&ouml;det.</p>
                        </div>
                        <div class="vf-path-media">
                            <img src="{{ asset('images/restaurant-backgrounds/bg-01.jpg') }}" alt="Public booking preview" class="h-full w-full object-cover">
                        </div>
                        <div class="vf-path-actions">
                            <a class="vf-btn-primary" href="{{ route('public.landing', 'golfbaren') }}">&Ouml;ppna restaurangsida</a>
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
                            <span>Personal</span>
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
                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">Hantera restauranger samt global r&auml;tt- och dryckeskatalog.</p>
                        </div>
                        <div class="vf-catalog-lines" aria-hidden="true">
                            <span>Restaurants</span>
                            <span>Dish templates</span>
                            <span>Drink templates</span>
                        </div>
                        <div class="vf-path-actions">
                            <a class="vf-btn-primary" href="{{ route('platform.restaurants.index') }}">Restauranger</a>
                            <a class="vf-btn-secondary" href="{{ route('platform.dish-templates.index') }}">R&auml;ttkatalog</a>
                            <a class="vf-btn-secondary" href="{{ route('platform.drink-templates.index') }}">Dryckeskatalog</a>
                        </div>
                    </article>
                </div>
            </section>

            <section class="grid gap-5 pb-16 sm:pb-24 lg:grid-cols-[0.85fr_1.15fr]">
                <aside class="vf-quickstart">
                    <p class="vf-kicker">Snabbstart</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">Recommended demo route.</h2>
                    <ol class="mt-7 space-y-5 text-sm text-slate-700">
                        <li class="vf-step"><span>1</span><p>Testa publik bokning p&aring; Golfbaren.</p></li>
                        <li class="vf-step"><span>2</span><p>Logga in som read-only restaurang&auml;gare.</p></li>
                        <li class="vf-step"><span>3</span><p>L&aring;s upp full demo om du vill skriva i admin.</p></li>
                    </ol>
                </aside>

                <div class="vf-demo-scope">
                    <div>
                        <p class="vf-kicker">Demo scope</p>
                        <h2 class="mt-3 text-4xl font-black tracking-tight text-white">One app, three operational lenses.</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-white/70">Recruiter-friendly depth without hiding the actual flows behind marketing polish.</p>
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

            <footer class="flex flex-col items-center justify-between gap-4 border-t border-slate-950/15 py-8 text-sm text-slate-600 sm:flex-row">
                <span>VenueFlow demo</span>
                <span>
                    Skapad av
                    <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer" class="font-bold text-slate-950 underline decoration-emerald-700 decoration-2 underline-offset-4 hover:text-emerald-900">
                        AlexAhman.se
                    </a>
                </span>
            </footer>
        </div>
    </main>
</body>
</html>
