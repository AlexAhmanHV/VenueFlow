<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\MembershipRole;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LiveBoardFloorPlanTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'UTC',
            'active' => true,
        ]);
    }

    private function makeResource(Restaurant $restaurant, string $name, ?float $x = null, ?float $y = null): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
            'position_x' => $x,
            'position_y' => $y,
        ]);
    }

    private function adminFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::RESTAURANT_ADMIN->value,
            'staff_role' => null,
        ]);

        return $user;
    }

    private function makeCheckedInBookingNow(Restaurant $restaurant, Resource $resource): GuestBooking
    {
        $booking = GuestBooking::query()->create([
            'restaurant_id' => $restaurant->id,
            'public_id' => (string) Str::uuid(),
            'status' => BookingStatus::CHECKED_IN,
            'customer_name' => 'Live Guest',
            'email' => 'live@example.com',
            'party_size' => 2,
            'cancel_token_hash' => 'hash-'.Str::random(8),
        ]);

        BookingItem::query()->create([
            'guest_booking_id' => $booking->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->subMinutes(15),
            'end_time' => Carbon::now()->addMinutes(45),
        ]);

        return $booking;
    }

    public function test_floor_plan_tab_shows_positioned_resource_as_occupied(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant, 'Table 1', 25.0, 40.0);
        $this->makeCheckedInBookingNow($restaurant, $resource);
        $admin = $this->adminFor($restaurant);

        $response = $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk();

        $response->assertSee('data-occupancy="occupied"', false);
        $response->assertSee('Live Guest');
    }

    public function test_floor_plan_tab_shows_free_resource_with_no_bookings(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant, 'Empty Table', 60.0, 60.0);
        $admin = $this->adminFor($restaurant);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk()
            ->assertSee('data-occupancy="free"', false);
    }

    public function test_unpositioned_resource_still_appears_on_floor_plan_tab(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant, 'Unplaced Table');
        $admin = $this->adminFor($restaurant);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk()
            ->assertSee('Unplaced Table');
    }
}
