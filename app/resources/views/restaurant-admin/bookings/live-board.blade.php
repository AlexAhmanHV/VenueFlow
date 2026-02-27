<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Live board · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        <div class="vf-card p-5">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold">Drag & drop-ombokning</h3>
                    <p class="text-sm text-slate-600">Dra en bokningspost till en ledig slot. Servern validerar konflikt i transaktion.</p>
                </div>
                <form method="GET" class="flex items-end gap-2">
                    <label class="text-sm">
                        Datum
                        <input type="date" name="date" value="{{ $boardDate }}" class="vf-input mt-1">
                    </label>
                    <button class="vf-btn-secondary">Visa</button>
                </form>
            </div>
        </div>

        <div id="move-status" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <div class="vf-card p-4 overflow-x-auto">
            <table class="min-w-[980px] w-full text-xs">
                <thead>
                    <tr>
                        <th class="px-2 py-2 text-left font-semibold text-slate-600">Resurs</th>
                        @foreach($slots as $slot)
                            <th class="px-2 py-2 text-center font-semibold text-slate-600">{{ \Carbon\Carbon::parse($slot, $restaurant->timezone)->format('H:i') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($resources as $resource)
                        <tr>
                            <td class="px-2 py-2 font-medium text-slate-700 whitespace-nowrap">{{ $resource->name }}</td>
                            @foreach($slots as $slot)
                                <td class="p-1">
                                    <button
                                        type="button"
                                        class="drop-slot h-10 w-full rounded-lg border border-slate-200 bg-slate-50/70 transition hover:border-emerald-400 hover:bg-emerald-50"
                                        data-resource-id="{{ $resource->id }}"
                                        data-start="{{ $slot }}"
                                        title="Släpp här"
                                    ></button>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="999" class="px-4 py-10 text-center text-slate-500">Inga aktiva resurser.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse($bookings as $booking)
                <article class="vf-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold">{{ $booking->customer_name }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->public_id }} · {{ $booking->party_size }} pers</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $booking->status->value }}</span>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach($booking->bookingItems as $item)
                            <div
                                draggable="true"
                                class="drag-item cursor-move rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                                data-booking-item-id="{{ $item->id }}"
                                data-booking-id="{{ $booking->id }}"
                                data-move-url="{{ route('restaurant.admin.bookings.move-item', [$restaurant->slug, $booking, $item]) }}"
                                data-duration="{{ $item->end_time->diffInMinutes($item->start_time) }}"
                            >
                                <p class="font-medium">{{ $item->resource->name }}</p>
                                <p class="text-slate-600">{{ $item->start_time->timezone($restaurant->timezone)->format('Y-m-d H:i') }}-{{ $item->end_time->timezone($restaurant->timezone)->format('H:i') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('restaurant.admin.bookings.status', [$restaurant->slug, $booking]) }}" class="mt-3 flex gap-2">
                        @csrf
                        <select name="status" class="vf-input">
                            <option value="CONFIRMED">CONFIRMED</option>
                            <option value="CHECKED_IN">CHECKED_IN</option>
                            <option value="NO_SHOW">NO_SHOW</option>
                        </select>
                        <button class="vf-btn-primary">Spara</button>
                    </form>
                </article>
            @empty
                <div class="vf-card p-8 text-center text-slate-500 lg:col-span-2">Inga bokningar på valt datum.</div>
            @endforelse
        </div>
    </section>

    <script>
        (() => {
            const statusBox = document.getElementById('move-status');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            let dragged = null;

            const showStatus = (ok, message) => {
                statusBox.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800', 'border-rose-200', 'bg-rose-50', 'text-rose-800');
                statusBox.classList.add(ok ? 'border-emerald-200' : 'border-rose-200', ok ? 'bg-emerald-50' : 'bg-rose-50', ok ? 'text-emerald-800' : 'text-rose-800');
                statusBox.textContent = message;
            };

            document.querySelectorAll('.drag-item').forEach((el) => {
                el.addEventListener('dragstart', () => {
                    dragged = el;
                    el.classList.add('opacity-60');
                });
                el.addEventListener('dragend', () => {
                    el.classList.remove('opacity-60');
                });
            });

            document.querySelectorAll('.drop-slot').forEach((slot) => {
                slot.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    slot.classList.add('ring-2', 'ring-emerald-400');
                });
                slot.addEventListener('dragleave', () => {
                    slot.classList.remove('ring-2', 'ring-emerald-400');
                });
                slot.addEventListener('drop', async (e) => {
                    e.preventDefault();
                    slot.classList.remove('ring-2', 'ring-emerald-400');
                    if (!dragged || !token) return;

                    const payload = new URLSearchParams();
                    payload.set('resource_id', slot.dataset.resourceId);
                    payload.set('start_time_local', slot.dataset.start);

                    try {
                        const response = await fetch(dragged.dataset.moveUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                            },
                            body: payload.toString()
                        });

                        const data = await response.json();
                        if (!response.ok) {
                            const error = data?.errors?.slot?.[0] || data?.errors?.resource_id?.[0] || data?.message || 'Kunde inte flytta bokningsposten.';
                            showStatus(false, error);
                            return;
                        }

                        showStatus(true, data.message || 'Bokningspost flyttad.');
                        window.location.reload();
                    } catch (_) {
                        showStatus(false, 'Nätverksfel vid flytt. Försök igen.');
                    }
                });
            });
        })();
    </script>
</x-app-layout>