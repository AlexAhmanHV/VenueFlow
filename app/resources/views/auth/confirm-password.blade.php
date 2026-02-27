<x-public-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <div class="text-center">
                <a href="/" class="text-3xl font-bold text-gray-900 dark:text-white">VenueFlow</a>
                <h2 class="mt-4 text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">Bekräfta lösenord</h2>
            </div>
            
            <div class="mt-10 rounded-lg bg-white p-8 shadow dark:bg-gray-800">
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                    Detta är ett säkert område i applikationen. Vänligen bekräfta ditt lösenord för att fortsätta.
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Lösenord</label>
                        <div class="mt-2">
                             <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="block w-full rounded-md border-gray-300 py-1.5 text-gray-900 shadow-sm focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Bekräfta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-public-layout>
