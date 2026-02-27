<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\GuestBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $tz = $restaurant->timezone;
        $nowLocal = Carbon::now($tz);
        $windowStartUtc = $nowLocal->copy()->subHours(1)->utc();
        $windowEndUtc = $nowLocal->copy()->addHours(2)->utc();

        $bookings = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [BookingStatus::CONFIRMED->value, BookingStatus::CHECKED_IN->value])
            ->whereHas('bookingItems', function ($q) use ($windowStartUtc, $windowEndUtc) {
                $q->where('end_time', '>=', $windowStartUtc)
                    ->where('start_time', '<=', $windowEndUtc);
            })
            ->with('bookingItems.resource')
            ->orderBy('created_at')
            ->get()
            ->map(function (GuestBooking $booking) use ($tz, $nowLocal) {
                $start = $booking->bookingItems->sortBy('start_time')->first()?->start_time?->timezone($tz);
                $booking->setAttribute('first_start_local', $start);
                $booking->setAttribute('is_late', $booking->status->value === BookingStatus::CONFIRMED->value
                    && $start
                    && $start->lt($nowLocal));

                return $booking;
            });

        return view('restaurant-admin.operations.index', compact('restaurant', 'bookings', 'nowLocal'));
    }
}
