<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedLayoutTest extends TestCase
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

    public function test_embed_request_strips_footer_and_reports_height(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1");

        $response->assertOk();
        $response->assertDontSee('Bokningssystem av');
        $response->assertSee('venueflowEmbedHeight');
    }

    public function test_non_embed_request_keeps_the_full_layout(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book");

        $response->assertOk();
        $response->assertSee('Bokningssystem av');
        $response->assertDontSee('venueflowEmbedHeight');
    }

    public function test_book_page_add_item_form_carries_embed_hidden_field(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant);

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1&resource_type=TABLE&date=2026-09-01&party_size=2&duration_minutes=60");

        $response->assertOk();
        $response->assertSee('name="embed" value="1"', false);
    }

    public function test_booking_details_page_carries_embed_through_form_and_link(): void
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
            ->get("/r/{$restaurant->slug}/book/details?embed=1");

        $response->assertOk();
        $response->assertSee('name="embed" value="1"', false);
        $response->assertSee(route('public.booking.create', ['slug' => $restaurant->slug, 'embed' => '1']), false);
    }
}
