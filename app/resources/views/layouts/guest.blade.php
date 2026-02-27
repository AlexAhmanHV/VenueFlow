<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white dark:bg-slate-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VenueFlow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-slate-900 dark:text-white">
        <div class="flex min-h-full">
            <!-- Left Side: Content -->
            <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <div class="mb-10">
                        <a href="/" class="flex items-center gap-2 text-emerald-600">
                            <x-application-logo class="h-10 w-auto fill-current" />
                            <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">VenueFlow</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>

            <!-- Right Side: Image/Branding -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <img class="absolute inset-0 h-full w-full object-cover" src="https://images.unsplash.com/photo-1560439514-4e9645039924?q=80&w=2073&auto=format&fit=crop" alt="Background">
                <div class="absolute inset-0 bg-gradient-to-tr from-emerald-900/80 to-slate-900/40 mix-blend-multiply"></div>
                <div class="absolute bottom-0 left-0 p-20 text-white">
                    <h3 class="text-3xl font-bold">Manage your venues with ease.</h3>
                    <p class="mt-4 text-lg text-emerald-100">Streamline bookings, manage staff, and grow your business with VenueFlow.</p>
                </div>
            </div>
        </div>
    </body>
</html>
