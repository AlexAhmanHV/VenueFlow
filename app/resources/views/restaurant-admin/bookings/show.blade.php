<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Bokning {{ $booking->public_id }}</h2></x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr]">
            <div class="vf-card p-6 space-y-4">
            <h3 class="text-lg font-semibold">Kunduppgifter</h3>
            <div class="grid gap-2 text-sm">
                <p><span class="font-semibold">Namn:</span> {{ $booking->customer_name }}</p>
                <p><span class="font-semibold">E-post:</span> {{ $booking->email }}</p>
                <p><span class="font-semibold">Status:</span> {{ $booking->status->value }}</p>
            </div>

            <div>
                <h4 class="mb-2 font-semibold">Bokade poster</h4>
                <div class="space-y-2">
                    @foreach($booking->bookingItems as $item)
                        <div class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <p class="font-medium">{{ $item->resource->name }}</p>
                            <p class="text-slate-600">{{ $item->start_time->timezone($restaurant->timezone)->format('Y-m-d H:i') }} - {{ $item->end_time->timezone($restaurant->timezone)->format('H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            </div>

            <div class="vf-card p-6">
            <h3 class="text-lg font-semibold">Uppdatera status</h3>
            <form method="POST" action="{{ route('restaurant.admin.bookings.status', [$restaurant->slug, $booking]) }}" class="mt-4 flex gap-3">
                @csrf
                <select name="status" class="vf-input">
                    <option value="CONFIRMED">CONFIRMED</option>
                    <option value="CHECKED_IN">CHECKED_IN</option>
                    <option value="NO_SHOW">NO_SHOW</option>
                </select>
                <button class="vf-btn-primary">Spara</button>
            </form>

            <h4 class="mt-6 font-semibold">Senaste händelser</h4>
            <div class="mt-2 space-y-2 text-sm">
                @forelse($booking->statusEvents as $event)
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <span class="font-medium">{{ $event->to_status }}</span>
                        <span class="text-slate-500">· {{ $event->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @empty
                    <p class="text-slate-500">Inga statushändelser ännu.</p>
                @endforelse
            </div>
        </div>
        </div>
    </section>
</x-app-layout>



