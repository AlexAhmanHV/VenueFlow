<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\MoveBookingItemRequest;
use App\Http\Requests\RestaurantAdmin\StoreBookingNoteRequest;
use App\Http\Requests\RestaurantAdmin\UpdateBookingStatusRequest;
use App\Models\BookingItem;
use App\Models\BookingNote;
use App\Models\BookingStatusEvent;
use App\Models\GuestBooking;
use App\Services\MoveBookingItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly MoveBookingItem $moveBookingItem)
    {
    }

    public function index(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $restaurant);

        $bookings = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->with('bookingItems.resource')
            ->latest()
            ->paginate(20);

        return view('restaurant-admin.bookings.index', compact('restaurant', 'bookings'));
    }

    public function liveBoard(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $restaurant);

        $boardDate = $request->string('date')->toString()
            ?: Carbon::now($restaurant->timezone)->format('Y-m-d');
        $dayLocal = Carbon::parse($boardDate, $restaurant->timezone);
        $todayStart = $dayLocal->copy()->startOfDay()->utc();
        $todayEnd = $dayLocal->copy()->endOfDay()->utc();

        $bookings = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereHas('bookingItems', function ($q) use ($todayStart, $todayEnd) {
                $q->whereBetween('start_time', [$todayStart, $todayEnd]);
            })
            ->with(['bookingItems.resource', 'notes' => fn ($q) => $q->latest()->limit(3)->with('author')])
            ->orderBy('created_at')
            ->get();

        $resources = $restaurant->resources()->where('active', true)->orderBy('type')->orderBy('name')->get();

        $openingHour = $restaurant->openingHours()->where('weekday', (int) $dayLocal->isoWeekday())->first();
        $opensAt = $openingHour?->opens_at ? substr((string) $openingHour->opens_at, 0, 5) : '12:00';
        $closesAt = $openingHour?->closes_at ? substr((string) $openingHour->closes_at, 0, 5) : '23:00';
        $slotStep = max(15, (int) ($restaurant->setting?->slot_interval_minutes ?? 30));

        $cursor = Carbon::parse($dayLocal->format('Y-m-d').' '.$opensAt, $restaurant->timezone);
        $close = Carbon::parse($dayLocal->format('Y-m-d').' '.$closesAt, $restaurant->timezone);
        $slots = [];
        while ($cursor->lt($close)) {
            $slots[] = $cursor->format('Y-m-d H:i');
            $cursor->addMinutes($slotStep);
        }

        return view('restaurant-admin.bookings.live-board', compact('restaurant', 'bookings', 'resources', 'slots', 'boardDate', 'slotStep'));
    }

    public function show(Request $request, string $slug, GuestBooking $booking)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $booking);

        $booking->load(['bookingItems.resource', 'preorders.items.menuItem', 'statusEvents.actor']);

        return view('restaurant-admin.bookings.show', compact('restaurant', 'booking'));
    }

    public function updateStatus(UpdateBookingStatusRequest $request, string $slug, GuestBooking $booking)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('updateStatus', $booking);

        $from = $booking->status?->value;
        $to = BookingStatus::from($request->string('status')->toString());

        $booking->update([
            'status' => $to,
            'cancelled_at' => $to === BookingStatus::CANCELLED ? now('UTC') : null,
        ]);

        BookingStatusEvent::create([
            'guest_booking_id' => $booking->id,
            'from_status' => $from,
            'to_status' => $to->value,
            'actor_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Status uppdaterad.');
    }

    public function storeNote(StoreBookingNoteRequest $request, string $slug, GuestBooking $booking)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('updateStatus', $booking);

        abort_unless($booking->restaurant_id === $restaurant->id, 404);

        BookingNote::create([
            'guest_booking_id' => $booking->id,
            'author_user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        return back()->with('status', 'Notering sparad.');
    }

    public function moveItem(
        MoveBookingItemRequest $request,
        string $slug,
        GuestBooking $booking,
        BookingItem $item
    ): JsonResponse {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('updateStatus', $booking);

        abort_unless($booking->restaurant_id === $restaurant->id, 404);
        abort_unless($item->guest_booking_id === $booking->id, 404);

        $moved = $this->moveBookingItem->execute(
            restaurant: $restaurant,
            booking: $booking,
            item: $item,
            resourceId: (int) $request->integer('resource_id'),
            startTimeLocal: $request->string('start_time_local')->toString(),
            actorUserId: $request->user()->id,
        );

        return response()->json([
            'ok' => true,
            'message' => 'Bokningspost flyttad.',
            'item' => [
                'id' => $moved->id,
                'resource_name' => $moved->resource?->name,
                'start_local' => $moved->start_time->timezone($restaurant->timezone)->format('Y-m-d H:i'),
                'end_local' => $moved->end_time->timezone($restaurant->timezone)->format('Y-m-d H:i'),
            ],
        ]);
    }
}
