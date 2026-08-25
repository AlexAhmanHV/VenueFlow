<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedAdminPageTest extends TestCase
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

    public function test_any_staff_member_can_view_the_embed_page(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/embed")
            ->assertOk()
            ->assertSee('data-slug="'.$restaurant->slug.'"', false);
    }

    public function test_member_of_another_tenant_cannot_view_the_embed_page(): void
    {
        $restaurantA = $this->makeRestaurant('restaurant-a');
        $restaurantB = $this->makeRestaurant('restaurant-b');
        $staffB = $this->staffFor($restaurantB);

        $this->actingAs($staffB)
            ->get("/r/{$restaurantA->slug}/admin/embed")
            ->assertForbidden();
    }
}
