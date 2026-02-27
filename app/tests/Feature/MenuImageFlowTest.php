<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuImageFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_upload_menu_item_image(): void
    {
        Storage::fake('public');

        [$user, $restaurant] = $this->managerForRestaurant('golfbaren');

        $response = $this->actingAs($user)->post("/r/{$restaurant->slug}/admin/menu", [
            'name' => 'Nachos',
            'price' => '129.00',
            'description' => 'Cheese',
            'active' => '1',
            'image' => UploadedFile::fake()->createWithContent('nachos.png', $this->tinyPngBinary()),
        ]);

        $response->assertRedirect();

        $item = MenuItem::query()->where('restaurant_id', $restaurant->id)->where('name', 'Nachos')->firstOrFail();
        $this->assertNotNull($item->image_path);
        $this->assertStringStartsWith('storage/uploads/menu/items/', $item->image_path);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $item->image_path));
    }

    public function test_manager_can_replace_and_remove_image_with_undo_record(): void
    {
        Storage::fake('public');

        [$user, $restaurant] = $this->managerForRestaurant('golfbaren');

        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Burger',
            'price' => 159,
            'active' => true,
            'image_path' => 'storage/uploads/menu/items/old.webp',
            'sort_order' => 10,
        ]);
        Storage::disk('public')->put('uploads/menu/items/old.webp', 'dummy');

        $this->actingAs($user)->put("/r/{$restaurant->slug}/admin/menu/{$item->id}", [
            'name' => 'Burger',
            'price' => '159.00',
            'description' => '',
            'tags' => '',
            'active' => '1',
            'image' => UploadedFile::fake()->createWithContent('new.png', $this->tinyPngBinary()),
        ])->assertRedirect();

        $item->refresh();
        $this->assertNotSame('storage/uploads/menu/items/old.webp', $item->image_path);

        $this->actingAs($user)->put("/r/{$restaurant->slug}/admin/menu/{$item->id}", [
            'name' => 'Burger',
            'price' => '159.00',
            'description' => '',
            'tags' => '',
            'remove_image' => '1',
        ])->assertRedirect();

        $item->refresh();
        $this->assertNull($item->image_path);
        $this->assertDatabaseCount('image_trash_items', 2);
    }

    public function test_bulk_action_is_tenant_scoped(): void
    {
        [$user, $restaurantA] = $this->managerForRestaurant('golfbaren');
        $restaurantB = Restaurant::query()->create([
            'name' => 'Other',
            'slug' => 'other',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);

        $foreignItem = MenuItem::query()->create([
            'restaurant_id' => $restaurantB->id,
            'name' => 'Hidden',
            'price' => 10,
            'active' => true,
            'sort_order' => 10,
        ]);

        $this->actingAs($user)->post("/r/{$restaurantA->slug}/admin/menu/bulk", [
            'item_ids' => [$foreignItem->id],
            'action' => 'set_inactive',
        ])->assertRedirect();

        $this->assertTrue($foreignItem->fresh()->active);
    }

    public function test_reorder_updates_sort_order(): void
    {
        [$user, $restaurant] = $this->managerForRestaurant('golfbaren');

        $itemOne = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'A',
            'price' => 10,
            'active' => true,
            'sort_order' => 10,
        ]);
        $itemTwo = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'B',
            'price' => 20,
            'active' => true,
            'sort_order' => 20,
        ]);

        $this->actingAs($user)
            ->postJson("/r/{$restaurant->slug}/admin/menu/reorder", [
                'ordered_ids' => [$itemTwo->id, $itemOne->id],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(10, (int) $itemTwo->fresh()->sort_order);
        $this->assertSame(20, (int) $itemOne->fresh()->sort_order);
    }

    private function managerForRestaurant(string $slug): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $restaurant = Restaurant::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);

        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => 'RESTAURANT_ADMIN',
            'staff_role' => null,
        ]);

        return [$user, $restaurant];
    }

    private function tinyPngBinary(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2Q5d0AAAAASUVORK5CYII=',
            true
        ) ?: '';
    }
}
