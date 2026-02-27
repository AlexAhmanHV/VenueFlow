<?php

namespace Database\Seeders;

use App\Enums\MembershipRole;
use App\Enums\ResourceType;
use App\Enums\StaffRole;
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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::updateOrCreate(
            ['email' => 'super@demo.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
            ]
        );

        $restaurant = Restaurant::updateOrCreate(
            ['slug' => 'golfbaren'],
            [
                'name' => 'Golfbaren',
                'timezone' => 'Europe/Stockholm',
                'active' => true,
                'email' => 'booking@golfbaren.test',
                'phone' => '08-123 45 67',
            ]
        );

        RestaurantSetting::updateOrCreate(
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

        RestaurantMembership::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'user_id' => $super->id],
            ['role' => MembershipRole::RESTAURANT_ADMIN, 'staff_role' => null]
        );

        $owner = User::updateOrCreate(
            ['email' => 'owner@demo.test'],
            [
                'name' => 'Demo Restaurant Owner',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ]
        );

        RestaurantMembership::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'user_id' => $owner->id],
            ['role' => MembershipRole::STAFF, 'staff_role' => StaffRole::MANAGER]
        );

        $restaurant->resources()->delete();
        $restaurant->menuItems()->delete();

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
            Resource::updateOrCreate(
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
            OpeningHour::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'weekday' => $weekday],
                ['opens_at' => '12:00', 'closes_at' => '23:00']
            );
        }

        $dishTemplates = [
            ['Nachotallrik', 'Krispiga nachos med dip', 129.00, ['snacks']],
            ['Hamburgare', 'Husets burgare med pommes', 189.00, ['main']],
            ['Caesarsallad', 'Kyckling, parmesan och krutonger', 169.00, ['main']],
            ['Cheesecake', 'Serveras med bär', 89.00, ['dessert']],
            ['Mozzarellapizza', 'Stenugnsbakad pizza med basilika', 179.00, ['main']],
            ['Charkbricka', 'Ostar, charkuterier och oliver', 199.00, ['sharing']],
            ['Fish and chips', 'Friterad torsk med pommes och aioli', 209.00, ['main']],
            ['Loaded fries', 'Pommes med cheddar, jalapeno och lök', 119.00, ['snacks']],
            ['Halloumisallad', 'Halloumi, quinoa och citrusdressing', 175.00, ['main']],
            ['Brownie', 'Chokladbrownie med vaniljglass', 95.00, ['dessert']],
        ];

        foreach ($dishTemplates as [$name, $description, $basePrice, $tags]) {
            $template = DishTemplate::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'base_price' => $basePrice,
                    'active' => true,
                    'tags' => $tags,
                ]
            );

            MenuItem::updateOrCreate(
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
                ]
            );
        }

        $drinkTemplates = [
            ['Coca-Cola 33cl', 'Kylskåpskall burk', 35.00, ['soft', 'non_alcoholic']],
            ['Coca-Cola Zero 33cl', 'Sockerfri cola', 35.00, ['soft', 'non_alcoholic']],
            ['Sprite 33cl', 'Citrusläsk', 35.00, ['soft', 'non_alcoholic']],
            ['Fanta Orange 33cl', 'Apelsinläsk', 35.00, ['soft', 'non_alcoholic']],
            ['Trocadero 33cl', 'Klassisk svensk läsk', 35.00, ['soft', 'non_alcoholic']],
            ['Loka Naturell 33cl', 'Kolsyrat vatten', 30.00, ['water', 'non_alcoholic']],
            ['Loka Citron 33cl', 'Kolsyrat vatten med citron', 30.00, ['water', 'non_alcoholic']],
            ['Apelsinjuice', 'Färskpressad stil', 45.00, ['juice', 'non_alcoholic']],
            ['Iste Persika', 'Kylt iste', 42.00, ['tea', 'non_alcoholic']],
            ['Red Bull 25cl', 'Energidryck', 42.00, ['energy', 'non_alcoholic']],
            ['Kaffe', 'Bryggkaffe', 32.00, ['coffee', 'hot']],
            ['Espresso', 'Enkel espresso', 34.00, ['coffee', 'hot']],
            ['Cappuccino', 'Espresso med mjölkskum', 42.00, ['coffee', 'hot']],
            ['Latte', 'Mjuk mjölkburen kaffe', 45.00, ['coffee', 'hot']],
            ['Te', 'Svart eller grönt te', 30.00, ['tea', 'hot']],
            ['Alkoholfri lager 33cl', 'Ljus alkoholfri öl', 49.00, ['beer', 'non_alcoholic']],
            ['Alkoholfri IPA 33cl', 'Humlearomatisk alkoholfri öl', 55.00, ['beer', 'non_alcoholic']],
            ['Pilsner 40cl', 'Kranöl lager', 69.00, ['beer']],
            ['IPA 40cl', 'Fruktig india pale ale', 79.00, ['beer']],
            ['APA 40cl', 'American pale ale', 76.00, ['beer']],
            ['Stout 33cl', 'Mörk rostad öl', 82.00, ['beer']],
            ['Veteöl 50cl', 'Ofiltrerad weissbier', 89.00, ['beer']],
            ['Cider torr 33cl', 'Torr apple cider', 74.00, ['cider']],
            ['Cider pear 33cl', 'Pärocider', 74.00, ['cider']],
            ['Husets vita glas', 'Friskt och torrt', 89.00, ['wine', 'white']],
            ['Husets röda glas', 'Mjuk och fruktig', 89.00, ['wine', 'red']],
            ['Husets rosé glas', 'Bärkryddigt rosévin', 89.00, ['wine', 'rose']],
            ['Sauvignon Blanc glas', 'Citrus och krispig syra', 105.00, ['wine', 'white']],
            ['Chardonnay glas', 'Fatkaraktär med gul frukt', 109.00, ['wine', 'white']],
            ['Pinot Noir glas', 'Lätt och elegant', 112.00, ['wine', 'red']],
            ['Cabernet Sauvignon glas', 'Mörka bär och struktur', 115.00, ['wine', 'red']],
            ['Prosecco glas', 'Mousserande', 99.00, ['wine', 'sparkling']],
            ['Champagne glas', 'Klassisk brut', 169.00, ['wine', 'sparkling']],
            ['Mojito', 'Rom, lime, mynta, soda', 139.00, ['cocktail']],
            ['Margarita', 'Tequila, lime, triple sec', 139.00, ['cocktail']],
            ['Whiskey Sour', 'Bourbon, citron, socker', 145.00, ['cocktail']],
            ['Negroni', 'Gin, bitter, vermouth', 149.00, ['cocktail']],
            ['Aperol Spritz', 'Aperol, prosecco, soda', 139.00, ['cocktail']],
            ['Espresso Martini', 'Vodka, kaffe, kaffelikör', 149.00, ['cocktail']],
            ['Gin & Tonic', 'Gin och premium tonic', 132.00, ['cocktail']],
            ['Moscow Mule', 'Vodka, ginger beer, lime', 139.00, ['cocktail']],
            ['Rom & Cola', 'Mörk rom med cola', 129.00, ['cocktail']],
            ['Virgin Mojito', 'Mynta, lime, socker, soda', 79.00, ['mocktail', 'non_alcoholic']],
            ['Virgin Passion', 'Passionsfrukt, lime, soda', 82.00, ['mocktail', 'non_alcoholic']],
            ['Alkoholfri Spritz', 'Bubblig bitter citrus', 85.00, ['mocktail', 'non_alcoholic']],
        ];

        foreach ($drinkTemplates as [$name, $description, $basePrice, $tags]) {
            $template = DrinkTemplate::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'base_price' => $basePrice,
                    'active' => true,
                    'tags' => $tags,
                ]
            );

            MenuItem::updateOrCreate(
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
                ]
            );
        }

        MenuItem::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Kvällens special'],
            [
                'dish_template_id' => null,
                'drink_template_id' => null,
                'description' => 'Kökets egna val för kvällen',
                'price' => 219.00,
                'active' => true,
                'tags' => ['special'],
            ]
        );

        Artisan::call('menu:generate-images');
    }
}
