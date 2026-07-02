<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoLoginController extends Controller
{
    private int $restaurantId;

    public function __invoke(): RedirectResponse
    {
        $user = User::where('email', 'owner@demo.test')->firstOrFail();

        Auth::login($user);

        $restaurant = DB::table('restaurants')->where('slug', 'golfbaren')->first();
        if ($restaurant) {
            $this->restaurantId = $restaurant->id;
            $this->seedStaticIfNeeded();
            $this->seedHistoricalIfNeeded();
            $this->seedTodayIfNeeded();
        }

        return redirect('/r/golfbaren/admin/dashboard');
    }

    private function seedStaticIfNeeded(): void
    {
        // Opening hours (Mon-Sun)
        $hasHours = DB::table('opening_hours')->where('restaurant_id', $this->restaurantId)->exists();
        if (! $hasHours) {
            $now = Carbon::now('UTC');
            $hours = [];
            for ($day = 1; $day <= 7; $day++) {
                $hours[] = [
                    'restaurant_id' => $this->restaurantId,
                    'weekday'       => $day,
                    'opens_at'      => '10:00:00',
                    'closes_at'     => '22:00:00',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
            DB::table('opening_hours')->insert($hours);
        }

        // Menu items
        $hasMenu = DB::table('menu_items')->where('restaurant_id', $this->restaurantId)->exists();
        if (! $hasMenu) {
            $now = Carbon::now('UTC');
            $items = [
                ['Golfarens burgare',     'Dubbel oxfiléburgare med cheddar, karamelliserad lök och hemgjord aioli.',  149.00, '["mat","populär"]',  1],
                ['Nachos deluxe',         'Chips med jalapeños, guacamole, salsa och smält ost.',                       125.00, '["mat","dela"]',      2],
                ['Ostbricka',             'Tre sorters ost med kex, druvor och marmelad.',                              165.00, '["mat"]',             3],
                ['Varmkorv med bröd',     'Klassisk grillad korv i mjukt bröd med senap och ketchup.',                  79.00, '["mat","snabb"]',     4],
                ['Friterade kycklingbitar','Kryddiga kycklingbitar med dippsås och potatisklyftorna.',                  139.00, '["mat"]',             5],
                ['Hantverksöl 50cl',      'Lokalt bryggd lager på fat. Fräsch och lättdrucken.',                        89.00, '["dryck","öl"]',      6],
                ['Vitt vin (glas)',        'Husets vita, friskt och fruktigt.',                                          95.00, '["dryck","vin"]',     7],
                ['Rödvin (glas)',          'Mjukt rödvin med bär och vaniljton.',                                        95.00, '["dryck","vin"]',     8],
                ['Röd lemonad',           'Hemgjord hallonlemonad med mynta och is.',                                    49.00, '["dryck","alkoholfri"]', 9],
                ['Kaffe',                 'Bryggkaffe eller espresso.',                                                  39.00, '["dryck","varm"]',   10],
                ['Mineralvatten 33cl',    'Kolsyrat eller stilla.',                                                      35.00, '["dryck"]',          11],
                ['Energidryck',           'Kall energidryck på burk.',                                                   45.00, '["dryck"]',          12],
            ];
            foreach ($items as [$name, $desc, $price, $tags, $sort]) {
                DB::table('menu_items')->insert([
                    'restaurant_id' => $this->restaurantId,
                    'name'          => $name,
                    'description'   => $desc,
                    'price'         => $price,
                    'active'        => true,
                    'tags'          => $tags,
                    'sort_order'    => $sort,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }
    }

    private function seedHistoricalIfNeeded(): void
    {
        $oldCount = DB::table('guest_bookings')
            ->where('restaurant_id', $this->restaurantId)
            ->where('created_at', '<', Carbon::today('Europe/Stockholm')->utc())
            ->count();

        if ($oldCount > 0) {
            return;
        }

        $resourceId = DB::table('resources')
            ->where('restaurant_id', $this->restaurantId)
            ->where('active', true)
            ->value('id');

        if (! $resourceId) {
            return;
        }

        $names = [
            ['Björn Lindqvist', 'bjorn@example.se', 2],
            ['Karin Ström', 'karin@example.se', 4],
            ['Peter Magnusson', 'peter@example.se', 2],
            ['Sofia Lundgren', 'sofia@example.se', 3],
            ['Anders Gustafsson', 'anders@example.se', 2],
            ['Helena Persson', 'helena@example.se', 4],
            ['Mikael Borg', 'mikael@example.se', 2],
            ['Camilla Åberg', 'camilla@example.se', 3],
        ];

        $statuses = ['CHECKED_IN', 'CHECKED_IN', 'CHECKED_IN', 'CHECKED_IN', 'NO_SHOW', 'CONFIRMED', 'CANCELLED'];

        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $dayBase = Carbon::now('Europe/Stockholm')->subDays($daysAgo)->startOfDay()->utc();
            $count = rand(4, 8);

            for ($i = 0; $i < $count; $i++) {
                $guest  = $names[array_rand($names)];
                $status = $statuses[array_rand($statuses)];
                $createdAt = $dayBase->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));

                $bookingId = DB::table('guest_bookings')->insertGetId([
                    'restaurant_id'     => $this->restaurantId,
                    'public_id'         => Str::uuid(),
                    'status'            => $status,
                    'customer_name'     => $guest[0],
                    'email'             => $guest[1],
                    'party_size'        => $guest[2],
                    'cancel_token_hash' => 'hist-' . Str::random(8),
                    'created_at'        => $createdAt,
                    'updated_at'        => $createdAt,
                ]);

                DB::table('booking_items')->insert([
                    'guest_booking_id' => $bookingId,
                    'resource_id'      => $resourceId,
                    'start_time'       => $createdAt->copy()->addHour(),
                    'end_time'         => $createdAt->copy()->addHours(2),
                    'price_minor'      => 25000,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);
            }
        }
    }

    private function seedTodayIfNeeded(): void
    {
        $dayStart = Carbon::today('Europe/Stockholm')->utc();
        $dayEnd   = Carbon::today('Europe/Stockholm')->endOfDay()->utc();

        $todayCount = DB::table('guest_bookings')
            ->where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->count();

        if ($todayCount > 0) {
            return;
        }

        $resourceId = DB::table('resources')
            ->where('restaurant_id', $this->restaurantId)
            ->where('active', true)
            ->value('id');

        if (! $resourceId) {
            return;
        }

        $menuItemId = DB::table('menu_items')
            ->where('restaurant_id', $this->restaurantId)
            ->value('id');

        $now = Carbon::now('UTC');

        $guests = [
            ['Erik Lindqvist',  'erik@demo.se',   2, 'CHECKED_IN', -4, true],
            ['Sara Bergström',  'sara@demo.se',   4, 'CHECKED_IN', -3, false],
            ['Maja Svensson',   'maja@demo.se',   2, 'CONFIRMED',  -2, true],
            ['Johan Karlsson',  'johan@demo.se',  3, 'CONFIRMED',  -1, false],
            ['Anna Nilsson',    'anna@demo.se',   2, 'CONFIRMED',   0, false],
            ['Patrik Holm',     'patrik@demo.se', 2, 'NO_SHOW',    -5, false],
            ['Lena Johansson',  'lena@demo.se',   4, 'CONFIRMED',   1, true],
            ['Marcus Eriksson', 'marcus@demo.se', 2, 'CONFIRMED',   2, false],
        ];

        foreach ($guests as $i => [$name, $email, $party, $status, $hoursOffset, $hasPreorder]) {
            $createdAt = $now->copy()->addHours($hoursOffset < 0 ? $hoursOffset : 0);
            $updatedAt = in_array($status, ['CHECKED_IN', 'NO_SHOW']) ? $now->copy() : $createdAt;

            $bookingId = DB::table('guest_bookings')->insertGetId([
                'restaurant_id'     => $this->restaurantId,
                'public_id'         => Str::uuid(),
                'status'            => $status,
                'customer_name'     => $name,
                'email'             => $email,
                'party_size'        => $party,
                'cancel_token_hash' => 'demo-' . $i,
                'created_at'        => $createdAt,
                'updated_at'        => $updatedAt,
            ]);

            DB::table('booking_items')->insert([
                'guest_booking_id' => $bookingId,
                'resource_id'      => $resourceId,
                'start_time'       => $now->copy()->addHours($i),
                'end_time'         => $now->copy()->addHours($i + 1),
                'price_minor'      => 25000,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            if ($hasPreorder && $menuItemId) {
                $preorderId = DB::table('preorders')->insertGetId([
                    'guest_booking_id' => $bookingId,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);

                DB::table('preorder_items')->insert([
                    'preorder_id'  => $preorderId,
                    'menu_item_id' => $menuItemId,
                    'qty'          => $party,
                    'price_each'   => 149.00,
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ]);
            }
        }
    }
}
