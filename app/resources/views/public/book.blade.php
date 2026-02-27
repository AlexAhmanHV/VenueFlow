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
                        <span class="absolute top-10 -left-2 w-max text-center text-sm font-medium text-indigo-600">Välj aktivitet</span>
                    </li>
                    <li class="relative flex-1 pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">2</span>
                        <span class="absolute top-10 -left-2 w-max text-center text-sm text-gray-500">Dina uppgifter</span>
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
                
                <!-- Left Column: Find and Add -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- Find Availability -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">1. Hitta lediga tider</h2>
                        <form method="GET" class="mt-4 grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-4">
                            <div class="sm:col-span-2">
                                <label for="resource_type_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Aktivitet</label>
                                <select id="resource_type_select" name="resource_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    @foreach(['GOLF' => 'Golf', 'SHUFFLEBOARD' => 'Shuffleboard', 'DART' => 'Dart', 'BILLIARDS' => 'Biljard', 'TABLE' => 'Bord'] as $type => $label)
                                        <option value="{{ $type }}" @selected($resourceType === $type)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="date_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Datum</label>
                                <input type="date" id="date_select" name="date" value="{{ $date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="party_size_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Antal gäster</label>
                                <input type="number" id="party_size_input" name="party_size" min="1" max="50" value="{{ $partySize }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="duration_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Längd (min)</label>
                                <input type="number" id="duration_input" name="duration_minutes" min="15" step="15" value="{{ $duration }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                            </div>
                            <div class="sm:col-span-4">
                                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Visa lediga tider
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Add Slot -->
                    @if(request()->has('resource_type'))
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">2. Välj en tid</h2>
                         <p class="mt-1 text-sm {{ count($slots) ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                            {{ count($slots) }} lediga tider hittades för ditt urval.
                        </p>
                        @if(count($slots))
                        <form method="POST" action="{{ route('public.booking.add-item', $restaurant->slug) }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="resource_type" value="{{ $resourceType }}">
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="party_size" value="{{ $partySize }}">
                            <input type="hidden" name="duration_minutes" value="{{ $duration }}">
                            
                            <div>
                                <label for="resource_slot" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Välj resurs och tid</label>
                                <select name="resource_slot" id="resource_slot" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                                    <option value="">Välj...</option>
                                    @foreach($slots as $slot)
                                        <option value="{{ $slot['resource_id'] }}|{{ $slot['start_time_local'] }}|{{ $slot['end_time_local'] }}"
                                            data-resource-id="{{ $slot['resource_id'] }}"
                                            data-start="{{ $slot['start_time_local'] }}"
                                            data-end="{{ $slot['end_time_local'] }}">
                                            {{ $slot['resource_name'] }} · {{ $slot['start_time_local'] }} - {{ $slot['end_time_local'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <input type="hidden" name="resource_id" id="resource_id">
                            <input type="hidden" name="start_time_local" id="start_time_local">
                            <input type="hidden" name="end_time_local" id="end_time_local">

                            <button type="submit" class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                Lägg till aktivitet i bokningen
                            </button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Right Column: Cart Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-12 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Din bokning</h2>
                        <div class="mt-4">
                            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($selectedItems as $index => $item)
                                <li class="flex items-center justify-between py-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item['resource_name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['start_time_local'] }} - {{ $item['end_time_local'] }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('public.booking.remove-item', $restaurant->slug) }}">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $index }}">
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300">Ta bort</button>
                                    </form>
                                </li>
                                @empty
                                <li class="py-3 text-sm text-gray-500">Du har inte lagt till något ännu.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="mt-6">
                             <a href="{{ route('public.booking.details', $restaurant->slug) }}" @class([
                                'w-full block text-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm',
                                'bg-indigo-600 hover:bg-indigo-500' => count($selectedItems),
                                'bg-gray-300 dark:bg-gray-600 cursor-not-allowed' => !count($selectedItems),
                             ])>
                                Fortsätt till dina uppgifter
                            </a>
                        </div>
                        <p class="mt-4 text-center text-xs text-gray-500">Tiderna hålls i 5 minuter.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const slotSelect = document.getElementById('resource_slot');
        slotSelect?.addEventListener('change', function (event) {
            const option = event.target.options[event.target.selectedIndex];
            document.getElementById('resource_id').value = option?.dataset?.resourceId || '';
            document.getElementById('start_time_local').value = option?.dataset?.start || '';
            document.getElementById('end_time_local').value = option?.dataset?.end || '';
        });
    </script>
</x-public-layout>

