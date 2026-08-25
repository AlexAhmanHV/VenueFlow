<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\UpdateFloorPlanRequest;
use App\Models\Resource;
use Illuminate\Http\Request;

class FloorPlanController extends Controller
{
    public function edit(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $resources = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('restaurant-admin.floor-plan.edit', compact('restaurant', 'resources'));
    }

    public function update(UpdateFloorPlanRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        foreach ($request->validated('positions') as $position) {
            Resource::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('id', $position['resource_id'])
                ->update([
                    'position_x' => $position['x'],
                    'position_y' => $position['y'],
                ]);
        }

        return back()->with('status', 'Golvplan sparad.');
    }
}
