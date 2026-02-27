<x-public-layout :restaurant="$restaurant">
    <div>
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
                        <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white hover:bg-indigo-700">1</a>
                        <span class="absolute top-10 -left-2 w-max text-center text-sm font-medium text-indigo-600">Välj aktivitet</span>
                    </li>
                    <li class="relative flex-1 pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-indigo-600"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white">2</span>
                        <span class="absolute top-10 -left-2 w-max text-center text-sm font-medium text-indigo-600">Dina uppgifter</span>
                    </li>
                    <li class="relative flex-1">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">3</span>
                        <span class="absolute top-10 -left-3 w-max text-center text-sm text-gray-500">Bekräftelse</span>
                    </li>
                </ol>
            </nav>

            <!-- Main Grid -->
            <div class="mt-24 grid grid-cols-1 gap-x-12 gap-y-8 lg:grid-cols-3">
                
                <!-- Left Column: Form -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-white/60 bg-white/85 p-6 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-900/85">
                        @if($errors->any())
                            <div class="mb-6 rounded-md border border-red-300 bg-red-50 p-4 dark:border-red-600 dark:bg-red-900/30">
                                <p class="text-sm font-medium text-red-700 dark:text-red-200">{{ $errors->first() }}</p>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('public.booking.store', $restaurant->slug) }}" class="space-y-8">
                            @csrf
                            <!-- Contact Information -->
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Dina uppgifter</h2>
                                <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                    <div>
                                        <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Namn</label>
                                        <input type="text" id="customer_name" name="customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-post</label>
                                        <input type="email" id="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefon</label>
                                        <input type="text" id="phone" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    </div>
                                     <div>
                                        <label for="party_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Antal gäster</label>
                                        <input type="number" id="party_size" name="party_size" min="1" max="50" value="{{ $partySize }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anteckning (valfritt)</label>
                                        <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pre-order Section -->
                            <div class="space-y-4 border-t border-gray-200 pt-8 dark:border-gray-700">
                                <details class="group">
                                    <summary class="flex cursor-pointer items-center justify-between text-lg font-medium text-gray-900 dark:text-white">
                                        <span>Förbeställ mat (valfritt)</span>
                                        <span class="ml-4 flex-shrink-0 transition-transform group-open:rotate-180">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                    </summary>
                                    <div class="mt-6 space-y-6">
                                        <div class="space-y-4">
                                            @foreach($menuItems as $item)
                                                <div class="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                                                        <p class="text-sm text-gray-500">{{ number_format($item->price, 2, ',', ' ') }} kr</p>
                                                    </div>
                                                    <input type="number" min="0" name="preorder_items[{{ $item->id }}]" value="0" class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" placeholder="0">
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($hasTableBooking)
                                            <div>
                                                <label for="preorder_serve_time_local" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Önskad serveringstid</label>
                                                <input type="datetime-local" id="preorder_serve_time_local" name="preorder_serve_time_local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" min="{{ $serveTimeMin }}" max="{{ $serveTimeMax }}">
                                                <p class="mt-2 text-xs text-gray-500">Serveringstid måste vara mellan första bokningens start och två timmar efter.</p>
                                            </div>
                                        @else
                                            <p class="rounded-md bg-gray-100 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                Serveringstid kan väljas när minst ett bord är bokat.
                                            </p>
                                        @endif
                                        <div>
                                            <label for="preorder_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anteckning för förbeställning (valfritt)</label>
                                            <textarea id="preorder_note" name="preorder_note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                                        </div>
                                    </div>
                                </details>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-8 dark:border-gray-700">
                                 <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Slutför bokning
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Cart Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-12 rounded-lg border border-white/60 bg-white/90 p-6 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-900/90">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Din bokning</h2>
                        <div class="mt-4">
                            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($selectedItems as $item)
                                <li class="py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item['resource_name'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['start_time_local'] }} - {{ $item['end_time_local'] }}</p>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                             <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="w-full block text-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600">
                                Tillbaka och ändra aktiviteter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>

