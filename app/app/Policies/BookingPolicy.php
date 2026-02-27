<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\GuestBooking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_super_admin || $user->memberships()->exists();
    }

    public function view(User $user, GuestBooking $booking): bool
    {
        return $user->can('view', $booking->restaurant);
    }

    public function updateStatus(User $user, GuestBooking $booking): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        $membership = $user->membershipFor($booking->restaurant);

        return $membership !== null && in_array($membership->role, [MembershipRole::RESTAURANT_ADMIN, MembershipRole::STAFF], true);
    }
}
