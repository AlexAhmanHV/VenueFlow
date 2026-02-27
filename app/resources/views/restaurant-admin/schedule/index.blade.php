<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Schema</span>
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Opening Hours Section -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Öppettider</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hantera veckans standardöppettider.</p>
            </div>
            <div class="border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                <form method="POST" action="{{ route('restaurant.admin.schedule.opening.store', $restaurant->slug) }}" class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-4">
                    @csrf
                    <div class="sm:col-span-1">
                        <label for="weekday" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Veckodag (1-7)</label>
                        <input type="number" name="weekday" id="weekday" min="1" max="7" placeholder="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                    <div class="sm:col-span-1">
                        <label for="opens_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Öppnar</label>
                        <input type="time" name="opens_at" id="opens_at" placeholder="12:00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                    <div class="sm:col-span-1">
                        <label for="closes_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stänger</label>
                        <input type="time" name="closes_at" id="closes_at" placeholder="23:00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Spara</button>
                    </div>
                </form>
            </div>
            <div class="flow-root p-6">
                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($openingHours->sortBy('weekday') as $row)
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dag {{ $row->weekday }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ \Carbon\Carbon::parse($row->opens_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($row->closes_at)->format('H:i') }}</dd>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">Inga öppettider har registrerats.</div>
                    @endforelse
                </dl>
            </div>
        </div>

        <!-- Blackout Dates Section -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Blackout-datum</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lägg till datum då restaurangen är helt stängd.</p>
            </div>
            <div class="border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                <form method="POST" action="{{ route('restaurant.admin.schedule.blackout.store', $restaurant->slug) }}" class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    @csrf
                    <div class="sm:col-span-1">
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Datum</label>
                        <input type="date" name="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                     <div class="sm:col-span-1">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Orsak (valfritt)</label>
                        <input type="text" name="reason" id="reason" placeholder="T.ex. personalfest" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Lägg till</button>
                    </div>
                </form>
            </div>
            <div class="flow-root p-6">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($blackoutDates as $row)
                        <li class="flex items-center justify-between py-4">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $row->date->format('Y-m-d') }}</p>
                                @if($row->reason)
                                    <p class="text-gray-500 dark:text-gray-400">{{ $row->reason }}</p>
                                @endif
                            </div>
                             <form method="POST" action="{{ route('restaurant.admin.schedule.blackout.destroy', [$restaurant->slug, $row]) }}" onsubmit="return confirm('Är du säker?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600">Ta bort</button>
                            </form>
                        </li>
                    @empty
                         <li class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">Inga blackout-datum har registrerats.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-restaurant-admin-layout>
