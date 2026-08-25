<?php

namespace Tests\Feature;

use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MaxSimultaneousBookingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(string $slug = 'test-restaurant'): Restaurant
    {
        return Restaurant::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    private function makeResource(Restaurant $restaurant, string $name = 'Table 1'): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    public function test_booking_is_rejected_once_max_simultaneous_bookings_is_reached(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        RestaurantSetting::query()->create([
            'restaurant_id' => $restaurant->id,
            'max_simultaneous_bookings' => 1,
        ]);
        $resourceA = $this->makeResource($restaurant, 'Table A');
        $resourceB = $this->makeResource($restaurant, 'Table B');

        // First booking succeeds and fills the restaurant's simultaneous-booking limit.
        $itemsA = [[
            'resource_id' => $resourceA->id,
            'resource_name' => $resourceA->name,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]];

        $response = $this
            ->withSession(["booking_wizard.{$restaurant->id}.items" => $itemsA])
            ->post("/r/{$restaurant->slug}/book/details", [
                'customer_name' => 'Anna Andersson',
                'email' => 'anna@example.com',
                'party_size' => 2,
            ]);

        $response->assertRedirect();
        $this->assertSame(1, GuestBooking::query()->where('restaurant_id', $restaurant->id)->count());

        // Second booking overlaps the same time window (different resource) and should be rejected
        // now that max_simultaneous_bookings is actually enforced.
        $itemsB = [[
            'resource_id' => $resourceB->id,
            'resource_name' => $resourceB->name,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]];

        $response = $this
            ->withSession(["booking_wizard.{$restaurant->id}.items" => $itemsB])
            ->post("/r/{$restaurant->slug}/book/details", [
                'customer_name' => 'Bo Bengtsson',
                'email' => 'bo@example.com',
                'party_size' => 2,
            ]);

        $response->assertSessionHasErrors(['slot']);
        $this->assertSame(1, GuestBooking::query()->where('restaurant_id', $restaurant->id)->count());
    }
}
