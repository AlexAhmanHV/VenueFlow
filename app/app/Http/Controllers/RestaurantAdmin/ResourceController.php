<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\StoreResourceRequest;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $resources = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('restaurant-admin.resources.index', compact('restaurant', 'resources'));
    }

    public function store(StoreResourceRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        Resource::create([
            ...$request->validated(),
            'restaurant_id' => $restaurant->id,
            'active' => $request->boolean('active', true),
        ]);

        return back()->with('status', 'Resurs skapad.');
    }

    public function update(StoreResourceRequest $request, string $slug, Resource $resource)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($resource->restaurant_id === $restaurant->id, 404);

        $resource->update([
            ...$request->validated(),
            'active' => $request->boolean('active', true),
        ]);

        return back()->with('status', 'Resurs uppdaterad.');
    }

    public function destroy(Request $request, string $slug, Resource $resource)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($resource->restaurant_id === $restaurant->id, 404);

        $resource->delete();

        return back()->with('status', 'Resurs borttagen.');
    }
}
