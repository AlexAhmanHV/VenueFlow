<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Dashboard</span>
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <div class="flex items-center rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                <div class="rounded-full bg-indigo-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bokningar idag</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $bookingsToday }}</p>
                </div>
            </div>
            <div class="flex items-center rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                <div class="rounded-full bg-green-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Incheckade idag</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $checkedInToday }}</p>
                </div>
            </div>
            <div class="flex items-center rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                <div class="rounded-full bg-red-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No-show idag</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $noShowsToday }}</p>
                </div>
            </div>
            <div class="flex items-center rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                <div class="rounded-full bg-yellow-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2a3 3 0 00-3 3v2h3zM4 15a4 4 0 014-4h2a4 4 0 014 4v5H4v-5zM9 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Beläggning (est.)</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $occupancyRate }}%</p>
                </div>
            </div>
            <div class="flex items-center rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                <div class="rounded-full bg-purple-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Förbeställning idag</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ number_format((float)$preorderRevenueToday, 0, ',', ' ') }} kr</p>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Kommande bokningar</h3>
            <div class="mt-4 flow-root">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        @if($upcomingBookings->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-0">Kund</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Tid</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Status</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($upcomingBookings as $booking)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-0">{{ $booking->customer_name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $booking->created_at->timezone($restaurant->timezone)->format('Y-m-d H:i') }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-right">
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $booking->status->value }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                            <div class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium">Inga kommande bokningar</h3>
                                    <p class="mt-1 text-sm">När en ny bokning görs kommer den att visas här.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-restaurant-admin-layout>
