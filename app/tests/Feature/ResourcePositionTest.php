<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePositionTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    public function test_resource_is_not_positioned_by_default(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);

        $this->assertFalse($resource->isPositioned());
    }

    public function test_resource_position_can_be_saved_and_read_back(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
            'position_x' => 12.5,
            'position_y' => 87.25,
        ]);

        $this->assertTrue($resource->fresh()->isPositioned());
        $this->assertSame(12.5, $resource->fresh()->position_x);
        $this->assertSame(87.25, $resource->fresh()->position_y);
    }
}
