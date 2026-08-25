<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Services\ResourceOccupancyResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourceOccupancyResolverTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private function makeResource(string $name): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    private function makeBooking(Resource $resource, Carbon $start, Carbon $end, BookingStatus $status): GuestBooking
    {
        $booking = GuestBooking::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'public_id' => (string) Str::uuid(),
            'status' => $status,
            'customer_name' => 'Test Guest',
            'email' => 'guest@example.com',
            'party_size' => 2,
            'cancel_token_hash' => 'hash-'.Str::random(8),
        ]);

        BookingItem::query()->create([
            'guest_booking_id' => $booking->id,
            'resource_id' => $resource->id,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $booking->load('bookingItems.resource');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    public function test_resource_with_no_bookings_is_free(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');

        $statuses = ResourceOccupancyResolver::resolve(collect(), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_resource_with_a_booking_covering_now_is_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::CHECKED_IN,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertSame('occupied', $statuses[$resource->id]);
    }

    public function test_resource_with_a_booking_starting_within_30_minutes_is_reserved_soon(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->addMinutes(20),
            $now->copy()->addMinutes(80),
            BookingStatus::CONFIRMED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertSame('reserved_soon', $statuses[$resource->id]);
    }

    public function test_cancelled_booking_does_not_count_as_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::CANCELLED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_no_show_booking_does_not_count_as_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::NO_SHOW,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_booking_far_in_the_future_leaves_resource_out_of_the_map(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->addHours(3),
            $now->copy()->addHours(4),
            BookingStatus::CONFIRMED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }
}
