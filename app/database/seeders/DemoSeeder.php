<?php

namespace Database\Seeders;

use App\Enums\MembershipRole;
use App\Enums\ResourceType;
use App\Models\DishTemplate;
use App\Models\DrinkTemplate;
use App\Models\MenuItem;
use App\Models\OpeningHour;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::query()->firstOrCreate(
            ['email' => 'super@demo.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
            ]
        );

        if (! $super->is_super_admin) {
            $super->forceFill(['is_super_admin' => true])->save();
        }

        $restaurant = Restaurant::query()->firstOrCreate(
            ['slug' => 'golfbaren'],
            [
                'name' => 'Golfbaren',
                'timezone' => 'Europe/Stockholm',
                'active' => true,
                'email' => 'booking@golfbaren.test',
                'phone' => '08-123 45 67',
            ]
        );

        RestaurantSetting::query()->firstOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'default_buffer_minutes' => 10,
                'slot_interval_minutes' => 15,
                'max_simultaneous_bookings' => 12,
                'default_durations' => [
                    'GOLF' => 60,
                    'SHUFFLEBOARD' => 60,
                    'DART' => 45,
                    'BILLIARDS' => 60,
                    'TABLE' => 90,
                ],
                'cancellation_cutoff_minutes' => 60,
            ]
        );

        RestaurantMembership::query()->firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'user_id' => $super->id],
            ['role' => MembershipRole::RESTAURANT_ADMIN, 'staff_role' => null]
        );

        $resources = [
            [ResourceType::GOLF, 'Golfbås 1', 1, 6],
            [ResourceType::GOLF, 'Golfbås 2', 1, 6],
            [ResourceType::SHUFFLEBOARD, 'Shuffleboard 1', 2, 8],
            [ResourceType::DART, 'Dartbana 1', 2, 6],
            [ResourceType::BILLIARDS, 'Biljard 1', 2, 6],
            [ResourceType::TABLE, 'Bord 1', 2, 4],
            [ResourceType::TABLE, 'Bord 2', 2, 4],
            [ResourceType::TABLE, 'Bord 3', 4, 6],
            [ResourceType::TABLE, 'Bord 4', 4, 8],
            [ResourceType::TABLE, 'Bord 5', 6, 10],
        ];

        foreach ($resources as [$type, $name, $min, $max]) {
            Resource::query()->firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $name],
                [
                    'type' => $type,
                    'capacity_min' => $min,
                    'capacity_max' => $max,
                    'active' => true,
                ]
            );
        }

        foreach (range(1, 7) as $weekday) {
            OpeningHour::query()->firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'weekday' => $weekday],
                ['opens_at' => '12:00', 'closes_at' => '23:00']
            );
        }

        $dishTemplates = [
            ['Nachotallrik', 'Krispiga nachos med dip', 129.00, ['snacks']],
            ['Hamburgare', 'Husets burgare med pommes', 189.00, ['main']],
            ['Caesarsallad', 'Kyckling, parmesan och krutonger', 169.00, ['main']],
            ['Mozzarellapizza', 'Stenugnsbakad pizza med basilika', 179.00, ['main']],
            ['Brownie', 'Chokladbrownie med vaniljglass', 95.00, ['dessert']],
        ];

        foreach ($dishTemplates as [$name, $description, $basePrice, $tags]) {
            $template = DishTemplate::query()->firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'base_price' => $basePrice,
                    'active' => true,
                    'tags' => $tags,
                ]
            );

            MenuItem::query()->firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'dish_template_id' => $template->id,
                ],
                [
                    'drink_template_id' => null,
                    'name' => $template->name,
                    'description' => $template->description,
                    'price' => $template->base_price,
                    'active' => true,
                    'tags' => $template->tags,
                    'sort_order' => $this->nextSortOrder($restaurant->id),
                ]
            );
        }

        $drinkTemplates = [
            ['Coca-Cola 33cl', 'Kylskåpskall burk', 35.00, ['soft', 'non_alcoholic']],
            ['Loka Citron 33cl', 'Kolsyrat vatten med citron', 30.00, ['water', 'non_alcoholic']],
            ['Kaffe', 'Bryggkaffe', 32.00, ['coffee', 'hot']],
            ['Pilsner 40cl', 'Kranöl lager', 69.00, ['beer']],
            ['Virgin Mojito', 'Mynta, lime, socker, soda', 79.00, ['mocktail', 'non_alcoholic']],
        ];

        foreach ($drinkTemplates as [$name, $description, $basePrice, $tags]) {
            $template = DrinkTemplate::query()->firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'base_price' => $basePrice,
                    'active' => true,
                    'tags' => $tags,
                ]
            );

            MenuItem::query()->firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'drink_template_id' => $template->id,
                ],
                [
                    'dish_template_id' => null,
                    'name' => $template->name,
                    'description' => $template->description,
                    'price' => $template->base_price,
                    'active' => true,
                    'tags' => $template->tags,
                    'sort_order' => $this->nextSortOrder($restaurant->id),
                ]
            );
        }
    }

    private function nextSortOrder(int $restaurantId): int
    {
        $max = (int) MenuItem::query()->where('restaurant_id', $restaurantId)->max('sort_order');
        return $max > 0 ? $max + 10 : 10;
    }
}
