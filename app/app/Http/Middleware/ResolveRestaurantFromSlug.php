<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\RestaurantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveRestaurantFromSlug
{
    public function __construct(private readonly RestaurantResolver $resolver)
    {
    }

    public function handle(Request $request, Closure $next, string $mode = 'active'): Response
    {
        $slug = (string) $request->route('slug');

        $restaurant = $mode === 'any'
            ? $this->resolver->resolveBySlug($slug)
            : $this->resolver->resolveActiveBySlug($slug);

        $request->attributes->set('restaurant', $restaurant);

        return $next($request);
    }
}
