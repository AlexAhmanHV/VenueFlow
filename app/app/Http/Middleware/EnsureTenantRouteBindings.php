<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRouteBindings
{
    /**
     * Ensures route-model-bound records cannot cross restaurant tenant boundaries.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = $request->attributes->get('restaurant');
        if (! $restaurant) {
            return $next($request);
        }

        foreach (($request->route()?->parameters() ?? []) as $parameter) {
            if (! $parameter instanceof Model) {
                continue;
            }

            if (! $this->hasRestaurantIdColumn($parameter)) {
                continue;
            }

            if ((int) $parameter->getAttribute('restaurant_id') !== (int) $restaurant->id) {
                abort(404);
            }
        }

        return $next($request);
    }

    private function hasRestaurantIdColumn(Model $model): bool
    {
        static $cache = [];

        $table = $model->getTable();

        if (! array_key_exists($table, $cache)) {
            $cache[$table] = Schema::hasColumn($table, 'restaurant_id');
        }

        return $cache[$table];
    }
}
