<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Inställningar</span>
        </h2>
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('restaurant.admin.settings.update', $restaurant->slug) }}" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bokningsregler</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Styr hur slot-generering och bokningskapacitet fungerar för restaurangen.</p>
            </div>
            
            <div class="border-t border-gray-200 p-6 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    @csrf
                    <div class="sm:col-span-3">
                        <label for="default_buffer_minutes" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Buffert före/efter (min)</label>
                        <input type="number" name="default_buffer_minutes" id="default_buffer_minutes" value="{{ old('default_buffer_minutes', $settings->default_buffer_minutes) }}" min="0" max="120" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <div class="sm:col-span-3">
                        <label for="cancellation_cutoff_minutes" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Avbokningsgräns (min)</label>
                        <input type="number" name="cancellation_cutoff_minutes" id="cancellation_cutoff_minutes" value="{{ old('cancellation_cutoff_minutes', $settings->cancellation_cutoff_minutes) }}" min="0" max="10080" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <div class="sm:col-span-3">
                        <label for="slot_interval_minutes" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Slot-steg (min)</label>
                        <select name="slot_interval_minutes" id="slot_interval_minutes" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                            @foreach([5, 10, 15, 20, 30, 60] as $interval)
                                <option value="{{ $interval }}" @selected((int)old('slot_interval_minutes', $settings->slot_interval_minutes) === $interval)>{{ $interval }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="max_simultaneous_bookings" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Max samtidiga bokningar</label>
                        <input type="number" name="max_simultaneous_bookings" id="max_simultaneous_bookings" value="{{ old('max_simultaneous_bookings', $settings->max_simultaneous_bookings) }}" min="1" max="500" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    </div>

                    <div class="col-span-full">
                        <div class="mt-2 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                             <h4 class="text-base font-semibold text-gray-900 dark:text-white">Standardlängd per aktivitet (min)</h4>
                             @php($durations = $settings->default_durations ?? [])
                             <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-6 sm:grid-cols-5">
                                 @foreach(\App\Enums\ResourceType::cases() as $type)
                                    <div>
                                        <label for="duration_{{ $type->value }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $type->name }}</label>
                                        <input type="number" id="duration_{{ $type->value }}" name="default_durations[{{ $type->value }}]" min="15" max="300" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" value="{{ old('default_durations.'.$type->value, $durations[$type->value] ?? 60) }}" required>
                                    </div>
                                 @endforeach
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-x-6 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50">
                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Spara inställningar
                </button>
            </div>
        </form>
    </div>
</x-restaurant-admin-layout>
