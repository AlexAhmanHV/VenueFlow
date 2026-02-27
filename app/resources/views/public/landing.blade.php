<x-public-layout :restaurant="$restaurant">
    <div class="relative bg-gray-800">
        <div class="absolute inset-0">
            <img class="h-full w-full object-cover" src="https://placehold.co/1600x900/374151/FFFFFF?text={{ urlencode($restaurant->name) }}" alt="Image of {{ $restaurant->name }}">
            <div class="absolute inset-0 bg-gray-700 mix-blend-multiply" aria-hidden="true"></div>
        </div>
        <div class="relative flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-4xl space-y-8 text-center">
                <div>
                    <p class="text-base font-semibold text-indigo-400">Välkommen till</p>
                    <h1 class="mt-2 text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $restaurant->name }}</h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-200">
                        Boka aktivitet eller bord på några minuter. Du behöver inget konto.
                    </p>
                </div>
                
                <div class="mx-auto grid w-full max-w-md grid-cols-1 gap-6">
                     <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="rounded-lg bg-indigo-600 px-8 py-4 text-center text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                        Starta bokning
                    </a>
                    <a href="{{ route('public.menu', $restaurant->slug) }}" class="rounded-lg bg-white/10 px-8 py-4 text-center text-lg font-semibold text-white ring-1 ring-inset ring-white/20 hover:ring-white/40">
                        Se menyn
                    </a>
                </div>

                <div class="mx-auto max-w-md rounded-lg border border-white/10 bg-black/20 p-6 backdrop-blur-sm">
                    <h2 class="text-base font-semibold leading-7 text-white">Snabbinfo</h2>
                     <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-300">Telefon</dt>
                            <dd class="font-medium text-white">{{ $restaurant->phone ?? 'Ej angivet' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-300">E-post</dt>
                            <dd class="font-medium text-white">{{ $restaurant->email ?? 'Ej angivet' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-300">Tidszon</dt>
                            <dd class="font-medium text-white">{{ $restaurant->timezone }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
