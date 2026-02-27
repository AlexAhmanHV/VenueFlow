<?php

namespace App\Http\Middleware;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantMembership
{
    public function handle(Request $request, Closure $next, string $requiredRole = 'STAFF'): Response
    {
        $restaurant = $request->attributes->get('restaurant');
        $user = $request->user();

        if (! $restaurant || ! $user) {
            abort(403);
        }

        $membership = $user->membershipFor($restaurant);
        if (! $membership) {
            abort(403);
        }

        if ($requiredRole === MembershipRole::RESTAURANT_ADMIN->value) {
            if ($membership->role !== MembershipRole::RESTAURANT_ADMIN) {
                abort(403);
            }
        } elseif ($requiredRole === StaffRole::MANAGER->value) {
            $isManager = $membership->role === MembershipRole::RESTAURANT_ADMIN
                || ($membership->role === MembershipRole::STAFF && $membership->staff_role === StaffRole::MANAGER);

            if (! $isManager) {
                abort(403);
            }
        }

        return $next($request);
    }
}
