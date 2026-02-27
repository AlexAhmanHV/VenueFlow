<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Bokningar · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-4 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-slate-600">Överblick över alla bokningar.</p>
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.live', $restaurant->slug) }}">Live board</a>
        </div>

        @forelse($bookings as $booking)
            <article class="vf-card p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-lg font-semibold">{{ $booking->customer_name }} <span class="text-sm font-normal text-slate-500">({{ $booking->party_size }} pers)</span></p>
                        <p class="text-sm text-slate-500">{{ $booking->public_id }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $booking->status->value }}</span>
                        <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.show', [$restaurant->slug, $booking]) }}">Visa</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="vf-card p-8 text-center text-slate-500">Inga bokningar hittades.</div>
        @endforelse

        <div class="vf-card p-3">{{ $bookings->links() }}</div>
    </section>
</x-app-layout>



