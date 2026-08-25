<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingDetailsPageTest extends TestCase
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

    private function makeResource(Restaurant $restaurant): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    public function test_details_page_renders_with_a_cart_item(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $items = [[
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]];

        $response = $this
            ->withSession(["booking_wizard.{$restaurant->id}.items" => $items])
            ->get("/r/{$restaurant->slug}/book/details");

        $response->assertOk();
        $response->assertSee('Table 1');
    }
}
