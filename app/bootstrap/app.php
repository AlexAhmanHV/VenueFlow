<?php

use App\Http\Middleware\EnsureRestaurantMembership;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTenantRouteBindings;
use App\Http\Middleware\ResolveRestaurantFromSlug;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'super_admin' => EnsureSuperAdmin::class,
            'resolve_restaurant' => ResolveRestaurantFromSlug::class,
            'restaurant_member' => EnsureRestaurantMembership::class,
            'tenant_bindings' => EnsureTenantRouteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
