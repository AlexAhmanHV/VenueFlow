<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $restaurant);

        $tz = $restaurant->timezone;
        $range = (int) $request->integer('range', 30);
        $range = in_array($range, [7, 30, 90, 365]) ? $range : 30;

        $from = Carbon::now($tz)->subDays($range)->startOfDay()->utc();
        $to   = Carbon::now($tz)->endOfDay()->utc();

        $bookings = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalBookings    = $bookings->count();
        $confirmedCount   = $bookings->where('status', BookingStatus::CONFIRMED)->count();
        $cancelledCount   = $bookings->where('status', BookingStatus::CANCELLED)->count();
        $noShowCount      = $bookings->where('status', BookingStatus::NO_SHOW)->count();
        $checkedInCount   = $bookings->where('status', BookingStatus::CHECKED_IN)->count();
        $totalGuests      = $bookings->sum('party_size');

        // Bookings per day (local date)
        $byDay = $bookings->groupBy(fn ($b) =>
            Carbon::parse($b->created_at)->timezone($tz)->format('Y-m-d')
        )->map->count()->sortKeys();

        // Peak hour distribution
        $items = BookingItem::query()
            ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
            ->where('guest_bookings.restaurant_id', $restaurant->id)
            ->whereBetween('booking_items.start_time', [$from, $to])
            ->whereNull('booking_items.deleted_at')
            ->pluck('booking_items.start_time');

        $byHour = $items->groupBy(fn ($t) =>
            Carbon::parse($t)->timezone($tz)->hour
        )->map->count()->sortKeys();

        return view('restaurant-admin.analytics.index', compact(
            'restaurant',
            'range',
            'totalBookings',
            'confirmedCount',
            'cancelledCount',
            'noShowCount',
            'checkedInCount',
            'totalGuests',
            'byDay',
            'byHour',
        ));
    }
}
