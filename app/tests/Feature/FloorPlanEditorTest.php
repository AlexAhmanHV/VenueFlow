<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloorPlanEditorTest extends TestCase
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

    private function managerFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::STAFF->value,
            'staff_role' => StaffRole::MANAGER->value,
        ]);

        return $user;
    }

    private function staffFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::STAFF->value,
            'staff_role' => StaffRole::STAFF->value,
        ]);

        return $user;
    }

    public function test_plain_staff_cannot_view_the_editor(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/floor-plan/edit")
            ->assertForbidden();
    }

    public function test_plain_staff_cannot_save_positions(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->patch("/r/{$restaurant->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resource->id, 'x' => 10, 'y' => 20],
                ],
            ])
            ->assertForbidden();
    }

    public function test_manager_can_save_positions(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);
        $manager = $this->managerFor($restaurant);

        $this->actingAs($manager)
            ->patch("/r/{$restaurant->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resource->id, 'x' => 33.5, 'y' => 66.25],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(33.5, $resource->fresh()->position_x);
        $this->assertSame(66.25, $resource->fresh()->position_y);
    }

    public function test_cannot_save_a_position_for_another_tenants_resource(): void
    {
        $restaurantA = $this->makeRestaurant('restaurant-a');
        $restaurantB = $this->makeRestaurant('restaurant-b');
        $resourceB = $this->makeResource($restaurantB, 'Other Tenant Table');
        $managerA = $this->managerFor($restaurantA);

        $this->actingAs($managerA)
            ->patch("/r/{$restaurantA->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resourceB->id, 'x' => 10, 'y' => 10],
                ],
            ])
            ->assertStatus(422);

        $this->assertNull($resourceB->fresh()->position_x);
    }

    public function test_editor_page_lists_unpositioned_resources(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant, 'Unplaced Table');
        $manager = $this->managerFor($restaurant);

        $this->actingAs($manager)
            ->get("/r/{$restaurant->slug}/admin/floor-plan/edit")
            ->assertOk()
            ->assertSee('Unplaced Table');
    }
}
