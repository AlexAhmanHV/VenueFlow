<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->is_super_admin || $user->membershipFor($restaurant) !== null;
    }

    public function manage(User $user, Restaurant $restaurant): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->membershipFor($restaurant)?->role === MembershipRole::RESTAURANT_ADMIN;
    }
}
