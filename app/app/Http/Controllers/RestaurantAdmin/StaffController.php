<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\StoreStaffRequest;
use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $memberships = $restaurant->memberships()->with('user')->orderByDesc('role')->get();

        return view('restaurant-admin.staff.index', compact('restaurant', 'memberships'));
    }

    public function store(StoreStaffRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $user = User::firstOrCreate(
            ['email' => $request->string('email')->toString()],
            [
                'name' => $request->string('name')->toString(),
                'password' => Hash::make(Str::random(32)),
            ]
        );

        RestaurantMembership::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'user_id' => $user->id,
            ],
            [
                'role' => MembershipRole::STAFF,
                'staff_role' => StaffRole::from($request->string('role')->toString()),
            ]
        );

        return back()->with('status', 'Personal uppdaterad.');
    }

    public function destroy(Request $request, string $slug, RestaurantMembership $membership)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($membership->restaurant_id === $restaurant->id, 404);

        $membership->delete();

        return back()->with('status', 'Medlem borttagen.');
    }
}
