<x-public-layout :restaurant="$restaurant">
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Ställ dig i kö</h1>
            <p class="mt-3 text-base text-gray-500 dark:text-gray-400">Inga tider tillgängliga? Vi kontaktar dig om en tid öppnar sig.</p>
        </div>

        @if($errors->any())
            <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.waitlist.store', $restaurant->slug) }}" class="mt-8 space-y-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="vf-label" for="customer_name">Namn</label>
                    <input id="customer_name" name="customer_name" type="text" class="vf-input mt-1 w-full" required
                           value="{{ old('customer_name') }}">
                </div>
                <div>
                    <label class="vf-label" for="party_size">Antal gäster</label>
                    <input id="party_size" name="party_size" type="number" min="1" max="50" class="vf-input mt-1 w-full" required
                           value="{{ old('party_size', 2) }}">
                </div>
                <div>
                    <label class="vf-label" for="email">E-post</label>
                    <input id="email" name="email" type="email" class="vf-input mt-1 w-full" required
                           value="{{ old('email') }}">
                </div>
                <div>
                    <label class="vf-label" for="phone">Telefon (valfritt)</label>
                    <input id="phone" name="phone" type="tel" class="vf-input mt-1 w-full"
                           value="{{ old('phone') }}">
                </div>
                <div>
                    <label class="vf-label" for="preferred_date">Önskat datum</label>
                    <input id="preferred_date" name="preferred_date" type="date" class="vf-input mt-1 w-full" required
                           min="{{ today()->toDateString() }}"
                           value="{{ old('preferred_date') }}">
                </div>
                <div>
                    <label class="vf-label" for="preferred_time">Önskad tid (valfritt)</label>
                    <input id="preferred_time" name="preferred_time" type="time" class="vf-input mt-1 w-full"
                           value="{{ old('preferred_time') }}">
                </div>
            </div>

            <div>
                <label class="vf-label" for="note">Meddelande (valfritt)</label>
                <textarea id="note" name="note" rows="3" class="vf-input mt-1 w-full resize-none"
                          maxlength="500">{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="vf-btn-primary w-full">Ställ mig i kö</button>
        </form>
    </div>
</x-public-layout>
