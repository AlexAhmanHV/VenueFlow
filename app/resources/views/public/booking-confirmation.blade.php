<x-public-layout :restaurant="$restaurant">
    <div class="bg-gray-100 dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Boka på {{ $restaurant->name }}</h1>
            </div>

            <!-- Step Indicator -->
            <nav aria-label="Progress" class="mx-auto mt-8 max-w-2xl">
                <ol role="list" class="flex items-center">
                    <li class="relative flex-1 pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-indigo-600"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white">1</span>
                    </li>
                    <li class="relative flex-1 pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-indigo-600"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white">2</span>
                    </li>
                    <li class="relative flex-1">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-indigo-600"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white">3</span>
                    </li>
                </ol>
            </nav>

            <!-- Main Content -->
            <div class="mt-16 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Bokning bekräftad!</h2>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300">Din bokning är registrerad och klar. En bekräftelse har skickats till din e-post.</p>
            </div>

            <div class="mx-auto mt-8 max-w-2xl">
                 <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">Bokningsinformation</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Boknings-ID</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $booking->public_id }}</dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Namn</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $booking->customer_name }}</dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">E-post</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $booking->email }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Status</dt>
                             <dd class="mt-1">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-100 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/50 dark:text-green-300 dark:ring-green-700/50">
                                    {{ $booking->status->value }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <h4 class="text-base font-medium text-gray-900 dark:text-white">Bokade tider</h4>
                        <ul role="list" class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                           @foreach($booking->bookingItems as $item)
                                <li class="flex items-center justify-between py-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->resource->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->start_time->timezone($restaurant->timezone)->format('Y-m-d H:i') }} - {{ $item->end_time->timezone($restaurant->timezone)->format('H:i') }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mt-8 flex justify-center gap-4">
                     <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Gör en ny bokning
                    </a>
                    <a href="{{ route('public.landing', $restaurant->slug) }}" class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600">
                        Till startsidan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
