<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmbedQueryFlagPropagationTest extends TestCase
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

    public function test_add_item_redirect_carries_embed_flag(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $response = $this->post("/r/{$restaurant->slug}/book/add-item?embed=1", [
            'resource_id' => $resource->id,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }

    public function test_add_item_redirect_omits_embed_flag_when_absent(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $response = $this->post("/r/{$restaurant->slug}/book/add-item", [
            'resource_id' => $resource->id,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString('embed=', $response->headers->get('Location'));
    }

    public function test_details_empty_cart_redirect_carries_embed_flag(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book/details?embed=1");

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }

    public function test_store_redirect_to_show_carries_embed_flag(): void
    {
        Notification::fake();

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
            ->post("/r/{$restaurant->slug}/book/details?embed=1", [
                'customer_name' => 'Anna Andersson',
                'email' => 'anna@example.com',
                'party_size' => 2,
            ]);

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }
}
