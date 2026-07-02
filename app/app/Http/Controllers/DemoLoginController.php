<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemoLoginController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = User::where('email', 'owner@demo.test')->firstOrFail();

        Auth::login($user);

        $this->seedTodayIfNeeded();

        return redirect('/r/golfbaren/admin/dashboard');
    }

    private function seedTodayIfNeeded(): void
    {
        $restaurant = DB::table('restaurants')->where('slug', 'golfbaren')->first();
        if (! $restaurant) {
            return;
        }

        $dayStart = Carbon::today('Europe/Stockholm')->utc();
        $dayEnd = Carbon::today('Europe/Stockholm')->endOfDay()->utc();

        $todayCount = DB::table('guest_bookings')
            ->where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->count();

        if ($todayCount > 0) {
            return;
        }

        $resourceId = DB::table('resources')
            ->where('restaurant_id', $restaurant->id)
            ->where('active', true)
            ->value('id');

        if (! $resourceId) {
            return;
        }

        $guests = [
            ['Erik Lindqvist',  'erik@demo.se',   2, 'CHECKED_IN', -4],
            ['Sara Bergström',  'sara@demo.se',   4, 'CHECKED_IN', -3],
            ['Maja Svensson',   'maja@demo.se',   2, 'CONFIRMED',  -2],
            ['Johan Karlsson',  'johan@demo.se',  3, 'CONFIRMED',  -1],
            ['Anna Nilsson',    'anna@demo.se',   2, 'CONFIRMED',   0],
            ['Patrik Holm',     'patrik@demo.se', 2, 'NO_SHOW',    -5],
            ['Lena Johansson',  'lena@demo.se',   4, 'CONFIRMED',   1],
            ['Marcus Eriksson', 'marcus@demo.se', 2, 'CONFIRMED',   2],
        ];

        $now = Carbon::now('UTC');

        foreach ($guests as $i => [$name, $email, $party, $status, $hoursOffset]) {
            $createdAt = $now->copy()->addHours($hoursOffset < 0 ? $hoursOffset : 0);
            $updatedAt = $status === 'CONFIRMED' ? $createdAt : $now->copy()->addHours($hoursOffset);

            $bookingId = DB::table('guest_bookings')->insertGetId([
                'restaurant_id'     => $restaurant->id,
                'public_id'         => \Illuminate\Support\Str::uuid(),
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
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
