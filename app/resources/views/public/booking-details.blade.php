<x-public-layout :restaurant="$restaurant">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center vf-reveal">
            <p class="font-display text-xs font-semibold uppercase tracking-[0.3em] text-red-500">Steg 2 av 3</p>
            <h1 class="font-display mt-2 text-4xl font-bold uppercase tracking-tight text-white sm:text-5xl">
                Dina uppgifter
            </h1>
            <p class="mt-2 text-sm text-white/50">{{ $restaurant->name }}</p>
        </div>

        {{-- Step indicator --}}
        <nav aria-label="Bokningssteg" class="mx-auto mt-10 max-w-xs">
            <ol class="flex items-center gap-0">
                <li>
                    <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="flex h-8 w-8 items-center justify-center border border-white/20 font-display text-sm font-bold text-white/50 transition hover:border-white/40">1</a>
                </li>
                <li class="mx-3 h-px flex-1 bg-red-700"></li>
                <li class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center bg-red-700 font-display text-sm font-bold text-white">2</span>
                    <span class="font-display text-xs font-semibold uppercase tracking-wider text-red-400">Uppgifter</span>
                </li>
                <li class="mx-3 h-px flex-1 bg-white/15"></li>
                <li class="flex h-8 w-8 items-center justify-center border border-white/20 font-display text-sm font-bold text-white/30">3</li>
            </ol>
        </nav>

        {{-- Main grid --}}
        <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-3">

            {{-- Left: form --}}
            <div class="lg:col-span-2">
                <div class="border border-white/10 bg-white/5 p-6 backdrop-blur-md sm:p-8">

                    @if($errors->any())
                        <div class="mb-6 border border-red-700/50 bg-red-900/30 p-4">
                            <p class="text-sm font-medium text-red-300">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.booking.store', $restaurant->slug) }}" class="space-y-8">
                        @csrf

                        {{-- Contact --}}
                        <div>
                            <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Kontaktuppgifter</h2>
                            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="customer_name" class="pub-label">Namn</label>
                                    <input type="text" id="customer_name" name="customer_name" class="pub-input mt-2 w-full" required value="{{ old('customer_name') }}">
                                </div>
                                <div>
                                    <label for="email" class="pub-label">E-post</label>
                                    <input type="email" id="email" name="email" class="pub-input mt-2 w-full" required value="{{ old('email') }}">
                                </div>
                                <div>
                                    <label for="phone" class="pub-label">Telefon (valfritt)</label>
                                    <input type="text" id="phone" name="phone" class="pub-input mt-2 w-full" value="{{ old('phone') }}">
                                </div>
                                <div>
                                    <label for="party_size" class="pub-label">Antal gäster</label>
                                    <input type="number" id="party_size" name="party_size" min="1" max="50" value="{{ old('party_size', $partySize) }}" class="pub-input mt-2 w-full" required>
                                </div>
                                @if(! config('demo.public_mode') || ! config('demo.restrict_public_notes'))
                                    <div class="sm:col-span-2">
                                        <label for="note" class="pub-label">Anteckning (valfritt)</label>
                                        <textarea id="note" name="note" rows="3" class="pub-input mt-2 w-full resize-none">{{ old('note') }}</textarea>
                                    </div>
                                @else
                                    <div class="sm:col-span-2 border border-white/10 bg-white/5 p-3 text-sm text-white/40">
                                        Fri text är avstängd i publik demo.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Pre-order --}}
                        @if(count($menuItems))
                        <div class="border-t border-white/10 pt-8">
                            <details class="group">
                                <summary class="flex cursor-pointer items-center justify-between">
                                    <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Förbeställ mat (valfritt)</h2>
                                    <svg class="h-5 w-5 text-white/40 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </summary>
                                <div class="mt-6 space-y-5">
                                    @foreach($menuItems as $item)
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold text-white">{{ $item->name }}</p>
                                                <p class="text-xs text-white/40">{{ number_format($item->price, 2, ',', ' ') }} kr</p>
                                            </div>
                                            <input type="number" min="0" name="preorder_items[{{ $item->id }}]" value="0" class="pub-input w-20 text-center" placeholder="0">
                                        </div>
                                    @endforeach

                                    @if($hasTableBooking)
                                        <div>
                                            <label for="preorder_serve_time_local" class="pub-label">Önskad serveringstid</label>
                                            <input type="datetime-local" id="preorder_serve_time_local" name="preorder_serve_time_local" class="pub-input mt-2 w-full" min="{{ $serveTimeMin }}" max="{{ $serveTimeMax }}">
                                        </div>
                                    @else
                                        <p class="border border-white/10 bg-white/5 p-4 text-sm text-white/40">
                                            Serveringstid kan väljas när minst ett bord är bokat.
                                        </p>
                                    @endif

                                    @if(! config('demo.public_mode') || ! config('demo.restrict_public_notes'))
                                        <div>
                                            <label for="preorder_note" class="pub-label">Anteckning för förbeställning</label>
                                            <textarea id="preorder_note" name="preorder_note" rows="2" class="pub-input mt-2 w-full resize-none"></textarea>
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                        @endif

                        <div class="border-t border-white/10 pt-8">
                            <button type="submit" class="pub-btn-primary w-full text-base">
                                Slutför bokning →
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right: summary --}}
            <div class="lg:col-span-1">
                <div class="sticky top-8 border border-white/10 bg-white/5 p-6 backdrop-blur-md">
                    <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Din bokning</h2>
                    <ul class="mt-5 divide-y divide-white/10">
                        @foreach($selectedItems as $item)
                        <li class="py-3">
                            <p class="text-sm font-semibold text-white">{{ $item['resource_name'] }}</p>
                            <p class="text-xs text-white/50">{{ $item['start_time_local'] }} – {{ $item['end_time_local'] }}</p>
                        </li>
                        @endforeach
                    </ul>
                    <div class="mt-6 border-t border-white/10 pt-5">
                        <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="pub-btn-secondary block w-full text-center text-xs">
                            ← Ändra aktiviteter
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-public-layout>
