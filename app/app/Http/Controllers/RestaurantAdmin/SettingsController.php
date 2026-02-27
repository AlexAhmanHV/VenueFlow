<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\UpdateRestaurantSettingsRequest;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $settings = $restaurant->setting;

        return view('restaurant-admin.settings.edit', compact('restaurant', 'settings'));
    }

    public function update(UpdateRestaurantSettingsRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $restaurant->setting()->updateOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'default_buffer_minutes' => $request->integer('default_buffer_minutes'),
                'cancellation_cutoff_minutes' => $request->filled('cancellation_cutoff_minutes')
                    ? $request->integer('cancellation_cutoff_minutes')
                    : null,
                'slot_interval_minutes' => $request->integer('slot_interval_minutes'),
                'max_simultaneous_bookings' => $request->filled('max_simultaneous_bookings')
                    ? $request->integer('max_simultaneous_bookings')
                    : null,
                'default_durations' => $request->input('default_durations'),
            ]
        );

        return back()->with('status', 'Inställningar sparade.');
    }
}
