<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ $restaurant->name }} &middot; <span class="text-gray-500">Personal</span>
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Add New Staff Form -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Lägg till personal</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bjud in en ny användare som anställd eller manager.</p>
            </div>
            <div class="border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800/50">
                <form method="POST" action="{{ route('restaurant.admin.staff.store', $restaurant->slug) }}" class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                    @csrf
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Namn</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-post</label>
                        <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                    </div>
                    <div class="sm:col-span-1">
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Roll</label>
                        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                           @foreach(\App\Enums\StaffRole::cases() as $role)
                                <option value="{{ $role->value }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-1 flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Lägg till
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Staff List -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="flow-root">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-0">Namn</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">E-post</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Roll</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Åtgärd</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                @forelse($memberships as $membership)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-0">{{ $membership->user->name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $membership->user->email }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium capitalize
                                                {{ $membership->staff_role === \App\Enums\StaffRole::MANAGER ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ strtolower($membership->staff_role->value ?? $membership->role->value) }}
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                            <form method="POST" action="{{ route('restaurant.admin.staff.destroy', [$restaurant->slug, $membership]) }}" onsubmit="return confirm('Är du säker på att du vill ta bort denna personal?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" {{ $membership->user_id === auth()->id() ? 'disabled' : '' }}>
                                                    {{ $membership->user_id === auth()->id() ? 'Kan ej ta bort dig själv' : 'Ta bort' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    <h3 class="mt-2 text-sm font-medium">Ingen personal tillagd</h3>
                                                    <p class="mt-1 text-sm">Använd formuläret ovan för att bjuda in den första.</p>
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
