<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $tz = $restaurant->timezone;
        $dayStart = Carbon::now($tz)->startOfDay()->utc();
        $dayEnd = Carbon::now($tz)->endOfDay()->utc();

        $upcomingBookings = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'CONFIRMED')
            ->latest()
            ->limit(10)
            ->get();

        $bookingsToday = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->count();

        $noShowsToday = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', BookingStatus::NO_SHOW->value)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->count();

        $checkedInToday = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', BookingStatus::CHECKED_IN->value)
            ->whereBetween('updated_at', [$dayStart, $dayEnd])
            ->count();

        $preorderRevenueToday = DB::table('preorder_items')
            ->join('preorders', 'preorders.id', '=', 'preorder_items.preorder_id')
            ->join('guest_bookings', 'guest_bookings.id', '=', 'preorders.guest_booking_id')
            ->where('guest_bookings.restaurant_id', $restaurant->id)
            ->whereBetween('guest_bookings.created_at', [$dayStart, $dayEnd])
            ->selectRaw('COALESCE(SUM(preorder_items.qty * preorder_items.price_each),0) AS total')
            ->value('total');

        $bookedMinutesToday = BookingItem::query()
            ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
            ->where('guest_bookings.restaurant_id', $restaurant->id)
            ->where('guest_bookings.status', '!=', BookingStatus::CANCELLED->value)
            ->whereBetween('booking_items.start_time', [$dayStart, $dayEnd])
            ->selectRaw('COALESCE(SUM(EXTRACT(EPOCH FROM (booking_items.end_time - booking_items.start_time))/60),0) AS minutes')
            ->value('minutes');

        $weekday = Carbon::now($tz)->isoWeekday();
        $opening = $restaurant->openingHours()->where('weekday', $weekday)->first();
        $openMinutes = 0;
        if ($opening) {
            $opens = Carbon::parse($opening->opens_at, $tz);
            $closes = Carbon::parse($opening->closes_at, $tz);
            $openMinutes = max(0, $closes->diffInMinutes($opens));
        }
        $resourceCount = $restaurant->resources()->where('active', true)->count();
        $capacityMinutes = max(1, $openMinutes * max(1, $resourceCount));
        $occupancyRate = round(((float) $bookedMinutesToday / $capacityMinutes) * 100, 1);

        return view('restaurant-admin.dashboard', compact(
            'restaurant',
            'upcomingBookings',
            'bookingsToday',
            'noShowsToday',
            'checkedInToday',
            'preorderRevenueToday',
            'occupancyRate'
        ));
    }
}
