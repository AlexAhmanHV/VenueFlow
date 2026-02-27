<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\StoreBlackoutDateRequest;
use App\Http\Requests\RestaurantAdmin\StoreOpeningHourRequest;
use App\Models\BlackoutDate;
use App\Models\OpeningHour;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $openingHours = OpeningHour::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('weekday')
            ->get();

        $blackoutDates = BlackoutDate::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('date')
            ->get();

        return view('restaurant-admin.schedule.index', compact('restaurant', 'openingHours', 'blackoutDates'));
    }

    public function storeOpening(StoreOpeningHourRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        OpeningHour::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'weekday' => $request->integer('weekday'),
            ],
            [
                'opens_at' => $request->string('opens_at'),
                'closes_at' => $request->string('closes_at'),
            ]
        );

        return back()->with('status', 'Öppettid sparad.');
    }

    public function storeBlackout(StoreBlackoutDateRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        BlackoutDate::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'date' => $request->string('date'),
            ],
            [
                'reason' => $request->string('reason')->toString() ?: null,
            ]
        );

        return back()->with('status', 'Blackout-datum sparat.');
    }

    public function destroyBlackout(Request $request, string $slug, BlackoutDate $blackout)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($blackout->restaurant_id === $restaurant->id, 404);

        $blackout->delete();

        return back()->with('status', 'Blackout-datum borttaget.');
    }
}
