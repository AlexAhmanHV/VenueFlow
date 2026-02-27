<x-public-layout :restaurant="$restaurant">
    <div class="mx-auto flex min-h-[80vh] max-w-5xl items-center px-4 py-16 sm:px-6 lg:px-8">
        <div class="w-full space-y-8 rounded-2xl border border-white/60 bg-white/80 p-8 text-center shadow-xl backdrop-blur-sm dark:border-gray-700 dark:bg-gray-900/80">
            <div>
                <p class="text-base font-semibold text-indigo-600 dark:text-indigo-400">Välkommen till</p>
                <h1 class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">{{ $restaurant->name }}</h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                    Boka aktivitet eller bord på några minuter. Du behöver inget konto.
                </p>
            </div>

            <div class="mx-auto grid w-full max-w-md grid-cols-1 gap-4">
                <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="rounded-lg bg-indigo-600 px-8 py-4 text-center text-lg font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Starta bokning
                </a>
                <a href="{{ route('public.menu', $restaurant->slug) }}" class="rounded-lg bg-white/90 px-8 py-4 text-center text-lg font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-white dark:bg-gray-800 dark:text-white dark:ring-gray-600">
                    Se menyn
                </a>
            </div>

            <div class="mx-auto max-w-md rounded-xl border border-gray-200/70 bg-white/70 p-6 text-left dark:border-gray-700 dark:bg-gray-800/70">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Snabbinfo</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Telefon</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $restaurant->phone ?? 'Ej angivet' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">E-post</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $restaurant->email ?? 'Ej angivet' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Tidszon</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $restaurant->timezone }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-public-layout>
