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
