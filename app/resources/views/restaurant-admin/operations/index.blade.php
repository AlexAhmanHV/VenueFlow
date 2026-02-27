<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Driftvy</span>
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Visar bokningar inom 2 timmar + sena ankomster. Uppdaterad: <span class="font-semibold">{{ $nowLocal->format('Y-m-d H:i') }}</span>
            </p>
            <a class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200" href="{{ route('restaurant.admin.bookings.live', $restaurant->slug) }}">
                Gå till Live Board &rarr;
            </a>
        </div>

        @if($bookings->count() > 0)
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($bookings as $booking)
                    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->public_id }} &middot; {{ $booking->party_size }} personer</p>
                                </div>
                                <div class="flex flex-shrink-0 items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $booking->status->value }}</span>
                                    @if($booking->is_late)
                                        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Sen</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    @foreach($booking->bookingItems as $item)
                                        <li class="flex items-center gap-3">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>{{ $item->resource->name }}: <span class="font-medium text-gray-800 dark:text-gray-200">{{ $item->start_time->timezone($restaurant->timezone)->format('H:i') }} - {{ $item->end_time->timezone($restaurant->timezone)->format('H:i') }}</span></span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 dark:bg-gray-800/50">
                            <form method="POST" action="{{ route('restaurant.admin.bookings.status', [$restaurant->slug, $booking]) }}" class="flex items-center gap-3">
                                @csrf
                                <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    <option value="CONFIRMED" @if($booking->status->value === 'CONFIRMED') selected @endif>Confirmed</option>
                                    <option value="CHECKED_IN" @if($booking->status->value === 'CHECKED_IN') selected @endif>Checked In</option>
                                    <option value="NO_SHOW" @if($booking->status->value === 'NO_SHOW') selected @endif>No Show</option>
                                </select>
                                <button class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Spara
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
                <div class="text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-medium">Inga bokningar i driftfönstret</h3>
                    <p class="mt-1 text-sm">Allt är lugnt för tillfället.</p>
                </div>
            </div>
        @endif
    </div>
</x-restaurant-admin-layout>
