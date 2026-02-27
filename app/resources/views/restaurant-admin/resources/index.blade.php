<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Resurser</span>
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Add New Resource Form -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Skapa ny resurs</h3>
            </div>
            <div class="border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                <form method="POST" action="{{ route('restaurant.admin.resources.store', $restaurant->slug) }}" class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                    @csrf
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Namn</label>
                        <input type="text" name="name" id="name" placeholder="T.ex. Bord 1, Bana 2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Typ</label>
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                            @foreach(\App\Enums\ResourceType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <label for="capacity_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Kap.</label>
                        <input type="number" name="capacity_min" id="capacity_min" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="capacity_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Kap.</label>
                        <input type="number" name="capacity_max" id="capacity_max" value="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>

                    <div class="sm:col-span-6">
                        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Skapa resurs
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resources List -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="flow-root">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-0">Namn</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Typ</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Kapacitet</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Åtgärd</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @forelse($resources as $resource)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-0">{{ $resource->name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $resource->type->name }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $resource->capacity_min }} - {{ $resource->capacity_max }}</td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                            <form method="POST" action="{{ route('restaurant.admin.resources.destroy', [$restaurant->slug, $resource]) }}" onsubmit="return confirm('Är du säker på att du vill ta bort denna resurs?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">Ta bort</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    <h3 class="mt-2 text-sm font-medium">Inga resurser har skapats</h3>
                                                    <p class="mt-1 text-sm">Använd formuläret ovan för att lägga till den första.</p>
                                                </div>
                                            </div>
                                        </td>
      
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-restaurant-admin-layout>
