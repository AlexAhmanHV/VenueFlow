<x-public-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md">
             <div class="text-center">
                <a href="/" class="text-3xl font-bold text-gray-900 dark:text-white">VenueFlow</a>
                <h2 class="mt-4 text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">Verifiera din e-postadress</h2>
            </div>

            <div class="mt-10 rounded-lg bg-white p-8 shadow dark:bg-gray-800">
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                    Tack för att du registrerade dig! Innan du kan börja, kan du verifiera din e-postadress genom att klicka på länken vi precis skickade till dig? Om du inte fick mailet, skickar vi gärna ett nytt.
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <p class="font-medium text-sm text-green-600 dark:text-green-300">
                            En ny verifieringslänk har skickats till den e-postadress du angav vid registreringen.
                        </p>
                    </div>
                @endif

                <div class="mt-6 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Skicka verifieringslänk igen
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-gray-900 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-400">
                            Logga ut
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
