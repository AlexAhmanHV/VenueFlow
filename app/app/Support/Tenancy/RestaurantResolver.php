<?php

namespace App\Support\Tenancy;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestaurantResolver
{
    public function resolveActiveBySlug(string $slug): Restaurant
    {
        $restaurant = Restaurant::query()
            ->where('slug', $slug)
            ->with('setting')
            ->first();

        if (! $restaurant || ! $restaurant->active) {
            throw (new ModelNotFoundException())->setModel(Restaurant::class, [$slug]);
        }

        return $restaurant;
    }

    public function resolveBySlug(string $slug): Restaurant
    {
        return Restaurant::query()
            ->where('slug', $slug)
            ->with('setting')
            ->firstOrFail();
    }
}
