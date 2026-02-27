<x-public-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <div class="text-center">
                <a href="/" class="text-3xl font-bold text-gray-900 dark:text-white">VenueFlow</a>
                <h2 class="mt-4 text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">Logga in på ditt konto</h2>
            </div>
            
            <div class="mt-10 rounded-lg bg-white p-8 shadow dark:bg-gray-800">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">E-postadress</label>
                        <div class="mt-2">
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                                   class="block w-full rounded-md border-gray-300 py-1.5 text-gray-900 shadow-sm focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-200">Lösenord</label>
                            @if (Route::has('password.request'))
                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Glömt lösenord?</a>
                                </div>
                            @endif
                        </div>
                        <div class="mt-2">
                             <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="block w-full rounded-md border-gray-300 py-1.5 text-gray-900 shadow-sm focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <label for="remember_me" class="ml-3 block text-sm leading-6 text-gray-900 dark:text-gray-200">Kom ihåg mig</label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Logga in
                        </button>
                    </div>
                </form>

                <p class="mt-10 text-center text-sm text-gray-500">
                    Inte medlem?
                    <a href="{{ route('register') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Registrera dig här</a>
                </p>
            </div>
        </div>
    </div>
</x-public-layout>
