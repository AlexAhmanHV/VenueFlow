<x-public-layout :restaurant="$restaurant">
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">

        <div class="text-center vf-reveal">
            <p class="font-display text-xs font-semibold uppercase tracking-[0.3em] text-red-500">Inga tider lediga?</p>
            <h1 class="font-display mt-2 text-4xl font-bold uppercase tracking-tight text-white sm:text-5xl">
                Ställ dig i kö
            </h1>
            <p class="mt-4 text-sm leading-relaxed text-white/50">
                Vi meddelar dig via e-post om en tid öppnar sig på ditt önskade datum.
            </p>
        </div>

        @if($errors->any())
            <div class="mt-8 border border-red-700/50 bg-red-900/30 p-4">
                <ul class="space-y-1 text-sm text-red-300">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.waitlist.store', $restaurant->slug) }}" class="mt-10 border border-white/10 bg-white/5 p-6 backdrop-blur-md sm:p-8">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="pub-label" for="customer_name">Namn</label>
                    <input id="customer_name" name="customer_name" type="text" class="pub-input mt-2 w-full" required value="{{ old('customer_name') }}">
                </div>
                <div>
                    <label class="pub-label" for="party_size">Antal gäster</label>
                    <input id="party_size" name="party_size" type="number" min="1" max="50" class="pub-input mt-2 w-full" required value="{{ old('party_size', 2) }}">
                </div>
                <div>
                    <label class="pub-label" for="email">E-post</label>
                    <input id="email" name="email" type="email" class="pub-input mt-2 w-full" required value="{{ old('email') }}">
                </div>
                <div>
                    <label class="pub-label" for="phone">Telefon (valfritt)</label>
                    <input id="phone" name="phone" type="tel" class="pub-input mt-2 w-full" value="{{ old('phone') }}">
                </div>
                <div>
                    <label class="pub-label" for="preferred_date">Önskat datum</label>
                    <input id="preferred_date" name="preferred_date" type="date" class="pub-input mt-2 w-full" required min="{{ today()->toDateString() }}" value="{{ old('preferred_date') }}">
                </div>
                <div>
                    <label class="pub-label" for="preferred_time">Önskad tid (valfritt)</label>
                    <input id="preferred_time" name="preferred_time" type="time" class="pub-input mt-2 w-full" value="{{ old('preferred_time') }}">
                </div>
            </div>

            <div class="mt-5">
                <label class="pub-label" for="note">Meddelande (valfritt)</label>
                <textarea id="note" name="note" rows="3" class="pub-input mt-2 w-full resize-none" maxlength="500">{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="pub-btn-primary mt-8 w-full">
                Ställ mig i kö →
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-white/30">
            <a href="{{ route('public.landing', $restaurant->slug) }}" class="transition hover:text-white/60">← Tillbaka</a>
        </p>
    </div>
</x-public-layout>
