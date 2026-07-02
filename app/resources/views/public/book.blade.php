<x-public-layout :restaurant="$restaurant">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center vf-reveal">
            <p class="font-display text-xs font-semibold uppercase tracking-[0.3em] text-red-500">Steg 1 av 3</p>
            <h1 class="font-display mt-2 text-4xl font-bold uppercase tracking-tight text-white sm:text-5xl">
                Välj aktivitet
            </h1>
            <p class="mt-2 text-sm text-white/50">{{ $restaurant->name }}</p>
        </div>

        {{-- Step indicator --}}
        <nav aria-label="Bokningssteg" class="mx-auto mt-10 max-w-xs">
            <ol class="flex items-center gap-0">
                <li class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center bg-red-700 font-display text-sm font-bold text-white">1</span>
                    <span class="font-display text-xs font-semibold uppercase tracking-wider text-red-400">Aktivitet</span>
                </li>
                <li class="mx-3 h-px flex-1 bg-white/15"></li>
                <li class="flex h-8 w-8 items-center justify-center border border-white/20 font-display text-sm font-bold text-white/30">2</li>
                <li class="mx-3 h-px flex-1 bg-white/15"></li>
                <li class="flex h-8 w-8 items-center justify-center border border-white/20 font-display text-sm font-bold text-white/30">3</li>
            </ol>
        </nav>

        {{-- Main grid --}}
        <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-3">

            {{-- Left: find + add --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Find availability --}}
                <div class="border border-white/10 bg-white/5 p-6 backdrop-blur-md">
                    <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Hitta lediga tider</h2>
                    <form method="GET" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="resource_type_select" class="block text-xs font-semibold uppercase tracking-widest text-white/50">Aktivitet</label>
                            <select id="resource_type_select" name="resource_type" class="pub-input mt-2 w-full">
                                @foreach(['GOLF' => 'Golf', 'SHUFFLEBOARD' => 'Shuffleboard', 'DART' => 'Dart', 'BILLIARDS' => 'Biljard', 'TABLE' => 'Bord'] as $type => $label)
                                    <option value="{{ $type }}" @selected($resourceType === $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_select" class="block text-xs font-semibold uppercase tracking-widest text-white/50">Datum</label>
                            <input type="date" id="date_select" name="date" value="{{ $date }}" class="pub-input mt-2 w-full" required>
                        </div>
                        <div>
                            <label for="party_size_input" class="block text-xs font-semibold uppercase tracking-widest text-white/50">Antal gäster</label>
                            <input type="number" id="party_size_input" name="party_size" min="1" max="50" value="{{ $partySize }}" class="pub-input mt-2 w-full" required>
                        </div>
                        <div>
                            <label for="duration_input" class="block text-xs font-semibold uppercase tracking-widest text-white/50">Längd (min)</label>
                            <input type="number" id="duration_input" name="duration_minutes" min="15" step="15" value="{{ $duration }}" class="pub-input mt-2 w-full" required>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="pub-btn-primary w-full">Visa lediga tider</button>
                        </div>
                    </form>
                </div>

                {{-- Slot picker --}}
                @if(request()->has('resource_type'))
                <div class="border border-white/10 bg-white/5 p-6 backdrop-blur-md">
                    <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Välj en tid</h2>
                    <p class="mt-1 text-sm {{ count($slots) ? 'text-green-400' : 'text-amber-400' }}">
                        {{ count($slots) }} lediga tider hittades.
                    </p>
                    @if(count($slots))
                    <form method="POST" action="{{ route('public.booking.add-item', $restaurant->slug) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="resource_type" value="{{ $resourceType }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="party_size" value="{{ $partySize }}">
                        <input type="hidden" name="duration_minutes" value="{{ $duration }}">
                        <div>
                            <label for="resource_slot" class="block text-xs font-semibold uppercase tracking-widest text-white/50">Resurs och tid</label>
                            <select name="resource_slot" id="resource_slot" class="pub-input mt-2 w-full" required>
                                <option value="">Välj...</option>
                                @foreach($slots as $slot)
                                    <option
                                        value="{{ $slot['resource_id'] }}|{{ $slot['start_time_local'] }}|{{ $slot['end_time_local'] }}"
                                        data-resource-id="{{ $slot['resource_id'] }}"
                                        data-start="{{ $slot['start_time_local'] }}"
                                        data-end="{{ $slot['end_time_local'] }}"
                                    >
                                        {{ $slot['resource_name'] }} · {{ $slot['start_time_local'] }} – {{ $slot['end_time_local'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="resource_id" id="resource_id">
                        <input type="hidden" name="start_time_local" id="start_time_local">
                        <input type="hidden" name="end_time_local" id="end_time_local">
                        <button type="submit" class="pub-btn-secondary w-full">Lägg till i bokningen</button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            {{-- Right: cart --}}
            <div class="lg:col-span-1">
                <div class="sticky top-8 border border-white/10 bg-white/5 p-6 backdrop-blur-md">
                    <h2 class="font-display text-lg font-semibold uppercase tracking-wide text-white">Din bokning</h2>
                    <ul class="mt-5 divide-y divide-white/10">
                        @forelse($selectedItems as $index => $item)
                        <li class="flex items-start justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $item['resource_name'] }}</p>
                                <p class="text-xs text-white/50">{{ $item['start_time_local'] }} – {{ $item['end_time_local'] }}</p>
                            </div>
                            <form method="POST" action="{{ route('public.booking.remove-item', $restaurant->slug) }}">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <button type="submit" class="text-xs font-semibold text-red-500 transition hover:text-red-400">Ta bort</button>
                            </form>
                        </li>
                        @empty
                        <li class="py-6 text-center text-sm text-white/30">Inga aktiviteter valda ännu.</li>
                        @endforelse
                    </ul>
                    <div class="mt-6">
                        @if(count($selectedItems))
                            <a href="{{ route('public.booking.details', $restaurant->slug) }}" class="pub-btn-primary block w-full text-center">
                                Fortsätt →
                            </a>
                        @else
                            <span class="block w-full cursor-not-allowed bg-white/10 px-4 py-3 text-center font-display text-sm font-semibold uppercase tracking-widest text-white/30">
                                Välj en aktivitet
                            </span>
                        @endif
                    </div>
                    <p class="mt-3 text-center text-xs text-white/25">Tiderna hålls i 5 minuter.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const slotSelect = document.getElementById('resource_slot');
        slotSelect?.addEventListener('change', function (e) {
            const opt = e.target.options[e.target.selectedIndex];
            document.getElementById('resource_id').value = opt?.dataset?.resourceId ?? '';
            document.getElementById('start_time_local').value = opt?.dataset?.start ?? '';
            document.getElementById('end_time_local').value = opt?.dataset?.end ?? '';
        });
    </script>
</x-public-layout>
