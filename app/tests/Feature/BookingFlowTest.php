<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\MembershipRole;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeRestaurant(string $slug = 'test-restaurant'): Restaurant
    {
        return Restaurant::query()->create([
            'name'     => ucfirst($slug),
            'slug'     => $slug,
            'timezone' => 'Europe/Stockholm',
            'active'   => true,
        ]);
    }

    private function makeResource(Restaurant $restaurant): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Table 1',
            'type'          => 'TABLE',
            'capacity'      => 4,
            'active'        => true,
        ]);
    }

    private function adminFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id'       => $user->id,
            'role'          => MembershipRole::RESTAURANT_ADMIN->value,
            'staff_role'    => null,
        ]);

        return $user;
    }

    private function bookingPayload(Resource $resource, string $date = '2026-08-01'): array
    {
        return [
            'customer_name' => 'Anna Andersson',
            'email'         => 'anna@example.com',
            'phone'         => '0701234567',
            'party_size'    => 2,
            'note'          => null,
            'booking_items' => [
                [
                    'resource_id'      => $resource->id,
                    'start_time_local' => "{$date} 18:00:00",
                    'end_time_local'   => "{$date} 20:00:00",
                ],
            ],
        ];
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    public function test_guest_can_create_booking(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        $resource   = $this->makeResource($restaurant);

        $response = $this->post(
            "/r/{$restaurant->slug}/book",
            $this->bookingPayload($resource)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('guest_bookings', [
            'restaurant_id'  => $restaurant->id,
            'customer_name'  => 'Anna Andersson',
            'email'          => 'anna@example.com',
            'status'         => BookingStatus::CONFIRMED->value,
        ]);
    }

    public function test_booking_requires_customer_name_and_email(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource   = $this->makeResource($restaurant);

        $payload = $this->bookingPayload($resource);
        unset($payload['customer_name'], $payload['email']);

        $response = $this->post("/r/{$restaurant->slug}/book", $payload);

        $response->assertSessionHasErrors(['customer_name', 'email']);
    }

    public function test_double_booking_same_slot_is_rejected(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        $resource   = $this->makeResource($restaurant);
        $payload    = $this->bookingPayload($resource);

        $this->post("/r/{$restaurant->slug}/book", $payload)->assertRedirect();
        $response = $this->post("/r/{$restaurant->slug}/book", $payload);

        $response->assertSessionHasErrors(['slot']);
        $this->assertSame(1, GuestBooking::query()->where('restaurant_id', $restaurant->id)->count());
    }

    public function test_admin_can_cancel_booking(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        $resource   = $this->makeResource($restaurant);
        $admin      = $this->adminFor($restaurant);

        $this->post("/r/{$restaurant->slug}/book", $this->bookingPayload($resource));
        $booking = GuestBooking::query()->where('restaurant_id', $restaurant->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch("/r/{$restaurant->slug}/admin/bookings/{$booking->id}/status", [
                'status' => BookingStatus::CANCELLED->value,
            ])
            ->assertRedirect();

        $this->assertSame(BookingStatus::CANCELLED->value, $booking->fresh()->status->value);
    }

    public function test_cancelled_slot_becomes_available_again(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        $resource   = $this->makeResource($restaurant);
        $admin      = $this->adminFor($restaurant);
        $payload    = $this->bookingPayload($resource);

        $this->post("/r/{$restaurant->slug}/book", $payload);
        $booking = GuestBooking::query()->where('restaurant_id', $restaurant->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch("/r/{$restaurant->slug}/admin/bookings/{$booking->id}/status", [
                'status' => BookingStatus::CANCELLED->value,
            ]);

        // Same slot should now be bookable again.
        $this->post("/r/{$restaurant->slug}/book", $payload)->assertRedirect();
        $this->assertSame(2, GuestBooking::query()->where('restaurant_id', $restaurant->id)->count());
    }

    public function test_booking_is_tenant_scoped(): void
    {
        Notification::fake();

        $restaurantA = $this->makeRestaurant('restaurant-a');
        $restaurantB = $this->makeRestaurant('restaurant-b');
        $resourceA   = $this->makeResource($restaurantA);
        $adminB      = $this->adminFor($restaurantB);

        $this->post("/r/{$restaurantA->slug}/book", $this->bookingPayload($resourceA));
        $booking = GuestBooking::query()->where('restaurant_id', $restaurantA->id)->firstOrFail();

        // Admin of restaurant B should not be able to touch restaurant A's booking.
        $this->actingAs($adminB)
            ->patch("/r/{$restaurantA->slug}/admin/bookings/{$booking->id}/status", [
                'status' => BookingStatus::CANCELLED->value,
            ])
            ->assertForbidden();
    }
}
