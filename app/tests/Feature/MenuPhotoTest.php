<?php

namespace Tests\Feature;

use App\Models\DishTemplate;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Support\MenuPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_names_map_to_a_bundled_photo(): void
    {
        $this->assertSame('/images/menu/photos/burger.webp', MenuPhoto::urlFor('Hamburgare'));
        $this->assertSame('/images/menu/photos/ipa.webp', MenuPhoto::urlFor('Alkoholfri IPA 33cl'));
        $this->assertNull(MenuPhoto::urlFor('Okänd rätt'));
        $this->assertNull(MenuPhoto::urlFor(null));
    }

    public function test_every_mapped_photo_exists_on_disk(): void
    {
        foreach (config('menu_photos') as $name => $key) {
            $this->assertFileExists(public_path("images/menu/photos/{$key}.webp"), "Missing photo for {$name}");
        }
    }

    public function test_generated_placeholder_is_replaced_by_the_photo(): void
    {
        $template = DishTemplate::query()->create([
            'name' => 'Hamburgare',
            'base_price' => 189,
            'active' => true,
            'image_path' => 'images/menu/dish-template-2.svg',
        ]);

        $this->assertSame('/images/menu/photos/burger.webp', $template->image_url);
    }

    public function test_an_uploaded_image_wins_over_the_photo(): void
    {
        $restaurant = $this->restaurant();
        $item = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Hamburgare',
            'price' => 189,
            'active' => true,
            'image_path' => 'storage/uploads/menu/items/own.webp',
        ]);

        $this->assertSame('/storage/uploads/menu/items/own.webp', $item->image_url);
        $this->assertSame('/storage/uploads/menu/items/own.webp', $item->photo_url);
    }

    public function test_public_menu_shows_photos_for_mapped_items_only(): void
    {
        $restaurant = $this->restaurant();
        foreach (['Hamburgare' => ['main'], 'Kvällens special' => ['special']] as $name => $tags) {
            MenuItem::query()->create([
                'restaurant_id' => $restaurant->id,
                'name' => $name,
                'price' => 189,
                'active' => true,
                'tags' => $tags,
            ]);
        }

        $this->get("/r/{$restaurant->slug}/menu")
            ->assertOk()
            ->assertSee('/images/menu/photos/burger.webp', false)
            ->assertSee('loading="lazy"', false);
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'Golfbaren',
            'slug' => 'golfbaren',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }
}
