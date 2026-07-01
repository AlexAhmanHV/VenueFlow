<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeRestaurant(string $slug = 'test-place'): Restaurant
    {
        return Restaurant::query()->create([
            'name'     => 'Test Place',
            'slug'     => $slug,
            'timezone' => 'Europe/Stockholm',
            'active'   => true,
        ]);
    }

    private function userWithRole(Restaurant $restaurant, string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id'       => $user->id,
            'role'          => $role,
            'staff_role'    => null,
        ]);

        return $user;
    }

    // ── Unauthenticated ───────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $restaurant = $this->makeRestaurant();

        $this->get("/r/{$restaurant->slug}/admin/dashboard")
            ->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_access_bookings(): void
    {
        $restaurant = $this->makeRestaurant();

        $this->get("/r/{$restaurant->slug}/admin/bookings")
            ->assertRedirect('/login');
    }

    // ── Non-member ────────────────────────────────────────────────────────────

    public function test_authenticated_non_member_cannot_access_admin_pages(): void
    {
        $restaurant = $this->makeRestaurant();
        $outsider   = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($outsider)
            ->get("/r/{$restaurant->slug}/admin/dashboard")
            ->assertForbidden();
    }

    // ── Staff role ────────────────────────────────────────────────────────────

    public function test_staff_can_access_dashboard(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff      = $this->userWithRole($restaurant, MembershipRole::STAFF->value);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/dashboard")
            ->assertOk();
    }

    public function test_staff_cannot_access_settings(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff      = $this->userWithRole($restaurant, MembershipRole::STAFF->value);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/settings")
            ->assertForbidden();
    }

    public function test_staff_cannot_manage_other_staff(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff      = $this->userWithRole($restaurant, MembershipRole::STAFF->value);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/staff")
            ->assertForbidden();
    }

    // ── Admin role ────────────────────────────────────────────────────────────

    public function test_admin_can_access_settings(): void
    {
        $restaurant = $this->makeRestaurant();
        $admin      = $this->userWithRole($restaurant, MembershipRole::RESTAURANT_ADMIN->value);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/settings")
            ->assertOk();
    }

    public function test_admin_can_manage_staff(): void
    {
        $restaurant = $this->makeRestaurant();
        $admin      = $this->userWithRole($restaurant, MembershipRole::RESTAURANT_ADMIN->value);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/staff")
            ->assertOk();
    }

    // ── Cross-tenant ──────────────────────────────────────────────────────────

    public function test_admin_of_one_restaurant_cannot_access_another(): void
    {
        $restaurantA = $this->makeRestaurant('place-a');
        $restaurantB = $this->makeRestaurant('place-b');
        $adminA      = $this->userWithRole($restaurantA, MembershipRole::RESTAURANT_ADMIN->value);

        $this->actingAs($adminA)
            ->get("/r/{$restaurantB->slug}/admin/dashboard")
            ->assertForbidden();
    }

    // ── SuperAdmin ────────────────────────────────────────────────────────────

    public function test_non_superadmin_cannot_access_platform(): void
    {
        $restaurant = $this->makeRestaurant();
        $admin      = $this->userWithRole($restaurant, MembershipRole::RESTAURANT_ADMIN->value);

        $this->actingAs($admin)
            ->get('/platform/restaurants')
            ->assertForbidden();
    }
}
