<x-public-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow dark:bg-gray-800">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Unlock Full Demo Access</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Public demo mode is active. Enter the full access key to unlock privileged demo accounts and routes.
            </p>

            @if ($errors->has('demo_access'))
                <div class="mt-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-800">
                    {{ $errors->first('demo_access') }}
                </div>
            @endif

            @if ($unlocked)
                <div class="mt-4 rounded-md border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-800">
                    Full demo access is already unlocked in this session.
                </div>
            @endif

            <form method="POST" action="{{ route('demo.access.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="access_key" class="block text-sm font-medium text-gray-900 dark:text-gray-100">Access key</label>
                    <input
                        id="access_key"
                        name="access_key"
                        type="password"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-md border-gray-300 py-2 text-gray-900 shadow-sm focus:ring-2 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                    <x-input-error :messages="$errors->get('access_key')" class="mt-2" />
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Unlock
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-sm">
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Back to login</a>
                @if ($unlocked)
                    <form method="POST" action="{{ route('demo.access.revoke') }}">
                        @csrf
                        <button type="submit" class="font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white">
                            Remove access
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-public-layout>
