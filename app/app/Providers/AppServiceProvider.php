<?php

namespace App\Providers;

use App\Models\GuestBooking;
use App\Models\MenuItem;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Policies\BookingPolicy;
use App\Policies\MenuPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\RestaurantPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('public-booking', function (Request $request) {
            return [
                Limit::perMinute(30)->by($request->ip()),
                Limit::perMinute(15)->by('session:'.$request->session()->getId()),
            ];
        });

        Gate::before(fn ($user) => $user->is_super_admin ? true : null);

        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(Resource::class, ResourcePolicy::class);
        Gate::policy(GuestBooking::class, BookingPolicy::class);
        Gate::policy(MenuItem::class, MenuPolicy::class);

        Blade::component('layouts.restaurant-admin', 'restaurant-admin-layout');
        Blade::component('layouts.public', 'public-layout');
    }
}
