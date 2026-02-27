<?php

namespace App\Http\Controllers\Platform;

use App\Enums\MembershipRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\InviteAdminRequest;
use App\Http\Requests\Platform\StorePlatformResourceRequest;
use App\Http\Requests\Platform\StoreRestaurantRequest;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::query()->with('setting')->orderBy('name')->get();

        return view('platform.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        return view('platform.restaurants.create');
    }

    public function store(StoreRestaurantRequest $request)
    {
        $restaurant = Restaurant::create([
            'name' => $request->string('name'),
            'slug' => $request->string('slug'),
            'address' => $request->string('address')->toString() ?: null,
            'timezone' => $request->string('timezone'),
            'phone' => $request->string('phone')->toString() ?: null,
            'email' => $request->string('email')->toString() ?: null,
            'active' => $request->boolean('active', true),
        ]);

        RestaurantSetting::create([
            'restaurant_id' => $restaurant->id,
            'default_buffer_minutes' => $request->integer('default_buffer_minutes'),
            'cancellation_cutoff_minutes' => $request->integer('cancellation_cutoff_minutes') ?: null,
        ]);

        $this->createInitialResources($restaurant, $request->input('activity_counts', []), [
            'min' => (int) $request->integer('table_capacity_min', 2),
            'max' => (int) $request->integer('table_capacity_max', 8),
        ]);

        return redirect()->route('platform.restaurants.index')->with('status', 'Restaurang skapad.');
    }

    public function admins(Restaurant $restaurant)
    {
        $admins = $restaurant->memberships()->with('user')->orderByDesc('role')->get();

        return view('platform.restaurants.admins', compact('restaurant', 'admins'));
    }

    public function inviteAdmin(InviteAdminRequest $request, Restaurant $restaurant)
    {
        $user = User::firstOrCreate(
            ['email' => $request->string('email')->toString()],
            [
                'name' => $request->string('name')->toString(),
                'password' => Hash::make(Str::random(32)),
            ]
        );

        RestaurantMembership::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'user_id' => $user->id],
            ['role' => MembershipRole::RESTAURANT_ADMIN]
        );

        return back()->with('status', 'Admin tillagd för restaurangen.');
    }

    public function activities(Restaurant $restaurant)
    {
        $resources = $restaurant->resources()->orderBy('type')->orderBy('name')->get();

        return view('platform.restaurants.activities', compact('restaurant', 'resources'));
    }

    public function storeActivity(StorePlatformResourceRequest $request, Restaurant $restaurant)
    {
        Resource::create([
            'restaurant_id' => $restaurant->id,
            'type' => $request->string('type')->toString(),
            'name' => $request->string('name')->toString(),
            'capacity_min' => $request->integer('capacity_min'),
            'capacity_max' => $request->integer('capacity_max'),
            'active' => $request->boolean('active', true),
        ]);

        return back()->with('status', 'Aktivitet tillagd.');
    }

    public function destroyActivity(Restaurant $restaurant, Resource $resource)
    {
        abort_unless($resource->restaurant_id === $restaurant->id, 404);
        $resource->delete();

        return back()->with('status', 'Aktivitet borttagen.');
    }

    /**
     * @param  array<string,mixed>  $counts
     * @param  array{min:int,max:int}  $tableCapacity
     */
    private function createInitialResources(Restaurant $restaurant, array $counts, array $tableCapacity): void
    {
        $labels = [
            'GOLF' => 'Golfbås',
            'SHUFFLEBOARD' => 'Shuffleboard',
            'DART' => 'Dartbana',
            'BILLIARDS' => 'Biljard',
            'TABLE' => 'Bord',
        ];

        foreach ($labels as $type => $label) {
            $count = (int) ($counts[$type] ?? 0);
            if ($count <= 0) {
                continue;
            }

            for ($i = 1; $i <= $count; $i++) {
                Resource::create([
                    'restaurant_id' => $restaurant->id,
                    'type' => $type,
                    'name' => $label.' '.$i,
                    'capacity_min' => $type === 'TABLE' ? $tableCapacity['min'] : 1,
                    'capacity_max' => $type === 'TABLE' ? $tableCapacity['max'] : 8,
                    'active' => true,
                ]);
            }
        }
    }
}
